@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Configurable Options</title>
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
                                        <h4 class="mb-3">Configurable Option Groups</h4>
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
                                    <form action="{{ url(Request::segment(1).'/setup/productservices/configurableoptions/store') }}" method="post" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <h4 class="card-title">Create a New Group</h4>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Group Name</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="text" name="name" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for=""
                                                        class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="text" name="description" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Assigned
                                                        Products</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <select name="productlinks[]" id="" class="form-control" multiple>
                                                            @foreach($product as $r)
                                                                <option value="{{ $r['id'] }}">{{ $r['groupname'] }} - {{ $r['name'] }} </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 d-flex justify-content-center">
                                                {{ csrf_field() }}
                                                <button type="submit" class="btn btn-success px-3 mx-1">Save Changes</button>
                                                <a href="{{ url(Request::segment(1).'/setup/productservices/configurableoptions') }}" class="btn btn-light px-3 mx-1">Back To Group List</a>
                                            </div>
                                        </div>
                                    </form>>
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
