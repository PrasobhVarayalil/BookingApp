<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Exceptions\ResourceInUseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Services\HotelService;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(
        private readonly RoomService $rooms,
        private readonly HotelService $hotels,
    ) {}

    public function index(Request $request): View
    {
        $hotelId = $request->query('hotel');
        $search = $request->query('search');

        return view('rooms.index', [
            'rooms' => $this->rooms->paginateWithHotel(self::PER_PAGE, $hotelId, $search)->withQueryString(),
            'hotels' => $this->hotels->all(),
            'filters' => ['hotel' => $hotelId, 'search' => $search],
        ]);
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $this->rooms->create($request->validated());

        return redirect()->route('rooms.index')->with('success', 'Room added.');
    }

    public function update(StoreRoomRequest $request, Room $room): RedirectResponse
    {
        $this->rooms->update($room, $request->validated());

        return redirect()->route('rooms.index')->with('success', 'Room updated.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        try {
            $this->rooms->delete($room);
        } catch (ResourceInUseException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('rooms.index')->with('success', 'Room deleted.');
    }
}
