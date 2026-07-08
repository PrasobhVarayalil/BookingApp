<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Services\SearchService;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $search,
    ) {}

    public function index(): View
    {
        return view('search.index', ['payload' => null, 'filters' => []]);
    }

    public function search(SearchRequest $request): View
    {
        return view('search.index', [
            'payload' => $this->search->search($request->params()),
            'filters' => $request->params(),
        ]);
    }
}
