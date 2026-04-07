@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Create New Server</title>
@endsection

@section('content')
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">

            <div class="row">
               <!-- Sidebar Shortcut -->

               <!-- End Sidebar -->

               <!-- MAIN CARD -->
               <div class="col-xl-12">
                  <div class="view-client-wrapper">
                     <div class="row">
                        <div class="col-12">
                           <div class="card-title mb-3">
                              <h4 class="mb-3">Servers</h4>
                           </div>
                           @if (session('message'))
                              <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                                 <h5>Something Went Wrong!</h5>
                                 <small>{!! session('message') !!}</small>
                                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                 </button>
                              </div>
                           @endif
                        </div>
                     </div>
                     <div class="row">
                        <form action="{{ route('admin.pages.setup.prodsservice.serverconfig.insert') }}" method="POST"
                           class="w-100" id="createNewServerForm">
                           <div class="col-lg-12">
                              <!-- START HERE -->
                              <div id="simpleform">
                                 <div class="card p-3">
                                    <h5 class="mb-3">Add Server</h5>
                                    <div class="alert alert-primary" role="alert">
                                       <i class="fa fa-exclamation-circle mr-2" aria-hidden="true"></i> Lorem, ipsum
                                       dolor
                                       sit amet consectetur adipisicing elit. Exercitationem laborum accusantium
                                       corrupti iusto alias, quae rerum quis hic distinctio accusamus!
                                    </div>
                                    <div class="row">
                                       <div class="col-lg-12">
                                          @csrf
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">
                                                Module<br>
                                                <small>Choose the control panel the server uses</small>
                                             </label>
                                             <div class="col-sm-12 col-lg-2 pt-2">
                                                <select name="type" class="form-control" id="module">
                                                   @foreach ($moduleList as $key => $module)
                                                      <option value="{{ $key }}">{{ $module }}</option>
                                                   @endforeach
                                                </select>
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">
                                                Hostname or IP Address <br>
                                                <small>Used to connect to your servers API</small>
                                             </label>
                                             <div class="col-sm-12 col-lg-6 pt-2">
                                                <input name="hostname" type="text" class="form-control" id="hostname"
                                                   value="{{ old('hostname') }}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Username</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input name="username" type="text" class="form-control" id="username"
                                                   value="{{ old('username') }}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Password</label>
                                             <div class="col-sm-12 col-lg-3">
                                                <input type="password" class="form-control" id="password"
                                                   value="{{ old('password') }}">
                                             </div>
                                          </div>
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">Access Hash</label>
                                             <div class="col-sm-12 col-lg-6">
                                                <textarea name="accesshash" type="text" class="form-control"
                                                   id="accessHash" rows="5" value="{{ old('accesshash') }}"></textarea>
                                             </div>
                                          </div>
                                          <div class="row">
                                             <div class="col-lg-12 text-center">
                                                <button type="button" id="continueProcess"
                                                   class="btn btn-success px-5 my-3">Continue</button>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div id="advancedForm" hidden>
                                 <div class="card p-3">
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Name
                                       </label>
                                       <div class="col-sm-12 col-lg-4">
                                          <input type="text" class="form-control" name="name"
                                             value="{{ old('name') }}">
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Hostname
                                       </label>
                                       <div class="col-sm-12 col-lg-4">
                                          <input type="text" class="form-control" id="hostname-post" name="hostname"
                                             value="{{ old('hostname') }}">
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          IP Address
                                       </label>
                                       <div class="col-sm-12 col-lg-4">
                                          <input type="text" class="form-control" id="ipaddress" name="ipaddress"
                                             value="{{ old('ipaddress') }}">
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Assigned IP Addresses
                                          (One per line)
                                       </label>
                                       <div class="col-sm-12 col-lg-6">
                                          <textarea id="" cols="30" rows="10" class="form-control" name="assignedips"
                                             value="{{ old('assignedips') }}"></textarea>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Monthly Cost
                                       </label>
                                       <div class="col-sm-12 col-lg-2">
                                          <input type="text" class="form-control" id="monthlycost" name="monthlycost"
                                             value="{{ old('monthlycost') }}">
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Datacenter/NOC
                                       </label>
                                       <div class="col-sm-12 col-lg-6">
                                          <input type="text" class="form-control" id="noc" name="noc"
                                             value="{{ old('noc') }}">
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Maximum No. of Accounts
                                       </label>
                                       <div class="col-sm-12 col-lg-2">
                                          <input type="text" class="form-control" id="maxaccounts" name="maxaccounts"
                                             value="{{ old('maxaccounts') }}">
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Server Status Address
                                       </label>
                                       <div class="col-sm-12 col-lg-6">
                                          <input type="text" class="form-control" id="statusaddress" name="statusaddress"
                                             value="{{ old('statusesaddress') }}">
                                          <small>To display this server on the server status page, enter the full path to
                                             the
                                             server status folder (required to be uploaded to each server you want to
                                             monitor)
                                             -
                                             eg. https://www.example.com/status/</small>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Enable/Disable
                                       </label>
                                       <div class="col-sm-12 col-lg-4 pt-2">
                                          <div class="custom-control custom-checkbox">
                                             <input value="0" type="hidden" class="custom-control-input"
                                                id="activeServerHidden" name="disabled">
                                             <input value="1" type="checkbox" class="custom-control-input"
                                                id="activeServer" name="disabled" value="{{ old('disabled') }}">
                                             <label for="activeServer" class="custom-control-label">Tick to disable this
                                                server</label>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div id="nameserverForm" hidden>
                                 <div class="card p-3">
                                    <h5 class="mb-3">Nameservers</h5>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Primary Nameserver</label>
                                       <div class="col-sm-12 col-lg-4">
                                          <input type="text" class="form-control" name="nameserver1"
                                             value="{{ old('nameserver1') }}">
                                       </div>
                                       <div class="col-sm-12 col-lg-6">
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">
                                                IP Address:
                                             </label>
                                             <div class="col-sm-12 col-lg-10">
                                                <input type="text" class="form-control" name="nameserver1ip"
                                                   value="{{ old('nameserver1ip') }}">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Secondary Nameserver</label>
                                       <div class="col-sm-12 col-lg-4">
                                          <input type="text" class="form-control" name="nameserver2"
                                             value="{{ old('nameserver2') }}">
                                       </div>
                                       <div class="col-sm-12 col-lg-6">
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">
                                                IP Address:
                                             </label>
                                             <div class="col-sm-12 col-lg-10">
                                                <input type="text" class="form-control" name="nameserver2ip"
                                                   value="{{ old('nameserver2ip') }}">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Third Nameserver</label>
                                       <div class="col-sm-12 col-lg-4">
                                          <input type="text" class="form-control" name="nameserver3"
                                             value="{{ old('nameserver3') }}">
                                       </div>
                                       <div class="col-sm-12 col-lg-6">
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">
                                                IP Address:
                                             </label>
                                             <div class="col-sm-12 col-lg-10">
                                                <input type="text" class="form-control" name="nameserver3ip"
                                                   value="{{ old('nameserver3ip') }}">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Fourth Nameserver</label>
                                       <div class="col-sm-12 col-lg-4">
                                          <input type="text" class="form-control" name="nameserver4"
                                             value="{{ old('nameserver4') }}">
                                       </div>
                                       <div class="col-sm-12 col-lg-6">
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">
                                                IP Address:
                                             </label>
                                             <div class="col-sm-12 col-lg-10">
                                                <input type="text" class="form-control" name="nameserver4ip"
                                                   value="{{ old('nameserver4ip') }}">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Fifth Nameserver</label>
                                       <div class="col-sm-12 col-lg-4">
                                          <input type="text" class="form-control" name="nameserver5"
                                             value="{{ old('nameserver5') }}">
                                       </div>
                                       <div class="col-sm-12 col-lg-6">
                                          <div class="form-group row">
                                             <label class="col-sm-12 col-lg-2 col-form-label">
                                                IP Address:
                                             </label>
                                             <div class="col-sm-12 col-lg-10">
                                                <input type="text" class="form-control" name="nameserver5ip"
                                                   value="{{ old('nameserver5ip') }}">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div id="serverForm" hidden>
                                 <div class="card p-3">
                                    <h5 class="mb-3">Server Details</h5>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Module</label>
                                       <div class="col-sm-12 col-lg-4">
                                          {{-- <select name="module" id="" class="form-control" id="module-post">
                                          <option value="0">G Suite Module</option>
                                       </select> --}}
                                          <div id="module-post"></div>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Username</label>
                                       <div class="col-sm-12 col-lg-6">
                                          <input name="username" type="text" class="form-control" id="username-post"
                                             value="{{ old('username') }}">
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Password</label>
                                       <div class="col-sm-12 col-lg-6">
                                          <input name="password" type="password" class="form-control" id="password-post"
                                             value="{{ old('password') }}">
                                       </div>
                                    </div>
                                    <div class="  form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Access Hash</label>
                                       <div class="col-sm-12 col-lg-6">
                                          <div id="accessHash-post"></div>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">Secure</label>
                                       <div class="col-sm-12 col-lg-6">
                                          <div class="custom-control custom-checkbox col-form-label">
                                             <input type="hidden" class="custom-control-input" id="sslSecureHidden"
                                                value="off" name="secure">
                                             <input type="checkbox" class="custom-control-input" id="sslSecure"
                                                name="secure" value="on">
                                             <label class="custom-control-label" for="sslSecure">Tick to use SSL Mode for
                                                Connections</label>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="form-group row">
                                       <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                          Port
                                       </label>
                                       <div class="col-sm-12 col-lg-1">
                                          <input type="text" class="form-control" value="1112" disabled>
                                       </div>
                                       <div class="col-sm-12 col-lg-4">
                                          <div class="custom-control custom-checkbox col-form-label">
                                             <input type="checkbox" class="custom-control-input" id="port" value="1">
                                             <label class="custom-control-label" for="port">Override with Custom
                                                Port</label>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-lg-12 text-center">
                              <button type="submit" class="btn btn-success px-3" id="btnAddServer" hidden>Save
                                 Changes</button>
                              <a href="{{ route('admin.pages.setup.prodsservice.serverconfig.index') }}">
                                 <button type="button" class="btn btn-light px-3" id="btnCnlServer" hidden>Cancel
                                    Changes</button>
                              </a>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
               <!-- End MAIN CARD -->
            </div>
         </div>
      </div>
   </div>
@endsection

@section('scripts')
   <script src="{{ Theme::asset('assets/js/server-add-form.js') }}"></script>
@endsection
