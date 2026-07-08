<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\City;
use App\Services\SearchService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $search,
    ) {}

    public function index(): View
    {
        return view('search.index', ['payload' => null, 'filters' => [], 'cities' => $this->cities()]);
    }

    public function search(SearchRequest $request): View
    {
        return view('search.index', [
            'payload' => $this->search->search($request->params()),
            'filters' => $request->params(),
            'cities' => $this->cities(),
        ]);
    }

    /**
     * @return Collection<int, string>
     */
    private function cities(): Collection
    {
        return City::orderBy('name')->distinct()->pluck('name');
    }
}
