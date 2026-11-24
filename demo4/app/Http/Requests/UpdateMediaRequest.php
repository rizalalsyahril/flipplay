<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateMediaRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $media = $this->route('media');
        $mediaId = $media instanceof Media ? $media->getKey() : $media;

        return [
            'anilist_id' => ['sometimes', 'nullable', 'integer', 'min:1', Rule::unique('media', 'anilist_id')->ignore($mediaId)],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('media', 'slug')->ignore($mediaId)],
            'type' => ['sometimes', 'required', 'string', Rule::in(['anime', 'drama', 'manga', 'novel'])],
            'cover_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'trailer_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'synopsis' => ['sometimes', 'nullable', 'string'],
            'episodes' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'year' => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:2100'],
            'score' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:10'],
            'genres' => ['sometimes', 'nullable', 'array'],
            'genres.*' => ['string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny([
                'anilist_id',
                'title',
                'slug',
                'type',
                'cover_url',
                'trailer_url',
                'synopsis',
                'episodes',
                'year',
                'score',
                'genres',
            ])) {
                $validator->errors()->add('request', 'Kirim minimal satu field untuk diperbarui.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'anilist_id.unique' => 'ID AniList sudah digunakan.',
            'slug.unique' => 'Slug media sudah digunakan.',
        ];
    }
}
