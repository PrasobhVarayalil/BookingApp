<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Exceptions\ResourceInUseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomTypeRequest;
use App\Models\RoomType;
use App\Services\HotelService;
use App\Services\RoomTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(
        private readonly RoomTypeService $roomTypes,
        private readonly HotelService $hotels,
    ) {}

    public function index(Request $request): View
    {
        $hotelId = $request->query('hotel');
        $search = $request->query('search');

        return view('rooms.index', [
            'roomTypes' => $this->roomTypes->paginateWithHotel(self::PER_PAGE, $hotelId, $search)->withQueryString(),
            'hotels' => $this->hotels->all(),
            'filters' => ['hotel' => $hotelId, 'search' => $search],
        ]);
    }

    public function store(StoreRoomTypeRequest $request): RedirectResponse
    {
        $numbers = $request->unitNumbers();

        if ($numbers === []) {
            return back()->withInput()->withErrors(['room_numbers' => 'Enter at least one room number.']);
        }

        $this->roomTypes->create($request->typeAttributes(), $numbers);

        return redirect()->route('rooms.index')->with('success', 'Room type added.');
    }

    public function update(StoreRoomTypeRequest $request, RoomType $roomType): RedirectResponse
    {
        $this->roomTypes->update($roomType, $request->typeAttributes());

        return redirect()->route('rooms.index')->with('success', 'Room type updated.');
    }

    public function destroy(RoomType $roomType): RedirectResponse
    {
        try {
            $this->roomTypes->delete($roomType);
        } catch (ResourceInUseException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('rooms.index')->with('success', 'Room type deleted.');
    }
}
