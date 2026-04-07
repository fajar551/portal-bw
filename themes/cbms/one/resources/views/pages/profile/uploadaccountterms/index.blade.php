@extends('layouts.clientbase')

@section('title')
    Upload Account Terms
@endsection

@section('page-title')
    Upload Account Terms
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row pb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ url('home') }}">Dashboard</a>
                            <span class="text-muted"> / Upload Account Terms</span>
                        </h6>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Upload Account Terms</h4>

                            <div class="form-group mt-4">
                                <label for="simpleinput">Upload the KTP/SIM/Passport in charge of the CBMS Account</label>
                                <p>Max File Size 2MB, extensions PNG/JPG/JPEG/PDF</p>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="inputGroupFile02">
                                    <label class="custom-file-label" for="inputGroupFile02"
                                        aria-describedby="inputGroupFileAddon02">Choose file</label>
                                </div>
                            </div>
                            <a href="" class="btn btn-success px-5">Submit</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row-->
        </div> <!-- container-fluid -->
    </div>
@endsection
