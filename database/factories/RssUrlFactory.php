<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RssUrl>
 */
class RssUrlFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domains = [
            'example.com',
            'blog.example.com',
            'news.example.com',
            'tech.example.com',
            'feed.example.com',
        ];

        $domain = $this->faker->randomElement($domains);
        $feedTypes = [
            'feed.xml',
            'rss.xml',
            'atom.xml',
            'feed.rss',
            'rss/feed.xml',
        ];

        return [
            'url' => 'https://'.$domain.'/'.$this->faker->randomElement($feedTypes),
        ];
    }
}
