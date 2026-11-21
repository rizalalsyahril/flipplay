<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $sort = $request->string('sort', 'id')->toString();
        $direction = $request->string('direction', 'asc')->lower()->toString();
        $allowedSorts = ['id', 'title', 'score', 'year', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $search = $request->string('search')->trim()->toString();

        $media = Media::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('synopsis', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('type'), fn ($query) => $query
                ->where('type', $request->string('type')->lower()->toString()))
            ->orderBy($sort, $direction)
            ->get();

        return MediaResource::collection($media);
    }

    public function store(StoreMediaRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] ??= Str::slug($validated['title']);

        $media = Media::create($validated);

        return (new MediaResource($media))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Media $media): MediaResource
    {
        return new MediaResource($media);
    }

    public function update(UpdateMediaRequest $request, Media $media): MediaResource
    {
        $media->update($request->validated());

        return new MediaResource($media);
    }

    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json([
            'message' => 'Media berhasil dihapus.',
        ]);
    }
}
