<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class AdminListAllClientTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    /**
     * @test
     * @group admin
     * @group admin.viewclients
     * 
     */
    public function can_open_view_clients_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.index'));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientsummary
     * 
     * harus pake trait ini biar ke tes:
     * use Illuminate\Foundation\Testing\DatabaseTransactions;
     * 
     * dan harus buat model factory nya dulu:
     * php artisan make:factory ClientFactory --model=Models/Client
     */
    public function can_open_client_summary_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientprofile
     * 
     */
    public function can_open_client_profile_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientprofile.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientprofile
     * 
     */
    public function can_update_profile()
    {
        $client = $this->client;
        $response = $this->actingAs($this->admin, 'admin')
        ->followingRedirects()
        ->post(route('admin.pages.clients.viewclients.clientprofile.update'), [
            'userid' => $client->id,
            'companyname' => '',
            'securityqid' => '',
            'securityqans' => '',
            'tax_id' => '',
            'address2' => '',
            'paymentmethod' => '',
            'billingcid' => '',
            'language' => '',
            'clientstatus' => '',
            'currency' => '',
            'groupid' => '',
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
        ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientcontacts
     * 
     */
    public function can_open_client_contacts_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientcontacts.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientcontacts
     * 
     */
    public function can_add_new_contact()
    {
        $client = $this->client;
        $response = $this->actingAs($this->admin, 'admin')
        ->followingRedirects()
        ->post(route('admin.pages.clients.viewclients.clientcontacts.create'), [
            'userid' => $client->id,
            'companyname' => 'Company Name',
            'tax_id' => '',
            'address2' => '',
            'email_preferences' => null,
            'permissions' => null,

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
        ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientservices
     * 
     */
    public function can_open_client_services_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientdomain
     * 
     */
    public function can_open_client_domains_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientdomain.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientinvoices
     * 
     */
    public function can_open_client_invoices_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientinvoices.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clienttransactions
     * 
     */
    public function can_open_client_transactions_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clienttransactions.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clienttickets
     * 
     */
    public function can_open_client_tickets_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clienttickets.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientemails
     * 
     */
    public function can_open_client_emails_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientemails.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientnotes
     * 
     */
    public function can_open_client_notes_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientnotes.index', ['userid' => $client->id]));   
        $response->assertOk();
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientnotes
     * 
     */
    public function can_add_new_note()
    {
        $client = $this->client;
        $response = $this->actingAs($this->admin, 'admin')
        ->followingRedirects()
        ->post(route('admin.pages.clients.viewclients.clientnotes.store'), [
            'userid' => $client->id,
            'note' => 'new note',
            'sticky' => false,
        ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     * @group admin
     * @group admin.viewclients.clientlog
     * 
     */
    public function can_open_client_logs_tab()
    {
        $client = $this->client;
        // DEBUG: uncomment kode dibawah
        // \Log::debug($client);

        $response = $this->actingAs($this->admin, 'admin')
                        ->get(route('admin.pages.clients.viewclients.clientlog.index', ['userid' => $client->id]));   
        $response->assertOk();
    }
}
