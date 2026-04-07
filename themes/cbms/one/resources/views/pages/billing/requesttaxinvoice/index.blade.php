@extends('layouts.clientbase')

@section('title')
    Request Tax Invoice
@endsection

@section('page-title')
    Request Tax Invoice
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row pb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <span
                                class="text-muted"> / Request Tax Invoice</span></h6>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-3">Ticket For Request Tax Invoice</h4>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="simpleinput">Name</label>
                                        <input type="text" id="nama" class="form-control" value=" " readonly="">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="simpleinput">Email</label>
                                        <input type="email" id="email" class="form-control" value=" "
                                            readonly="">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="simpleinput">Title</label>
                                        <input type="text" id="title" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="simpleinput">Department</label>
                                        <select class="form-control">
                                            <option value="1">Technical Support</option>
                                            <option value="2">Billing Department</option>
                                            <option value="3">Sales Department</option>
                                            <option value="5" selected="selected">Permintaan Faktur Pajak</option>
                                            <option value="8">Manage The Box VDS Cpanel</option>
                                            <option value="9">Manage The Box VDS Non Cpanel</option>
                                            <option value="10">Manage The Box Dedicated/Colo Cpanel</option>
                                            <option value="11">Manage The Box Dedicated/Colo Non Cpanel</option>
                                            <option value="13">Visit Data Center</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="simpleinput">Related Service</label>
                                        <select class="form-control">
                                            <option value="">Tidak ada</option>
                                            <option value="D639">Domain - argawibowo.my.id (Aktif)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="simpleinput">Priority</label>
                                        <select class="form-control">
                                            <option value="High">High</option>
                                            <option value="Medium" selected="selected">Medium</option>
                                            <option value="Low">Low</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="simpleinput">Message</label>
                                        <div id="snow-editor" style="height: 200px;"></div>
                                        <!-- end Snow-editor-->
                                    </div>
                                </div>
                                <div class="col-lg-10">
                                    <div class="form-group">
                                        <label>Attachment File</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="customFile">
                                            <label class="custom-file-label" for="customFile">Choose file</label>
                                        </div>
                                        <small>File Allowed: .jpg, .gif, .jpeg, .png, .pdf, .doc, .docx
                                        </small>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group pt-4">
                                        <div class="form-group mt-1">
                                            <a href="#" class="btn btn-outline-success btn-block"><i
                                                    class="feather-plus"></i>
                                                Add More
                                                File</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="aggrementCheck">
                                            <label class="custom-control-label" for="aggrementCheck">
                                                I have learned that Tax Invoice Holes can only be made in the current month.
                                            </label>
                                        </div>
                                        <p>
                                            Complete information about this is informed on the page
                                            <a href="">http://qwrd.so/fakturpajak</a>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Invoice Number</label>
                                        <input type="text" name="" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck1">
                                            <label class="custom-control-label" for="customCheck1">
                                                I have attached a valid NPWP Scan and PKP Scan</label>
                                        </div>
                                        <p>Please Click Attachment/Attachment to attach a Scan of NPWP and Scan of PKP</p>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Tax Invoices Want To Be Sent In The Form</label>
                                        <select class="form-control">
                                            <option value="Email (Efaktur) (Gratis)">Email (E-invoice) (Free)</option>
                                            <option value=" Invoice Cetak Bermaterai (+Rp25.000)">
                                                Stamped Print Invoice
                                                (+Rp25.000)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <a href="" class="btn btn-success"><i class="feather-send"></i> Send Request</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row-->
        </div> <!-- container-fluid -->
    </div>
@endsection

@section('scripts')
    <script src="{{ url('clientarea/assets/plugins/katex/katex.min.js') }}"></script>
    <script src="{{ url('clientarea/assets/plugins/quill/quill.min.js') }}"></script>
    <script src="{{ url('clientarea/assets/pages/quilljs-demo.js') }}"></script>
@endsection
