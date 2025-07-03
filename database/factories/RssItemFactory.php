<?php

namespace Database\Factories;

use App\Models\RssUrl;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RssItem>
 */
class RssItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sources = [
            'TechCrunch' => 'https://techcrunch.com',
            'The Verge' => 'https://theverge.com',
            'Ars Technica' => 'https://arstechnica.com',
            'Wired' => 'https://wired.com',
            'Engadget' => 'https://engadget.com',
        ];

        $source = $this->faker->randomElement(array_keys($sources));
        $sourceUrl = $sources[$source];

        return [
            'user_id' => User::factory(),
            'rss_url_id' => null, // Default to null, can be set explicitly
            'title' => $this->faker->sentence(),
            'source' => $source,
            'source_url' => $sourceUrl,
            'link' => $this->faker->url(),
            'publish_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'description' => $this->faker->paragraph(),
        ];
    }

    /**
     * Indicate that the RSS item is from a specific source.
     */
    public function fromSource(string $source, string $sourceUrl): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => $source,
            'source_url' => $sourceUrl,
        ]);
    }

    /**
     * Indicate that the RSS item belongs to a specific RSS URL.
     */
    public function forRssUrl(RssUrl $rssUrl): static
    {
        return $this->state(fn (array $attributes) => [
            'rss_url_id' => $rssUrl->id,
            'user_id' => $rssUrl->user_id,
        ]);
    }

    /**
     * Indicate that the RSS item is recent (last 7 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'publish_date' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the RSS item belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
