@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Create New Issue</title>
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
                                        <h4 class="mb-3">Network Issues</h4>
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
                            <form action="./store" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <h4 class="card-title">Create New Issue</h4>
                                        <div class="form-group row">
                                            <label for="title-issue" class="col-sm-2 col-form-label">Title</label>
                                            <div class="col-sm-10 col-lg-4">
                                            @if ($errors->has('title'))
                                                <span class="text-danger">{{ $errors->first('title') }}</span>
                                            @endif
                                            <input type="text" name="title" id="title-issue" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="type-issue" class="col-sm-2 col-form-label">Type</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <select name="type" id="type-issue"
                                                    class="form-control select2-search-disable" style="width: 100%;">
                                                    <option value="Server">Server</option>
                                                    <option value="System">System</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="server-type" class="col-sm-2 col-form-label">Server</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <select name="server_id" id="server-type"
                                                    class="form-control select2-search-disable" style="width: 100%;">
                                                    <option value="0">None</option>
                                                    @foreach ($server as $data)
                                                    <option value="{{$data->id}}">{{$data->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="priority-type" class="col-sm-2 col-form-label">Priority</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <select name="priority" id="priority-type"
                                                    class="form-control select2-search-disable" style="width: 100%;">
                                                    <option value="Critical">Critical</option>
                                                    <option value="Low">Low</option>
                                                    <option value="Medium">Medium</option>
                                                    <option value="High">High</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="status-type" class="col-sm-2 col-form-label">Status</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <select name="status" id="status-type"
                                                    class="form-control select2-search-disable" style="width: 100%;">
                                                    <option value="Reported">Reported</option>
                                                    <option value="Investigating">Investigating</option>
                                                    <option value="In Progress">In Progress</option>
                                                    <option value="Outage">Outage</option>
                                                    <option value="Scheduled">Scheduled</option>
                                                    <option value="Resolved">Resolved</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="start-date" class="col-sm-2 col-form-label">Start Date</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <input type="date" name="startdate" value="{{ \Carbon\Carbon::now()->isoFormat('DD/MM/YYYY') }}" id="start-date" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="end-date" class="col-sm-2 col-form-label">End Date</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <input type="date" name="enddate" id="end-date" class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <h4 class="card-title">Description</h4>
                                        @if ($errors->has('description'))
                                            <span class="text-danger">{{ $errors->first('description') }}</span>
                                        @endif
                                        <textarea name="description" class="summernote">Write Description</textarea>
                                        <div class="col-lg-12 d-flex justify-content-center">
                                            <button type="submit" class="btn btn-success px-3 m-3">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                    <!-- End MAIN CARD -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/libs/summernote/summernote.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script>
@endsection
