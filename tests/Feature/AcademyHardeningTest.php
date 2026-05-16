<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\Order;
use App\Models\User;
use App\Support\VideoEmbedParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AcademyHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_only_access_is_enforced(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($student)
            ->get('/admin/courses/create')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/courses/create')
            ->assertOk();
    }

    public function test_student_cannot_access_a_course_without_enrollment(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse();
        $section = $this->createSection($course);
        $this->createLesson($section);

        $this->actingAs($student)
            ->get("/dashboard/learn/{$course->slug}")
            ->assertForbidden();
    }

    public function test_student_cannot_download_a_protected_attachment_without_enrollment(): void
    {
        Storage::fake('local');

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse();
        $section = $this->createSection($course);
        $lesson = $this->createLesson($section);

        $path = 'lesson-attachments/'.$lesson->id.'/notes.pdf';
        Storage::disk('local')->put($path, 'secret lesson notes');

        $attachment = LessonAttachment::create([
            'lesson_id' => $lesson->id,
            'file_path' => $path,
            'file_name' => 'notes.pdf',
            'file_size' => Storage::disk('local')->size($path),
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($student)
            ->get("/dashboard/attachments/{$attachment->id}/download")
            ->assertForbidden();
    }

    public function test_free_course_enrollment_is_completed_immediately(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse([
            'price' => 0,
        ]);

        $this->actingAs($student)
            ->post("/courses/{$course->slug}/checkout")
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_paid_course_stays_locked_until_payment_is_completed(): void
    {
        config([
            'services.stripe.secret' => null,
            'services.payments.test_mode' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse([
            'price' => 149.99,
        ]);
        $section = $this->createSection($course);
        $this->createLesson($section);

        $response = $this->actingAs($student)
            ->post("/courses/{$course->slug}/checkout");

        $order = Order::query()->latest()->first();

        $response->assertRedirect("/checkout/{$order->id}/test");

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($student)
            ->get("/dashboard/learn/{$course->slug}")
            ->assertForbidden();
    }

    public function test_checkout_test_routes_are_forbidden_in_production_even_if_test_mode_is_enabled(): void
    {
        config([
            'app.env' => 'production',
            'services.payments.test_mode' => true,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse([
            'price' => 149.99,
        ]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'order_number' => 'KA-PROD-'.Str::upper(Str::random(6)),
            'amount' => $course->price,
            'currency' => $course->currency,
            'status' => 'pending',
            'payment_method' => 'stripe',
        ]);

        $this->actingAs($student)
            ->get(route('checkout.test', $order))
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('checkout.test.complete', $order))
            ->assertForbidden();
    }

    public function test_production_success_url_does_not_complete_paid_order_when_webhook_secret_is_missing(): void
    {
        app()->setLocale('en');

        config([
            'app.env' => 'production',
            'services.stripe.secret' => 'sk_test_production',
            'services.stripe.webhook_secret' => null,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse([
            'price' => 149.99,
            'currency' => 'USD',
        ]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'order_number' => 'KA-PROD-SUCCESS-'.Str::upper(Str::random(6)),
            'amount' => $course->price,
            'currency' => $course->currency,
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

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/*' => Http::response([
                'id' => 'cs_prod_pending',
                'client_reference_id' => (string) $order->id,
                'payment_status' => 'paid',
                'amount_total' => 14999,
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'course_id' => (string) $course->id,
                ],
            ], 200),
        ]);

        $this->actingAs($student)
            ->get(route('checkout.success', $order).'?session_id=cs_prod_pending')
            ->assertRedirect(route('student.orders'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'success URL') && str_contains($message, 'webhook'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_local_success_url_can_complete_paid_order_only_when_session_is_valid_and_webhook_secret_is_missing(): void
    {
        app()->setLocale('en');

        config([
            'app.env' => 'local',
            'services.stripe.secret' => 'sk_test_local',
            'services.stripe.webhook_secret' => null,
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse([
            'price' => 149.99,
            'currency' => 'USD',
        ]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'order_number' => 'KA-LOCAL-SUCCESS-'.Str::upper(Str::random(6)),
            'amount' => $course->price,
            'currency' => $course->currency,
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

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/*' => Http::response([
                'id' => 'cs_local_complete',
                'client_reference_id' => (string) $order->id,
                'payment_status' => 'paid',
                'amount_total' => 14999,
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'course_id' => (string) $course->id,
                ],
            ], 200),
        ]);

        $this->actingAs($student)
            ->get(route('checkout.success', $order).'?session_id=cs_local_complete')
            ->assertRedirect(route('student.learn.course', $course));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'transaction_id' => 'cs_local_complete',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_stripe_webhook_completes_the_order_and_creates_enrollment(): void
    {
        config([
            'services.stripe.webhook_secret' => 'whsec_test',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse([
            'price' => 79.00,
            'currency' => 'USD',
        ]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'order_number' => 'KA-WEBHOOK-'.Str::upper(Str::random(6)),
            'amount' => $course->price,
            'currency' => $course->currency,
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

        $sessionId = 'cs_test_123';
        $event = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $sessionId,
                    'client_reference_id' => (string) $order->id,
                    'payment_status' => 'paid',
                    'amount_total' => 7900,
                    'currency' => 'usd',
                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'course_id' => (string) $course->id,
                    ],
                ],
            ],
        ];

        $payload = json_encode($event, JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');

        $this->call(
            'POST',
            '/stripe/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
            ],
            $payload
        )->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'transaction_id' => $sessionId,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_unsigned_stripe_webhook_is_rejected_in_production_when_secret_is_missing(): void
    {
        config([
            'app.env' => 'production',
            'services.stripe.webhook_secret' => null,
        ]);

        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_unsigned',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/stripe/webhook',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        )->assertStatus(503);
    }

    public function test_lesson_completion_updates_progress_percentage(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = $this->createCourse([
            'price' => 0,
        ]);
        $section = $this->createSection($course);
        $firstLesson = $this->createLesson($section, ['order' => 1]);
        $secondLesson = $this->createLesson($section, ['order' => 2, 'title' => 'Second lesson']);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);

        $this->actingAs($student)
            ->post("/dashboard/lessons/{$firstLesson->id}/complete")
            ->assertRedirect();

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id,
            'lesson_id' => $firstLesson->id,
            'is_completed' => true,
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'progress_percentage' => 50,
        ]);

        $this->actingAs($student)
            ->post("/dashboard/lessons/{$secondLesson->id}/complete")
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'progress_percentage' => 100,
        ]);
    }

    private function createCourse(array $attributes = []): Course
    {
        $owner = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        return Course::create(array_merge([
            'user_id' => $owner->id,
            'title' => 'Secure Academy '.Str::random(6),
            'subtitle' => 'Professional content',
            'description' => '<p>Safe content for the course page.</p>',
            'price' => 49.99,
            'currency' => 'USD',
            'level' => 'beginner',
            'duration_hours' => 12,
            'slug' => 'secure-course-'.Str::lower(Str::random(10)),
            'meta_description' => 'A secure sample course.',
            'course_format' => 'mixed',
            'status' => 'published',
            'published_at' => now(),
        ], $attributes));
    }

    private function createSection(Course $course, array $attributes = []): CourseSection
    {
        return CourseSection::create(array_merge([
            'course_id' => $course->id,
            'title' => 'Section '.Str::random(4),
            'description' => 'Section description',
            'order' => 1,
        ], $attributes));
    }

    private function createLesson(CourseSection $section, array $attributes = []): Lesson
    {
        $type = $attributes['type'] ?? 'mixed';
        $video = in_array($type, ['video', 'mixed'], true)
            ? VideoEmbedParser::normalize('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            : ['video_url' => null, 'video_provider' => null, 'video_id' => null];

        return Lesson::create(array_merge([
            'course_section_id' => $section->id,
            'title' => 'Lesson '.Str::random(4),
            'description' => '<p>Short lesson description.</p>',
            'type' => $type,
            'content' => '<p>Lesson body.</p>',
            'duration_minutes' => 20,
            'order' => 1,
            'status' => 'published',
        ], $video, $attributes));
    }
}
