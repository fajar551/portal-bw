@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Data Export</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <!-- End Sidebar -->
                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-4">Reports</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 col-lg-10">
                                    <!-- START HERE -->
                                    <h4 class="font-weight-bold">Client Data Export</h4>
                                </div>
                                <div class="col-6 col-lg-2 d-flex">
                                    <div class="dropdown ml-auto">
                                        <a class="btn btn-light btn-sm dropdown-toggle" href="#" role="button"
                                            id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false"> <i class="fa fa-cogs" aria-hidden="true"></i>
                                            Tools
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#">Export to CSV</a>
                                            <a class="dropdown-item" href="#">View Printable Version</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <p>This report allows you to generate a JSON export of data relating to a given client.
                                        You can choose which data points you wish to be included in the export below.</p>
                                </div>
                            </div>
                            <div class="card p-3">
                                <form action="#">
                                    <div class="form-gorup">
                                        <label for="clients">
                                            Choose the client to export
                                        </label>
                                        <select class="form-control" name="clients" id="clietn">
                                            <option value="0" selected>Airi Satou</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="row">
                                <div class="col-lg-3 col-sm-12 px-3">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck1">
                                            <label class="custom-control-label" for="customCheck1">Profile Data</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck2">
                                            <label class="custom-control-label" for="customCheck2">Domains</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck3">
                                            <label class="custom-control-label" for="customCheck3">Transaction</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck4">
                                            <label class="custom-control-label" for="customCheck4">Consent History</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-12 px-3">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck5">
                                            <label class="custom-control-label" for="customCheck5">Pay Method</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck6">
                                            <label class="custom-control-label" for="customCheck6">Billable Items</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck7">
                                            <label class="custom-control-label" for="customCheck7">Tickets</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck8">
                                            <label class="custom-control-label" for="customCheck8">Activity Log</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-12 px-3">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck9">
                                            <label class="custom-control-label" for="customCheck9">Contacts</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck10">
                                            <label class="custom-control-label" for="customCheck10">Invoices</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck11">
                                            <label class="custom-control-label" for="customCheck11">Emails</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-12 px-3">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck12">
                                            <label class="custom-control-label" for="customCheck12">Product/Services</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck13">
                                            <label class="custom-control-label" for="customCheck13">Quotes</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck14">
                                            <label class="custom-control-label" for="customCheck14">Notes</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <button class="btn btn-success px-5"><i class="fa fa-download mr-2"
                                            aria-hidden="true"></i>Download Export</button>
                                    <br>
                                    <small>* Generating an export for a client with a substantial amount of history may take
                                        a while</small>
                                </div>
                                <div class="col-sm-12 col-md-6 text-lg-right">
                                    <h6 class="text-muted">Report generated on 04/08/2021 14:05</h6>
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
