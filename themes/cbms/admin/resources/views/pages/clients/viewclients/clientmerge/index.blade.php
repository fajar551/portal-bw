@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Merge Client</title>
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
                                        <h4 class="mb-4">Merge Client</h4>
                                        <p>This process allows you to merge two client accounts into one.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card p-3">
                                <div class="row justify-content-center">
                                    <div class="col-lg-6">
                                        <form action="#" class="w-100">
                                            <div class="form-group row">
                                                <label for="firstName" class="col-sm-4 col-form-label text-lg-right">First
                                                    Name</label>
                                                <div class="col-sm-5">
                                                    <input type="text" class="form-control" id="firstName" placeholder=""
                                                        disabled>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="firstName" class="col-sm-4 col-form-label text-lg-right">Second
                                                    Client ID</label>
                                                <div class="col-sm-4">
                                                    <input type="text" class="form-control" id="firstName" placeholder="">
                                                </div>
                                                <div class="col-sm-3">
                                                    <button class="btn btn-block btn-success">Merge</button>
                                                </div>
                                            </div>
                                            <div class="form-group row justify-content-center">
                                                <div class="col-sm-6">
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" id="customRadioInline1" name="customRadioInline"
                                                            class="custom-control-input">
                                                        <label class="custom-control-label" for="customRadioInline1">Merge
                                                            to
                                                            First
                                                            Client</label>
                                                    </div>
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" id="customRadioInline2" name="customRadioInline"
                                                            class="custom-control-input">
                                                        <label class="custom-control-label" for="customRadioInline2">Merge
                                                            to
                                                            Second
                                                            Client</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-lg-4">
                                    <form action="#">
                                        <div class="form-group">
                                            <label for="">
                                                Enter Name, Company or Email to Search:
                                            </label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Search Result</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        Matches will appear here as you type
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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
