@extends('layouts.clientbase')

@section('title')
   Contact / Sub-Account
@endsection

@section('page-title')
   Contact / Sub-Account
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         <div class="row pb-3">
            <div class="col-xl-8 col-lg-8">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ url('home') }}">Dashboard</a> <span
                        class="text-muted"> / Contact Sub Account</span></h6>
               </div>
            </div>
            <div class="col-xl-4 col-lg-4">
               <div class="pull-right">
                  <a href="#tambah-kontak" class="btn btn-success-qw" data-toggle="modal"><i class="feather-plus"></i>
                     Add Contact</a>
               </div>
            </div>
         </div>

         <div class="row">

            <div class="col-xl-12 col-lg-12">
               <div class="card">
                  @if (session('success'))
                     <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <h5>New Contact Created!</h5>
                        <p class="m-0">{!! session('success') !!}</p>
                     </div>
                  @endif
                  @if ($errors->any())
                     <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                        <h5>Something Wrong!</h5>
                        <ul class="m-0 p-0 list-unstyled">
                           @foreach ($errors->all() as $error)
                              <li><small>{{ $error }}</small></li>
                           @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  @endif
                  <div class="card-body">
                     <h4 class="card-title mb-3">Contact / Sub-Account List</h4>
                     <table id="contactsSq" class="table">
                        <thead>
                           <tr>
                              {{-- <th>No</th> --}}
                              <th>No. User</th>
                              <th>Name</th>
                              <th>Company Name</th>
                              <th>Email</th>
                              <th>Phone</th>
                              <th>Actions</th>
                           </tr>
                        </thead>
                        <tbody>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
         <!-- end row-->

      </div> <!-- container-fluid -->
   </div>

   <div class="modal fade" id="tambah-kontak" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
      aria-hidden="true" data-keyboard="false" data-backdrop="static">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-add-contact" role="document">

         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalCenterTitle">Add New Contact</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <form action="{{ route('pages.profile.contactsub.create') }}" method="POST" id="contact-form"
               class="needs-validation" novalidate>
               @csrf
               <div class="modal-body">
                  <div class="row m-1">
                     <div class="col-lg-12">
                        <h5 class="mt-2 text-qw">Personal Data</h5>
                        <hr />
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">First Name</label>
                           <input type="text" id="namadepan" name="firstname" class="form-control" required>
                           <div class="invalid-feedback">First Name is required</div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">Last Name</label>
                           <input type="text" id="namabelakang" name="lastname" class="form-control" required>
                           <div class="invalid-feedback">Last Name is required</div>
                        </div>
                     </div>
                     <div class="col-lg-12">
                        <div class="form-group">
                           <label for="simpleinput">Company Name</label>
                           <input type="text" id="namaperusahaan" name="companyname" class="form-control" required>
                           <div class="invalid-feedback">Company Name is required</div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">Email</label>
                           <input type="email" id="email" name="email" class="form-control" required>
                           <div class="invalid-feedback">Correct email format is required e.g (your@mail.com)</div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">Phone</label>
                           <input type="text" id="telepon" name="phonenumber" class="form-control" required>
                           <div class="invalid-feedback">Phone Number is required</div>
                        </div>
                     </div>
                  </div>

                  <div class="row m-1">
                     <div class="col-lg-12">
                        <h5 class="mt-2 text-qw">Address</h5>
                        <hr />
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">Address 1</label>
                           <input type="text" id="alamat" name="address1" class="form-control" required>
                           <div class="invalid-feedback">Main Address is required</div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">Address 2</label>
                           <input type="text" id="alamat2" name="address2" class="form-control">
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">City</label>
                           <input type="text" id="kota" name="city" class="form-control" required>
                           <div class="invalid-feedback">City is required</div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">Province/Region</label>
                           <input type="text" id="provinsi" name="state" class="form-control" required>
                           <div class="invalid-feedback">Region/State is required</div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">Postal Code</label>
                           <input type="number" id="kodepos" name="postcode" class="form-control" required>
                           <div class="invalid-feedback">Postal Code is required</div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="simpleinput">Country</label>
                           <select class="form-control" name="country" id="negara" required>
                              @foreach ($countries as $country)
                                 <option value="{{ $country['id'] }}">
                                    {{ $country['name'] }}</option>
                                 {{-- {{ $auth->country == $country['id'] ? 'selected' : '' }} --}}
                              @endforeach
                           </select>
                           <div class="invalid-feedback">Country is required</div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="form-group">
                           <label for="tax_id">Tax ID</label>
                           <input type="number" id="tax_id" class="form-control" name="tax_id" autocomplete="off">
                        </div>
                     </div>
                     <div class="col-lg-6 d-flex align-items-center">
                        <div class="form-group">
                           <div class="custom-control custom-checkbox mt-2">
                              <input type="hidden" name="subaccount" value="0" class="custom-control-input"
                                 id="hiddenSubaccount">
                              <input type="checkbox" name="subaccount" value="1" class="custom-control-input"
                                 id="subaccount" data-toggle="collapse" data-target="#sub-acc-collapse">
                              <label class="custom-control-label font-weight-normal" for="subaccount">Activate
                                 Sub-Account</label>
                           </div>
                        </div>
                     </div>
                     <div id="sub-acc-collapse" class="collapse px-1 py-3 bg-light">
                        <div class="col-lg-12">
                           <label>Sub-Account Permissions</label>
                        </div>
                        <div class="col-lg-12">
                           <div class="form-group row">
                              @foreach ($allPermission as $permission)
                                 <div class="col-lg-6">
                                    <div class="custom-control custom-checkbox">
                                       <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                          class="custom-control-input" id="{{ $permission }}">
                                       <label class="custom-control-label font-weight-normal"
                                          for="{{ $permission }}">{!! __("client.subaccountperms$permission") !!}</label>
                                    </div>
                                 </div>
                              @endforeach
                           </div>
                        </div>
                        <div class="col-lg-12">
                           <div class="form-group">
                              <label>Password</label>
                              <input class="form-control" type="password" name="password" id="password"
                                 autocomplete="off">
                           </div>
                           <div class="form-group">
                              <label>Confirm Password</label>
                              <input class="form-control" type="password" name="conf_password" id="conf_password"
                                 autocomplete="off">
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row m-1">
                     <div class="col-lg-12">
                        <h5 class="mt-2 text-qw">Email Configuration</h5>
                     </div>
                     <div class="col-lg-12">
                        <div class="form-group">
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[general]" value="1"
                                 class="custom-control-input" id="customCheck2">
                              <label class="custom-control-label" for="customCheck2">General Emails - Announcements &
                                 Password Reminder</label>
                           </div>
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[product]" value="1"
                                 class="custom-control-input" id="customCheck3">
                              <label class="custom-control-label" for="customCheck3">Product Emails - Order Details,
                                 Product Information etc.</label>
                           </div>
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[domain]" value="1"
                                 class="custom-control-input" id="customCheck4">
                              <label class="custom-control-label" for="customCheck4">Domain Emails - Renewal Notices,
                                 Registration Confirmations, etc...
                              </label>
                           </div>
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[invoice]" value="1"
                                 class="custom-control-input" id="customCheck5">
                              <label class="custom-control-label" for="customCheck5">Invoice Emails - Invoices & Billing
                                 Reminders
                              </label>
                           </div>
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[support]" value="1"
                                 class="custom-control-input" id="customCheck6">
                              <label class="custom-control-label" for="customCheck6">Support Emails - Allow this user to
                                 open tickets in your account
                              </label>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-light" data-dismiss="modal" id="btnReset">Close</button>
                  <button type="submit" class="btn btn-success-qw" id="btnContact">Save
                     changes</button>
               </div>
            </form>
         </div>
      </div>
   </div>
@endsection

@section('scripts')
   <script src="{{ Theme::asset('assets/plugins/parsleyjs/parsley.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/pages/form-validation.init.js') }}"></script>
   <script type="text/javascript">
      let dtTable;

      $(() => {
         dtIndex();
         $('#btnReset').on('click', () => {
            document.getElementById("contact-form").reset();
         })
      })

      const dtIndex = () => {
         dtTable = $('#contactsSq').DataTable({
            columnDefs: [{
               "width": "1%",
               "targets": 0
            }],
            stateSave: true,
            processing: true,
            responsive: true,
            serverSide: true,
            autoWidth: false,
            searching: false,
            destroy: true,
            language: {
               paginate: {
                  previous: "<i class='mdi mdi-chevron-left'>",
                  next: "<i class='mdi mdi-chevron-right'>",
               },
            },
            drawCallback: () => {
               $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
            },
            ajax: {
               url: "{!! route('dt_Contacts') !!}",
               type: "GET",
            },
            columns: [{
                  data: 'DT_RowIndex',
                  name: 'DT_RowIndex',
                  width: '1%',
                  className: 'text-center align-middle',
                  orderable: false,
                  searchable: false,
                  visible: true
               },
               {
                  data: 'name',
                  name: 'name',
                  width: '1%',
                  className: 'text-left align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'companyName',
                  name: 'companyName',
                  width: '2%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'email',
                  name: 'email',
                  width: '1%',
                  className: 'text-center align-middle',
                  defaultContent: 'N/A',
               },
               {
                  data: 'phonenumber',
                  name: 'phonenumber',
                  width: '1%',
                  className: 'text-center align-middle',
                  searchable: false,
                  defaultContent: 'Off',
               },
               {
                  data: 'actions',
                  name: 'actions',
                  width: '2%',
                  className: 'text-center align-middle',
                  orderable: false,
                  searchable: false,
                  defaultContent: 'N/A',
               },
            ]
         })
      }
   </script>
@endsection
