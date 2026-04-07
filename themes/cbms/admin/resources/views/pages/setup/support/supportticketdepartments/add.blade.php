@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Add New Department</title>
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
                                        <h4 class="mb-3">Support Ticket Departments</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    @if(Session::has('success'))
                                    <div class="alert alert-success">
                                        {{ Session::get('success') }}
                                        @php
                                            Session::forget('success');
                                        @endphp
                                    </div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif


                                    <div class="card p-3">
                                        <h4 class="card-title mb-3">Add New Department</h4>
                                        <form action="{{ url(Request::segment(1).'/setup/support/configticketdepartments/store') }}" method="post" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Department Name
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="text" name="name" class="form-control">
                                                        @if ($errors->has('email'))
                                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Description
                                                    </label>
                                                    <div class="col-sm-12 col-lg-10">
                                                        <input type="text" name="description" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Email Address
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="email" name="email" class="form-control">
                                                        @if ($errors->has('email'))
                                                            <span class="text-danger">{{ $errors->first('email') }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Assigned Admin Users
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        {{-- @foreach($admin as $r)
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="admins[]"  class="custom-control-input" value="{{ $r->id }}" id="assignAdminCheck{{ $r->id }}">
                                                            <label class="custom-control-label" for="assignAdminCheck{{ $r->id }}">{{ $r->username }} ({{ $r->firstname.' '.$r->lastname }})</label>
                                                        </div>
                                                        @endforeach --}}
                                                        @foreach($admin as $r)
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" name="admins[]" class="custom-control-input" value="{{ $r->id }}" id="assignAdminCheck{{ $r->id }}"
                                                            {{ in_array($r->id, $assignedAdmins) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="assignAdminCheck{{ $r->id }}">{{ $r->username }} ({{ $r->firstname.' '.$r->lastname }})</label>
                                                        </div>
                                                        @endforeach  
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Clients Only
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="clientsonly" id="clientsOnlyCheck">
                                                            <label class="custom-control-label" for="clientsOnlyCheck">Only allow registered clients to open tickets in this department</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Pipe Replies Only
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="piperepliesonly" class="custom-control-input" id="pipeRepliesCheck">
                                                            <label class="custom-control-label"
                                                                for="pipeRepliesCheck">Require all tickets to be opened from
                                                                the client area</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        No Autoresponder
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="noautoresponder" id="noAutoResponderCheck">
                                                            <label class="custom-control-label"
                                                                for="noAutoResponderCheck">Do not send the autoresponder
                                                                message for new tickets</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Feedback Request
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="feedbackrequest" id="feedbackRequestCheck">
                                                            <label class="custom-control-label"
                                                                for="feedbackRequestCheck">Send ticket feedback
                                                                rating/review request on close of ticket</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Hidden?
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="hidden" class="custom-control-input" id="hiddenCheck">
                                                            <label class="custom-control-label" for="hiddenCheck">Hide from
                                                                clients</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card-title mb-3"><span class="font-weight-bold">POP3 Importing
                                                        Configuration </span>(Only
                                                    required if using POP3 Import method)</div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group row">
                                                            <label for=""
                                                                class="col-sm-12 col-lg-2 col-form-label">Hostname</label>
                                                            <div class="col-sm-12 col-lg-4">
                                                                <input type="text" class="form-control" name="host" placeholder="mail.example.com">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="" class="col-sm-12 col-lg-2 col-form-label">POP3
                                                                Port</label>
                                                            <div class="col-sm-12 col-lg-2">
                                                                <input type="text" class="form-control" name="port" value="110">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="" class="col-sm-12 col-lg-2 col-form-label">Email
                                                                Address</label>
                                                            <div class="col-sm-12 col-lg-4">
                                                                <input type="email" name="login" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="" class="col-sm-12 col-lg-2 col-form-label">Email
                                                                Password</label>
                                                            <div class="col-sm-12 col-lg-4">
                                                                <input type="password" name="password" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row ">
                                            <div class="col-lg-12 d-flex justify-content-center">
                                                {{ csrf_field() }}
                                                <button type="submit" class="btn btn-success px-2 mx-1">Add New Department</button>
                                                <a href="{{ url(Request::segment(1).'/setup/support/configticketdepartments') }}" class="btn btn-light px-3 mx-1">Cancel Changes</a>
                                            </div>
                                        </div>
                                        </form>
                                    </div>
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
