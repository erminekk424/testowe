<?php

namespace App\Http\Controllers\Marketplace;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Models\Game;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Octane\Facades\Octane;

class GlobalSearchController extends Controller
{
    public function __invoke(SearchRequest $request)
    {
        $request->authenticate();
        $rawQuery = $request->getCleanQuery();
        $filters = $request->validated('filter', []);

        $filterHash = md5(json_encode($filters));

        $cacheKey = Str::of($rawQuery)
            ->lower()
            ->slug()
            ->prepend('search:')
            ->append(":f{$filterHash}");
        $ttl = 60;

//            ->tags(['search'])

        return Cache::remember($cacheKey, $ttl, function () use (
            $rawQuery,
            $filters,
        ) {
//            [$gameId, $searchResults] = Octane::concurrently([
            $gameId = $this->resolveGameId($filters['game'] ?? null);
            $searchResults = $this->executeSearch($rawQuery, $filters);
//            ]);

//                $query = Product::search($rawQuery)->query(fn(Builder $builder) => $builder->where('name', 'ILIKE', "%{$rawQuery}%")->with('game:id,uuid'));
//
//                if ($filterGame = $request->validated('filter.game')) {
//                    $game = Game::firstWhere('uuid', $filterGame);
//
//                    if ($game) {
//                        $query->where('game_id', $game->id);
//                    }
//                }
//                if ($filterType = $request->validated('filter.type')) {
//                    $type = ProductType::tryFrom($filterType);
//
//                    if ($type) {
//                        $query->where('type', $filterType);
//                    }
//                }

//                $results = $query->paginate(10);

            return response()->json([
                'data' => SearchResultResource::collection($searchResults->items()),
                'meta' => ['total' => $searchResults->total()],
            ]);

//            return response()->json([
//                'data' => SearchResultResource::collection($results->items()),
//                'meta' => [
//                    'total' => $results->total(),
//                ],
//            ]);
        });
    }

    protected function resolveGameId(?string $uuid): ?int
    {
        if (!$uuid) {
            return null;
        }

//        $table = Octane::table('game_uuid_to_id');
//        if ($id = $table->get($uuid, 'id')) {
//            return $id;
//        }

        /** @var Game|null $game */
        $game = Game::firstWhere('uuid', $uuid);
        if ($game) {
//            $table->set($uuid, ['id' => $game->id]);
            return $game->id;
        }

        return null;
    }

    protected function executeSearch(string $rawQuery, array $filters)
    {
        $query = Product::search($rawQuery)
            ->query(fn(Builder $builder) => $builder
                ->where('name', 'ILIKE', "%{$rawQuery}%")
                ->with('game:id,uuid')
            );

        if (!empty($filters['type']) && ProductType::tryFrom($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['game']) && $gameId = Octane::table('game_uuid_to_id')->get($filters['game'], 'id')) {
            $query->where('game_id', $gameId);
        }

        return $query->paginate(10);
    }
}
