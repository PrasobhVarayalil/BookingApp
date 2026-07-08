<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Exceptions\ResourceInUseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Models\Hotel;
use App\Services\HotelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(
        private readonly HotelService $hotels,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'city' => $request->query('city'),
            'rating' => $request->query('rating'),
        ];

        return view('hotels.index', [
            'hotels' => $this->hotels->paginate($filters, self::PER_PAGE)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function store(StoreHotelRequest $request): RedirectResponse
    {
        $this->hotels->create($request->validated());

        return redirect()->route('hotels.index')->with('success', 'Hotel added.');
    }

    public function update(StoreHotelRequest $request, Hotel $hotel): RedirectResponse
    {
        $this->hotels->update($hotel, $request->validated());

        return redirect()->route('hotels.index')->with('success', 'Hotel updated.');
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        try {
            $this->hotels->delete($hotel);
        } catch (ResourceInUseException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('hotels.index')->with('success', 'Hotel deleted.');
    }
}
