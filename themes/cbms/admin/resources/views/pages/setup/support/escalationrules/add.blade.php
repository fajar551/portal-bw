@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Add New Rule</title>
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
                                        <h4 class="mb-3">Support Ticket Escalations</h4>
                                    </div>
                                </div>
                            </div>
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
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <h4 class="card-title mb-3">
                                            Add New Rule
                                        </h4>
                                        <form action="{{ url(Request::segment(1).'/setup/support/escalationrules/store') }}" method="post" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Name</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="text" name="name" class="form-control">
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-lg-12">
                                                <h6 class="mb-3">Conditions</h6>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label"> Departments</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <select name="departments[]"  class="form-control" multiple="multiple">
                                                            @foreach($dept as $r)
                                                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                                                            @endforeach
                                                            
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Statuses</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <select name="statuses[]" id="" class="form-control" multiple="multiple">
                                                            @foreach($status as $r)
                                                            <option value="{{ $r->title }}">{{ $r->title }}</option>
                                                            @endforeach
                                                            
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Priorities
                                                    </label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <select name="priorities[]"  class="form-control" multiple="multiple">
                                                            <option value="Low">Low</option>
                                                            <option value="Medium">Medium</option>
                                                            <option value="High">High</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Time Elapsed
                                                    </label>
                                                    <div class="col-sm-12 col-lg-2">
                                                        <input type="text" name="timeelapsed" class="form-control">
                                                    </div>
                                                    <div class="col-sm-12 col-lg-3 pt-2">
                                                        <p>Minutes Since Last Reply</p>
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-lg-12">
                                                <h6 class="mb-3">Actions</h6>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Department
                                                    </label>
                                                    <div class="col-sm-12 col-lg-4">
                                                        <select name="newdepartment" id="" class="form-control">
                                                            <option value="0">No Change</option> 
                                                            @foreach($dept as $r)
                                                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Status
                                                    </label>
                                                    <div class="col-sm-12 col-lg-4">
                                                        <select name="newstatus" id="" class="form-control">
                                                            <option value="0">No Change</option>
                                                            @foreach($status as $r)
                                                            <option value="{{ $r->title }}">{{ $r->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Priority
                                                    </label>
                                                    <div class="col-sm-12 col-lg-4">
                                                        <select name="newpriority" id="" class="form-control">
                                                            <option value="0">No Change</option>
                                                            <option value="Low">Low</option>
                                                            <option value="Medium">Medium</option>
                                                            <option value="High">High</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Flag To
                                                    </label>
                                                    <div class="col-sm-12 col-lg-4">
                                                        <select name="flagto" id="" class="form-control">
                                                            <option value="0">No Change</option>
                                                            @foreach($admin as $r)
                                                                <option value="{{ $r->id }}">{{ $r->username }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">
                                                        Notify Admins
                                                    </label>
                                                    <div class="col-sm-12 col-lg-4">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="notify[]" class="custom-control-input" id="notifyAdmins0" value="all">
                                                            <label class="custom-control-label" for="notifyAdmins0">Tick to
                                                                send a notification email to all admins of the assigned
                                                                department
                                                                Also notify the following people:</label>
                                                        </div>

                                                        <div class="custom-control custom-checkbox">
                                                            @foreach($admin as $r)
                                                            <div class="user-admin mr-2 mb-3">
                                                                <input type="checkbox" name="notify[]" class="custom-control-input" id="notifyAdmins2" value="{{ $r->id }}">
                                                                <label class="custom-control-label" for="notifyAdmins2">{{ $r->username }} ({{ $r->firstname }} {{ $r->lastname }})</label>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Add Reply</label>
                                                    <div class="col-sm-12 col-lg-10">
                                                        <textarea id="addreply" name="addreply"  class="form-control  summernote" ></textarea>
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-lg-12 text-center">
                                                {{ csrf_field() }}
                                                
                                                <button type="submit" class="btn btn-success px-3">Save Changes</button>
                                                <a href="{{ url(Request::segment(1).'/setup/support/escalationrules') }}" class="btn btn-light px-3 mx-1">Cancel Changes</a>
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

@section('scripts')
    <!-- Summernote js -->
    <script src="{{ Theme::asset('assets/libs/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script>
@endsection
