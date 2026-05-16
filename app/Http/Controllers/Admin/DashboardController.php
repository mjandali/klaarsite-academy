<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
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

        return Inertia::render('Admin/Dashboard', ['stats' => $stats]);
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
}
