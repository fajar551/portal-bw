@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Add Announcements</title>
@endsection
<link href="{{ Theme::asset('assets/css/bootstrap-datetimepicker.min.css') }}" type="text/css" rel="stylesheet" />
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <!-- <div class="row">
                                            <div class="col-12 p-3">
                                                <div class="page-title-box d-flex align-items-center justify-content-between">
                                                    <h4 class="mb-0">Dashboard</h4>
                                                </div>
                                            </div>
                                        </div> -->
                <!-- end page title -->
                <div class="row">
                     


                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Announcements</h4>
                                    </div>
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if ($message = Session::get('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                                            id="success-alert">
                                            <h5>Successfully Updated!</h5>
                                            <small>{{ $message }}</small>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    @if ($message = Session::get('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                            id="danger-alert">
                                            <h5>Error:</h5>
                                            <small>{{ $message }}</small>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <form id="post" action="{{route('admin.pages.support.announcements.post')}}" method="post">
                                        @csrf
                                        <div class="card p-3">
                                            <h4 class="card-title">Add Announcement</h4>
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label for="announcementSupport"
                                                        class="col-sm-2 col-form-label">Date</label>
                                                    <div class="col-sm-4">
                                                        <input value="{{old('date') ? old('date') : date('Y-m-d H:i')}}" type="text" name="date" id="announcementSupport" class="form-control datetime" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="titleAnnouncement" class="col-sm-2 col-form-label">Title</label>
                                                    <div class="col-sm-5">
                                                        <input value="{{old('title')}}" type="text" name="title" id="titleAnnouncement" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="announcements"
                                                        class="col-sm-2 col-form-label">Announcement</label>
                                                    <div class="col-sm-8">
                                                        {{-- <div class="summernote"></div> --}}
                                                        <textarea name="message" class="summernote" required>{{old('message')}}</textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="published" class="col-sm-2 col-form-label">Published (?)</label>
                                                    <div class="col-sm-10">
                                                        <div class="form-check mt-2">
                                                            <input name="published" class="form-check-input" type="checkbox" id="defaultCheck1">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 d-flex justify-content-center">
                                                <button class="btn btn-success px-5">Save Changes</button>&nbsp;
                                                <a class="btn btn-light" href="{{route('admin.announcements_index')}}">Cancel</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <!-- Summernote js -->
    <script src="{{ Theme::asset('assets/libs/summernote/summernote-bs4.min.js') }}"></script>
    <!-- init js -->
    <script src="{{ Theme::asset('assets/js/pages/form-editor.init.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>

    <script type="text/javascript">
    $(document).ready(function () {

        $('.datetime').datetimepicker({
            format: 'yyyy-mm-dd hh:ii'
        });


    });
    </script>
@endsection
