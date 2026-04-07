@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Create New Bundle</title>
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
                                        <h4 class="mb-3">Product Bundles</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <h4 class="card-title mb-3">Create New Bundle</h4>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Name</label>
                                                    <div class="col-sm-12 col-lg-4">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Valid From</label>
                                                    <div class="col-sm-12 col-lg-2">
                                                        <input type="date" name="" id="" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Valid Until</label>
                                                    <div class="col-sm-12 col-lg-2">
                                                        <input type="date" name="" id="" class="form-control">
                                                    </div>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="noExpiry">
                                                            <label class="custom-control-label" for="noExpiry">No
                                                                Expiry</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Uses</label>
                                                    <div class="col-sm-12 col-lg-3">
                                                        <input type="text" name="" id="" class="form-control" value="0">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Maximum Uses</label>
                                                    <div class="col-sm-12 col-lg-3">
                                                        <input type="text" name="" id="" class="form-control" value="0">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Bundle Items</label>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <p>Save Name First, Then you can add items</p>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Allow
                                                        Promotions</label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="allowPromotions">
                                                            <label class="custom-control-label" for="allowPromotions">Tick
                                                                to allow promotion codes to be used in conjunction with this
                                                                bundle</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Show in Product
                                                        Group</label>
                                                    <div class="col-sm-12 col-lg-5 pt-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="showOrderForm">
                                                            <label class="custom-control-label" for="showOrderForm">Tick
                                                                to display in product group on order form</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="collapse" id="orderForm">
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Product
                                                            Group</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <select name="" id="" class="form-control">
                                                                <option value="0">Choose one...</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Product
                                                            Description</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <textarea name="" id="" cols="30" rows="5" class="form-control">

                                                                                                                                                                                                                                                  </textarea>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-5">
                                                            You may use HTML in this field
                                                            <p>
                                                                &lt;br /&gt; <span class="ml-2">New Line</span><br>
                                                                &lt;strong&gt;Bold&lt;/strong&gt;
                                                                <span class="ml-2"><strong>Bold</strong></span><br>
                                                                &lt;em&gt;Italics&lt;/em&gt;
                                                                <span class="ml-2"><em>Italics</em></span><br>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Display
                                                            Price</label>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <input type="number" class="form-control" placeholder="0.00">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <p>Optionally enter a headline price to display in the product
                                                                listing</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Sort Order</label>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <input type="number" class="form-control" placeholder="0">
                                                        </div>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <p>Optionally enter a headline price to display in the product
                                                                listing</p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Featured</label>
                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="featuredCheck">
                                                                <label class="custom-control-label"
                                                                    for="featuredCheck">Check
                                                                    this custom checkbox</label>
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
                        <!-- End MAIN CARD -->
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        <script src="{{ Theme::asset('assets/js/check-reveal.js') }}"></script>
    @endsection
