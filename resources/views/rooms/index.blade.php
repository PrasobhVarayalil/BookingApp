@extends('layouts.app')

@section('title', 'Room Types')
@section('crumb', 'Inventory')

@section('content')
<div class="hb-toolbar mb-3">
    <form method="GET" class="hb-filters">
        <div class="hb-filter-field">
            <i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control form-control-sm" placeholder="Search type or hotel" style="min-width:200px">
        </div>
        <div class="hb-filter-field">
            <i class="bi bi-buildings"></i>
            <select name="hotel" class="form-select form-select-sm">
                <option value="">All hotels</option>
                @foreach ($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected($filters['hotel'] === $hotel->id)>{{ $hotel->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-sm btn-soft"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if ($filters['search'] || $filters['hotel'])
            <a href="{{ route('rooms.index') }}" class="btn btn-sm btn-link text-decoration-none px-2">Clear</a>
        @endif
    </form>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoom">
        <i class="bi bi-plus-lg me-1"></i>Add room type
    </button>
</div>

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
                            <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#editRoom{{ $loop->index }}"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('rooms.destroy', $roomType) }}" class="d-inline" onsubmit="return confirm('Delete {{ $roomType->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editRoom{{ $loop->index }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('rooms.update', $roomType) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit room type</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @include('rooms.partials.fields', ['roomType' => $roomType])
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="6"><div class="hb-empty"><i class="bi bi-door-open"></i>No room types found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($roomTypes->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $roomTypes->links() }}</div>
    @endif
</div>

<div class="modal fade" id="createRoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('rooms.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add room type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('rooms.partials.fields', ['roomType' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save room type</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->any() && ! old('_method'))
    @push('scripts')
    <script>new bootstrap.Modal(document.getElementById('createRoom')).show();</script>
    @endpush
@endif
@endsection
