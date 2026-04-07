@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Invoice (Number)</title>
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
                                        <h4 class="mb-3">Invoices #778</h4>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="card p-3">
                                                <div class="row">
                                                    <div class="col-lg-8">
                                                        <nav>
                                                            <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                                                                <li class="nav-item" role="presentation">
                                                                    <a class="nav-link active" id="nav-summary-tab"
                                                                        data-toggle="tab" href="#nav-summary" role="tab"
                                                                        aria-controls="nav-summary"
                                                                        aria-selected="true">Summary</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" id="nav-add-payment-tab"
                                                                        data-toggle="tab" href="#nav-add-payment" role="tab"
                                                                        aria-controls="nav-add-payment"
                                                                        aria-selected="false">Add
                                                                        Payment</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" id="nav-options-tab"
                                                                        data-toggle="tab" href="#nav-options" role="tab"
                                                                        aria-controls="nav-options"
                                                                        aria-selected="false">Options</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" id="nav-credit-tab"
                                                                        data-toggle="tab" href="#nav-credit" role="tab"
                                                                        aria-controls="nav-credit"
                                                                        aria-selected="false">Credit</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" id="nav-refund-tab"
                                                                        data-toggle="tab" href="#nav-refund" role="tab"
                                                                        aria-controls="nav-refund"
                                                                        aria-selected="false">Refund</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" id="nav-notes-tab" data-toggle="tab"
                                                                        href="#nav-notes" role="tab"
                                                                        aria-controls="nav-notes"
                                                                        aria-selected="false">Notes</a>
                                                                </li>
                                                            </ul>
                                                        </nav>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="float-right">
                                                            <div class="btn-group mb-2" role="group"
                                                                aria-label="Action Button Group">
                                                                <button type="button"
                                                                    class="btn btn-light d-flex align-items-center">
                                                                    <i class="ri-article-fill mr-2"></i>View
                                                                    as
                                                                    Client</button>
                                                                <button type="button"
                                                                    class="btn btn-light d-flex align-items-center">
                                                                    <i class="ri-printer-fill mr-2"></i>Print</button>
                                                                <button type="button"
                                                                    class="btn btn-light d-flex align-items-center">
                                                                    <i
                                                                        class="ri-download-cloud-fill mr-2"></i>Download</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- ============================================== -->
                                                <!-- InvoiceTab - Summary -->
                                                <!-- ============================================== -->
                                                <div class="tab-content" id="nav-tabContent">
                                                    <div class="tab-pane fade show active" id="nav-summary" role="tabpanel"
                                                        aria-labelledby="nav-summary-tab">
                                                        <div class="card px-3 pb-3 pt-1">
                                                            <div class="row">
                                                                <div class="col-lg-12 bg-light p-2">
                                                                    <div class="float-lg-right">
                                                                        <button class="btn btn-primary px-3">
                                                                            Publish
                                                                        </button>
                                                                        <button class="btn btn-warning px-3">
                                                                            Publish and Send Email
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 p-2">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>Client Name</td>
                                                                                    <td>Tiger Nixon (View Invoices)</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Invoice Date</td>
                                                                                    <td>12/05/2021</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Due Date</td>
                                                                                    <td>26/05/2021</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Total Due</td>
                                                                                    <td>Rp. 0.00</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>Balance</td>
                                                                                    <td class="text-success">Rp. 0.00
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6 p-2">
                                                                    <div class="text-center">
                                                                        <h3 class="font-weight-bold text-muted">DRAFT
                                                                        </h3>
                                                                        <p>Payment Method: <strong>No Transaction
                                                                                Applied</strong></p>
                                                                    </div>
                                                                    <div class="row justify-content-center">
                                                                        <div class="col-sm-8">
                                                                            <div class="form-group row">
                                                                                <div class="col-sm-8 my-1">
                                                                                    <select name="invoice-stats"
                                                                                        id="invoice-stats"
                                                                                        class="form-control">
                                                                                        <option>Invoice Created</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-sm-4 my-1">
                                                                                    <button
                                                                                        class="btn btn-light btn-block">Send
                                                                                        Email</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row justify-content-center">
                                                                        <div
                                                                            class="col-lg-8 col-sm-12  d-flex justify-content-between">
                                                                            <button class="btn btn-success m-1 px-3">Attempt
                                                                                Capture</button>
                                                                            <button class="btn btn-light m-1 px-3">Mark
                                                                                Cancelled</button>
                                                                            <button class="btn btn-light m-1 px-3">Mark
                                                                                Unpaid</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- ============================================== -->
                                                    <!-- InvoiceTab - Add Payment -->
                                                    <!-- ============================================== -->
                                                    <div class="tab-pane fade" id="nav-add-payment" role="tabpanel"
                                                        aria-labelledby="nav-add-payment-tab">
                                                        <div class="row">
                                                            <div class="col-lg-12 p-3">
                                                                <div class="alert alert-warning d-flex align-items-center"
                                                                    role="alert">
                                                                    <div class="col-1">
                                                                        <i class="ri-information-line ml-3"
                                                                            style="font-size: 48px;"></i>
                                                                    </div>
                                                                    <div class="col-10">
                                                                        <p style="font-size: 32px; margin-bottom: 0;">
                                                                            This is a Draft Invoice.
                                                                        </p>
                                                                        <p class="mb-0">
                                                                            Please Publish first to apply a payment
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- ============================================== -->
                                                    <!-- InvoiceTab - Options -->
                                                    <!-- ============================================== -->
                                                    <div class="tab-pane fade" id="nav-options" role="tabpanel"
                                                        aria-labelledby="nav-options-tab">
                                                        <div class="row py-3">
                                                            <div class="col-lg-6">
                                                                <div class="form-group row">
                                                                    <label for="invoiceDate"
                                                                        class="col-sm-2 col-form-label">Invoice
                                                                        Date</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="date" class="form-control"
                                                                            id="invoiceDate">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="paymentMethodOptions"
                                                                        class="col-sm-3 col-form-label">Payment
                                                                        Method</label>
                                                                    <div class="col-sm-9">
                                                                        <select class="form-control">
                                                                            <option value="Select..."></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="invoiceOptions"
                                                                        class="col-sm-2 col-form-label">Invoice
                                                                        #</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" class="form-control"
                                                                            id="invoiceOptions">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group row">
                                                                    <label for="dueDateOption"
                                                                        class="col-sm-2 col-form-label">Due
                                                                        Date</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="date" class="form-control"
                                                                            id="dueDateOption">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="taxRate" id="taxRate"
                                                                        class="col-sm-2 col-form-label">
                                                                        Tax Rate
                                                                    </label>
                                                                    <div class="col-sm-5">
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text">1</span>
                                                                            </div>
                                                                            <input type="text" class="form-control"
                                                                                placeholder="0.00">
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text">.00</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        <div class="input-group mb-3">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text">2</span>
                                                                            </div>
                                                                            <input type="text" class="form-control"
                                                                                placeholder="0.00">
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text">.00</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="statusInvoiceOptions"
                                                                        class="col-sm-2 col-form-label">Status</label>
                                                                    <div class="col-sm-10">
                                                                        <select class="form-control">
                                                                            <option value="1">Draft</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-12 d-flex justify-content-center">
                                                                <button class="btn btn-success px-3 mt-3">Save
                                                                    Changes</button>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                    <!-- ============================================== -->
                                                    <!-- InvoiceTab - Credit -->
                                                    <!-- ============================================== -->
                                                    <div class="tab-pane fade" id="nav-credit" role="tabpanel"
                                                        aria-labelledby="nav-credit-tab">
                                                        <div class="row py-3 justify-content-center">
                                                            <div class="col-lg-5">
                                                                <h4 class="card-title">Add Credit to Invoice</h4>
                                                                <p class="mb-3 text-success">Rp0.00
                                                                    Available</p>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <input type="text" name="addCredit" id="addCredit"
                                                                            class="form-control" placeholder="0.00">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <button class="btn btn-success px-5">
                                                                            Go
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-5">
                                                                <h4 class="card-title">Remove Credit to Invoice</h4>
                                                                <p class="mb-3 text-danger">Rp0.00
                                                                    Available</p>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <input type="text" name="removeCredit"
                                                                            id="removeCredit" class="form-control"
                                                                            placeholder="0.00">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <button class="btn btn-success px-5">
                                                                            Go
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- ============================================== -->
                                                    <!-- InvoiceTab - Refund -->
                                                    <!-- ============================================== -->
                                                    <div class="tab-pane fade" id="nav-refund" role="tabpanel"
                                                        aria-labelledby="nav-refund-tab">
                                                        <div class="row py-3">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row">
                                                                    <label for="transactionRefund"
                                                                        class="col-sm-2 col-form-label">
                                                                        Transactions
                                                                    </label>
                                                                    <div class="col-sm-5">
                                                                        <select name="transRefund" id="transRefund"
                                                                            class="form-control">
                                                                            <option>No Transactions Applied To This
                                                                                Invoice Yet</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="amountRefund"
                                                                        class="col-sm-2 col-form-label">Amount
                                                                        Refund</label>
                                                                    <div class="col-sm-3">
                                                                        <input type="number" name="amountRef"
                                                                            id="input-amount" class="form-control"
                                                                            placeholder="0.00">
                                                                    </div>
                                                                    <div class="col-sm-3 d-flex align-items-center">
                                                                        <h6>Leave blank for full refund</h6>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="refundType"
                                                                        class="col-sm-2 col-form-label">Refund
                                                                        Type</label>
                                                                    <div class="col-sm-5">
                                                                        <select name="refundType" id="refundType"
                                                                            class="form-control">
                                                                            <option value="0">Refund Through Gateway (If
                                                                                supported by module)</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="reversePayment"
                                                                        class="col-sm-2 col-form-label">Reverse
                                                                        Payment</label>
                                                                    <div class="col-sm-6">
                                                                        <div class="form-check mt-2">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                value="0" id="defaultCheck1">
                                                                            <label class="form-check-label"
                                                                                for="defaultCheck1">
                                                                                Undo automated actions triggered by
                                                                                this transaction - where possible.
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="sendEmail"
                                                                        class="col-sm-2 col-form-label">Send
                                                                        Email</label>
                                                                    <div class="col-sm-6">
                                                                        <div class="form-check mt-2">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                value="0" id="defaultCheck2">
                                                                            <label class="form-check-label"
                                                                                for="defaultCheck2">
                                                                                Tick to Send Confirmation Email
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12 d-flex justify-content-center">
                                                                <button class="btn btn-success px-5"
                                                                    disabled>Refund</button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <!-- ============================================== -->
                                                    <!-- InvoiceTab - Notes -->
                                                    <!-- ============================================== -->
                                                    <div class="tab-pane fade" id="nav-notes" role="tabpanel"
                                                        aria-labelledby="nav-notes-tab">
                                                        <div class="row py-3">
                                                            <div class="col-lg-12">
                                                                <textarea name="notesInvoice" id="notesInvoice" cols="30"
                                                                    class="form-control" rows="10"></textarea>
                                                            </div>
                                                            <div class="col-lg-12 d-flex justify-content-center">
                                                                <button class="btn btn-success px-5 my-3">
                                                                    Save Changes
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <h5>Invoice Items</h5>
                                                        <div class="table-responsive">
                                                            <table id="datatable"
                                                                class="table table-bordered dt-responsive w-100">
                                                                <thead>
                                                                    <tr class="text-center">
                                                                        <th style="width: 1000px;">
                                                                            Description
                                                                        </th>
                                                                        <th style="width: 120px;">Amount
                                                                        </th>
                                                                        <th style="width: 100px;">Taxed
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <td>
                                                                        <textarea class="form-control" name="description"
                                                                            id="descriptionInvoice" cols="3"
                                                                            rows="1"></textarea>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="amountInvoice"
                                                                            id="amount-invoice" class="form-control">
                                                                    </td>
                                                                    <td>
                                                                        <div class="form-check text-center mt-2">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                value="" id="defaultCheck1">
                                                                        </div>
                                                                    </td>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-sm mt-2">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="width: 58vw; text-align: right;">
                                                                            Sub Total
                                                                        </td>
                                                                        <td style="text-align: center;">
                                                                            Rp.0.00</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="width: 100px; text-align: right;">
                                                                            PPn @ 10%
                                                                        </td>
                                                                        <td
                                                                            style="text-align:
                                                                                                                                                    center;">
                                                                            Rp.0.00
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="width: 15vw; text-align: right;">
                                                                            Credit
                                                                        </td>
                                                                        <td style="text-align: center;">
                                                                            Rp.0.00</td>
                                                                    </tr>
                                                                    <tr style="background-color: #252B3B; color: #ffffff;">
                                                                        <td style="width: 15vw; text-align: right;">
                                                                            Total Due
                                                                        </td>
                                                                        <td style="text-align: center;">
                                                                            Rp.0.00</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12 d-flex justify-content-center">
                                                        <button class="btn btn-success mx-1">
                                                            Save Changes
                                                        </button>
                                                        <button class="btn btn-light mx-1">
                                                            Cancel Changes
                                                        </button>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-12 my-2">
                                                        <h5>Transaction</h5>
                                                        <div class="table-responsive">
                                                            <table id="datatable2"
                                                                class="display table table-borderless dt-responsive w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Date</th>
                                                                        <th>Payment Method</th>
                                                                        <th>Transaction ID</th>
                                                                        <th>Amount</th>
                                                                        <th>Transaction Fees</th>
                                                                    </tr>
                                                                </thead>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-lg-12 my-2">
                                                        <h5>Transaction History</h5>
                                                        <div class="table-responsive">
                                                            <table id="datatable3"
                                                                class="display table dt-responsive w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Date</th>
                                                                        <th>Payment Method</th>
                                                                        <th>Transaction ID</th>
                                                                        <th>Status</th>
                                                                        <th>Description</th>
                                                                    </tr>
                                                                </thead>
                                                            </table>
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
    <script src="{{ Theme::asset('assets/js/accordion-radio.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/create-invoice.js') }}"></script>
@endsection
