@extends('layouts.app')

@section('title', 'Bookings')
@section('crumb', 'Reservations')

@section('content')
@php
    $hotelOptions = $roomTypes
        ->map(fn ($rt) => ['id' => $rt->hotel->id, 'name' => $rt->hotel->name.' — '.$rt->hotel->city])
        ->unique('id')
        ->sortBy('name')
        ->values();

    $roomTypeData = $roomTypes->map(fn ($rt) => [
        'id' => $rt->id,
        'hotel_id' => $rt->hotel_id,
        'name' => $rt->name,
        'price' => (float) $rt->price_per_night,
        'max' => (int) $rt->max_occupancy,
        'label' => $rt->name.' ($'.number_format((float) $rt->price_per_night, 2).'/night · up to '.$rt->max_occupancy.')',
    ])->values();

    $hotelNames = $hotelOptions->pluck('name', 'id');

    $prefillTypeId = old('room_type_id', $prefill['room_type_id'] ?? '');
    $prefillHotelId = old('hotel_id', optional($roomTypes->firstWhere('id', $prefillTypeId))->hotel_id);
@endphp
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
                    <th>Reference</th>
                    <th>Guest</th>
                    <th>Hotel / Room</th>
                    <th>Stay</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    @php $nights = $booking->checkin_date->diffInDays($booking->checkout_date); @endphp
                    <tr>
                        <td class="fw-semibold">{{ $booking->booking_reference }}</td>
                        <td>
                            <div class="fw-semibold">{{ $booking->guest_name }}</div>
                            <div class="text-muted small">{{ $booking->guest_email }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="hb-thumb hb-grad-sky"><i class="bi bi-building"></i></span>
                                <div>
                                    <div class="fw-semibold">{{ $booking->roomType->hotel->name }}</div>
                                    <div class="text-muted small">{{ $booking->roomType->name }} · Room {{ $booking->roomUnit?->room_number ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $booking->checkin_date->format('M d') }} → {{ $booking->checkout_date->format('M d, Y') }}
                            <div class="text-muted small">{{ $nights }} {{ Str::plural('night', $nights) }} · {{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }}</div>
                        </td>
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
                    <tr><td colspan="7"><div class="hb-empty"><i class="bi bi-calendar-x"></i>No bookings yet.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($bookings->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $bookings->links() }}</div>
    @endif
</div>

<div class="modal fade" id="createBooking" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <form method="POST" action="{{ route('bookings.store') }}" id="bookingForm">
                @csrf
                <div class="modal-header hb-modal-head">
                    <div>
                        <h5 class="modal-title mb-0">New booking</h5>
                        <div class="text-muted small">Pick a room and dates — we auto-assign an available unit.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-7 hb-booking-form">
                            <div id="bookingAlert" class="alert alert-warning d-none d-flex align-items-center mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <span id="bookingAlertText">No rooms available for the selected type and dates.</span>
                            </div>

                            <div class="hb-section">
                                <div class="hb-section-title"><i class="bi bi-door-open"></i>Room &amp; stay</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Hotel</label>
                                        <select id="hotelSelect" class="form-select" required>
                                            <option value="">Select a hotel</option>
                                            @foreach ($hotelOptions as $hotel)
                                                <option value="{{ $hotel['id'] }}" @selected($prefillHotelId === $hotel['id'])>{{ $hotel['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Room type</label>
                                        <select name="room_type_id" id="roomTypeSelect" class="form-select @error('room_type_id') is-invalid @enderror" required>
                                            <option value="">Select a room type</option>
                                        </select>
                                        @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Check-in</label>
                                        <input type="date" name="checkin_date" id="checkinDate" min="{{ now()->toDateString() }}" value="{{ old('checkin_date', $prefill['checkin_date'] ?? '') }}" class="form-control @error('checkin_date') is-invalid @enderror" required>
                                        @error('checkin_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Check-out</label>
                                        <input type="date" name="checkout_date" id="checkoutDate" min="{{ now()->addDay()->toDateString() }}" value="{{ old('checkout_date', $prefill['checkout_date'] ?? '') }}" class="form-control @error('checkout_date') is-invalid @enderror" required>
                                        @error('checkout_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Guests <span class="text-muted fw-normal" id="guestsHint"></span></label>
                                        <input type="number" min="1" name="guests" id="guestsInput" value="{{ old('guests', $prefill['guests'] ?? 1) }}" class="form-control @error('guests') is-invalid @enderror" required>
                                        <div class="invalid-feedback" id="guestsError">@error('guests'){{ $message }}@enderror</div>
                                    </div>
                                    <input type="hidden" name="room_unit_id" id="roomUnitId" value="">
                                </div>
                            </div>

                            <div class="hb-section">
                                <div class="hb-section-title"><i class="bi bi-person-badge"></i>Guest details</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Guest name</label>
                                        <input type="text" name="guest_name" value="{{ old('guest_name') }}" class="form-control @error('guest_name') is-invalid @enderror" required>
                                        @error('guest_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Guest email</label>
                                        <input type="email" name="guest_email" value="{{ old('guest_email') }}" class="form-control @error('guest_email') is-invalid @enderror" required>
                                        @error('guest_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Guest phone <span class="text-muted fw-normal">(optional)</span></label>
                                        <input type="text" name="guest_phone" value="{{ old('guest_phone') }}" class="form-control @error('guest_phone') is-invalid @enderror">
                                        @error('guest_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5 hb-booking-aside">
                            <div class="hb-aside-head">
                                <span class="hb-aside-ico"><i class="bi bi-receipt"></i></span>
                                <div>
                                    <div class="t">Booking summary</div>
                                    <div class="s">Updates as you choose</div>
                                </div>
                            </div>
                            <div class="hb-sum">
                                <div class="hb-sum-row"><span class="k">Hotel</span><span class="v" id="sumHotel">—</span></div>
                                <div class="hb-sum-row"><span class="k">Room type</span><span class="v" id="sumType">—</span></div>
                                <div class="hb-sum-row"><span class="k">Rate / night</span><span class="v" id="sumRate">—</span></div>
                                <div class="hb-sum-row"><span class="k">Max guests</span><span class="v" id="sumMax">—</span></div>
                                <div class="hb-sum-row"><span class="k">Assigned room</span><span class="v"><span class="hb-room-pill is-empty" id="sumRoom"><i class="bi bi-hash"></i>Not assigned</span></span></div>
                                <div class="hb-sum-row"><span class="k">Check-in</span><span class="v" id="sumCheckin">—</span></div>
                                <div class="hb-sum-row"><span class="k">Check-out</span><span class="v" id="sumCheckout">—</span></div>
                                <div class="hb-sum-row"><span class="k">Nights × rate</span><span class="v" id="sumNights">—</span></div>
                            </div>
                            <div class="hb-sum-total">
                                <span class="k">Estimated total</span>
                                <span class="v" id="sumTotal">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="bookingSubmit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Confirm booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const hotelSelect = document.getElementById('hotelSelect');
    const typeSelect = document.getElementById('roomTypeSelect');
    const unitId = document.getElementById('roomUnitId');
    const checkin = document.getElementById('checkinDate');
    const checkout = document.getElementById('checkoutDate');
    const guestsInput = document.getElementById('guestsInput');
    const guestsHint = document.getElementById('guestsHint');
    const guestsError = document.getElementById('guestsError');
    const submitBtn = document.getElementById('bookingSubmit');
    const alertBox = document.getElementById('bookingAlert');
    const alertText = document.getElementById('bookingAlertText');

    const sum = {
        hotel: document.getElementById('sumHotel'),
        type: document.getElementById('sumType'),
        rate: document.getElementById('sumRate'),
        max: document.getElementById('sumMax'),
        room: document.getElementById('sumRoom'),
        checkin: document.getElementById('sumCheckin'),
        checkout: document.getElementById('sumCheckout'),
        nights: document.getElementById('sumNights'),
        total: document.getElementById('sumTotal'),
    };

    let hasUnit = false;

    const roomTypes = @json($roomTypeData);
    const hotelNames = @json($hotelNames);
    const oldType = @json($prefillTypeId);

    const ts = (el) => el.tomselect || window.hbInitSelect(el);
    const money = (n) => '$' + Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const fmtDate = (v) => v ? new Date(v + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
    const nightsBetween = (a, b) => (a && b) ? Math.max(0, Math.round((new Date(b) - new Date(a)) / 86400000)) : 0;

    const showAlert = (msg) => { alertText.textContent = msg; alertBox.classList.remove('d-none'); };
    const hideAlert = () => alertBox.classList.add('d-none');

    const setRoom = (id, number) => {
        unitId.value = id || '';
        if (number) {
            sum.room.className = 'hb-room-pill';
            sum.room.innerHTML = '<i class="bi bi-hash"></i>Room ' + number;
        } else {
            sum.room.className = 'hb-room-pill is-empty';
            sum.room.innerHTML = '<i class="bi bi-hash"></i>Not assigned';
        }
    };

    const currentType = () => roomTypes.find(rt => rt.id === typeSelect.value);

    const renderSummary = () => {
        const type = currentType();
        sum.hotel.textContent = hotelNames[hotelSelect.value] || '—';
        sum.type.textContent = type ? type.name : '—';
        sum.rate.textContent = type ? money(type.price) : '—';
        sum.max.textContent = type ? `${type.max} ${type.max === 1 ? 'guest' : 'guests'}` : '—';
        sum.checkin.textContent = fmtDate(checkin.value);
        sum.checkout.textContent = fmtDate(checkout.value);

        const nights = nightsBetween(checkin.value, checkout.value);
        if (type && nights > 0) {
            sum.nights.textContent = `${nights} × ${money(type.price)}`;
            sum.total.textContent = money(nights * type.price);
        } else {
            sum.nights.textContent = type ? `${money(type.price)} / night` : '—';
            sum.total.textContent = money(0);
        }
    };

    const guestsOk = () => {
        const type = currentType();
        const g = parseInt(guestsInput.value || '0', 10);
        if (!Number.isInteger(g) || g < 1) return false;
        return type ? g <= type.max : true;
    };

    const refreshSubmit = () => {
        submitBtn.disabled = !hasUnit || !guestsOk();
    };

    const applyGuestsConstraint = () => {
        const type = currentType();
        if (type) {
            guestsInput.max = type.max;
            guestsHint.textContent = `· up to ${type.max}`;
        } else {
            guestsInput.removeAttribute('max');
            guestsHint.textContent = '';
        }

        const over = !guestsOk();
        guestsInput.classList.toggle('is-invalid', over && !!guestsInput.value);
        if (over && type && parseInt(guestsInput.value || '0', 10) > type.max) {
            guestsError.textContent = `This room type sleeps at most ${type.max} ${type.max === 1 ? 'guest' : 'guests'}.`;
        } else {
            guestsError.textContent = '';
        }
        refreshSubmit();
    };

    const populateTypes = () => {
        const hotelId = hotelSelect.value;
        const control = ts(typeSelect);
        control.clear(true);
        control.clearOptions();
        control.addOption({ value: '', text: 'Select a room type' });
        roomTypes.filter(rt => rt.hotel_id === hotelId).forEach(rt => control.addOption({ value: rt.id, text: rt.label }));
        control.refreshOptions(false);

        const keep = roomTypes.find(rt => rt.id === oldType && rt.hotel_id === hotelId);
        control.setValue(keep ? oldType : '', true);

        loadUnits();
    };

    const loadUnits = async () => {
        const typeId = typeSelect.value;
        const ci = checkin.value;
        const co = checkout.value;

        hideAlert();
        setRoom('', null);
        hasUnit = false;
        renderSummary();
        applyGuestsConstraint();

        if (!typeId || !ci || !co) return;

        const params = new URLSearchParams({ checkin_date: ci, checkout_date: co });
        let res;
        try {
            res = await fetch(`{{ url('/bookings/available-units') }}/${typeId}?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
        } catch (e) {
            showAlert('Could not check availability. Please check your connection and try again.');
            refreshSubmit();
            return;
        }

        const json = await res.json().catch(() => ({}));

        if (!res.ok) {
            showAlert(json.message || 'Please choose valid check-in and check-out dates.');
            refreshSubmit();
            return;
        }

        const units = json.data || [];

        if (units.length === 0) {
            showAlert('No rooms available for this room type on the selected dates. Try different dates.');
            refreshSubmit();
            return;
        }

        hasUnit = true;
        setRoom(units[0].id, units[0].room_number);
        refreshSubmit();
    };

    const syncCheckoutMin = () => {
        if (!checkin.value) return;
        const next = new Date(checkin.value);
        next.setDate(next.getDate() + 1);
        checkout.min = next.toISOString().slice(0, 10);
        if (checkout.value && checkout.value <= checkin.value) checkout.value = '';
    };

    hotelSelect.tomselect?.on('change', populateTypes);
    typeSelect.tomselect?.on('change', loadUnits);
    checkin.addEventListener('change', syncCheckoutMin);
    [checkin, checkout].forEach(el => el?.addEventListener('change', loadUnits));
    guestsInput.addEventListener('input', applyGuestsConstraint);

    populateTypes();

    @if ($errors->any() || session('error') || request()->has('room_type_id'))
    new bootstrap.Modal(document.getElementById('createBooking')).show();
    @endif
})();
</script>
@endpush
@endsection
