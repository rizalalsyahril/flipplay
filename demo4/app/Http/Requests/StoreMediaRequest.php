<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $title = $this->input('title');

        if (! $this->filled('slug') && is_string($title) && trim($title) !== '') {
            $this->merge(['slug' => Str::slug($title)]);
        }
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'anilist_id' => ['nullable', 'integer', 'min:1', 'unique:media,anilist_id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:media,slug'],
            'type' => ['required', 'string', Rule::in(['anime', 'drama', 'manga', 'novel'])],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'trailer_url' => ['nullable', 'string', 'max:2048'],
            'synopsis' => ['nullable', 'string'],
            'episodes' => ['nullable', 'integer', 'min:1'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['string', 'max:50'],
        ];
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
