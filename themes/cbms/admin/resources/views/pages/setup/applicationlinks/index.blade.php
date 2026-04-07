@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Application Links</title>
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
                                        <h4 class="mb-3">Application Links</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>Application Links allow you to grant access to user accounts in WHMCS from third
                                            party applications.</p>
                                        <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Vero ducimus voluptatum
                                            dolorum omnis vel atque provident! Dolorum commodi velit tempora, obcaecati nemo
                                            et blanditiis a, iste quas saepe sit dolor.</p>
                                        <div class="card border p-3">
                                            <div class="row">
                                                <div class="col">
                                                    <div class="app-links-img-container">
                                                        <img src="https://proto.qwords.com/modules/servers/cpanel/logo.png"
                                                            alt="cPanel.logo">
                                                    </div>
                                                </div>
                                                <div class="col-8">
                                                    <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Distinctio
                                                        in excepturi error necessitatibus provident, officiis porro illo est
                                                        cupiditate, beatae dolore veritatis alias voluptatum, doloremque at
                                                        ullam? Aspernatur, porro quae.</p>
                                                    <em>Lorem ipsum dolor sit amet.</em>
                                                    <div>
                                                        <div class="badge badge-secondary"><i
                                                                class="fas fa-times mr-1"></i>DISABLED</div>
                                                        <div class="border badge badge-info"><i
                                                                class="fas fa-file-signature mr-1    "></i> View Log (0
                                                            Warning)</div>
                                                    </div>
                                                </div>
                                                <div class="col text-center">
                                                    <div class="form-group">
                                                        <input type="checkbox" data-toggle="toggle" disabled>
                                                    </div>
                                                    <a href="" class="btn-link">Configure</a>
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


@section('scripts')
    <script src="{{ Theme::asset('assets/libs/bootstrap-switch-custom/bootstrap4-toggle.min.js') }}"></script>
@endsection
