<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function start(Request $request, Course $course): RedirectResponse|Response
    {
        abort_unless($course->isPublished(), 404);

        if ($request->user()->enrollments()->where('course_id', $course->id)->exists()) {
            return redirect()->route('student.learn.course', $course);
        }

        if ((float) $course->price <= 0) {
            $this->enroll($request->user(), $course);

            return redirect()
                ->route('student.learn.course', $course)
                ->with('success', __('app.checkout.success'));
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'order_number' => 'KA-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
            'amount' => $course->price,
            'currency' => strtoupper($course->currency ?: 'USD'),
            'status' => 'pending',
            'payment_method' => 'stripe',
        ]);

        $order->payments()->create([
            'transaction_id' => 'pending_'.$order->order_number,
            'provider' => 'stripe',
            'amount' => $order->amount,
            'currency' => $order->currency,
            'status' => 'pending',
        ]);

        if (config('services.stripe.secret')) {
            return $this->redirectToStripe($order, $course);
        }

        if ((bool) config('services.payments.test_mode')) {
            return redirect()->route('checkout.test', $order);
        }

        return redirect()->route('courses.show', $course)->with('error', __('app.checkout.unavailable'));
    }

    public function test(Order $order)
    {
        $this->authorizeOrder($order);
        abort_if(app()->environment('production') && ! config('services.payments.test_mode'), 403);

        $order->load('course');

        return Inertia::render('Checkout/Test', ['order' => $order]);
    }

    public function completeTest(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);
        abort_if(app()->environment('production') && ! config('services.payments.test_mode'), 403);

        $this->completeOrder($order, 'test_'.$order->order_number, ['mode' => 'local_test'], 'test');

        return redirect()->route('student.learn.course', $order->course)->with('success', __('app.checkout.success'));
    }

    public function success(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);
        $order->load('course');

        if ($order->status === 'completed') {
            return redirect()->route('student.learn.course', $order->course)->with('success', __('app.checkout.success'));
        }

        $sessionId = trim((string) $request->query('session_id'));

        if ($sessionId === '' || ! config('services.stripe.secret')) {
            return redirect()->route('student.orders')->with('error', $this->paymentPendingMessage());
        }

        $session = $this->fetchStripeSession($sessionId);

        if (! $session || ! $this->stripeSessionMatchesOrder($order, $session) || ! $this->stripeSessionIsPaid($session)) {
            return redirect()->route('student.orders')->with('error', $this->paymentPendingMessage());
        }

        if (! config('services.stripe.webhook_secret')) {
            $this->completeOrder($order, $sessionId, $session, 'success');

            return redirect()->route('student.learn.course', $order->course)->with('success', __('app.checkout.success'));
        }

        return redirect()->route('student.orders')->with('success', $this->paymentAwaitingWebhookMessage());
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        return redirect()->route('courses.show', $order->course)->with('error', __('app.checkout.cancelled'));
    }

    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (config('services.stripe.webhook_secret') && ! $this->validStripeSignature($payload, $signature)) {
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (! is_array($event)) {
            return response('Invalid payload', 400);
        }

        if (in_array($event['type'] ?? null, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $session = $event['data']['object'] ?? [];
            $orderId = data_get($session, 'metadata.order_id');
            $order = $orderId ? Order::with(['user', 'course', 'payments'])->find($orderId) : null;

            if (! $order) {
                Log::warning('Stripe webhook received for missing order.', ['order_id' => $orderId, 'event_type' => $event['type'] ?? null]);

                return response('OK');
            }

            if (! $this->stripeSessionMatchesOrder($order, $session) || ! $this->stripeSessionIsPaid($session)) {
                Log::warning('Stripe webhook session failed verification.', [
                    'order_id' => $order->id,
                    'session_id' => $session['id'] ?? null,
                    'event_type' => $event['type'] ?? null,
                ]);

                return response('OK');
            }

            $this->completeOrder($order, (string) ($session['id'] ?? 'stripe_'.$order->order_number), $session, 'webhook');
        }

        return response('OK');
    }

    private function redirectToStripe(Order $order, Course $course): Response
    {
        $order->loadMissing('user');

        $response = Http::asForm()
            ->withToken(config('services.stripe.secret'))
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => route('checkout.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancel', $order),
                'client_reference_id' => (string) $order->id,
                'customer_email' => $order->user->email,
                'metadata[order_id]' => (string) $order->id,
                'metadata[course_id]' => (string) $course->id,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => strtolower($order->currency),
                'line_items[0][price_data][unit_amount]' => $this->stripeAmountForOrder($order),
                'line_items[0][price_data][product_data][name]' => $course->title,
            ]);

        if (! $response->successful() || ! $response->json('url')) {
            return redirect()->route('courses.show', $course)->with('error', app()->getLocale() === 'ar' ? 'تعذر إنشاء جلسة الدفع.' : 'Could not create checkout session.');
        }

        $sessionId = $response->json('id');

        if ($sessionId) {
            $order->payments()->latest()->first()?->update([
                'transaction_id' => $sessionId,
                'response_data' => ['checkout_session_created' => true],
            ]);
        }

        return Inertia::location($response->json('url'));
    }

    private function fetchStripeSession(string $sessionId): ?array
    {
        $response = Http::withToken(config('services.stripe.secret'))
            ->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId);

        $data = $response->json();

        return $response->successful() && is_array($data) ? $data : null;
    }

    private function stripeSessionMatchesOrder(Order $order, array $session): bool
    {
        $metadataOrderId = (string) data_get($session, 'metadata.order_id', '');
        $clientReferenceId = (string) ($session['client_reference_id'] ?? '');
        $metadataCourseId = (string) data_get($session, 'metadata.course_id', '');
        $currency = strtoupper((string) ($session['currency'] ?? ''));
        $amountTotal = $session['amount_total'] ?? null;

        return $metadataOrderId === (string) $order->id
            && $clientReferenceId === (string) $order->id
            && ($metadataCourseId === '' || $metadataCourseId === (string) $order->course_id)
            && is_numeric($amountTotal)
            && (int) $amountTotal === $this->stripeAmountForOrder($order)
            && $currency === strtoupper((string) $order->currency);
    }

    private function stripeSessionIsPaid(array $session): bool
    {
        return ($session['payment_status'] ?? null) === 'paid';
    }

    private function completeOrder(Order $order, string $transactionId, array $responseData = [], string $source = 'stripe'): void
    {
        DB::transaction(function () use ($order, $transactionId, $responseData, $source): void {
            $order = Order::with(['user', 'course', 'payments'])->lockForUpdate()->findOrFail($order->id);

            $payment = $order->payments()
                ->where('transaction_id', $transactionId)
                ->latest()
                ->first();

            if (! $payment) {
                $payment = $order->payments()
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
            }

            $paymentData = [
                'transaction_id' => $transactionId,
                'provider' => 'stripe',
                'amount' => $order->amount,
                'currency' => strtoupper($order->currency),
                'status' => 'completed',
                'response_data' => array_merge($responseData, ['reconciled_via' => $source]),
                'processed_at' => now(),
            ];

            if ($payment) {
                $payment->update($paymentData);
            } else {
                $order->payments()->create($paymentData);
            }

            if ($order->status !== 'completed') {
                $order->update(['status' => 'completed']);
            }

            $this->enroll($order->user, $order->course);
        });
    }

    private function enroll($user, Course $course): void
    {
        $user->enrollments()->firstOrCreate(
            ['course_id' => $course->id],
            ['enrolled_at' => now(), 'progress_percentage' => 0]
        );
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless(auth()->check() && (auth()->id() === $order->user_id || auth()->user()->isAdmin()), 403);
    }

    private function validStripeSignature(string $payload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $parts = [];

        foreach (explode(',', $signature) as $piece) {
            [$key, $value] = array_pad(explode('=', $piece, 2), 2, null);
            $parts[$key] = $value;
        }

        if (empty($parts['t']) || empty($parts['v1'])) {
            return false;
        }

        if (abs(time() - (int) $parts['t']) > 300) {
            return false;
        }

        $signedPayload = $parts['t'].'.'.$payload;
        $expected = hash_hmac('sha256', $signedPayload, config('services.stripe.webhook_secret'));

        return hash_equals($expected, $parts['v1']);
    }

    private function stripeAmountForOrder(Order $order): int
    {
        return (int) round(((float) $order->amount) * 100);
    }

    private function paymentPendingMessage(): string
    {
        return app()->getLocale() === 'ar'
            ? 'لم نستطع تأكيد الدفع بعد. سيبقى الطلب معلّقاً حتى يصل تأكيد Stripe الصحيح.'
            : 'Payment could not be confirmed yet. The order will stay pending until Stripe confirms it.';
    }

    private function paymentAwaitingWebhookMessage(): string
    {
        return app()->getLocale() === 'ar'
            ? 'تم استلام الدفع. سيتم فتح الكورس تلقائياً بمجرد وصول تأكيد Stripe webhook.'
            : 'Payment received. The course will unlock automatically as soon as the Stripe webhook confirms it.';
    }
}
