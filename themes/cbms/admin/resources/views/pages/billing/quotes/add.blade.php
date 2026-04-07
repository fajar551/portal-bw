@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Create New Quotes</title>
@endsection

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
                                        <h4 class="mb-3">Quotes</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <h5 class="mb-3">General Information</h5>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group row">
                                                    <label for="subjectQuotes"
                                                        class="col-sm-2 col-form-label">Subject</label>
                                                    <div class="col-sm-10">
                                                        <input type="email" class="form-control" id="subjectQuotes">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="dateCreated" class="col-sm-2 col-form-label">Date
                                                        Created</label>
                                                    <div class="col-sm-10">
                                                        <input type="date" class="form-control" id="dateCreated">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group row">
                                                    <label for="stageQuotes" class="col-sm-2 col-form-label">Stage</label>
                                                    <div class="col-sm-10">
                                                        <select class="form-control" id="stageQuotes">
                                                            <option>Draft</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="validUntil" class="col-sm-2 col-form-label">Valid
                                                        Until</label>
                                                    <div class="col-sm-10">
                                                        <input type="date" class="form-control" id="validUntil">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12 d-lg-flex justify-content-center mb-3">
                                                <button class="btn btn-primary px-3 m-1">Save Changes</button>
                                                <button class="btn btn-light px-3 m-1">Duplicate</button>
                                                <button class="btn btn-light px-3 m-1">Printable Version</button>
                                                <button class="btn btn-light px-3 m-1">View PDF</button>
                                                <button class="btn btn-light px-3 m-1">Download PDF</button>
                                                <button class="btn btn-light px-3 m-1">Email as PDF</button>
                                                <button class="btn btn-light px-3 m-1">Convert to Invoice</button>
                                                <button class="btn btn-danger px-3 m-1">Delete</button>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <h5>Client Infomartion</h5>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="radioButtonClient"
                                                        id="existingClient" value="option1" checked>
                                                    <label class="form-check-label" for="existingClient">
                                                        <strong>Quote for existing client:</strong>
                                                    </label>
                                                    <input type="text" name="clientName" id="client-name"
                                                        class="form-control">
                                                </div>


                                                <div class="form-check mt-3">
                                                    <input class="form-check-input" type="radio" name="radioButtonClient"
                                                        id="exampleRadios2" value="option2">
                                                    <label class="form-check-label" for="exampleRadios2">
                                                        Quote for new client:
                                                    </label>
                                                </div>
                                                <div class="collapse" id="collapseExample">
                                                    <div class="card px-4">
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="form-group row">
                                                                    <label for="firstName" class="col-sm-3 col-form-label">
                                                                        First Name
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="first_name" id="firstName"
                                                                            class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="lastName" class="col-sm-3 col-form-label">
                                                                        Last Name
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="last_name" id="lastName"
                                                                            class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="companyName"
                                                                        class="col-sm-3 col-form-label">
                                                                        Company Name
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="company_name"
                                                                            id="companyName" class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="emailAddress"
                                                                        class="col-sm-3 col-form-label">
                                                                        Email Address
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="email" name="email_address"
                                                                            id="emailAddress" class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="phoneNumber"
                                                                        class="col-sm-3 col-form-label">
                                                                        Phone Number
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="phone_number"
                                                                            id="phoneNumber" class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="currency" class="col-sm-3 col-form-label">
                                                                        Email Address
                                                                    </label>
                                                                    <div class="col-sm-3">
                                                                        <select name="currency" id="currency"
                                                                            class="form-control">
                                                                            <option value="0">IDR</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group row">
                                                                    <label for="address1" class="col-sm-3 col-form-label">
                                                                        Address 1
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="address1" id="address1"
                                                                            class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="address2" class="col-sm-3 col-form-label">
                                                                        Address 2
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="address2" id="address2"
                                                                            class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="cities" class="col-sm-3 col-form-label">
                                                                        City
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="cities" id="cities"
                                                                            class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="stateRegion"
                                                                        class="col-sm-3 col-form-label">
                                                                        State/Region
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="stateRegion"
                                                                            id="stateRegion" class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="postCode" class="col-sm-3 col-form-label">
                                                                        Postcode
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" name="postCode" id="postCode"
                                                                            class="form-control">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="country" class="col-sm-3 col-form-label">
                                                                        Country
                                                                    </label>
                                                                    <div class="col-sm-8">
                                                                        <select name="count" id="country"
                                                                            class="form-control">
                                                                            <option value="0">United States</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <h5>Line Items</h5>
                                                <div>
                                                    <table id="datatable" class="table dt-responsive w-100">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th style="width: 50px;">Qty</th>
                                                                <th style="width: 100vw">Description</th>
                                                                <th style="width: 70px;">Unit Price</th>
                                                                <th style="width: 70px;">Discount</th>
                                                                <th style="width: 50px;">Total</th>
                                                                <th style="width: 50px;">Taxed</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <input type="number" name="quantity" id="qty-quote"
                                                                        class="form-control" />
                                                                </td>
                                                                <td>
                                                                    <textarea name="description" id="description-quotes"
                                                                        cols="10" rows="1" class="form-control"></textarea>
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="unitPrice" id="unit-price"
                                                                        placeholder="0.00" class="form-control" />
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="discount" id="discount"
                                                                        placeholder="0.00" class="form-control" />
                                                                </td>
                                                                <td>

                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="ordercheck1">
                                                                        <label class="custom-control-label"
                                                                            for="ordercheck1">&nbsp;</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table class="table table-sm mt-2">
                                                        <tbody>
                                                            <tr class="text-right font-weight-bold">
                                                                <td style="width: 71vw;">Sub Total</td>
                                                                <td>Rp.0.00</td>
                                                            </tr>
                                                            <tr class="text-right font-weight-bold">
                                                                <td style="width: 71vw;">PPn @ 10%</td>
                                                                <td>Rp.0.00</td>
                                                            </tr>
                                                            <tr class="text-right font-weight-bold">
                                                                <td style="width: 71vw;">Total Due</td>
                                                                <td>Rp.0.00</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h5 class="mt-2">
                                                    Notes
                                                </h5>
                                                <form>
                                                    <div class="form-group row d-flex align-items-center">
                                                        <label for="note1" class="col-sm-4 col-form-label">Proposal Text
                                                            (Displayed at the Top of the Quote)</label>
                                                        <div class="col-sm-8">
                                                            <textarea name="note1" id="note1" cols="30" rows="5"
                                                                class="form-control"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row d-flex align-items-center">
                                                        <label for="note2" class="col-sm-4 col-form-label">Customer
                                                            Notes
                                                            (Displayed as a Footer to the Quote)</label>
                                                        <div class="col-sm-8">
                                                            <textarea name="note2" id="note2" cols="30" rows="5"
                                                                class="form-control"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row d-flex align-items-center">
                                                        <label for="note3" class="col-sm-4 col-form-label">Admin Only
                                                            Notes
                                                            (Private Notes)</label>
                                                        <div class="col-sm-8">
                                                            <textarea name="note3" id="note3" cols="30" rows="5"
                                                                class="form-control"></textarea>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-lg-12 d-lg-flex justify-content-center mb-3">
                                                <button class="btn btn-primary px-3 m-1">Save Changes</button>
                                                <button class="btn btn-light px-3 m-1">Duplicate</button>
                                                <button class="btn btn-light px-3 m-1">Printable Version</button>
                                                <button class="btn btn-light px-3 m-1">View PDF</button>
                                                <button class="btn btn-light px-3 m-1">Download PDF</button>
                                                <button class="btn btn-light px-3 m-1">Email as PDF</button>
                                                <button class="btn btn-light px-3 m-1">Convert to Invoice</button>
                                                <button class="btn btn-danger px-3 m-1">Delete</button>
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

@section('scripts')
    <!-- Required datatable js -->
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/add-new-quotes.js') }}"></script>
@endsection
