<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminInvoicesTest extends TestCase
{
    /**
     * @test
     * @group admin
     * @group admin.invoices
     * 
     */
    public function can_open_invoices_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.invoicesIndex'));   
        $response->assertOk();
    }
}
