<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AdminCancellationRequestTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.cancellationrequests
     * 
     */
    public function can_open_cancellation_requests_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.cancellationrequests.index'));   
        $response->assertOk();
    }
}
