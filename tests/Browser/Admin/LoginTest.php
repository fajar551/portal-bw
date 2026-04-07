<?php

namespace Tests\Browser\Admin;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Admin;

class LoginTest extends DuskTestCase
{
    public function testCreateDummyAdminUsingConsole()
    {
        $this->artisan('admin:generate')
        ->expectsOutput('Admin DUMY generated. Email: admin@admin.com Password: password')
        ->assertExitCode(0);
    }

    public function testOpenAdminLoginPage()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function created_admin_can_login()
    {
        $this->browse(function (Browser $browser) {
            $user = Admin::find(1);
            $browser->visit('/admin/login')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/admin')
                    ->screenshot('loginadmin');
        });
    }
}
