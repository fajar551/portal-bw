@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Compose Message</title>
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
                                        <h4 class="mb-3">Send Email Message</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">From</label>
                                                    <div class="col-sm-12 col-lg-3">
                                                        <input type="text" class="form-control" name="companyName"
                                                            placeholder="Company Name" />
                                                    </div>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <input type="email" name="mail" class="form-control"
                                                            placeholder="e.g: email@yourcompany.com">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form label">Recipients</label>
                                                    <div class="col-sm-12 col-lg-4">
                                                        <select name="recipients" id="recipients-list" class="form-control"
                                                            multiple>
                                                            <option value="0">example0@mail.com</option>
                                                            <option value="1">example1@mail.com</option>
                                                            <option value="2">example2@mail.com</option>
                                                            <option value="3">example3@mail.com</option>
                                                            <option value="4">example4@mail.com</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-12 col-lg-6 d-flex align-items-center">
                                                        <p class="m-0 p-0">Emails are sent individually
                                                            so email addresses won't be seen by others
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">CC</label>
                                                    <div class="col-sm-12 col-lg-6">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                    <div class="col-sm-12 col-lg-4 d-flex align-items-center">
                                                        <p class="m-0 p-0">Comma separate emails</p>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">BCC</label>
                                                    <div class="col-sm-12 col-lg-6">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                    <div class="col-sm-12 col-lg-4 d-flex align-items-center">
                                                        <p class="m-0 p-0">Comma separate emails</p>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Subject</label>
                                                    <div class="col-sm-12 col-lg-10">
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="summernote"></div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row">
                                                    <label class="col-sm 12 col-lg-2 col-form label">
                                                        Attachments
                                                    </label>
                                                    <div class="col-lg-10">
                                                        <div class="input-group">
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input"
                                                                    id="inputGroupFile04"
                                                                    aria-describedby="inputGroupFileAddon04">
                                                                <label class="custom-file-label"
                                                                    for="inputGroupFile04">Choose file</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col sm-12 col-lg-2 col-form-label">Marketing
                                                        Email?</label>
                                                    <div class="col-sm-12 col-lg-8 pt-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="0"
                                                                id="defaultCheck1">
                                                            <label class="form-check-label" for="defaultCheck1">
                                                                Don't send this email to clients who have opted out of
                                                                marketing emails
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-lg-2 col-form-label">Save
                                                        Message</label>
                                                    <div class="col-sm-12 col-lg-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1"
                                                                id="defaultCheck2">
                                                            <label class="form-check-label" for="defaultCheck2">
                                                                Tick to save and enter save name:
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-lg-6">
                                                        <input type="text" class="form-control w-50">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12 d-flex justify-content-center align-content-center">
                                                <button class="btn btn-light px-5 mx-1">
                                                    Message Preview
                                                </button>
                                                <button class="btn btn-success px-5 mx-1">
                                                    Send Message
                                                </button>
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
    <!-- Summernote js -->
    <script src="{{ Theme::asset('assets/libs/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script>
@endsection
