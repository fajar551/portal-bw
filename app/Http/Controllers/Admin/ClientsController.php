<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers

// Models

// Traits

class ClientsController extends Controller
{

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }
  
    public function ViewClients_clientmerge()
    {
        return view('pages.clients.viewclients.clientmerge.index');
    }
    public function ViewClients_clientservices()
    {
        return view('pages.clients.viewclients.clientservices.index');
    }
    public function ViewClients_clientdomain()
    {
        return view('pages.clients.viewclients.clientdomain.index');
    }
    public function ViewClients_clientbillableitems()
    {
        return view('pages.clients.viewclients.clientbillableitems.index');
    }
    public function ViewClients_clienttickets()
    {
        return view('pages.clients.viewclients.clienttickets.index');
    }
    public function ViewClients_clientnotes()
    {
        return view('pages.clients.viewclients.clientnotes.index');
    }
    public function ViewClients_clientlog()
    {
        return view('pages.clients.viewclients.clientlog.index');
    }
    



    public function MassMail()
    {
        return view('pages.clients.massmail.index');
    }
    public function MassMail_composemessage()
    {
        return view('pages.clients.massmail.composemessage.index');
    }

}
