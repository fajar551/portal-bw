<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminGatewayLogTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.gatewaylog
     * 
     */
    public function can_open_gateway_log_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.billing.gatewaylog.index'));   
        $response->assertOk();
    }
}
