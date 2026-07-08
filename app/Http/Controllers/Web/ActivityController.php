<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $logName = $request->query('log');
        $event = $request->query('event');

        $activities = Activity::with('causer', 'subject')
            ->when($logName, fn ($q) => $q->where('log_name', $logName))
            ->when($event, fn ($q) => $q->where('event', $event))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('activity.index', [
            'activities' => $activities,
            'filters' => ['log' => $logName, 'event' => $event],
            'logNames' => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name'),
            'events' => Activity::query()->whereNotNull('event')->distinct()->orderBy('event')->pluck('event'),
        ]);
    }
}
