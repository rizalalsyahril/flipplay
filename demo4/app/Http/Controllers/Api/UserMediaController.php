<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Http\Resources\UserSummaryResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class UserMediaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $media = $request->user()->media()
            ->orderBy('media.id')
            ->paginate(max(1, min(50, $request->integer('per_page', 12))));

        return MediaResource::collection($media);
    }

    public function indexForUser(User $user): AnonymousResourceCollection
    {
        return MediaResource::collection($user->media()->orderBy('media.id')->get());
    }

    public function users(Media $media): AnonymousResourceCollection
    {
        return UserSummaryResource::collection(
            $media->users()->orderBy('users.id')->get(),
        );
    }

    public function store(Request $request, Media $media): JsonResponse
    {
        return $this->storeForUser($request, $request->user(), $media);
    }

    public function storeForUser(Request $request, User $user, Media $media): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['planned', 'watching', 'completed', 'dropped'])],
            'progress' => ['nullable', 'integer', 'min:0'],
        ]);

        $user->media()->syncWithoutDetaching([
            $media->id => [
                'status' => $validated['status'] ?? 'planned',
                'progress' => $validated['progress'] ?? 0,
            ],
        ]);

        return response()->json([
            'message' => 'Media ditambahkan ke koleksi.',
        ]);
    }

    public function destroy(Request $request, Media $media): JsonResponse
    {
        $request->user()->media()->detach($media->id);

        return response()->json([
            'message' => 'Media dihapus dari koleksi.',
        ]);
    }

    public function destroyForUser(User $user, Media $media): JsonResponse
    {
        $user->media()->detach($media->id);

        return response()->json([
            'message' => 'Media dihapus dari koleksi.',
        ]);
    }
}
