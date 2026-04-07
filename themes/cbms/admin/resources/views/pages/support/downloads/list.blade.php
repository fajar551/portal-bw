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
                                        <h4 class="mb-3">Downloads </h4>
                                    </div>
                                </div>
                            </div>
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
                                                    <div class="form-group row">
                                                        <label for="catName" class="col-sm-2 col-form-label">Category
                                                            Name</label>
                                                        <div class="col-sm-3">
                                                            <input type="text" name="catName" id="catName"
                                                                class="form-control" />
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <div class="form-check mt-2">
                                                                <input class="form-check-input" type="checkbox"
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
                                                            <input type="text" name="description" id="description-input"
                                                                class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12 d-flex justify-content-center">
                                                        <button class="btn btn-success px-3">Add Category</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="nav-add-download" role="tabpanel"
                                                aria-labelledby="nav-add-download-tab">
                                                <div class="card pt-3 px-3">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">
                                                            Type
                                                        </label>
                                                        <div class="col-sm-12 col-lg-3">
                                                            <select name="" id="" class="form-control">
                                                                <option value="0">ZIP File</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for=""
                                                            class="col-sm-12 col-lg-2 col-form-label">Title</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input type="text" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="description"
                                                            class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <textarea name="" class="form-control" id="" cols="30"
                                                                rows="5"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="Upload File"
                                                            class="col-sm-12 col-lg-2 d-flex align-items-center">Upload
                                                            File</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="uploadFileOption" id="exampleRadios1"
                                                                    value="option1" checked>
                                                                <label class="form-check-label" for="exampleRadios1">
                                                                    Manual FTP Upload to Downloads Folder
                                                                </label>
                                                            </div>
                                                            <div class="d-flex">
                                                                <div class="d-inline">
                                                                    <label for="" class="pt-2">Enter Filename:
                                                                    </label>
                                                                </div>
                                                                <div class="d-inline w-25">
                                                                    <input type="text" class="form-control ml-2">
                                                                </div>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="uploadFileOption" id="exampleRadios2"
                                                                    value="option2">
                                                                <label class="form-check-label" for="exampleRadios2">
                                                                    Upload File
                                                                </label>
                                                            </div>
                                                            <div class="d-flex">
                                                                <div class="d-inline">
                                                                    <label for="" class="pt-2">Choose File:
                                                                    </label>
                                                                </div>
                                                                <div class="d-inline w-25">
                                                                    <div class="input-group mb-3">
                                                                        <div class="custom-file ml-2">
                                                                            <input type="file" class="custom-file-input"
                                                                                id="uploadFile"
                                                                                aria-describedby="inputGroupFileAddon01">
                                                                            <label class="custom-file-label"
                                                                                for="uploadFile">Choose file</label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <p class="text-danger">Server Max File Upload Size: 64M - To
                                                                increase this limit you need to modify your servers
                                                                php.ini file</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2">Clients
                                                            Only</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="clientsOnlyCheck">
                                                                <label class="custom-control-label"
                                                                    for="clientsOnlyCheck">Tick this box to only allow
                                                                    logged in clients permission to download it</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2">Product
                                                            Download</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="productDownloadCheck">
                                                                <label class="custom-control-label"
                                                                    for="productDownloadCheck">Tick this box if this
                                                                    download should only be available after a product or
                                                                    addon purchase</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2">Hidden</label>
                                                        <div class="col-sm-12 col-lg-10">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="hiddenCheck">
                                                                <label class="custom-control-label" for="hiddenCheck">Tick
                                                                    this box to only allow
                                                                    logged in clients permission to download it</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="p-3 text-center">
                                                        <button class="btn btn-success px-2">Add Download</button>
                                                        <button class="btn btn-light px-2">Cancel Changes</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <nav aria-label="breadcrumb">
                                                        <ol class="breadcrumb">
                                                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                                                            <li class="breadcrumb-item active" aria-current="page">File Name
                                                            </li>
                                                        </ol>
                                                    </nav>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="bg-light px-3 py-2 my-3 rounded">
                                                        <h3>
                                                            Files
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3 p-3 font-size-16">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-file-fill mr-2"></i>
                                                        <a href="{{ url('admin/support/downloads/detail') }}"
                                                            class="link-category">
                                                            Invoice Payment #778
                                                        </a>
                                                        <div class="action-btn ml-3 mt-1">
                                                            <a href="#delete" title="Delete Category" class="delete-icon">
                                                                <i class="ri-indeterminate-circle-fill"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <p class="p-0 m-0">Description</p>
                                                    <p class="text-muted">Downloads: 0</p>
                                                </div>
                                                <div class="col-lg-3 p-3 font-size-16">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-file-fill mr-2"></i>
                                                        <a href="#" class="link-category">
                                                            Invoice Payment #779
                                                        </a>
                                                        <div class="action-btn ml-3 mt-1">
                                                            <a href="#delete" title="Delete Category" class="delete-icon">
                                                                <i class="ri-indeterminate-circle-fill"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <p class="p-0 m-0">Description</p>
                                                    <p class="text-muted">Downloads: 0</p>
                                                </div>
                                                <div class="col-lg-3 p-3 font-size-16">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-file-fill mr-2"></i>
                                                        <a href="#" class="link-category">
                                                            Invoice Payment #780
                                                        </a>
                                                        <div class="action-btn ml-3 mt-1">
                                                            <a href="#delete" title="Delete Category" class="delete-icon">
                                                                <i class="ri-indeterminate-circle-fill"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <p class="p-0 m-0">Description</p>
                                                    <p class="text-muted">Downloads: 0</p>
                                                </div>
                                                <div class="col-lg-3 p-3 font-size-16">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-file-fill mr-2"></i>
                                                        <a href="#" class="link-category">
                                                            Invoice Payment #781
                                                        </a>
                                                        <div class="action-btn ml-3 mt-1">
                                                            <a href="#delete" title="Delete Category" class="delete-icon">
                                                                <i class="ri-indeterminate-circle-fill"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <p class="p-0 m-0">Description</p>
                                                    <p class="text-muted">Downloads: 0</p>
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
