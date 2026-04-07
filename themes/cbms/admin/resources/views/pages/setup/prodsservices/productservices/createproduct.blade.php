@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Create New Product</title>
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
                                        <h4 class="mb-3">Products/Services</h4>
                                    </div>
                                </div>
                            </div>
                            @if (session('message'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                    id="success-alert">
                                    <h5>Something Went Wrong!</h5>
                                    <small>{!! session('message') !!}</small>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <form
                                            action="{{ route('admin.pages.setup.prodsservices.productservices.createproduct.add') }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" value="{{ $prodId }}" name="id">
                                            <div class="row mb-3">
                                                <div class="col-lg-12">
                                                    <h5>Create a New Product</h5>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                {{-- <label class="col-sm-12 col-lg-4 col-form-label">Product Type<br> --}}
                                                <label class="col-sm-12 col-lg-4 col-form-label"><br>
                                                    {{-- <small>Defines how WHMCS manages the item.
                                          Don't see the type of product you're looking for? Choose Other</small> --}}
                                                </label>
                                                <div class="col-sm-12 col-lg-8">
                                                    <div class="row clearfix">
                                                        {{-- <div class="col-lg-3 col-md-6 col-sm-12 d-flex">
                                             <label>
                                                <input type="radio" name="type" id="radio1" value="sharedhosting"
                                                   class="card-input-element" checked>
                                                <div class="card-radio">
                                                   <i class="ri-server-fill"></i>
                                                   <p>Shared Hosting</p>
                                                </div>
                                             </label>
                                          </div> --}}
                                                        {{-- <div class="col-lg-3 col-md-6 col-sm-12 d-flex">
                                             <label>
                                                <input type="radio" name="type" id="radio2" value="resellerhosting"
                                                   class="card-input-element">
                                                <div class="card-radio">
                                                   <i class="ri-cloud-fill"></i>
                                                   <p>Reseller Hosting</p>
                                                </div>
                                             </label>
                                          </div> --}}
                                                        {{-- <div class="col-lg-3 col-md-6 col-sm-12 d-flex">
                                             <label>
                                                <input type="radio" name="type" id="radio3" value="server"
                                                   class="card-input-element">
                                                <div class="card-radio">
                                                   <i class="ri-hard-drive-2-fill"></i>
                                                   <p>VPS/Server</p>

                                                </div>
                                             </label>
                                          </div> --}}
                                                        <div class="col-lg-3 col-md-6 col-sm-12 d-flex">
                                                            <label>
                                                                <input type="radio" name="type" id="radio2"
                                                                    value="other" class="card-input-element" checked>
                                                                <div class="card-radio">
                                                                    <i class="ri-file-copy-line"></i>
                                                                    <p>Other</p>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-lg-4 col-form-label">
                                                    Product Group
                                                    <br>
                                                    <small>Click here to create a new product group</small>
                                                </label>
                                                <div class="col-sm-12 col-lg-4">
                                                    <select name="gid" id="gid" class="form-control">
                                                        @foreach ($prodGroup as $prod)
                                                            <option value="{{ $prod->id }}">
                                                                {{ $prod->hidden == 1 ? $prod->name . ' (Hidden)' : $prod->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-lg-4 col-form-label">
                                                    Product Name
                                                    <br>
                                                    <small>The default display name for your new product</small>
                                                </label>
                                                <div class="col-sm-12 col-lg-4">
                                                    <input type="text" name="name" class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-lg-4 col-form-label">
                                                    Module
                                                    <br>
                                                    <small>Choose a module for automation</small>
                                                </label>
                                                <div class="col-sm-12 col-lg-4">
                                                    <select name="module" id="module" class="form-control">
                                                        <option value="0" selected>None</option>
                                                        @foreach ($modules as $key => $modules)
                                                            <option value="{{ $key }}">{{ $modules }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-12 col-lg-4 col-form-label" id="hidden">
                                                    Create as Hidden
                                                    <br>
                                                    <small>A hidden product is not visible to end users</small>
                                                </label>
                                                <div class="col-sm-12 col-lg-4">
                                                    <input type="hidden" data-toggle="toggle" name="hidden"
                                                        value="0">
                                                    <input type="checkbox" data-toggle="toggle" name="hidden"
                                                        value="1">
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-success px-5">Continue</button>
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
    <script src="{{ Theme::asset('assets/libs/bootstrap-switch-custom/bootstrap4-toggle.min.js') }}"></script>
@endsection
