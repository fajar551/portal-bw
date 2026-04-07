@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Downloads</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">



                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Downloads</h4>
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
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <nav>
                                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                <a class="nav-item nav-link active" id="nav-add-category-tab"
                                                    data-toggle="tab" href="#nav-add-category" role="tab"
                                                    aria-controls="nav-add-category" aria-selected="true">Add
                                                    Category</a>
                                                <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab"
                                                    href="#nav-add-download" role="tab" aria-controls="nav-add-download"
                                                    aria-selected="false">Add Download</a>
                                            </div>
                                        </nav>
                                        <div class="tab-content" id="nav-tabContent">
                                            <div class="tab-pane fade show active" id="nav-add-category" role="tabpanel"
                                                aria-labelledby="nav-add-category-tab">
                                                <div class="card p-3">
                                                    <form action="../support/downloads/category-store" method="post" enctype="multipart/form-data">
                                                        {{ csrf_field() }}
                                                        <div class="form-group row">
                                                            <label for="catName" class="col-sm-2 col-form-label">Category
                                                                Name</label>
                                                            <div class="col-sm-3">
                                                                @if ($errors->has('name'))
                                                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                                                @endif
                                                                <input type="text" name="name" id="catName"
                                                                    class="form-control" />
                                                            </div>
                                                            <div class="col-sm-3">
                                                                <div class="form-check mt-2">
                                                                    <input class="form-check-input" name="hidden" type="checkbox"
                                                                        id="gridCheck1">
                                                                    <label class="form-check-label" for="gridCheck1">
                                                                        Tick to Hide
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="description-input"
                                                                class="col-sm-2 col-form-label">Description</label>
                                                            <div class="col-sm-8">
                                                                @if ($errors->has('description'))
                                                                    <span class="text-danger">{{ $errors->first('description') }}</span>
                                                                @endif
                                                                <input type="text" name="description" id="description-input"
                                                                    class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 d-flex justify-content-center">
                                                            <button type="submit" class="btn btn-success px-3">Add Category</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade font-size-16" id="nav-add-download" role="tabpanel"
                                                aria-labelledby="nav-add-download-tab">
                                                <div class="card pt-3 px-3">
                                                    <div class="alert alert-info" role="alert">
                                                        You cannot add a download to the top level category
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <nav aria-label="breadcrumb">
                                                    <ol class="breadcrumb">
                                                        <li class="breadcrumb-item active" aria-current="page">Home</li>
                                                    </ol>
                                                </nav>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="bg-light px-3 py-2 my-3 rounded">
                                                    <h3>
                                                        Categories
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4 p-3 font-size-18">
                                                <div class="d-flex align-items-center">
                                                    <i class=" ri-folder-fill mr-2"></i>
                                                    <a href="{{ url('admin/support/downloads/list') }}"
                                                        class="link-category">
                                                        Invoice(3)
                                                    </a>
                                                    <div class="action-btn ml-3 mt-1">
                                                        <a href="#edit" class="p-0 mr-2" title="Edit Category"
                                                            class="edit-icon">
                                                            <i class="ri-edit-box-line"></i>
                                                        </a>
                                                        <a href="#delete" title="Delete Category" class="delete-icon">
                                                            <i class="ri-indeterminate-circle-fill"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <p class="text-muted font-size-12">Files of all invoice</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
