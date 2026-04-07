<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.login
     * 
     */
    public function CanOpenAdminLoginPage()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @group admin
     * @group admin.login
     * 
     */
    public function created_admin_can_login()
    {
        $response = $this->actingAs($this->admin, 'admin')
                         ->get('/admin');
        
        $response->assertSeeText('Dashboard');
    }
}
