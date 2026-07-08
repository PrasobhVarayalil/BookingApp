@extends('layouts.app')

@section('title', 'Room Types')
@section('crumb', 'Inventory')

@section('content')
<x-filter-toolbar>
    @include('rooms.partials.filters')

    <x-slot:actions>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoom">
            <i class="bi bi-plus-lg me-1"></i>Add room type
        </button>
    </x-slot:actions>
</x-filter-toolbar>

<div class="card">
    <div class="table-responsive">
        <table class="table hb-table align-middle">
            <thead>
                <tr>
                    <th>Room type</th>
                    <th>Hotel</th>
                    <th>Price / night</th>
                    <th>Max guests</th>
                    <th>Room numbers</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roomTypes as $roomType)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="hb-thumb hb-grad-teal"><i class="bi bi-door-open-fill"></i></span>
                                <span class="fw-semibold">{{ $roomType->name }}</span>
                            </div>
                        </td>
                        <td>{{ $roomType->hotel->name }}<div class="text-muted small">{{ $roomType->hotel->city }}</div></td>
                        <td class="fw-semibold">${{ number_format((float) $roomType->price_per_night, 2) }}</td>
                        <td><i class="bi bi-people text-muted me-1"></i>{{ $roomType->max_occupancy }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($roomType->units->take(4) as $unit)
                                    <span class="badge text-bg-light">{{ $unit->room_number }}</span>
                                @endforeach
                                @if ($roomType->units_count > 4)
                                    <span class="badge text-bg-light">+{{ $roomType->units_count - 4 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('rooms.show', $roomType) }}" class="btn btn-sm btn-soft"><i class="bi bi-eye"></i></a>
                            <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#editRoom{{ $loop->index }}"><i class="bi bi-pencil"></i></button>
                            <x-delete-form :action="route('rooms.destroy', $roomType)" :confirm="'Delete '.$roomType->name.'?'" />
                        </td>
                    </tr>

                    <x-modal-form
                        :id="'editRoom'.$loop->index"
                        title="Edit room type"
                        :action="route('rooms.update', $roomType)"
                        method="PUT"
                        submit="Save changes"
                    >
                        @include('rooms.partials.fields', ['roomType' => $roomType])
                    </x-modal-form>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="door-open" message="No room types found." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($roomTypes->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $roomTypes->links() }}</div>
    @endif
</div>

<x-modal-form id="createRoom" title="Add room type" :action="route('rooms.store')" submit="Save room type">
    @include('rooms.partials.fields', ['roomType' => null])
</x-modal-form>

@if ($errors->any() && ! old('_method'))
    @push('scripts')
    <script>new bootstrap.Modal(document.getElementById('createRoom')).show();</script>
    @endpush
@endif
@endsection
