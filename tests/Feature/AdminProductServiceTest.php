<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminProductServiceTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.productservices
     * 
     */
    public function can_open_service_list_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.setup.prodsservices.productservices.index', ['serviceType' => 'index']));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.productservices
     * 
     */
    public function can_open_shared_hosting_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.setup.prodsservices.productservices.index', ['serviceType' => 'sharedhosting']));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.productservices
     * 
     */
    public function can_open_reseller_account_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.setup.prodsservices.productservices.index', ['serviceType' => 'reselleraccount']));   
        $response->assertOk();
    }
    
    /**
     * @test
     * @group admin
     * @group admin.productservices
     * 
     */
    public function can_open_vps_servers_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.setup.prodsservices.productservices.index', ['serviceType' => 'vpsservers']));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.productservices
     * 
     */
    public function can_open_other_services_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.setup.prodsservices.productservices.index', ['serviceType' => 'otherservices']));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.productservices
     * 
     */
    public function can_not_open_not_found_product_services_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.setup.prodsservices.productservices.index', ['serviceType' => 'xxx']));   
        $response->assertStatus(200);
    }
}
