 <div class="row">
     <div class="col-lg-12 d-lg-none d-sm-block">
         <button class="btn btn-primary-cust btn-block mb-3 d-flex align-items-center" type="button"
             data-toggle="collapse" data-target="#collapseExample" aria-expanded="false"
             aria-controls="collapseExample">
             <i class="ri-menu-line mr-2"></i>Menu
         </button>
         <div class="collapse hidden-menu-tab" id="collapseExample">
             <div class="card card-body">
                 <a class="{{ Request::is("admin/clients/clientsummary") || Request::is('admin/clients/clientsummary/*') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientsummary?userid=" .($userid ?? "0") ) }}">Summary</a>
                 <a class="{{ Request::is("admin/clients/clientprofile") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientprofile?userid=" .($userid ?? "0") ) }}">Profile</a>
                 <a class="nav-link {{ Request::is("admin/clients/clientcontacts") || Request::is("admin/clients/clientcontacts/*") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientcontacts?userid=" .($userid ?? "0") ) }}">Contacts</a>
                 <a class="nav-link {{ Request::is("admin/clients/clientservices") || Request::is('admin/clients/clientservices/*') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientservices?userid=" .($userid ?? "0") ) }}">Product/Services</a>
                 <a class="nav-link {{ Request::is("admin/clients/clientdomain") || Request::is("admin/clients/clientdomain/*") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientdomain?userid=" .($userid ?? "0") ) }}">Domain</a>
                 {{-- <a class="nav-link {{ Request::is("admin/clients/clientbillableitems") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientbillableitems?userid=" .($userid ?? "0") ) }}">Billable Items</a> --}}
                 <a class="nav-link {{ Request::is("admin/clients/clientinvoices") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientinvoices?userid=" .($userid ?? "0") ) }}">Invoices</a>
                 {{-- <a class="nav-link {{ Request::is("admin/clients/clientquotes") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientquotes?userid=" .($userid ?? "0") ) }}">Quotes</a> --}}
                 <a class="nav-link {{ Request::is("admin/clients/clienttransactions") || Request::is("admin/clients/clienttransactions/*") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clienttransactions?userid=" .($userid ?? "0") ) }}">Transaction</a>
                 <a class="nav-link {{ Request::is("admin/clients/clienttickets") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clienttickets?userid=" .($userid ?? "0") ) }}">Tickets</a>
                 <a class="nav-link {{ Request::is("admin/clients/clientemails") || Request::is("admin/clients/clientemails/*") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientemails?userid=" .($userid ?? "0") ) }}">Emails</a>
                 <a class="nav-link {{ Request::is("admin/clients/clientnotes") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientnotes?userid=" .($userid ?? "0") ) }}">Notes ({{ $notesCount ?? 0 }})</a>
                 <a class="nav-link {{ Request::is("admin/clients/clientlog") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientlog?userid=" .($userid ?? "0") ) }}">Log</a>
             </div>
         </div>
     </div>
 </div>
 <div class="row">
     <div class="col-12 d-none d-sm-block">
         <ul class="nav nav-tabs">
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientsummary') || Request::is('admin/clients/clientsummary/*') ? 'active' : '' }}"
                    href="{{ url("admin/clients/clientsummary?userid=" .($userid ?? "0") ) }}">Summary</a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientprofile') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientprofile?userid=" .($userid ?? "0") ) }}">Profile</a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientcontacts') || Request::is("admin/clients/clientcontacts/*") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientcontacts?userid=" .($userid ?? "0") ) }}">Contacts</a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientservices') || Request::is('admin/clients/clientservices/*') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientservices?userid=" .($userid ?? "0") ) }}">Product/Services</a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientdomain') || Request::is("admin/clients/clientdomain/*") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientdomain?userid=" .($userid ?? "0") ) }}">Domain</a>
             </li>
             {{-- <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientbillableitems') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientbillableitems?userid=" .($userid ?? "0") ) }}">Billable Items</a>
             </li> --}}
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientinvoices') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientinvoices?userid=" .($userid ?? "0") ) }}">Invoices</a>
             </li>
             {{-- <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientquotes') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientquotes?userid=" .($userid ?? "0") ) }}">Quotes</a>
             </li> --}}
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clienttransactions') || Request::is("admin/clients/clienttransactions/*") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clienttransactions?userid=" .($userid ?? "0") ) }}">Transaction</a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clienttickets') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clienttickets?userid=" .($userid ?? "0") ) }}">Tickets</a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientemails') || Request::is("admin/clients/clientemails/*") ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientemails?userid=" .($userid ?? "0") ) }}">Emails</a>
             </li>
             <li class="nav-item hide-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientnotes') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientnotes?userid=" .($userid ?? "0") ) }}">Notes ({{ $notesCount ?? 0 }})</a>
             </li>
             <li class="nav-item hide-item">
                 <a class="nav-link {{ Request::is('admin/clients/clientlog') ? 'active' : '' }}"
                     href="{{ url("admin/clients/clientlog?userid=" .($userid ?? "0") ) }}">Log</a>
             </li>
             <div class="dropdown hidetab ml-auto">
                 <a class="btn btn-light dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                     data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <i class="ri-arrow-down-s-line"></i>
                 </a>
                 <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left"
                     aria-labelledby="dropdownMenuLink">
                     {{-- <a class="dropdown-item {{ Request::is('admin/clients/clienttickets') ? 'active' : '' }}"
                         href="{{ url("admin/clients/clienttickets?userid=" .($userid ?? "0") ) }}">Tickets</a>
                     <a class="dropdown-item {{ Request::is('admin/clients/clientemails') ? 'active' : '' }}"
                         href="{{ url("admin/clients/clientemails?userid=" .($userid ?? "0") ) }}">Emails</a> --}}
                     <a class="dropdown-item {{ Request::is('admin/clients/clientnotes') ? 'active' : '' }}"
                         href="{{ url("admin/clients/clientnotes?userid=" .($userid ?? "0") ) }}">Notes</a>
                     <a class="dropdown-item {{ Request::is('admin/clients/clientlog') ? 'active' : '' }}"
                         href="{{ url("admin/clients/clientlog?userid=" .($userid ?? "0") ) }}">Log</a>
                 </div>
             </div>
         </ul>
     </div>
 </div>

{{-- Sticky Notes Section --}}
@if(isset($clientNotes) && count($clientNotes) > 0)
<div class="row mb-4 mt-4">
    <div class="col-12">
        @foreach($clientNotes as $note)
        <div class="card border-warning mb-3">
            <div class="card-header text-white" style="background: #fef1d5">
                <small class="text-muted">
                    {{ (new App\Helpers\Functions)->fromMySQLDate($note->created, "time") }}
                </small>
            </div>
            <div class="card-body">
                <p class="mb-1">{!! Markdown::convertToHtml($note->note) !!}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<script>
    console.log('Client Notes Available:', @json($clientNotes ?? []));
</script>