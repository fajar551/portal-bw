<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminSupportTicketsTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     * @group admin
     * @group admin.supporttickets
     * 
     */
    public function can_open_support_tickets_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.support.supporttickets.indexa'));   
        $response->assertOk();
    }
}
