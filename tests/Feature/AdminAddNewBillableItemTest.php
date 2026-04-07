<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAddNewBillableItemTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.billable
     * 
     */
    public function can_open_add_new_billable_item_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.billing.billableitems.add'));   
        $response->assertOk();
    }
}
