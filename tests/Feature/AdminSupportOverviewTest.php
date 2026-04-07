<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSupportOverviewTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.supportoverview
     * 
     */
    public function can_open_support_overview_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.supportoverview_index'));   
        $response->assertOk();
    }
}
