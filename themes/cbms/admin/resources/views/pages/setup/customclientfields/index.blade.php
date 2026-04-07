@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Custom Client Fields</title>
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
                                        <h4 class="mb-3">Custom Client Fields</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <form action="{{route('admin.pages.setup.customclientfields.save')}}" method="post">
                                        @csrf
                                        <div class="card p-3">
                                            <p>This is where you configure custom fields which appear in the clients profile</p>
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
                                            @foreach ($result->toArray() as $data)
                                                @php
                                                    $fid = $data['id'];
                                                @endphp
                                                <div class="container bg-light py-3">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Field Name</label>
                                                                <div class="col-sm-12 col-lg-5">
                                                                    <input name="fieldname[{{$fid}}]" value="{{$data['fieldname']}}" type="text" class="form-control">
                                                                </div>
                                                                <div class="col-sm-12 col-lg-5 text-right">
                                                                    <div class="d-flex justify-content-end align-items-center">
                                                                        <label class="mb-0 mr-3">Display Order</label>
                                                                        <input name="sortorder[{{$fid}}]" value="{{$data['sortorder']}}" type="number" class="form-control w-25">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Field Type</label>
                                                                <div class="col-sm-12 col-lg-3">
                                                                    <select name="fieldtype[{{$fid}}]" class="form-control">
                                                                        <option value="text" {{$data['fieldtype'] == 'text' ? 'selected' : ''}}>Text Box</option>
                                                                        <option value="link" {{$data['fieldtype'] == 'link' ? 'selected' : ''}}>Link/URL</option>
                                                                        <option value="password" {{$data['fieldtype'] == 'password' ? 'selected' : ''}}>Password</option>
                                                                        <option value="dropdown" {{$data['fieldtype'] == 'dropdown' ? 'selected' : ''}}>Drop Down</option>
                                                                        <option value="tickbox" {{$data['fieldtype'] == 'tickbox' ? 'selected' : ''}}>Tick Box</option>
                                                                        <option value="textarea" {{$data['fieldtype'] == 'textarea' ? 'selected' : ''}}>Text Area</option>
                                                                        <option value="image" {{$data['fieldtype'] == 'image' ? 'selected' : ''}}>Image</option>
                                                                        <option value="hidden" {{$data['fieldtype'] == 'hidden' ? 'selected' : ''}}>Disabled</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                                                <div class="col-sm-12 col-lg-5">
                                                                    <input name="description[{{$fid}}]" value="{{$data['description']}}" type="text" class="form-control">
                                                                </div>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <p>The explanation to show users</p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Validation</label>
                                                                <div class="col-sm-12 col-lg-5">
                                                                    <input name="regexpr[{{$fid}}]" value="{{App\Helpers\Sanitize::encode($data['regexpr'])}}" type="text" class="form-control">
                                                                </div>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <p>Regular Expression Validation String</p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Select Option</label>
                                                                <div class="col-sm-12 col-lg-5">
                                                                    <input name="fieldoptions[{{$fid}}]" value="{{$data['fieldoptions']}}" type="text" class="form-control">
                                                                </div>
                                                                <div class="col-sm-12 col-lg-5 pt-2">
                                                                    <p> For Dropdowns Only - Comma Seperated List</p>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label"></label>
                                                                <div class="col-sm-12 col-lg-5">
                                                                    <div class="d-flex ">
                                                                        <div class="custom-control custom-checkbox mr-3">
                                                                            <input name="adminonly[{{$fid}}]" {{$data['adminonly'] == 'on' ? 'checked' : ''}} type="checkbox" class="custom-control-input"
                                                                                id="adminOnly{{$fid}}">
                                                                            <label class="custom-control-label" for="adminOnly{{$fid}}">Admin
                                                                                Only</label>
                                                                        </div>
                                                                        <div class="custom-control custom-checkbox mr-3">
                                                                            <input name="required[{{$fid}}]" {{$data['required'] == 'on' ? 'checked' : ''}} type="checkbox" class="custom-control-input"
                                                                                id="requiredOnly{{$fid}}">
                                                                            <label class="custom-control-label"
                                                                                for="requiredOnly{{$fid}}">Required Field</label>
                                                                        </div>
                                                                        <div class="custom-control custom-checkbox mr-3">
                                                                            <input name="showorder[{{$fid}}]" {{$data['showorder'] == 'on' ? 'checked' : ''}} type="checkbox" class="custom-control-input"
                                                                                id="showOrder{{$fid}}">
                                                                            <label class="custom-control-label" for="showOrder{{$fid}}">Show
                                                                                on Order Form</label>
                                                                        </div>
                                                                        <div class="custom-control custom-checkbox mr-3">
                                                                            <input name="showinvoice[{{$fid}}]" {{$data['showinvoice'] == 'on' ? 'checked' : ''}} type="checkbox" class="custom-control-input"
                                                                                id="showInvoices{{$fid}}">
                                                                            <label class="custom-control-label" for="showInvoices{{$fid}}">Show
                                                                                on Invoice</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-12 col-lg-5 text-right">
                                                                    <a href="#" onclick="deleteCustomfiledConfirm('{{$fid}}');return false;" class="btn btn-danger btn-sm">Delete Field</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                            @endforeach
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h4 class="card-title mb-3 font-weight-bold">Add New Custom Field</h4>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Field Name</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input name="addfieldname" type="text" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Field Type</label>
                                                        <div class="col-sm-12 col-lg-3">
                                                            <select name="addfieldtype" id="" class="form-control">
                                                                <option value="text">Text Box</option>
                                                                <option value="link">Link/URL</option>
                                                                <option value="password">Password</option>
                                                                <option value="dropdown">Drop Down</option>
                                                                <option value="tickbox">Tick Box</option>
                                                                <option value="textarea">Text Area</option>
                                                                <option value="image">Image</option>
                                                                <option value="hidden">Disabled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input name="adddescription" type="text" class="form-control">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                            <p>The explanation to show users</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Validation</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input name="addregexpr" type="text" class="form-control">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                            <p>Regular Expression Validation String</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Select Option</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input name="addfieldoptions" type="text" class="form-control">
                                                            <div class="d-flex">
                                                                <div class="custom-control custom-checkbox mr-3">
                                                                    <input name="addadminonly" type="checkbox" class="custom-control-input"
                                                                        id="adminOnly">
                                                                    <label class="custom-control-label" for="adminOnly">Admin
                                                                        Only</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox mr-3">
                                                                    <input name="addrequired" type="checkbox" class="custom-control-input"
                                                                        id="requiredOnly">
                                                                    <label class="custom-control-label"
                                                                        for="requiredOnly">Required Field</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox mr-3">
                                                                    <input name="addshoworder" type="checkbox" class="custom-control-input"
                                                                        id="showOrder">
                                                                    <label class="custom-control-label" for="showOrder">Show
                                                                        on Order Form</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox mr-3">
                                                                    <input name="addshowinvoice" type="checkbox" class="custom-control-input"
                                                                        id="showInvoices">
                                                                    <label class="custom-control-label" for="showInvoices">Show
                                                                        on Invoice</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                            <p> For Dropdowns Only - Comma Seperated List</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 text-center">
                                                    <button class="btn btn-success px-3">Save Changes</button>
                                                    <button class="btn btn-light px-3">Cancel Changes</button>
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

@section('scripts')
    <script src="{{Theme::asset('js/customfields.js')}}"></script>
@endsection
