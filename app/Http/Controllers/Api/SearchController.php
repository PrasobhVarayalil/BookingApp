<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Services\SearchService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $search,
    ) {}

    public function __invoke(SearchRequest $request): AnonymousResourceCollection
    {
        $payload = $this->search->search($request->params());

        return SearchResultResource::collection($payload['results'])
            ->additional(['meta' => $payload['meta']]);
    }
}
