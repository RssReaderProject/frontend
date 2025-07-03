<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthenticationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test the complete authentication flow.
     */
    public function test_complete_authentication_flow(): void
    {
        $this->browse(function (Browser $browser) {
            // Test registration
            $browser->visit('/register')
                    ->assertSee('Register')
                    ->type('name', 'Test User')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Register')
                    ->waitForLocation('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->assertSee('Dashboard')
                    ->assertSee('Test User');

            // Test logout
            $browser->click('.dropdown-toggle')
                    ->waitFor('.dropdown-menu')
                    ->click('.dropdown-item.text-danger')
                    ->waitForLocation('/login')
                    ->assertPathIs('/login')
                    ->assertDontSee('Test User');

            // Test login
            $browser->visit('/login')
                    ->assertSee('Log in')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Log in')
                    ->waitForLocation('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->assertSee('Dashboard')
                    ->assertSee('Test User');
        });
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we have a clean database
        $this->artisan('migrate:fresh');
    }

    /**
     * Test that unauthenticated users are redirected to login.
     */
    public function test_unauthenticated_users_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                    ->waitForLocation('/login')
                    ->assertPathIs('/login')
                    ->assertSee('Log in');
        });
    }

    /**
     * Test registration validation.
     */
    public function test_registration_validation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->assertSee('Register')
                    // Remove required attributes so we can test server-side validation
                    ->script("document.querySelectorAll('[required]').forEach(e => e.removeAttribute('required'));");
            $browser->press('Register')
                    ->waitForLocation('/register')
                    ->waitForText('The name field is required.')
                    ->assertSee('The email field is required.')
                    ->assertSee('The password field is required.');
        });
    }

    /**
     * Test login validation.
     */
    public function test_login_validation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertSee('Log in')
                    // Remove required attributes so we can test server-side validation
                    ->script("document.querySelectorAll('[required]').forEach(e => e.removeAttribute('required'));");
            $browser->press('Log in')
                    ->waitForLocation('/login')
                    ->waitForText('The email field is required.')
                    ->assertSee('The password field is required.');
        });
    }

    /**
     * Test invalid login credentials.
     */
    public function test_invalid_login_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertSee('Log in')
                    ->type('email', 'invalid@example.com')
                    ->type('password', 'wrongpassword')
                    ->press('Log in')
                    ->waitForText('These credentials do not match our records.')
                    ->assertSee('These credentials do not match our records.');
        });
    }
} 