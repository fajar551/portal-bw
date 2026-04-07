@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Add Billable Items</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                     
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Billable Items</h4>
                                    <div class="card p-3">
                                        <h6>Add Billable Item</h6>
                                        <hr class="mt-1">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label for="client-billable-item"
                                                        class="col-sm-2 col-form-label">Client</label>
                                                    <div class="col-sm-10">
                                                        <select class="form-control" name="client-billable-item"
                                                            id="client-billable-item">
                                                            <option></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="product-service"
                                                        class="col-sm-2 col-form-label">Product/Service</label>
                                                    <div class="col-sm-10">
                                                        <select class="form-control" name="product-service"
                                                            id="product-service">
                                                            <option></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="description-billable"
                                                        class="col-sm-2 col-form-label">Description</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="description-billable"
                                                            id="description-billable" />
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="hours" class="col-sm-2 col-form-label">Hours/Qty</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" name="hours" id="hours"
                                                            placeholder="0.0" />
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <label class="mt-2">
                                                            Hours
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="hours" class="col-sm-2 col-form-label">Amount</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="hours" id="hours"
                                                            placeholder="0.0" />
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="hours" class="col-sm-2 col-form-label">Invoice
                                                        Action</label>
                                                    <div class="col-sm-10">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="exampleRadios" id="exampleRadios1" value="option1"
                                                                checked>
                                                            <label class="form-check-label" for="exampleRadios1">
                                                                Don't Invoice Now
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="exampleRadios" id="exampleRadios2" value="option2">
                                                            <label class="form-check-label" for="exampleRadios2">
                                                                Invoice on Next Cron Run
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="exampleRadios" id="exampleRadios3" value="option3">
                                                            <label class="form-check-label" for="exampleRadios3">
                                                                Add to User's Next Invoice
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="exampleRadios" id="exampleRadios4" value="option4">
                                                            <label class="form-check-label" for="exampleRadios4">
                                                                Invoice as Normal for Due Date
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="exampleRadios" id="exampleRadios5" value="option5">
                                                            <div class="d-flex align-items-center">
                                                                <label class="form-check-label" for="exampleRadios5">
                                                                    Recur Every
                                                                </label>
                                                                <input type="text" name="times_invoice" id="times_invoice"
                                                                    class="form-control mx-2" />
                                                                <select name="duration" id="durationInvoice"
                                                                    class="form-control mx-2">
                                                                    <option>Never</option>
                                                                </select>
                                                                for
                                                                <input type="text" name="count_billable" id="count_billable"
                                                                    class="form-control mx-2" />
                                                                Times
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="hours" class="col-sm-2 col-form-label">(Next) Due
                                                        Date</label>
                                                    <div class="col-sm-10">
                                                        <input type="date" class="form-control" name="due-date"
                                                            id="due-date" />
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="hours" class="col-sm-2 col-form-label">Invoice
                                                        Count</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="inv-count"
                                                            id="inv-count" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <button class="btn btn-success px-3 float-lg-right">Save
                                                    Changes</button>
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
