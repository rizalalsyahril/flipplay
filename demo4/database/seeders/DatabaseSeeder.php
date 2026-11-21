<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $mediaItems = collect([
            [
                'title' => 'Demon Slayer: Kimetsu no Yaiba - Infinity Castle',
                'synopsis' => 'Tanjiro dan para pembasmi iblis menghadapi Muzan dalam pertarungan di Infinity Castle.',
                'genres' => ['Action', 'Fantasy'],
                'episodes' => 1,
                'year' => 2025,
                'type' => 'anime',
            ],
            [
                'title' => 'Your Name',
                'synopsis' => 'Dua remaja yang tidak saling mengenal mengalami pertukaran tubuh dan berusaha menemukan satu sama lain.',
                'genres' => ['Romance', 'Fantasy'],
                'episodes' => 1,
                'year' => 2016,
                'type' => 'anime',
            ],
            [
                'title' => 'Weathering with You',
                'synopsis' => 'Seorang remaja bertemu gadis yang memiliki kemampuan untuk menghentikan hujan.',
                'genres' => ['Romance', 'Fantasy', 'Drama'],
                'episodes' => 1,
                'year' => 2019,
                'type' => 'anime',
            ],
        ])->map(function (array $attributes): Media {
            return Media::query()->updateOrCreate(
                ['slug' => Str::slug($attributes['title'])],
                $attributes + [
                    'anilist_id' => null,
                    'cover_url' => null,
                    'trailer_url' => null,
                    'score' => null,
                ],
            );
        })->values();

        User::query()
            ->whereIn('email', ['test@example.com', 'demo2@example.com', 'demo3@example.com'])
            ->delete();

        $user = User::query()->updateOrCreate(
            ['email' => 'rizal@example.com'],
            ['name' => 'Rizal', 'password' => 'password'],
        );

        $user->media()->syncWithoutDetaching([
            $mediaItems[0]->id => ['status' => 'planned', 'progress' => 0],
        ]);
    }
}
