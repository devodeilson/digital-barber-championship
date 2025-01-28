<?php

namespace App\Http\Controllers\Api;

use App\Models\Content;
use App\Models\Championship;
use App\Http\Requests\ContentRequest;
use App\Http\Resources\ContentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends ApiController
{
    public function index(Request $request)
    {
        $contents = Content::query()
            ->with(['user', 'championship'])
            ->when($request->championship_id, function ($query, $championshipId) {
                return $query->where('championship_id', $championshipId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ContentResource::collection($contents);
    }

    public function store(ContentRequest $request)
    {
        $championship = Championship::findOrFail($request->championship_id);
        
        if (!$championship->isActive()) {
            return $this->respondError('Championship is not active');
        }

        if (!$championship->hasParticipant(auth()->id())) {
            return $this->respondError('You are not a participant of this championship');
        }

        $content = new Content($request->validated());
        $content->user_id = auth()->id();
        
        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('contents', 'public');
            $content->media_url = $path;
        }

        $content->save();

        return $this->respondCreated(
            new ContentResource($content)
        );
    }

    public function show(Content $content)
    {
        return $this->respondSuccess(
            new ContentResource($content->load(['user', 'championship', 'votes']))
        );
    }

    public function update(ContentRequest $request, Content $content)
    {
        if ($content->user_id !== auth()->id()) {
            return $this->respondForbidden('You can only edit your own content');
        }

        if (!$content->isEditable()) {
            return $this->respondError('Content can no longer be edited');
        }

        $content->fill($request->validated());

        if ($request->hasFile('media')) {
            if ($content->media_url) {
                Storage::disk('public')->delete($content->media_url);
            }
            $path = $request->file('media')->store('contents', 'public');
            $content->media_url = $path;
        }

        $content->save();

        return $this->respondSuccess(
            new ContentResource($content)
        );
    }

    public function destroy(Content $content)
    {
        if ($content->user_id !== auth()->id()) {
            return $this->respondForbidden('You can only delete your own content');
        }

        if ($content->media_url) {
            Storage::disk('public')->delete($content->media_url);
        }

        $content->delete();

        return $this->respondNoContent();
    }

    public function approve(Content $content)
    {
        if (!auth()->user()->isAdmin()) {
            return $this->respondForbidden('Only administrators can approve content');
        }

        $content->approve();

        return $this->respondSuccess(
            new ContentResource($content)
        );
    }

    public function reject(Content $content)
    {
        if (!auth()->user()->isAdmin()) {
            return $this->respondForbidden('Only administrators can reject content');
        }

        $content->reject();

        return $this->respondSuccess(
            new ContentResource($content)
        );
    }

    public function myContents(Request $request)
    {
        $contents = Content::where('user_id', auth()->id())
            ->with(['championship'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ContentResource::collection($contents);
    }
} 