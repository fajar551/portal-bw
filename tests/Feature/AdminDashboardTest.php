<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AdminDashboardTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.dashboard
     * 
     */
    public function can_see_dashboard()
    {
        $response = $this->actingAs($this->admin, 'admin')
                         ->get('/admin/dashboard');
        
        $response->assertSeeText('Dashboard');
    }

    /**
     * @test
     * @group admin
     * @group admin.dashboard
     * 
     */
    public function can_see_pending_orders()
    {
        $response = $this->actingAs($this->admin, 'admin')
                         ->get('/admin/dashboard');
        
        $response->assertSeeText('Pending Orders');
    }

    /**
     * @test
     * @group admin
     * @group admin.dashboard
     * 
     */
    public function can_see_awaiting_reply()
    {
        $response = $this->actingAs($this->admin, 'admin')
                         ->get('/admin/dashboard');
        
        $response->assertSeeText('Awaiting Reply');
    }

    /**
     * @test
     * @group admin
     * @group admin.dashboard
     * 
     */
    public function can_see_pending_cancellations()
    {
        $response = $this->actingAs($this->admin, 'admin')
                         ->get('/admin/dashboard');
        
        $response->assertSeeText('Pending Cancellation(s)');
    }

    /**
     * @test
     * @group admin
     * @group admin.dashboard
     * 
     */
    public function can_see_pending_module_actions()
    {
        $response = $this->actingAs($this->admin, 'admin')
                         ->get('/admin/dashboard');
        
        $response->assertSeeText('Pending Module Actions');
    }
}
