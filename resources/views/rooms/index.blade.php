@extends('layouts.app')

@section('title', 'Rooms')
@section('crumb', 'Inventory')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex flex-wrap gap-2">
        <div class="input-icon">
            <i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Search room or hotel" style="min-width:230px">
        </div>
        <select name="hotel" class="form-select" style="max-width:220px">
            <option value="">All hotels</option>
            @foreach ($hotels as $hotel)
                <option value="{{ $hotel->id }}" @selected($filters['hotel'] === $hotel->id)>{{ $hotel->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-soft"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if ($filters['search'] || $filters['hotel'])
            <a href="{{ route('rooms.index') }}" class="btn btn-link text-decoration-none">Reset</a>
        @endif
    </form>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoom">
        <i class="bi bi-plus-lg me-1"></i>Add room
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table hb-table align-middle">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Hotel</th>
                    <th>Price / night</th>
                    <th>Max guests</th>
                    <th>Inventory</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="hb-thumb hb-grad-teal"><i class="bi bi-door-open-fill"></i></span>
                                <span class="fw-semibold">{{ $room->name }}</span>
                            </div>
                        </td>
                        <td>{{ $room->hotel->name }}<div class="text-muted small">{{ $room->hotel->city }}</div></td>
                        <td class="fw-semibold">${{ number_format((float) $room->price_per_night, 2) }}</td>
                        <td><i class="bi bi-people text-muted me-1"></i>{{ $room->max_occupancy }}</td>
                        <td><span class="badge text-bg-light">{{ $room->total_rooms }} units</span></td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#editRoom{{ $loop->index }}"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('rooms.destroy', $room) }}" class="d-inline" onsubmit="return confirm('Delete {{ $room->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editRoom{{ $loop->index }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('rooms.update', $room) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit room</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @include('rooms.partials.fields', ['room' => $room])
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
                    <tr><td colspan="6"><div class="hb-empty"><i class="bi bi-door-open"></i>No rooms found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($rooms->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $rooms->links() }}</div>
    @endif
</div>

<div class="modal fade" id="createRoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('rooms.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('rooms.partials.fields', ['room' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save room</button>
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
