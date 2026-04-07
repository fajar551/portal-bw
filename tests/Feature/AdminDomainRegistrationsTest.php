<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AdminDomainRegistrationsTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.domainregistrations
     * 
     */
    public function can_open_domain_registrations_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.domainregistrations.index'));   
        $response->assertOk();
    }
}
