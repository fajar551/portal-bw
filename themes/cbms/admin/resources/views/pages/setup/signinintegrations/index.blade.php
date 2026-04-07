@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Sign-In Integrations</title>
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
                                        <h4 class="mb-3">Sign-In Integrations</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p class="p-0 mb-5">
                                            The following 3rd party services are supported for allowing users to register
                                            and sign in. You may activate one or more of these.
                                            Allow customers to register and sign in using their Facebook accounts.
                                        </p>
                                        <div class="row">
                                            <div class="col-lg-4 col-sm-12">
                                                <div class="card product-card">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="card-body text-center p-3">
                                                                <img src="https://proto.qwords.com/assets/img/auth/facebook_signin.png"
                                                                    alt="fb-logo" width="240px">
                                                                <p class="m-0 px-5 my-3">
                                                                    Allow customers to register and sign in using their
                                                                    Facebook
                                                                    accounts.
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-center py-1">
                                                            <button type="button" class="btn btn-success px-5 my-3"
                                                                data-toggle="modal"
                                                                data-target="#facebookModal">Activate</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-sm-12">
                                                <div class="card product-card">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="card-body text-center p-3">
                                                                <img src="https://proto.qwords.com/assets/img/auth/google_signin.png"
                                                                    alt="fb-logo" width="150px">
                                                                <p class="m-0 px-5 my-3">
                                                                    Allow customers to register and sign in using their
                                                                    Google accounts.
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-center py-1">
                                                            <button type="button" class="btn btn-success px-5 my-3"
                                                                data-toggle="modal"
                                                                data-target="#googleModal">Activate</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-sm-12">
                                                <div class="card product-card">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="card-body text-center p-3">
                                                                <img src="https://proto.qwords.com/assets/img/auth/twitter_oauth.png"
                                                                    alt="fb-logo" width="240px">
                                                                <p class="m-0 px-5 my-3">
                                                                    Allow customers to register and sign in using their
                                                                    Twitter accounts.
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-center py-1">
                                                            <button type="button" class="btn btn-success px-5 my-3"
                                                                data-toggle="modal"
                                                                data-target="#twitterModal">Activate</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Modal Each Product Card --}}
                                        {{-- Facebook Modal --}}
                                        <div class="modal fade" id="facebookModal" tabindex="-1"
                                            aria-labelledby="facebookModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-center" id="facebookModalLabel">
                                                            Facebook</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Facebook requires you to create an application and retrieve the
                                                            app ID and secret. For more information, please refer to the
                                                            help guide.</p>
                                                        <form action="#">
                                                            <div class="form-group">
                                                                <label>appId</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>appSecret</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <div class="mr-auto">
                                                            <a href="{{ url('admin/dashboard') }}" target="_blank">
                                                                <button type="button" class="btn btn-info">Help</button>
                                                            </a>
                                                            <button type="button" class="btn btn-light"
                                                                data-dismiss="modal">Cancel</button>
                                                        </div>
                                                        <div class="ml-auto">
                                                            <button type="button" class="btn btn-success px-2">Save &
                                                                Activate</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Google Modal --}}
                                        <div class="modal fade" id="googleModal" tabindex="-1"
                                            aria-labelledby="googleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-center" id="googleModalLabel">
                                                            Google</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Google requires you to create an application and retrieve a
                                                            client ID and secret. For more information, please refer to the
                                                            help guide.</p>
                                                        <form action="#">
                                                            <div class="form-group">
                                                                <label>ClientId</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>ClientSecret</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <div class="mr-auto">
                                                            <a href="{{ url('admin/dashboard') }}" target="_blank">
                                                                <button type="button" class="btn btn-info">Help</button>
                                                            </a>
                                                            <button type="button" class="btn btn-light"
                                                                data-dismiss="modal">Cancel</button>
                                                        </div>
                                                        <div class="ml-auto">
                                                            <button type="button" class="btn btn-success px-2">Save &
                                                                Activate</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Twitter Modal --}}
                                        <div class="modal fade" id="twitterModal" tabindex="-1"
                                            aria-labelledby="twitterModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-center" id="twitterModalLabel">
                                                            Twitter</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Twitter requires that you create an application and retrieve the
                                                            consumer key and secret. For more information, please refer to
                                                            the help guide.</p>
                                                        <form action="#">
                                                            <div class="form-group">
                                                                <label>ConsumerKey</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>ConsumerSecret</label>
                                                                <input type="text" class="form-control">
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <div class="mr-auto">
                                                            <a href="{{ url('admin/dashboard') }}" target="_blank">
                                                                <button type="button" class="btn btn-info">Help</button>
                                                            </a>
                                                            <button type="button" class="btn btn-light"
                                                                data-dismiss="modal">Cancel</button>
                                                        </div>
                                                        <div class="ml-auto">
                                                            <button type="button" class="btn btn-success px-2">Save &
                                                                Activate</button>
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
