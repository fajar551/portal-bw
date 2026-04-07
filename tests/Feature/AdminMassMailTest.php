<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminMassMailTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    /**
     * @test
     * @group admin
     * @group admin.massmail
     * 
     */
    public function can_open_mass_mail_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.massmail.index'));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.massmail
     * 
     */
    public function can_open_mass_mail_send_message_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->post(route('admin.pages.clients.massmail.sendmessage'), [
                            "type" => "massmail",
                            "clientcountry" => [
                                "ID"
                            ],
                            "clientstatus" => [
                                "Active",
                                "Inactive",
                                "Closed"
                            ],
                        ]);   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.massmail
     * 
     */
    public function can_send_message()
    { 
        $response = $this->actingAs($this->admin, 'admin')
        ->postJson(route('admin.pages.clients.massmail.send'), [
            'message' => $this->faker->paragraph(),
            'subject' => $this->faker->sentence(),
            'fromname' => $this->faker->name(),
            'fromemail' => $this->faker->email(),
            'recipients' => 1,

            'type' => 'general',

            // 'save' => '',
            // 'savename' => '',
            // 'attachment' => '',
        ]);

        $response->assertStatus(200)->assertJson(['result' => 'success']);
    }

    /**
     * @test
     * @group admin
     * @group admin.massmail
     * 
     */
    public function can_load_message()
    { 
        $response = $this->actingAs($this->admin, 'admin')
        ->postJson(route('admin.pages.clients.massmail.loadmessage'), [
            'id' => $this->client->id,
            'messagename' => 'Client Signup Email',
        ]);

        $response->assertStatus(200)->assertJson(['result' => 'success']);
    }

    /**
     * @test
     * @group admin
     * @group admin.massmail
     * 
     */
    public function can_preview_message()
    { 
        $response = $this->actingAs($this->admin, 'admin')
        ->postJson(route('admin.pages.clients.massmail.preview'));

        $response->assertStatus(200)->assertJson(['result' => 'success']);
    }
}
