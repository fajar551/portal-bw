<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminBillableItemListTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.billable
     * 
     */
    public function can_open_billable_item_list_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.billing.billableitems.index'));   
        $response->assertOk();
    }
}
