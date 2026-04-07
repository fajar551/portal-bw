<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminConsoleTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.console
     * 
     */
    public function CreateDummyAdminUsingConsole()
    {
        $this->artisan('admin:generate')
        ->expectsOutput('Admin DUMY generated. Email: admin@admin.com Password: password')
        ->assertExitCode(0);
    }

    /**
     * @test
     * @group admin
     * @group admin.console
     * 
     */
    public function can_create_admin_permission_console()
    {
        $this->artisan('adminpermissions:generate')
        ->assertExitCode(0);
    }
}
