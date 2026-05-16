<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\PageView;
use App\Models\UtmCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class AnalyticsTracker
{
    /**
     * @var array<int, string>
     */
    private const TRACKED_PAGE_ROUTES = [
        'home',
        'about',
        'contact',
        'privacy',
        'terms',
        'courses.index',
        'courses.show',
        'student.dashboard',
        'student.my-courses',
        'student.orders',
        'student.learn.course',
        'student.learn.lesson',
        'login',
        'register',
        'password.request',
        'password.reset',
        'verification.notice',
        'profile.edit',
        'student.profile',
    ];

    public function rememberAttribution(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $incoming = array_filter([
            'utm_source' => $this->cleanNullable($request->query('utm_source')),
            'utm_medium' => $this->cleanNullable($request->query('utm_medium')),
            'utm_campaign' => $this->cleanNullable($request->query('utm_campaign')),
        ]);

        if ($incoming === []) {
            return;
        }

        $request->session()->put('analytics.utm', array_merge(
            $request->session()->get('analytics.utm', []),
            $incoming,
        ));
    }

    public function shouldTrackPage(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if (! $request->isMethod('GET') || ! $routeName) {
            return false;
        }

        if (! in_array($routeName, self::TRACKED_PAGE_ROUTES, true)) {
            return false;
        }

        if ($request->user()?->isAdmin()) {
            return false;
        }

        return ! $this->isBot($request->userAgent());
    }

    public function capturePageView(Request $request): void
    {
        if (! $this->shouldTrackPage($request)) {
            return;
        }

        $routeName = (string) $request->route()?->getName();
        $course = $this->resolveCourse($request);
        $lesson = $this->resolveLesson($request);

        $eventType = match ($routeName) {
            'courses.show', 'student.learn.course' => PageView::EVENT_COURSE_VIEW,
            'student.learn.lesson' => PageView::EVENT_LESSON_VIEW,
            default => PageView::EVENT_PAGE_VIEW,
        };

        $this->store(array_merge(
            $this->contextFromRequest($request),
            [
                'course_id' => $course?->id,
                'lesson_id' => $lesson?->id,
                'event_type' => $eventType,
                'route_name' => $routeName,
                'path' => $this->requestPath($request),
            ],
        ));
    }

    public function captureCheckoutStart(Request $request, Order $order): void
    {
        if ($request->user()?->isAdmin() || $this->isBot($request->userAgent())) {
            return;
        }

        $this->store(array_merge(
            $this->contextFromRequest($request),
            [
                'course_id' => $order->course_id,
                'order_id' => $order->id,
                'event_type' => PageView::EVENT_CHECKOUT_START,
                'route_name' => 'checkout.start',
                'path' => $this->requestPath($request),
            ],
        ));
    }

    public function capturePurchaseCompleted(Order $order, string $source): void
    {
        if (PageView::query()
            ->where('order_id', $order->id)
            ->where('event_type', PageView::EVENT_PURCHASE_COMPLETED)
            ->exists()) {
            return;
        }

        $checkoutStart = PageView::query()
            ->where('order_id', $order->id)
            ->where('event_type', PageView::EVENT_CHECKOUT_START)
            ->latest('id')
            ->first();

        $this->store([
            'user_id' => $order->user_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'event_type' => PageView::EVENT_PURCHASE_COMPLETED,
            'route_name' => $source === 'webhook' ? 'stripe.webhook' : 'checkout.success',
            'path' => $source === 'webhook' ? '/stripe/webhook' : '/checkout/'.$order->id.'/success',
            'referrer' => $checkoutStart?->referrer,
            'utm_source' => $checkoutStart?->utm_source,
            'utm_medium' => $checkoutStart?->utm_medium,
            'utm_campaign' => $checkoutStart?->utm_campaign,
            'device_type' => $checkoutStart?->device_type,
            'visitor_hash' => $checkoutStart?->visitor_hash,
        ]);
    }

    private function contextFromRequest(Request $request): array
    {
        $sessionUtm = $request->hasSession() ? $request->session()->get('analytics.utm', []) : [];

        return [
            'user_id' => $request->user()?->id,
            'referrer' => $this->cleanNullable($request->headers->get('referer')),
            'utm_source' => $this->cleanNullable($request->query('utm_source') ?? Arr::get($sessionUtm, 'utm_source')),
            'utm_medium' => $this->cleanNullable($request->query('utm_medium') ?? Arr::get($sessionUtm, 'utm_medium')),
            'utm_campaign' => $this->cleanNullable($request->query('utm_campaign') ?? Arr::get($sessionUtm, 'utm_campaign')),
            'device_type' => $this->deviceType($request->userAgent()),
            'visitor_hash' => $this->visitorHash($request),
        ];
    }

    private function store(array $attributes): void
    {
        $attributes['created_at'] = $attributes['created_at'] ?? now();

        PageView::create($attributes);
        $this->syncCampaign($attributes);
    }

    private function syncCampaign(array $attributes): void
    {
        $campaign = $attributes['utm_campaign'] ?? null;

        if (! $campaign) {
            return;
        }

        $timestamp = $attributes['created_at'] instanceof Carbon
            ? $attributes['created_at']
            : Carbon::parse((string) $attributes['created_at']);

        $record = UtmCampaign::query()->firstOrNew(['name' => $campaign]);

        $record->fill([
            'source' => $attributes['utm_source'] ?? $record->source,
            'medium' => $attributes['utm_medium'] ?? $record->medium,
            'first_seen_at' => $record->first_seen_at ?? $timestamp,
            'last_seen_at' => $timestamp,
        ]);

        $record->save();
    }

    private function resolveCourse(Request $request): ?Course
    {
        $course = $request->route()?->parameter('course');

        return $course instanceof Course ? $course : null;
    }

    private function resolveLesson(Request $request): ?Lesson
    {
        $lesson = $request->route()?->parameter('lesson');

        return $lesson instanceof Lesson ? $lesson : null;
    }

    private function requestPath(Request $request): ?string
    {
        $path = $request->path();
        $query = $request->getQueryString();

        return $query ? $path.'?'.$query : $path;
    }

    private function cleanNullable(?string $value): ?string
    {
        $clean = trim((string) $value);

        return $clean !== '' ? mb_substr($clean, 0, 2048) : null;
    }

    private function deviceType(?string $userAgent): string
    {
        $agent = strtolower((string) $userAgent);

        if ($agent !== '' && preg_match('/ipad|tablet|kindle|playbook|silk/', $agent)) {
            return 'tablet';
        }

        if ($agent !== '' && preg_match('/mobile|iphone|ipod|android|blackberry|phone/', $agent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function visitorHash(Request $request): string
    {
        $payload = implode('|', [
            now()->toDateString(),
            $request->user()?->id ?? 'guest',
            $request->ip() ?? 'unknown',
            $request->userAgent() ?? 'unknown',
        ]);

        return hash_hmac('sha256', $payload, config('app.key') ?: 'analytics');
    }

    private function isBot(?string $userAgent): bool
    {
        $agent = strtolower((string) $userAgent);

        return $agent !== '' && preg_match('/bot|crawl|spider|slurp|curl|wget|preview|headless|facebookexternalhit/', $agent) === 1;
    }
}
