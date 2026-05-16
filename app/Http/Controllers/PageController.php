<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function home()
    {
        $featuredCourses = Course::published()
            ->withCount(['sections', 'lessons'])
            ->latest()
            ->take(3)
            ->get();

        return Inertia::render('Home', [
            'featuredCourses' => $featuredCourses,
        ]);
    }

    public function about()
    {
        return Inertia::render('About');
    }

    public function contact()
    {
        return Inertia::render('Contact');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // MVP note: validated contact messages are acknowledged here.
        // Hook this into Mail or database storage when the production mailbox is ready.
        return back()->with('success', app()->getLocale() === 'ar' ? 'تم إرسال رسالتك بنجاح.' : 'Your message has been sent successfully.');
    }

    public function privacy()
    {
        return Inertia::render('Privacy');
    }

    public function terms()
    {
        return Inertia::render('Terms');
    }
}
