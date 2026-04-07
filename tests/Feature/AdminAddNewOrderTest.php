<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAddNewOrderTest extends TestCase
{
   
    /**
     * @test
     * @group admin
     * @group admin.orders
     * 
     */
    public function can_open_add_new_order_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.orders.addneworder.index'));   
        $response->assertOk();
    }

    /**
     * @testX
     * @group admin
     * @group admin.orders
     * 
     */
    public function can_add_order()
    {
        $formData = [
            "promocode" => "", 
            "orderstatus" => "Pending", 
            "pid" => ["1"], 
            "domain" => [], 
            "billingcycle" => ["Monthly"], 
            "qty" => [], 
            "priceoverride" => [], 
            "regaction" => [], 
            "regdomain" => [], 
            "regperiod" => [], 
            "eppcode" => [], 
            "domainpriceoverride" => [], 
            "domainrenewoverride" => [], 
        ];
        $params = [
            "action" => "submitorder", 
            "calconly" => false,
        ];
        $post = array_merge($formData, $params);
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.pages.orders.addneworder.actionCommand'));
        $response->assertOk();
    }
}
