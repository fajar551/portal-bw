@extends('layouts.clientbase')

@section('tab-title')
    View Addons
@endsection

@section('content')
    <div class="page-content" id="addons-page">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h3 class="font-weight-bold">Product Addons</h3>
                </div>
                <div class="col-sm-12 col-md-3 mt-3">
                    <select name="cat" class="cat form-control">
                        <option value="0">Categories</option>
                    </select>
                </div>
                <div class="col-md-10">
                    <div class="row mt-3">
                        @foreach ($addons as $addon)
                            <div class="col-sm-12 col-md-6">
                                <div class="card card-addons p-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="w-100">
                                            <h5 class="mb-2">{{ $addon->name }}</h5>
                                            <select name="cat" class="form-control form-control-sm dropdown">
                                                <option value="0">Assign Product</option>
                                                <option value="1">Relabs 20Mbps</option>
                                            </select>
                                        </div>
                                        <div class="addons-text w-50 text-center">
                                            <div class="text-muted mb-2">
                                                Rp. 200.000
                                            </div>
                                            <button class="btn btn-block btn-sm btn-main-relabs">Order</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div> <!-- container-fluid -->
    </div>
@endsection
