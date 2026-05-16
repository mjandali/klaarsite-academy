<?php

namespace App\Http\Middleware;

use App\Support\AnalyticsTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalytics
{
    public function __construct(private readonly AnalyticsTracker $tracker)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->tracker->rememberAttribution($request);

        /** @var Response $response */
        $response = $next($request);

        if ($response->isSuccessful()) {
            $this->tracker->capturePageView($request);
        }

        return $response;
    }
}
