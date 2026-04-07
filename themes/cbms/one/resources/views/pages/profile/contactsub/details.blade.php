@php
$fullname = "$contact_data->firstname " . "$contact_data->lastname";
@endphp
@extends('layouts.clientbase')

@section('title')
   Contact Detail ({{ $fullname }})
@endsection

@section('page-title')
   Contact Detail ({{ $fullname }})
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         <div class="row pb-3">
            <div class="col-xl-8 col-lg-8">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ url('home') }}">Dashboard </a> <span
                        class="text-muted"> / </span>
                     <a href="{{ route('pages.profile.contactsub.index') }}"> Contact Sub
                        Account</a> <span class="text-muted"> / </span> <span class="text-muted"> Details
                     </span>
                  </h6>
               </div>
            </div>
         </div>

         <div class="row">
            <div class="col-xl-12 col-lg-12">
               <div class="card">
                  @if (session('success'))
                     <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <h5>Contact Updated!</h5>
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
                     <h5 class="card-title mb-3">Contact / Sub-Account Details</h5>
                     <form action="{{ route('pages.profile.contactsub.update', $contact_data->id) }}" method="POST"
                        autocomplete="off" class="needs-validation" novalidate>
                        @csrf
                        <div class="row">
                           <div class="col-lg-12">
                              <h5 class="mt-2 text-qw">Personal Data</h5>
                              <hr />
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="namadepan">First Name</label>
                                 <input type="text" class="form-control" name="firstname"
                                    value="{{ $contact_data->firstname }}" id="namadepan" required>
                                 <div class="invalid-feedback">First Name is required</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="namabelakang">Last Name</label>
                                 <input type="text" class="form-control" name="lastname"
                                    value="{{ $contact_data->lastname }}" id="namabelakang" required>
                                 <div class="invalid-feedback">Last Name is required</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-12">
                              <div class="form-group">
                                 <label for="namaperusahaan">Company Name</label>
                                 <input type="text" class="form-control" name="companyname"
                                    value="{{ $contact_data->companyname }}" id="namaperusahaan" required>
                                 <div class="invalid-feedback">Company Name is required</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="email">Email</label>
                                 <input type="text" class="form-control" name="email"
                                    value="{{ $contact_data->email }}" id="email" required>
                                 <div class="invalid-feedback">Correct email format is required e.g (your@mail.com)</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="phonenumber">Phone</label>
                                 <input type="text" class="form-control" name="phonenumber"
                                    value="{{ $contact_data->phonenumber }}" id="phone" required>
                                 <div class="invalid-feedback">Phone Number is required</div>
                              </div>
                           </div>
                           <div class="col-lg-12">
                              <h5 class="mt-2 text-qw">Address</h5>
                              <hr />
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="alamat">Address</label>
                                 <input type="text" class="form-control" name="address1"
                                    value="{{ $contact_data->address1 }}" id="alamat" required>
                                 <div class="invalid-feedback">Main Address is required</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="alamat2">Address 2</label>
                                 <input type="text" class="form-control" name="address2"
                                    value="{{ $contact_data->address2 }}" id="alamat2">
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="kota">City</label>
                                 <input type="text" class="form-control" name="city" value="{{ $contact_data->city }}"
                                    id="kota" required>
                                 <div class="invalid-feedback">City is required</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="region">State/Region</label>
                                 <input type="text" class="form-control" name="state"
                                    value="{{ $contact_data->state }}" id="region" required>
                                 <div class="invalid-feedback">Region/State is required</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="kodepos">Postal Code</label>
                                 <input type="text" class="form-control" name="postcode"
                                    value={{ $contact_data->postcode }} id="kodepos" required>
                                 <div class="invalid-feedback">Postal Code is required</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="negara">Country</label>
                                 <select class="form-control" name="country" id="negara">
                                    @foreach ($countries as $country)
                                       <option value="{{ $country['id'] }}"
                                          {{ $contact_data->country == $country['id'] ? 'selected' : '' }}>
                                          {{ $country['name'] }}</option>
                                       {{-- {{ $auth->country == $country['id'] ? 'selected' : '' }} --}}
                                    @endforeach
                                 </select>
                                 <div class="invalid-feedback">Country is required</div>
                              </div>
                           </div>
                           <div class="col-sm-12 col-lg-6">
                              <div class="form-group">
                                 <label for="tax_id">Tax ID</label>
                                 <input type="text" class="form-control" name="tax_id" value="" id="tax_id"
                                    autocomplete="false">
                              </div>
                           </div>
                           <div class="col-lg-6 d-flex align-items-center">
                              <div class="form-group">
                                 <div class="custom-control custom-checkbox mt-2">
                                    <input type="hidden" name="subaccount" value="0" class="custom-control-input"
                                       id="hiddenSubaccount">
                                    <input type="checkbox" name="subaccount" value="1" class="custom-control-input"
                                       id="subaccount" {{ $contact_data->subaccount == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-normal" for="subaccount">Activate
                                       Sub-Account</label>
                                 </div>
                              </div>
                           </div>
                           <div class="col-lg-12">
                              <div id="sub-acc-collapse"
                                 class="collapse px-1 py-3 bg-light {{ $contact_data->subaccount == 1 ? 'show' : '' }}">
                                 <div class="col-lg-12">
                                    <label>Sub-Account Permissions</label>
                                 </div>
                                 <div class="col-lg-12">
                                    <div class="form-group row">
                                       @foreach ($allPermission as $permission)
                                          <div class="col-lg-6">
                                             <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="permissions[{{ $permission }}]"
                                                   value="{{ $permission }}" class="custom-control-input"
                                                   id="{{ $permission }}" @foreach ($contactPermissionActive as $key => $value)
                                                {{ $value == $permission ? 'checked' : '' }}
                                       @endforeach
                                       >
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
                        <hr />
                     </div>
                     <div class="col-lg-12">
                        <div class="form-group">
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[general]" value="1"
                                 class="custom-control-input" id="customCheck2"
                                 {{ $contact_data->generalemails == 1 ? 'checked' : '' }}>
                              <label class="custom-control-label" for="customCheck2">General Emails -
                                 Announcements &
                                 Password Reminder</label>
                           </div>
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[product]" value="1"
                                 class="custom-control-input" id="customCheck3"
                                 {{ $contact_data->productemails == 1 ? 'checked' : '' }}>
                              <label class="custom-control-label" for="customCheck3">Product Emails - Order
                                 Details,
                                 Product Information etc.</label>
                           </div>
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[domain]" value="1"
                                 class="custom-control-input" id="customCheck4"
                                 {{ $contact_data->domainemails == 1 ? 'checked' : '' }}>
                              <label class="custom-control-label" for="customCheck4">Domain Emails - Renewal
                                 Notices,
                                 Registration Confirmations, etc...
                              </label>
                           </div>
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[invoice]" value="1"
                                 class="custom-control-input" id="customCheck5"
                                 {{ $contact_data->invoiceemails == 1 ? 'checked' : '' }}>
                              <label class="custom-control-label" for="customCheck5">Invoice Emails - Invoices &
                                 Billing
                                 Reminders
                              </label>
                           </div>
                           <div class="custom-control custom-checkbox">
                              <input type="checkbox" name="email_preferences[support]" value="1"
                                 class="custom-control-input" id="customCheck6"
                                 {{ $contact_data->supportemails == 1 ? 'checked' : '' }}>
                              <label class="custom-control-label" for="customCheck6">Support Emails - Allow this
                                 user to
                                 open tickets in your account
                              </label>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="text-center">
                  <button type="submit" class="btn btn-success-qw">
                     Save Changes
                  </button>
                  <a href="{{ route('pages.profile.contactsub.index') }}">
                     <button type="button" class="btn btn-light">
                        Cancel Changes
                     </button>
                  </a>
                  <button type="button" class="btn btn-outline-danger act-delete" data-id="{{ $contact_data->id }}">
                     Delete Contact
                  </button>
               </div>
               </form>
            </div>
         </div>
      </div>
   </div>
   <!-- end row-->

   </div> <!-- container-fluid -->
   </div>
@endsection

@section('scripts')
   <!-- JQuery Serialize Json -->
   <script src="{{ Theme::asset('assets/plugins/serialize-json/jquery.serializejson.min.js') }}"></script>

   <!-- Sweetalert2 -->
   <script src="{{ Theme::asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

   <script src="{{ Theme::asset('assets/plugins/parsleyjs/parsley.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/pages/form-validation.init.js') }}"></script>

   <script type="text/javascript">
      $(() => {
         subaccountCheck();

         $('body').on('click', '.act-delete', function() {
            actionDeleteContact($(this).attr('data-id'));
         });

      });

      const Toast = Swal.mixin({
         toast: true,
         position: 'top-end',
         showConfirmButton: true,
         timerProgressBar: true,
         timer: 3000,
      });

      const redirectPage = () => {
         window.location.replace("{!! route('pages.profile.contactsub.index') !!}")
      }

      const actionDeleteContact = (id) => {
         Swal.fire({
            title: "Are you sure?",
            html: `This <b>Contact</b> will be deleted.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, Delete!",
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: (data) => {
               const options = {
                  method: 'DELETE',
                  headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  },
                  body: JSON.stringify({
                     id
                  }),
               };

               return fetch("{!! route('pages.profile.contactsub.delete') !!}", options)
                  .then(response => {
                     if (!response.ok) throw new Error(response.statusText);

                     return response.json()
                  })
                  .catch(error => {
                     Swal.showValidationMessage(`Request failed: ${error}`);
                  });
            },
         }).then((response) => {
            if (response.value) {
               const {
                  result,
                  message
               } = response.value;

               Swal.fire(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success'
               )

               setTimeout(redirectPage(), 5000)

               filterTable(null);
            }
         }).catch(swal.noop);
      }

      const subaccountCheck = () => {
         $('#subaccount').click(function() {
            $('#sub-acc-collapse').toggleClass('show')
         })
      }
   </script>
@endsection
