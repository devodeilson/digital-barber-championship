<?php

namespace App\Http\Controllers\Api;

use App\Models\Vote;
use App\Models\Content;
use App\Http\Requests\VoteRequest;
use App\Http\Resources\VoteResource;
use Illuminate\Http\Request;

class VoteController extends ApiController
{
    public function store(VoteRequest $request, Content $content)
    {
        if ($content->user_id === auth()->id()) {
            return $this->respondError('You cannot vote on your own content');
        }

        if (!$content->isApproved()) {
            return $this->respondError('Content must be approved to receive votes');
        }

        if (!$content->championship->isActive()) {
            return $this->respondError('Championship is not active');
        }

        $existingVote = Vote::where('user_id', auth()->id())
            ->where('content_id', $content->id)
            ->first();

        if ($existingVote) {
            return $this->respondError('You have already voted on this content');
        }

        $vote = Vote::create([
            'user_id' => auth()->id(),
            'content_id' => $content->id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        $content->updateAverageRating();

        return $this->respondCreated(
            new VoteResource($vote)
        );
    }

    public function update(VoteRequest $request, Vote $vote)
    {
        if ($vote->user_id !== auth()->id()) {
            return $this->respondForbidden('You can only update your own votes');
        }

        if (!$vote->isEditable()) {
            return $this->respondError('Vote can no longer be edited');
        }

        $vote->update([
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        $vote->content->updateAverageRating();

        return $this->respondSuccess(
            new VoteResource($vote)
        );
    }

    public function destroy(Vote $vote)
    {
        if ($vote->user_id !== auth()->id()) {
            return $this->respondForbidden('You can only delete your own votes');
        }

        if (!$vote->isDeletable()) {
            return $this->respondError('Vote can no longer be deleted');
        }

        $content = $vote->content;
        $vote->delete();
        $content->updateAverageRating();

        return $this->respondNoContent();
    }

    public function myVotes(Request $request)
    {
        $votes = Vote::where('user_id', auth()->id())
            ->with(['content.championship'])
            ->when($request->championship_id, function ($query, $championshipId) {
                return $query->whereHas('content', function ($q) use ($championshipId) {
                    $q->where('championship_id', $championshipId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return VoteResource::collection($votes);
    }

    public function contentVotes(Content $content)
    {
        if (!$content->isApproved()) {
            return $this->respondError('Content must be approved to view votes');
        }

        $votes = $content->votes()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return VoteResource::collection($votes);
    }
} 