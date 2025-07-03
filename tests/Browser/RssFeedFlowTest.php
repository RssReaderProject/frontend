<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Carbon\Carbon;

class RssFeedFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test the full RSS feed flow for an existing user.
     */
    public function test_rss_feed_flow(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'rssuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Mock the microservice POST endpoint
        Http::fake([
            'http://localhost:8080/rss' => Http::response([
                'items' => [
                    [
                        'title' => 'Example RSS Item Title 1',
                        'link' => 'http://example.com/article1',
                        'description' => 'This is the first example RSS item for testing purposes.',
                        'publish_date' => now()->toDateTimeString(),
                        'rss_url' => 'http://example.com/feed.xml',
                    ],
                    [
                        'title' => 'Example RSS Item Title 2',
                        'link' => 'http://example.com/article2',
                        'description' => 'This is the second example RSS item for testing purposes.',
                        'publish_date' => now()->subDay()->toDateTimeString(),
                        'rss_url' => 'http://example.com/feed.xml',
                    ],
                ],
            ], 200, ['Content-Type' => 'application/json']),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            // Login
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password123')
                ->press('Log in')
                ->waitForLocation('/rss/urls')
                ->assertPathIs('/rss/urls')
                ->assertSee($user->name);

            // Add an RSS URL
            $browser->visitRoute('rss.urls.create')
                ->type('url', 'http://example.com/feed.xml')
                ->press('Add RSS URL')
                ->waitForText('RSS URL created successfully.')
                ->assertSee('RSS URL created successfully.');

            // Manually trigger RSS fetching, since in the app this happens after the response
            // and would not use the HTTP fake in this test process otherwise.
            app(\App\Services\RssFetcherService::class)->fetchForUser($user);

            // Go to RSS items list
            $browser->visitRoute('rss.items.index')
                ->waitForText('Posts')
                ->assertSee('Posts')
                ->assertSee('Example RSS Item Title 1')
                ->assertSee('Example RSS Item Title 2');

            // Filter by current date
            $today = Carbon::now()->format('Y-m-d');
            $browser->waitFor('#date')
                ->value('#date', $today)
                ->pause(1000)
                ->press('Apply Filters')
                ->pause(2000)
                ->assertSee('Example RSS Item Title 1')
                ->assertDontSee('Example RSS Item Title 2'); // Assume only one matches

            // Go back to RSS URLs and delete the entry
            $browser->visitRoute('rss.urls.index')
                ->waitForText('http://example.com/feed.xml')
                ->click('button[title="Delete"]') // Click the delete button
                ->acceptDialog() // Accept the browser confirmation dialog
                ->waitForText('RSS URL deleted successfully.')
                ->assertSee('RSS URL deleted successfully.');

            // Go back to items list, all items should be gone
            $browser->visitRoute('rss.items.index')
                ->waitForText('No posts found')
                ->assertSee('No posts found');
        });
    }
} 