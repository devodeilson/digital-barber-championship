<?php

namespace App\Http\Controllers\Api;

use App\Models\Championship;
use App\Http\Requests\ChampionshipRequest;
use App\Http\Resources\ChampionshipResource;
use Illuminate\Http\Request;
use App\Services\CacheService;

class ChampionshipController extends ApiController
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index(Request $request)
    {
        $cacheKey = "championships:list:" . md5(json_encode($request->all()));
        
        return $this->cacheService->remember($cacheKey, function () use ($request) {
            $championships = Championship::query()
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->paginate($request->per_page ?? 15);

            return ChampionshipResource::collection($championships);
        });
    }

    public function store(ChampionshipRequest $request)
    {
        $championship = Championship::create($request->validated());

        return $this->respondCreated(
            new ChampionshipResource($championship)
        );
    }

    public function show(Championship $championship)
    {
        $cacheKey = $this->cacheService->getChampionshipKey($championship->id);
        
        return $this->cacheService->remember($cacheKey, function () use ($championship) {
            return $this->respondSuccess(
                new ChampionshipResource($championship->load(['participants', 'contents']))
            );
        });
    }

    public function update(ChampionshipRequest $request, Championship $championship)
    {
        $championship->update($request->validated());

        return $this->respondSuccess(
            new ChampionshipResource($championship)
        );
    }

    public function destroy(Championship $championship)
    {
        $championship->delete();

        return $this->respondNoContent();
    }

    public function join(Championship $championship)
    {
        if ($championship->participants()->where('user_id', auth()->id())->exists()) {
            return $this->respondError('Already joined this championship');
        }

        $championship->participants()->attach(auth()->id());

        return $this->respondSuccess(null, 'Successfully joined championship');
    }

    public function leave(Championship $championship)
    {
        $championship->participants()->detach(auth()->id());

        return $this->respondSuccess(null, 'Successfully left championship');
    }

    public function activate(Championship $championship)
    {
        if ($championship->status !== 'draft') {
            return $this->respondError('Championship can only be activated from draft status');
        }

        $championship->activate();

        return $this->respondSuccess(
            new ChampionshipResource($championship)
        );
    }

    public function finish(Championship $championship)
    {
        if ($championship->status !== 'active') {
            return $this->respondError('Championship must be active to finish');
        }

        $championship->finish();

        return $this->respondSuccess(
            new ChampionshipResource($championship)
        );
    }
} 