@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Edit Server {{ $serverSelected->name }}</title>
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
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <form
                              action="{{ route('admin.pages.setup.prodsservice.serverconfig.update', $serverSelected->id) }}"
                              method="POST" id="updateServerForm">
                              <div class="card p-3">
                                 <h5 class="mb-3">Edit Server</h5>
                                 <div class="row">
                                    <div class="col-lg-12">
                                       @csrf
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">Name</label>
                                          <div class="col-sm-12 col-md-6">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->name }}" name="name">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">Hostname</label>
                                          <div class="col-sm-12 col-md-6">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->hostname }}" name="hostname">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">IP Address</label>
                                          <div class="col-sm-12 col-md-6">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->ipaddress }}" name="ipaddress">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="assignedips" class="col-sm-12 col-md-2 col-form-label">Assigned IP
                                             Addresses
                                             (One
                                             per line)</label>
                                          <div class="col-sm-12 col-md-6">
                                             <textarea name="assignedips" id="assignedips" cols="30" rows="10"
                                                class="form-control">{{ $serverSelected->assignedips }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">Monthly Cost</label>
                                          <div class="col-sm-12 col-md-3">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->monthlycost }}" name="monthlycost">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">Datacenter/NOC</label>
                                          <div class="col-sm-12 col-md-4">
                                             <input type="text" class="form-control" value="{{ $serverSelected->noc }}"
                                                name="noc">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">Max No. of
                                             Accounts</label>
                                          <div class="col-sm-12 col-md-3">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->maxaccounts }}" name="maxaccounts">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">Server Status
                                             Address</label>
                                          <div class="col-sm-12 col-md-10">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->statusaddress }}" name="statusaddress">
                                             <small>To display this server on the server status page, enter the full path
                                                to
                                                the server status folder (required to be uploaded to each server you want
                                                to
                                                monitor) - eg. https://www.example.com/status/</small>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">Enable/Disable</label>
                                          <div class="col-sm-12 col-md-6">
                                             <div class="custom-control custom-checkbox col-form-label">
                                                <input name="disabled" type="hidden" class="custom-control-input"
                                                   id="disabledHidden" value="0">
                                                <input name="disabled" type="checkbox" class="custom-control-input"
                                                   id="disabled" {{ $serverSelected->disabled == 1 ? 'checked' : '' }}
                                                   value="1">
                                                <label class="custom-control-label " for="disabled">Tick to disable
                                                   this
                                                   server</label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <h6>Nameservers</h6>
                              <div class="card p-3">
                                 <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-4 col-form-label">
                                             Primary Nameserver
                                          </label>
                                          <div class="col-sm-12 col-md-8">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver1 }}" name="nameserver1">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">
                                             IP Address:
                                          </label>
                                          <div class="col-sm-12 col-md-10">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver1ip }}" name="nameserver1ip">
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-4 col-form-label">
                                             Second Nameserver
                                          </label>
                                          <div class="col-sm-12 col-md-8">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver2 }}" name="nameserver2">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">
                                             IP Address:
                                          </label>
                                          <div class="col-sm-12 col-md-10">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver2ip }}" name="nameserver2ip">
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-4 col-form-label">
                                             Third Nameserver
                                          </label>
                                          <div class="col-sm-12 col-md-8">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver3 }}" name="nameserver3">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">
                                             IP Address:
                                          </label>
                                          <div class="col-sm-12 col-md-10">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver3ip }}" name="nameserver3ip">
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-4 col-form-label">
                                             Fourth Nameserver
                                          </label>
                                          <div class="col-sm-12 col-md-8">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver4 }}" name="nameserver4">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">
                                             IP Address:
                                          </label>
                                          <div class="col-sm-12 col-md-10">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver4ip }}" name="nameserver4ip">
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-4 col-form-label">
                                             Fifth Nameserver
                                          </label>
                                          <div class="col-sm-12 col-md-8">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver5 }}" name="nameserver5">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">
                                             IP Address:
                                          </label>
                                          <div class="col-sm-12 col-md-10">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->nameserver5ip }}" name="nameserver5ip">
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <h6>Server Details</h6>
                              <div class="card p-3">
                                 <div class="row">
                                    <div class="col-12">
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">Module</label>
                                          <div class="col-sm-12 col-md-3">
                                             <select name="type" id="type" class="form-control">
                                                @foreach ($modulesSelected as $item => $module)
                                                   <option value="{{ $module }}"
                                                      {{ $module == $serverSelected->type ? 'selected' : '' }}>
                                                      {{ $module }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">
                                             Username
                                          </label>
                                          <div class="col-sm-12 col-md-6">
                                             <input type="text" class="form-control"
                                                value="{{ $serverSelected->username }}" name="username">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">
                                             Password
                                          </label>
                                          <div class="col-sm-12 col-md-6">
                                             <input type="password" class="form-control"
                                                value="{{ old('password') }}" name="password">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2 col-form-label">
                                             Access Hash
                                          </label>
                                          <div class="col-sm-12 col-md-6">
                                             <textarea name="accesshash" id="accesshash" class="form-control"
                                                rows="5">{{ $serverSelected->accesshash }}</textarea>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label for="" class="col-sm-12 col-md-2">
                                             Secure
                                          </label>
                                          <div class="col-sm-12 col-md-4">
                                             <div class="custom-control custom-checkbox">
                                                <input name="secure" type="checkbox" class="custom-control-input"
                                                   id="secureCheck" value="on"
                                                   {{ $serverSelected->secure == 'on' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="secureCheck">Tick to use SSL
                                                   Mode
                                                   for Connections</label>
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
                                       <div class="text-center">
                                          <button class="btn btn-success px-3" type="submit" id="btnUpdateForm">Save
                                             Changes</button>
                                          <button class="btn btn-light px-3" type="button" id="btnCnclUpdateForm">Cancel
                                             Changes</button>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- End MAIN CARD -->
            </div>
         </div>
      </div>
   </div>
@endsection
