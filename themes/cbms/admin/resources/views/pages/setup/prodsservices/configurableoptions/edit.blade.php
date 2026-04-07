@extends('layouts.basecbms')

@section('title')
<title>{{ Cfg::getValue('CompanyName') }} -  Configurable Options</title>
@endsection
<style>
    @media(min-width:768px){
        .modal-dialog.modal-xl {
            width: 100%;
            max-width: 100%;
        }
    }
</style>
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
                        <div class="msg-alert"></div>
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- START HERE -->
                                <div class="card p-3">
                                    <form action="{{ url(Request::segment(1).'/setup/productservices/configurableoptions/update') }}" method="post" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <h4 class="card-title">Manage Group</h4>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Group Name</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="text" name="name" value="{{ $data->name }}" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Description</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="text" name="description" value="{{ $data->description }}" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-12 col-lg-2 col-form-label">Assigned Products</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <select name="productlinks[]" id="" class="form-control" multiple>
                                                            @foreach($product as $r)
                                                            <option value="{{ $r['id'] }}" {{ (in_array($r['id'],$link))?'selected':'' }}>{{ $r['groupname'] }} - {{ $r['name'] }} </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="card-title pt-3">
                                                    <h4 class="mb-3">Configurable Options</h4>
                                                </div>
                                                <div class="d-flex justify-content-center mb-3">
                                                    <!-- <a href="#" class="btn btn-light px-3 mx-1" data-toggle="modal" data-target="#addnewconfig">Add New Configurable Option</a> -->
                                                    <button type="button" onclick="addconfigoption()" class="btn btn-light px-3 mx-1">Add New Configurable Option</button>
                                                </div>
                                                <div class="formtable">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Option</th>
                                                                <th>Sort Order</th>
                                                                <th>Hidden</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            @foreach($configoptions as $r)
                                                            <tr id="tr{{ $r->id }}">
                                                                <td>{{ $r->optionname }}</td>
                                                                <td><input type="text" name="order[{{ $r->id }}]" value="{{ $r->order }}" class="form-control input-inline input-100"></td>
                                                                <td><input type="checkbox" name="hidden[{{ $r->id }}]" value="1" {{ ($r->order != 0)?'checked':'' }}></td>
                                                                <td>
                                                                    <form id="co{{  $r->id }}" action="{{ url(Request::segment(1).'/setup/productservices/configurableoptions/destroy') }}" method="POST">
                                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                                        <input type="hidden" name="_method" value="DELETE">
                                                                        <input type="hidden" name="id" value="{{  $r->id }}">
                                                                        <button type="button" title="Edit {{ $r->optionname }}" data-id="{{  $r->id }}" class="btn btn-info btn-xs editconfig"><i class="far fa-edit"></i></button>
                                                                        <button type="button" data-id="{{  $data->id }}" data-opid="{{  $r->id }}" data-title="{{ $r->optionname }}" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>

                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 d-flex justify-content-center">
                                                {{ csrf_field() }}
                                                @method('PUT')
                                                <input type="hidden" value="{{ $data->id }}" name="id" />
                                                <button type="submit" class="btn btn-success px-3 mx-1">Save Changes</button>
                                                <a href="{{ url(Request::segment(1).'/setup/productservices/configurableoptions') }}" class="btn btn-light px-3 mx-1">Back To Group List</a>
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

            <!-- modal popup-->
            <!-- Modal -->
            <div class="modal fade" id="addnewconfig" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <form action="" id="manageConfigurable" method="post" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Configurable Options</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="modal-msg-alert"></div>
                                    <div class="container-fluid">
                                        <div class="render">
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <div class="form-group row">
                                                    <label for="" class="col-sm-4">Option Name</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="configoptionname" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group row">
                                                    <label class="col-sm-4">Option Type:</label>
                                                    <div class="col-sm-8">
                                                        <select name="configoptiontype" class="form-control">
                                                            <option value="1">Dropdown</option>
                                                            <option value="2">Radio</option>
                                                            <option value="3">Yes/No</option>
                                                            <option value="4">Quantity</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <table class="table" id="tableloadfrom">
                                            <thead>
                                                <tr>
                                                    <th>Option</th>
                                                    <th>One Time/Monthly</th>
                                                    <th>Semi-Annual</th>
                                                    <th>Biennial</th>
                                                    <th>Triennial</th>
                                                    <th>Order</th>
                                                    <th>Hide</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="form-group row">
                                                            <label for="" class="col-3">Add Option:</label>
                                                            <div class="col-9">
                                                                <input type="text" name="addoptionname" class="form-control">
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>

                                                    </td>
                                                    <td>

                                                    </td>
                                                    <td>

                                                    </td>

                                                    <td>

                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="text" name="addsortorder" value="0" class="form-control" style="width: 60px;">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group">
                                                            <input type="checkbox" name="addhidden" value="1">
                                                        </div>
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>  
                                </div>
                            </div>
                            <div class="modal-footer">
                                {{ csrf_field() }}
                                <input type="hidden" value="{{ $data->id }}" name="gid" />
                                <button type="submit" class="btn btn-primary">Save changes</button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>


            <!-- end---->


            <!-- modal popup EDIT-->
            <!-- Modal -->
            <div class="modal fade" id="addnewconfigedit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <form action="" id="manageConfigurable" method="post" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Configurable Options</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="modal-msg-alert"></div>
                                <div class="container-fluid">
                                    <div class="render">
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label for="" class="col-sm-4">Option Name</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="configoptionname" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4">Option Type:</label>
                                                <div class="col-sm-8">
                                                    <select name="configoptiontype" class="form-control">
                                                        <option value="1">Dropdown</option>
                                                        <option value="2">Radio</option>
                                                        <option value="3">Yes/No</option>
                                                        <option value="4">Quantity</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--
                                    <table class="table" id="tableloadfrom">
                                        <thead>
                                            <tr>
                                                <th>Option</th>
                                                <th</th>
                                                <th</th>
                                                <th>One Time/Monthly</th>
                                                <th>Semi-Annual</th>
                                                <th>Biennial</th>
                                                <th>Triennial</th>
                                                <th>Order</th>
                                                <th>Hide</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr>
                                                <td>
                                                    <input type="text" name="optionname[149]" value="Account|Akun" class="form-control" style="min-width:180px;">
                                                </td>
                                                <td rowspan="2">
                                                    <b>IDR</b>
                                                </td>
                                                <td>Setup</td>
                                                <td><input type="text" name="price[1][149][1]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][2]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][3]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][4]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][5]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][11]" value="0.00" class="form-control"></td>
                                                <td rowspan="6"><input type="text" name="sortorder[149]" value="1" class="form-control"></td>
                                                <td rowspan="6"><input type="checkbox" name="hidden[149]" value="1"></td>

                                            </tr>
                                            <tr>

                                                <td>Price</td>
                                                <td><input type="text" name="price[1][149][1]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][2]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][3]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][4]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][5]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][11]" value="0.00" class="form-control"></td>
                                                <td rowspan="6"><input type="text" name="sortorder[149]" value="1" class="form-control"></td>
                                                <td rowspan="6"><input type="checkbox" name="hidden[149]" value="1"></td>

                                            </tr>
                                            <tr>
                                                <td rowspan="2">
                                                    <b>USD</b>
                                                </td>
                                                <td>Price</td>
                                                <td><input type="text" name="price[1][149][1]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][2]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][3]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][4]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][5]" value="0.00" class="form-control"></td>
                                                <td><input type="text" name="price[1][149][11]" value="0.00" class="form-control"></td>
                                                <td rowspan="6"><input type="text" name="sortorder[149]" value="1" class="form-control"></td>
                                                <td rowspan="6"><input type="checkbox" name="hidden[149]" value="1"></td>

                                            </tr>

                                            <tr>
                                                <td>
                                                    <div class="form-group row">
                                                        <label for="" class="col-3">Add Option:</label>
                                                        <div class="col-9">
                                                            <input type="text" name="addoptionname" class="form-control">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>

                                                </td>
                                                <td>

                                                </td>
                                                <td>

                                                </td>

                                                <td>

                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" name="addsortorder" value="0" class="form-control" style="width: 60px;">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="checkbox" name="addhidden" value="1">
                                                    </div>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>

                                    -->

                                    <table class="table" id="tableloadfrom">
                                        <tbody>
                                            <tr style="text-align:center;font-weight:bold;">
                                                <td>Options</td>
                                                <td width="70">&nbsp;</td>
                                                <td width="70">&nbsp;</td>
                                                <td width="70">One Time/<br>Monthly</td>
                                                <td width="70">Quarterly</td>
                                                <td width="70">Semi-Annual</td>
                                                <td width="70">Annual</td>
                                                <td width="70">Biennial</td>
                                                <td width="70">Triennial</td>
                                                <td width="80">Order</td>
                                                <td width="30">Hide</td>
                                            </tr>
                                            <tr style="text-align:center;">
                                                <td rowspan="6"><input type="text" name="optionname[149]" value="Account|Akun" class="form-control" style="min-width:180px;"></td>
                                                <td rowspan="2"><b>IDR</b></td>
                                                <td>Setup</td>
                                                <td><input type="text" name="price[1][149][1]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][2]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][3]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][4]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][5]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][11]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td rowspan="6"><input type="text" name="sortorder[149]" value="1" class="form-control" style="width:60px;"></td>
                                                <td rowspan="6"><input type="checkbox" name="hidden[149]" value="1"></td>
                                            </tr>
                                            <tr style="text-align:center;">
                                                <td>Pricing</td>
                                                <td><input type="text" name="price[1][149][6]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][7]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][8]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][9]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][10]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[1][149][12]" value="0.00" class="form-control" style="width:80px;"></td>
                                            </tr>
                                            <tr style="text-align:center;">
                                                <td rowspan="2"><b>USD</b></td>
                                                <td>Setup</td>
                                                <td><input type="text" name="price[3][149][1]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][2]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][3]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][4]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][5]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][11]" value="0.00" class="form-control" style="width:80px;"></td>
                                            </tr>
                                            <tr  style="text-align:center;">
                                                <td>Pricing</td>
                                                <td><input type="text" name="price[3][149][6]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][7]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][8]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][9]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][10]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[3][149][12]" value="0.00" class="form-control" style="width:80px;"></td>
                                            </tr>
                                            <tr  style="text-align:center;">
                                                <td rowspan="2" ><b>USDWIRE</b></td>
                                                <td>Setup</td>
                                                <td><input type="text" name="price[5][149][1]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][2]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][3]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][4]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][5]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][11]" value="0.00" class="form-control" style="width:80px;"></td>
                                            </tr>
                                            <tr style="text-align:center;">
                                                <td>Pricing</td>
                                                <td><input type="text" name="price[5][149][6]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][7]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][8]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][9]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][10]" value="0.00" class="form-control" style="width:80px;"></td>
                                                <td><input type="text" name="price[5][149][12]" value="0.00" class="form-control" style="width:80px;"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="9"><b>Add Option:</b> <input type="text" name="addoptionname" class="form-control" style="display:inline-block;width:60%;"></td>
                                                <td><input type="text" name="addsortorder" value="0" class="form-control" style="width:60px;"></td>
                                                <td><input type="checkbox" name="addhidden" value="1"></td>
                                            </tr>
                                        </tbody>
                                    </table>


                                </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                {{ csrf_field() }}
                                <input type="hidden" value="{{ $data->id }}" name="gid" />
                                <button type="submit" class="btn btn-primary">Save changes</button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                        </div>
                </div>
            </div>


            <!-- end---->




        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    
    function addconfigoption() {
        window.open('{{ url(Request::segment(1).'/setup/productservices/configurableoptions/poppup?gid='.$data->id) }}','configoptions','top=500,width=1200,height=800,scrollbars=yes');
    }
    $(document).ready(function() {
       //editconfig
       $( ".editconfig" ).click(function() {
            var id=$(this).data('id');
            return  window.open('{{ url(Request::segment(1).'/setup/productservices/configurableoptions/poppup?cid=') }}'+id,'configoptions','top=500,width=1200,height=800,scrollbars=yes');
        });

        $("#manageConfigurable").submit(function() {
            var xajaxFile = "{{ url(Request::segment(1).'/setup/productservices/configurableoptions/manageoptions') }}";
            $('.modal-msg-alert').html('');
            $.ajax({
                type: 'POST',
                url: xajaxFile,
                data: $("#manageConfigurable").serialize(),
                dataType: 'json',
                success: function(data) {
                    if (!data.error) {
                        console.log(data, 'ini adalah dtanya');
                        loadtable(data,'.addnewconfig .render');
                        var addhtml = '';

                        $(".modal-msg-alert").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="glyphicon glyphicon-ok-circle iconleft" aria-hidden="true"></span> ' + data.alert + "</div>");
                    } else {
                        $(".modal-msg-alert").html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="glyphicon glyphicon-exclamation-sign iconleft" aria-hidden="true"></span> ' + data.alert + "</div>");
                    }
                }
            });
            return false;
        });


        $('.table').on('click', '.delete', function() {
            var id = $(this).data('id');
            var opid = $(this).data('opid');
            var token = "{{ csrf_token() }}";
            Swal.fire({
                    title: "Warning..!",
                    text: "Do you want to delete Configurable Option Groups  " + $(this).data('title') + " ?",
                    icon: "warning",
                    showCancelButton: true,
                    cancelButtonColor: '#d33',
                    buttons: true,
                    dangerMode: true,
                })
                .then((value) => {
                    if (value.isConfirmed) {
                        $('#fd' + $(this).data('id')).submit();
                        $.ajax({
                            type: 'POST',
                            url: '{{ url(Request::segment(1).'/setup/productservices/configurableoptions/manageoptions/destroy') }}',
                            data: {
                                id: id,
                                opid: opid,
                                _token: token,
                                _method: 'DELETE'
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (!data.error) {

                                    var addhtml = '';
                                    $('#tr' + opid).remove();
                                    $(".msg-alert").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="glyphicon glyphicon-ok-circle iconleft" aria-hidden="true"></span> ' + data.alert + "</div>");
                                } else {
                                    $(".msg-alert").html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="glyphicon glyphicon-exclamation-sign iconleft" aria-hidden="true"></span> ' + data.alert + "</div>");
                                }
                            }
                        });


                       
                    } else {
                        return false;
                    }
                });
            return false;
        });


       


    });

    let loadtable = function(data,render){
        
        console.log(data.data.price,'adadad');

        var table='';
        var i=1;
        $.each(data.data.price,function( k, v ){
            
            console.log(k,'ini k');
            console.log(v,'ini v');

            var curren=data.data.curren;
            console.log(curren[k],'nyata');
            if(i == 1){
                table+=`
                        <tr style="text-align:center;">
                            <td rowspan="`+data.data.totalcurrencies+`"><input type="text" name="optionname[`+data.data.id+`]" value="`+data.data.optionname+`" class="form-control" style="min-width:180px;"></td>
                            <td rowspan="2"><b>`+k+`</b></td>
                            <td>Setup</td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][1]" value="`+v.getValue(1)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][2]" value="`+v.getValue(2)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][3]" value="`+v.getValue(3)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][4]" value="`+v.getValue(4)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][5]" value="`+v.getValue(5)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][11]" value="`+v.getValue(11)+`" class="form-control" style="width:80px;"></td>
                            <td rowspan="6"><input type="text" name="sortorder[`+data.data.id+`]" value="1" class="form-control" style="width:60px;"></td>
                            <td rowspan="6"><input type="checkbox" name="hidden[`+data.data.id+`]" value="1"></td>
                        </tr>
                        <tr style="text-align:center;">
                            <td>Pricing</td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][6]" value="`+v.getValue(6)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][7]" value="`+v.getValue(7)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][8]" value="`+v.getValue(8)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][9]" value="`+v.getValue(9)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][10]" value="`+v.getValue(10)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][12]" value="`+v.getValue(12)+`" class="form-control" style="width:80px;"></td>
                        </tr>
                        
                        `;

            }else{
                table+=` <tr style="text-align:center;">
                            <td rowspan="2"><b>`+k+`</b></td>
                            <td>Setup</td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][1]" value="`+v.getValue(1)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][2]" value="`+v.getValue(2)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][3]" value="`+v.getValue(3)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][4]" value="`+v.getValue(4)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][5]" value="`+v.getValue(5)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][11]" value="`+v.getValue(11)+`" class="form-control" style="width:80px;"></td>
                        </tr>
                        <tr  style="text-align:center;">
                            <td>Pricing</td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][6]" value="`+v.getValue(6)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][7]" value="`+v.getValue(7)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][8]" value="`+v.getValue(8)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][9]" value="`+v.getValue(9)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][10]" value="`+v.getValue(10)+`" class="form-control" style="width:80px;"></td>
                            <td><input type="text" name="price[`+curren.getValue(k)+`][`+data.data.id+`][12]" value="`+v.getValue(12)+`" class="form-control" style="width:80px;"></td>
                        </tr>`;


            }
            



            i++;
        });

        var table=`
                     <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label for="" class="col-sm-4">Option Name</label>
                                <div class="col-sm-8">
                                    <input type="text" name="configoptionname" value="`+data.data.optionname+`" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label class="col-sm-4">Option Type:</label>
                                <div class="col-sm-8">
                                    <select name="configoptiontype" class="form-control">
                                        <option value="1">Dropdown</option>
                                        <option value="2">Radio</option>
                                        <option value="3">Yes/No</option>
                                        <option value="4">Quantity</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>



                <table class="table" id="tableloadfrom">
                    <tbody>
                        <tr style="text-align:center;font-weight:bold;">
                            <td>Options</td>
                            <td width="70">&nbsp;</td>
                            <td width="70">&nbsp;</td>
                            <td width="70">One Time/<br>Monthly</td>
                            <td width="70">Quarterly</td>
                            <td width="70">Semi-Annual</td>
                            <td width="70">Annual</td>
                            <td width="70">Biennial</td>
                            <td width="70">Triennial</td>
                            <td width="80">Order</td>
                            <td width="30">Hide</td>
                        </tr>
                       
                       `+table+`
                       
                       
                       
                    </tbody>
                </table>
        
                `;



        $(render).html(table);
       
    }


</script>
@endsection




