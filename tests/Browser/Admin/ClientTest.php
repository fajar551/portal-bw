<?php

namespace Tests\Browser\Admin;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class ClientTest extends DuskTestCase
{
    /**
     * @test
     */
    public function admin_can_add_new_client()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(Admin::find(1))
            ->assertAuthenticated('admin');
                // ->visitRoute('admin.pages.clients.addnewclient.index')
                // ->assertSee('Add New Client');

            // $browser->visitRoute('admin.pages.clients.addnewclient.index')
            //     ->type('firstname', 'Dusk')
            //     ->type('lastname', 'User')
            //     ->type('companyname', 'Dusk Company Name')
            //     ->type('email', 'dusk@testing.com')
            //     ->type('password', Hash::make('password'))
            //     ->type('address1', 'address1')
            //     ->type('address2', 'address2')
            //     ->type('city', 'city')
            //     ->type('state', 'state')
            //     ->select('country', 'ID')
            //     ->type('phonenumber', '+6282118309114')
            //     ->type('phonenumber', '+6282118309114')
            //     ->select('clientstatus', 'Active')
            //     ->select('currency', '1')
            //     ->check('latefeeoveride')
            //     ->check('overideduenotices')
            //     ->check('taxexempt')
            //     ->check('separateinvoices')
            //     ->check('disableautocc')
            //     ->check('marketing_emails_opt_in')
            //     ->check('overrideautoclose')
            //     ->check('allow_sso')
            //     ->check('email_preferences[general]')
            //     ->check('email_preferences[invoice]')
            //     ->check('email_preferences[support]')
            //     ->check('email_preferences[product]')
            //     ->check('email_preferences[domain]')
            //     ->check('email_preferences[affiliate]')
            //     ->type('notes', 'Insert from dusk test')
            //     ->press('Save Changes');
        });
    }
}
