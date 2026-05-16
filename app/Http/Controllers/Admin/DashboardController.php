<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\PageView;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_courses' => Course::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];

        $lastThirtyDays = now()->subDays(29)->startOfDay();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'analytics' => [
                'periods' => [
                    $this->periodSummary('today', now()->startOfDay()),
                    $this->periodSummary('7d', now()->subDays(6)->startOfDay()),
                    $this->periodSummary('30d', $lastThirtyDays),
                ],
                'topCourses' => PageView::query()
                    ->with('course:id,title,slug')
                    ->selectRaw('course_id, COUNT(*) as total')
                    ->where('event_type', PageView::EVENT_COURSE_VIEW)
                    ->whereNotNull('course_id')
                    ->where('created_at', '>=', $lastThirtyDays)
                    ->groupBy('course_id')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
                    ->map(fn (PageView $view) => [
                        'course_id' => $view->course_id,
                        'title' => $view->course?->title,
                        'slug' => $view->course?->slug,
                        'total' => (int) $view->total,
                    ]),
                'topReferrers' => PageView::query()
                    ->selectRaw('referrer, COUNT(*) as total')
                    ->whereNotNull('referrer')
                    ->where('created_at', '>=', $lastThirtyDays)
                    ->groupBy('referrer')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
                    ->map(fn ($view) => [
                        'referrer' => $view->referrer,
                        'total' => (int) $view->total,
                    ]),
                'topCampaigns' => PageView::query()
                    ->selectRaw('utm_campaign, utm_source, utm_medium, COUNT(*) as total')
                    ->whereNotNull('utm_campaign')
                    ->where('created_at', '>=', $lastThirtyDays)
                    ->groupBy('utm_campaign', 'utm_source', 'utm_medium')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
                    ->map(fn ($view) => [
                        'utm_campaign' => $view->utm_campaign,
                        'utm_source' => $view->utm_source,
                        'utm_medium' => $view->utm_medium,
                        'total' => (int) $view->total,
                    ]),
                'funnel' => [
                    'course_views' => PageView::query()
                        ->where('event_type', PageView::EVENT_COURSE_VIEW)
                        ->where('created_at', '>=', $lastThirtyDays)
                        ->count(),
                    'checkout_starts' => PageView::query()
                        ->where('event_type', PageView::EVENT_CHECKOUT_START)
                        ->where('created_at', '>=', $lastThirtyDays)
                        ->count(),
                    'completed_purchases' => PageView::query()
                        ->where('event_type', PageView::EVENT_PURCHASE_COMPLETED)
                        ->where('created_at', '>=', $lastThirtyDays)
                        ->count(),
                ],
            ],
        ]);
    }

    public function students()
    {
        $students = User::where('role', 'student')
            ->with('enrollments.course')
            ->paginate(20);

        return Inertia::render('Admin/Students', ['students' => $students]);
    }

    public function orders()
    {
        $orders = Order::with('user', 'course')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Orders', ['orders' => $orders]);
    }

    public function settings()
    {
        return Inertia::render('Admin/Settings');
    }

    private function periodSummary(string $key, Carbon $from): array
    {
        $query = PageView::query()->where('created_at', '>=', $from);

        return [
            'key' => $key,
            'total' => (clone $query)->count(),
            'page_views' => (clone $query)->where('event_type', PageView::EVENT_PAGE_VIEW)->count(),
            'course_views' => (clone $query)->where('event_type', PageView::EVENT_COURSE_VIEW)->count(),
            'lesson_views' => (clone $query)->where('event_type', PageView::EVENT_LESSON_VIEW)->count(),
            'checkout_starts' => (clone $query)->where('event_type', PageView::EVENT_CHECKOUT_START)->count(),
            'completed_purchases' => (clone $query)->where('event_type', PageView::EVENT_PURCHASE_COMPLETED)->count(),
        ];
    }
}
