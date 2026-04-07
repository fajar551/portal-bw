<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAnnouncementsTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.announcements
     * 
     */
    public function can_open_announcements_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.announcements_index'));   
        $response->assertOk();
    }
}
