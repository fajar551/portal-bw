<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminListAllOrderTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.orders
     * 
     */
    public function can_open_list_orders_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.orders.listallorders.index'));   
        $response->assertOk();
    }
}
