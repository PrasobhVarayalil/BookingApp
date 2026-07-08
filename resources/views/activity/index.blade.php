@extends('layouts.app')

@section('title', 'Activity log')
@section('crumb', 'Audit trail')

@php
    $eventTone = [
        'created' => 'hb-chip-green',
        'updated' => 'hb-chip-amber',
        'deleted' => 'hb-chip',
    ];
@endphp

@section('content')
<x-filter-toolbar>
    <x-filter-field icon="collection">
        <select name="log" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All logs</option>
            @foreach ($logNames as $name)
                <option value="{{ $name }}" @selected($filters['log'] === $name)>{{ ucfirst($name) }}</option>
            @endforeach
        </select>
    </x-filter-field>

    <x-filter-field icon="lightning-charge">
        <select name="event" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All events</option>
            @foreach ($events as $event)
                <option value="{{ $event }}" @selected($filters['event'] === $event)>{{ ucfirst($event) }}</option>
            @endforeach
        </select>
    </x-filter-field>

    <x-filter-submit
        :clear-url="route('activity.index')"
        :show-clear="(bool) ($filters['log'] || $filters['event'])"
    />
</x-filter-toolbar>

<div class="card">
    <div class="table-responsive">
        <table class="table hb-table align-middle">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Actor</th>
                    <th>Event</th>
                    <th>Description</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $activity)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $activity->created_at->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ $activity->created_at->format('H:i:s') }}</div>
                        </td>
                        <td>{{ $activity->causer?->name ?? 'System' }}</td>
                        <td>
                            @if ($activity->event)
                                <span class="hb-chip {{ $eventTone[$activity->event] ?? 'hb-chip' }}">{{ ucfirst($activity->event) }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($activity->description) }}</td>
                        <td>
                            @if ($activity->subject_type)
                                <span class="fw-semibold">{{ class_basename($activity->subject_type) }}</span>
                                <div class="text-muted small"><code>{{ Str::limit($activity->subject_id, 8, '') }}</code></div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="clock-history" message="No activity recorded yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($activities->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $activities->links() }}</div>
    @endif
</div>
@endsection
