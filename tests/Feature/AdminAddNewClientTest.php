<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminAddNewClientTest extends TestCase
{
    use WithFaker, DatabaseTransactions;
    
    /**
     * @test
     * @group admin
     * @group admin.addnewclient
     * 
     */
    public function can_see_form_add_new_client()
    {
        $response = $this->actingAs($this->admin, 'admin')
        ->get(route('admin.pages.clients.addnewclient.index'));
        $response->assertStatus(200);
    }

    /**
     * @test
     * @group admin
     * @group admin.addnewclient
     * 
     */
    public function admin_can_add_new_client()
    {
        $response = $this->actingAs($this->admin, 'admin')
        ->followingRedirects()
        ->post(route("admin.pages.clients.addnewclient.create"), [
            'firstname' => $this->faker->firstNameMale(),
            'lastname' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'password' => Hash::make('password'),
            'address1' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postcode' => $this->faker->postcode(),
            'country' => $this->faker->countryCode(),
            'phonenumber' => $this->faker->e164PhoneNumber(),
            'language' => 'english',
            'clientstatus' => 'Active',
            'currency' => 1,

            // optional but must be there
            'companyname' => $this->faker->name(),
            'securityqid' => '',
            'securityqans' => '',
            'tax_id' => '',
            'address2' => '',
            'paymentmethod' => '',
            'billingcid' => '',
            'latefeeoveride' => '',
            'overideduenotices' => '',
            'taxexempt' => '',
            'separateinvoices' => '',
            'disableautocc' => '',
            'marketing_emails_opt_in' => '',
            'overrideautoclose' => '',
            'allow_sso' => '',
            'notes' => '',
            'twofaenabled' => '',
            'email_preferences' => [],
            'groupid' => 0,
            'credit' => '',
        ]);

        $response->assertStatus(200);
    }
}
