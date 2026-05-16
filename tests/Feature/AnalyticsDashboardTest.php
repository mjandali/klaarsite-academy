<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\PageView;
use App\Models\UtmCampaign;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
{
    public function test_public_and_student_activity_is_tracked_with_attribution(): void
    {
        config([
            'app.env' => 'testing',
            'services.payments.test_mode' => true,
            'services.stripe.secret' => null,
            'services.stripe.webhook_secret' => null,
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $course = Course::factory()
            ->for($admin)
            ->published()
            ->create([
                'title' => 'Analytics Course',
                'course_format' => 'mixed',
            ]);

        $section = CourseSection::factory()
            ->for($course)
            ->create(['order' => 1]);

        $lesson = Lesson::factory()
            ->for($section, 'section')
            ->published()
            ->create([
                'order' => 1,
                'type' => 'mixed',
            ]);

        $paidCourse = Course::factory()
            ->for($admin)
            ->published()
            ->create([
                'title' => 'Paid Analytics Course',
                'price' => 199.00,
                'currency' => 'USD',
            ]);

        $this->withHeader('Referer', 'https://google.com/search?q=academy')
            ->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)')
            ->get(route('courses.show', $course).'?utm_source=google&utm_medium=cpc&utm_campaign=spring-launch')
            ->assertOk();

        $student->enrollments()->create([
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);

        $this->actingAs($student)
            ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
            ->get(route('student.learn.course', $course))
            ->assertOk();

        $this->actingAs($student)
            ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
            ->get(route('student.learn.lesson', [$course, $lesson]))
            ->assertOk();

        $checkoutResponse = $this->actingAs($student)
            ->withHeader('Referer', route('courses.show', $paidCourse))
            ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
            ->post(route('checkout.start', $paidCourse));

        $order = Order::query()->latest('id')->firstOrFail();

        $checkoutResponse->assertRedirect(route('checkout.test', $order));

        $this->actingAs($student)
            ->post(route('checkout.test.complete', $order))
            ->assertRedirect(route('student.learn.course', $paidCourse));

        $this->assertDatabaseHas('page_views', [
            'event_type' => PageView::EVENT_COURSE_VIEW,
            'route_name' => 'courses.show',
            'course_id' => $course->id,
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'spring-launch',
            'device_type' => 'mobile',
        ]);

        $this->assertDatabaseHas('page_views', [
            'event_type' => PageView::EVENT_COURSE_VIEW,
            'route_name' => 'student.learn.course',
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);

        $this->assertDatabaseHas('page_views', [
            'event_type' => PageView::EVENT_LESSON_VIEW,
            'route_name' => 'student.learn.lesson',
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
        ]);

        $this->assertDatabaseHas('page_views', [
            'event_type' => PageView::EVENT_CHECKOUT_START,
            'route_name' => 'checkout.start',
            'order_id' => $order->id,
            'course_id' => $paidCourse->id,
            'user_id' => $student->id,
        ]);

        $this->assertDatabaseHas('page_views', [
            'event_type' => PageView::EVENT_PURCHASE_COMPLETED,
            'route_name' => 'checkout.success',
            'order_id' => $order->id,
            'course_id' => $paidCourse->id,
            'user_id' => $student->id,
        ]);

        $this->assertDatabaseHas('utm_campaigns', [
            'name' => 'spring-launch',
            'source' => 'google',
            'medium' => 'cpc',
        ]);

        $this->assertNotNull(PageView::query()->where('event_type', PageView::EVENT_COURSE_VIEW)->where('route_name', 'courses.show')->value('visitor_hash'));
        $this->assertTrue(UtmCampaign::query()->where('name', 'spring-launch')->exists());
    }

    public function test_admin_visits_and_bots_are_not_tracked(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $course = Course::factory()
            ->for($admin)
            ->published()
            ->create();

        $this->actingAs($admin)
            ->withHeader('User-Agent', 'Mozilla/5.0')
            ->get(route('courses.show', $course))
            ->assertOk();

        $this->withHeader('User-Agent', 'Googlebot/2.1 (+http://www.google.com/bot.html)')
            ->get(route('courses.show', $course))
            ->assertOk();

        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_admin_dashboard_displays_analytics_summary(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $course = Course::factory()
            ->for($admin)
            ->published()
            ->create([
                'title' => 'Top Course',
            ]);

        $lesson = Lesson::factory()
            ->for(CourseSection::factory()->for($course)->create(['order' => 1]), 'section')
            ->published()
            ->create(['order' => 1]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'order_number' => 'KA-AN-'.Str::upper(Str::random(8)),
            'amount' => 120,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'stripe',
        ]);

        PageView::create([
            'course_id' => $course->id,
            'event_type' => PageView::EVENT_COURSE_VIEW,
            'route_name' => 'courses.show',
            'path' => 'courses/'.$course->slug,
            'referrer' => 'https://google.com',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'launch',
            'device_type' => 'desktop',
            'visitor_hash' => 'visitor-1',
            'created_at' => now(),
        ]);

        PageView::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'event_type' => PageView::EVENT_LESSON_VIEW,
            'route_name' => 'student.learn.lesson',
            'path' => 'dashboard/learn/'.$course->slug.'/lessons/'.$lesson->id,
            'referrer' => 'https://google.com',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'launch',
            'device_type' => 'desktop',
            'visitor_hash' => 'visitor-1',
            'created_at' => now(),
        ]);

        PageView::create([
            'course_id' => $course->id,
            'order_id' => $order->id,
            'event_type' => PageView::EVENT_CHECKOUT_START,
            'route_name' => 'checkout.start',
            'path' => 'courses/'.$course->slug.'/checkout',
            'referrer' => 'https://google.com',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'launch',
            'device_type' => 'desktop',
            'visitor_hash' => 'visitor-1',
            'created_at' => now(),
        ]);

        PageView::create([
            'course_id' => $course->id,
            'order_id' => $order->id,
            'event_type' => PageView::EVENT_PURCHASE_COMPLETED,
            'route_name' => 'stripe.webhook',
            'path' => '/stripe/webhook',
            'referrer' => 'https://google.com',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'launch',
            'device_type' => 'desktop',
            'visitor_hash' => 'visitor-1',
            'created_at' => now(),
        ]);

        PageView::create([
            'event_type' => PageView::EVENT_PAGE_VIEW,
            'route_name' => 'about',
            'path' => 'about',
            'referrer' => 'https://bing.com',
            'device_type' => 'desktop',
            'visitor_hash' => 'visitor-2',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('analytics.periods.0.key', 'today')
                ->where('analytics.periods.0.page_views', 1)
                ->where('analytics.periods.0.course_views', 1)
                ->where('analytics.periods.0.lesson_views', 1)
                ->where('analytics.periods.0.checkout_starts', 1)
                ->where('analytics.periods.0.completed_purchases', 1)
                ->where('analytics.funnel.course_views', 1)
                ->where('analytics.funnel.checkout_starts', 1)
                ->where('analytics.funnel.completed_purchases', 1)
                ->where('analytics.topCourses.0.title', 'Top Course')
                ->where('analytics.topCourses.0.total', 1)
                ->where('analytics.topReferrers.0.referrer', 'https://google.com')
                ->where('analytics.topCampaigns.0.utm_campaign', 'launch')
            );
    }
}
