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
                    <div class="col-xl-10">
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
                            <form action="./../update/{{$data->id}}" method="post" enctype="multipart/form-data">
                            @method('PUT')
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
                                            <input type="text" name="title" value="{{$data->title}}" id="title-issue" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="type-issue" class="col-sm-2 col-form-label">Type</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <select name="type" id="type-issue"
                                                    class="form-control select2-search-disable" style="width: 100%;">
                                                    <option value="Server"  {{ ($data->type == 'Server' )?'selected':'' }}>Server</option>
                                                    <option value="System" {{ ($data->type == 'System' )?'selected':'' }}>System</option>
                                                    <option value="Other" {{ ($data->type == 'Other' )?'selected':'' }}>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="server-type" class="col-sm-2 col-form-label">Server</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <select name="server_id" id="server-type"
                                                    class="form-control select2-search-disable" style="width: 100%;">
                                                    <option value="0">None</option>
                                                    @foreach ($server as $r)
                                                    <option value="{{$r->id}}" {{ ($r->id == $data->server )?'selected':'' }} >{{$r->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="priority-type" class="col-sm-2 col-form-label">Priority</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <select name="priority" id="priority-type"
                                                    class="form-control select2-search-disable" style="width: 100%;">
                                                    <option value="Critical"  {{ ($data->priority == 'Critical' )?'selected':'' }}>Critical</option>
                                                    <option value="Low"  {{ ($data->priority == 'Low' )?'selected':'' }}>Low</option>
                                                    <option value="Medium"  {{ ($data->priority == 'Medium' )?'selected':'' }}>Medium</option>
                                                    <option value="High" {{ ($data->priority == 'High' )?'selected':'' }}>High</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="status-type" class="col-sm-2 col-form-label">Status</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <select name="status" id="status-type"
                                                    class="form-control select2-search-disable" style="width: 100%;">
                                                    <option value="Reported" {{ ($data->status == 'Reported' )?'selected':'' }} >Reported</option>
                                                    <option value="Investigating" {{ ($data->status == 'Investigating' )?'selected':'' }}>Investigating</option>
                                                    <option value="In Progress" {{ ($data->status == 'In Progress' )?'selected':'' }}>In Progress</option>
                                                    <option value="Outage" {{ ($data->status == 'Outage' )?'selected':'' }}>Outage</option>
                                                    <option value="Scheduled" {{ ($data->status == 'Scheduled' )?'selected':'' }}>Scheduled</option>
                                                    <option value="Resolved"  {{ ($data->status == 'Scheduled' )?'selected':'' }}>Resolved</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="start-date" class="col-sm-2 col-form-label">Start Date</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <input type="date" name="startdate" value="{{$data->startdate}}" id="start-date" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="end-date" class="col-sm-2 col-form-label">End Date</label>
                                            <div class="col-sm-10 col-lg-2">
                                                <input type="date" name="enddate"  value="{{$data->enddate}}" id="end-date" class="form-control">
                                            </div>
                                        </div>
                                        <hr>
                                        <h4 class="card-title">Description</h4>
                                        @if ($errors->has('description'))
                                            <span class="text-danger">{{ $errors->first('description') }}</span>
                                        @endif
                                        <textarea name="description" class="summernote">{{$data->description}}</textarea>
                                        <div class="col-lg-12 d-flex justify-content-center">
                                            <input type="hidden" value="{{ $data->id}}" name="id">
                                            <button type="submit" class="btn btn-success px-3 m-3">Update Changes</button>
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
