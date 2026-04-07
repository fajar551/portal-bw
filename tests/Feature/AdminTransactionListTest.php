<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTransactionListTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.transactionlist
     * 
     */
    public function can_open_transaction_list_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.transactionlist'));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.transactionlist
     * 
     */
    public function admin_can_add_transaction()
    {
        $response = $this->actingAs($this->admin, 'admin')
        ->followingRedirects()
        ->post(route("admin.TransactionStore"), [
            'amountin' => 10,
            'amountout' => 20,
            'paymentmethod' => '',
        ]);

        $response->assertStatus(200);
    }
}
