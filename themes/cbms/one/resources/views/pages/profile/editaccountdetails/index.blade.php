@extends('layouts.clientbase')

@section('title')
    Edit Account Details
@endsection

@section('page-title')
    Detail Profile
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row pb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ url('home') }}">Dashboard</a> <span
                                class="text-muted"> / Detail Profile</span></h6>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                                <h5>Update Successful!</h5>
                                <small>{{ $message }}</small>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
                                <h5>Something Wrong!</h5>
                                <ul class="m-0 p-0 list-unstyled">
                                    @foreach ($errors->all() as $error)
                                        <li><small>{{ $error }}</small></li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <div class="card-body">
                            {{-- <h4 class="card-title mb-3">Detail Profile</h4> --}}
                            <form action="{{ route('pages.profile.editaccountdetails.update') }}" method="POST">
                                @csrf
                                <input type="hidden" value="{{ $auth->id }}" name="userid">
                                <div class="row m-1">
                                    <div class="col-lg-12">
                                        <div class="title-form">
                                            <h5 class="mt-2 text-qw">Personal Data</h5>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">ID Number</label>
                                            <input type="text" id="id-number" class="form-control"
                                                value="3403010710900004">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">First Name</label>
                                            <input type="text" name="firstName" class="form-control"
                                                value="{{ $auth->firstname }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Last Name</label>
                                            <input type="text" name="lastName" class="form-control"
                                                value="{{ $auth->lastname }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Company Name</label>
                                            <input type="text" name="companyName" class="form-control"
                                                value="{{ $auth->companyname }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Email</label>
                                            <input type="email" name="email" id="email" class="form-control"
                                                value="{{ $auth->email }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Phone</label>
                                            <input type="text" name="phone" id="telepon" class="form-control"
                                                value="{{ $auth->phonenumber }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Tax ID</label>
                                            <input type="text" name="tax_id" id="tax_id" class="form-control"
                                                value="{{ $auth->tax_id }}" placeholder="Tax ID">
                                        </div>
                                    </div>
                                </div>

                                <div class="row m-1">
                                    <div class="col-lg-12">
                                        <div class="title-form">
                                            <h5 class="mt-2 text-qw">Address</h5>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Address 1</label>
                                            <input type="text" id="alamat" name="address1" class="form-control"
                                                value="{{ $auth->address1 }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Address 2</label>
                                            <input type="text" id="alamat2" name="address2" class="form-control"
                                                value="{{ $auth->address2 }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">City</label>
                                            <input type="text" id="kota" name="city" class="form-control"
                                                value="{{ $auth->city }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Province/Region</label>
                                            <input type="text" id="profinsi" name="state" class="form-control"
                                                value="{{ $auth->state }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Postal Code</label>
                                            <input type="text" id="kodepos" name="postalCode" class="form-control"
                                                value="{{ $auth->postcode }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="simpleinput">Country</label>
                                            <select class="form-control" name="country">
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country['id'] }}"
                                                        {{ $auth->country == $country['id'] ? 'selected' : '' }}>
                                                        {{ $country['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button class="btn btn-success-qw px-3">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row-->
        </div> <!-- container-fluid -->
    </div>
@endsection
