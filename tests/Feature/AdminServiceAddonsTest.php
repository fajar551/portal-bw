<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminServiceAddonsTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.serviceaddons
     * 
     */
    public function can_open_service_addons_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.serviceaddons.index'));   
        $response->assertOk();
    }
}
