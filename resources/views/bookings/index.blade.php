@extends('layouts.app')

@section('title', 'Bookings')
@section('crumb', 'Reservations')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBooking">
        <i class="bi bi-calendar-plus me-1"></i>New booking
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table hb-table align-middle">
            <thead>
                <tr>
                    <th>Hotel / Room</th>
                    <th>Stay</th>
                    <th>Guests</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    @php $nights = $booking->checkin_date->diffInDays($booking->checkout_date); @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="hb-thumb hb-grad-sky"><i class="bi bi-building"></i></span>
                                <div>
                                    <div class="fw-semibold">{{ $booking->room->hotel->name }}</div>
                                    <div class="text-muted small">{{ $booking->room->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $booking->checkin_date->format('M d') }} → {{ $booking->checkout_date->format('M d, Y') }}
                            <div class="text-muted small">{{ $nights }} {{ Str::plural('night', $nights) }}</div>
                        </td>
                        <td><i class="bi bi-people text-muted me-1"></i>{{ $booking->guests }}</td>
                        <td class="fw-semibold">${{ number_format((float) $booking->total_price, 2) }}</td>
                        <td>
                            @if ($booking->status === \App\Models\Booking::STATUS_CONFIRMED)
                                <span class="hb-chip hb-chip-green"><i class="bi bi-check-circle"></i>Confirmed</span>
                            @else
                                <span class="hb-chip hb-chip-amber"><i class="bi bi-x-circle"></i>Cancelled</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($booking->status === \App\Models\Booking::STATUS_CONFIRMED)
                                <form method="POST" action="{{ route('bookings.destroy', $booking) }}" class="d-inline" onsubmit="return confirm('Cancel this booking?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="hb-empty"><i class="bi bi-calendar-x"></i>No bookings yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($bookings->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $bookings->links() }}</div>
    @endif
</div>

<div class="modal fade" id="createBooking" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('bookings.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Room</label>
                        <select name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                            <option value="">Select a room</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id') === $room->id)>
                                    {{ $room->hotel->name }} — {{ $room->name }} (${{ number_format((float) $room->price_per_night, 2) }}/night)
                                </option>
                            @endforeach
                        </select>
                        @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Check-in</label>
                            <input type="date" name="checkin_date" value="{{ old('checkin_date') }}" class="form-control @error('checkin_date') is-invalid @enderror" required>
                            @error('checkin_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Check-out</label>
                            <input type="date" name="checkout_date" value="{{ old('checkout_date') }}" class="form-control @error('checkout_date') is-invalid @enderror" required>
                            @error('checkout_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Guests</label>
                        <input type="number" min="1" name="guests" value="{{ old('guests', 1) }}" class="form-control @error('guests') is-invalid @enderror" required>
                        @error('guests')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->any() || session('error'))
    @push('scripts')
    <script>new bootstrap.Modal(document.getElementById('createBooking')).show();</script>
    @endpush
@endif
@endsection
