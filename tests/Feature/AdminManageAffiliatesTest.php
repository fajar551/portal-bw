<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AdminManageAffiliatesTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.manageaffiliates
     * 
     */
    public function can_open_manage_affiliates_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.manageaffiliates.index'));   
        $response->assertOk();
    }
}
