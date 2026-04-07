@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Apps & Integrations</title>
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
                                        <h4 class="mb-3">Apps & Integrations</h4>
                                    </div>
                                    <div class="row justify-content-end">
                                        <div class="col-sm-12 col-lg-10">
                                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link active" id="pills-featured-tab" data-toggle="pill"
                                                        href="#pills-featured" role="tab" aria-controls="pills-featured"
                                                        aria-selected="true">Featured</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="pills-browse-tab" data-toggle="pill"
                                                        href="#pills-browse" role="tab" aria-controls="pills-browse"
                                                        aria-selected="false">Browse</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="pills-active-tab" data-toggle="pill"
                                                        href="#pills-active" role="tab" aria-controls="pills-active"
                                                        aria-selected="false">Active</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="pills-search-tab" data-toggle="pill"
                                                        href="#pills-search" role="tab" aria-controls="pills-search"
                                                        aria-selected="false">Search</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-sm-12 col-lg-2">
                                            <form>
                                                <div class="custom-search-bar">
                                                    <i class="ri-search-line icon"></i>
                                                    <input type="text" class="field form-control" placeholder="Search">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div style="background-color: transparent;">
                                        <div class="tab-content" id="pills-tabContent">
                                            {{-- Featured Tab --}}
                                            <div class="tab-pane fade show active" id="pills-featured" role="tabpanel"
                                                aria-labelledby="pills-featured-tab">
                                                <div class="featured-section">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            @include('includes.carouselappintegrations')
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 row">
                                                        <div class="col-lg-12">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            New & Noteworthy Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that have been updated
                                                                            recently.</p>
                                                                    </div>
                                                                    <div
                                                                        class="col-lg-2 d-flex align-items-center justify-content-end">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row new-apps">
                                                                    <div class="col-lg-3 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://www.serverpronto.com/spu/wp-content/uploads/cpanel-logo.jpg"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>cPanel & WHM™</h3>
                                                                                <small>The world's most popular web hosting
                                                                                    control panel</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-arrow-down-s-line float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://www.marketgoo.com/wp-content/uploads/2019/07/marketgoo_color.svg"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>marketgoo</h3>
                                                                                <small>Resellable SEO Tools for Web Hosting
                                                                                    Providers.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://fraudlabspro.hexa-soft.com/images/mediakit/flp-trans.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>FraudLabs Pro</h3>
                                                                                <small>Fraud detection to prevent fraud and
                                                                                    minimize chargebacks.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b9/Slack_Technologies_Logo.svg/1200px-Slack_Technologies_Logo.svg.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>

                                                                            <div class="app-description">
                                                                                <h3>Slack</h3>
                                                                                <small>Slack is a leading team collaboration
                                                                                    tool.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://annarbor.wordcamp.org/2016/files/2016/04/plesk-logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Plesk</h3>
                                                                                <small>The leading Server, Website &
                                                                                    WordPress management
                                                                                    platform.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://cdn.pixabay.com/photo/2015/05/26/09/37/paypal-784404_1280.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>PayPal</h3>
                                                                                <small>PayPal is the #1 most recognised and
                                                                                    widely used payment gateway.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://fair.digital/wp-content/uploads/2020/10/ox.svg"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>OX App Suite</h3>
                                                                                <small>Offer feature-rich, secure and
                                                                                    reliable professional email.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://logos-download.com/wp-content/uploads/2019/11/Authorize.net_Logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Authorize.net</h3>
                                                                                <small>The leading credit card processing
                                                                                    gateway with fee matching.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 row">
                                                        <div class="col-lg-6 col-sm-12">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Web Hosting Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow you to provision web
                                                                            hosting services.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row web-hosting-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://www.serverpronto.com/spu/wp-content/uploads/cpanel-logo.jpg"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>cPanel & WHM™</h3>
                                                                                <small>The world's most popular web hosting
                                                                                    control panel</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-arrow-down-s-line float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://annarbor.wordcamp.org/2016/files/2016/04/plesk-logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Plesk</h3>
                                                                                <small>The leading Server, Website &
                                                                                    WordPress management
                                                                                    platform.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-sm-12">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Digital Services Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow you to resell value
                                                                            add digital services.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row digital-service-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://axiadata.co.id/wp-content/uploads/2020/08/DigiCert-blue-transparent-logo.png"
                                                                                    alt="digicert-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>DigiCert</h3>
                                                                                <small>A market leading SSL provider of
                                                                                    RapidSSL, GeoTrust and Symantec.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://cdn.freebiesupply.com/images/large/2x/weebly-logo-transparent.png"
                                                                                    alt="weebly-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Weebly</h3>
                                                                                <small>Powerful Intuitive Drag & Drop Site
                                                                                    Builder</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 row">
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Provisioning Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow you to resell value
                                                                            add digital services.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row provisioning-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center ">
                                                                                <img src="https://www.pinclipart.com/picdir/big/563-5631369_gear-vector-png-clipart.png"
                                                                                    alt="digicert-logo.jpg" width="50">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Auto Release</h3>
                                                                                <small>Used for triggering automation
                                                                                    without a remote API.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center ">
                                                                                <img src="https://www.buzinga.com.au/wp-content/uploads/2014/03/signed-contract.png"
                                                                                    alt="weebly-logo.jpg" width="50">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Software Licensing</h3>
                                                                                <small>Securely license and protect your PHP
                                                                                    applications</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Payments Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow you to accept and
                                                                            process payments.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row provisioning-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://cdn.pixabay.com/photo/2015/05/26/09/37/paypal-784404_1280.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>PayPal</h3>
                                                                                <small>PayPal is the #1 most recognised and
                                                                                    widely used payment gateway.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://logos-download.com/wp-content/uploads/2019/11/Authorize.net_Logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Authorize.net</h3>
                                                                                <small>The leading credit card processing
                                                                                    gateway with fee matching.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 row">
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Domains Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow you to register and
                                                                            manage domain names.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row provisioning-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://logodownload.org/wp-content/uploads/2017/10/godaddy-logo-8.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>GoDaddy</h3>
                                                                                <small>Industry-leading domain registrar
                                                                                    with over 77M domain names.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://hostbillapp.com/appstore/domain_enom/images/logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Enom</h3>
                                                                                <small>A leading reseller domain registrar
                                                                                    supporting over 20 million
                                                                                    domains.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Sign-In/Social Auth Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow users to sign-in
                                                                            using social accounts.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row social-auth-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://www.google.co.id/images/branding/googlelogo/2x/googlelogo_color_160x56dp.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Google</h3>
                                                                                <small>Allow customers to register and sign
                                                                                    in using their Google accounts.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://brandslogo.net/wp-content/uploads/2016/09/facebook-logo-preview-400x400.png"
                                                                                    alt="cpanel-logo.jpg" width="100">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Facebook</h3>
                                                                                <small>Allow customers to register and sign
                                                                                    in using their Facebook
                                                                                    accounts.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 row">
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Marketing & Analytics Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow you to market and
                                                                            promote your services.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row marketing-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://thisweekinstartups.com/wp-content/uploads/2015/06/Mailchimp-Logo.jpg"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>MailChimp</h3>
                                                                                <small>Build customer mailing lists and
                                                                                    leverage e-commerce marketing.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="http://gossippost24.com/wp-content/uploads/2021/03/google-analytics.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Google Analytics</h3>
                                                                                <small>Track visitors and conversions with
                                                                                    E-Commerce Integration Tracking.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Notification Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow you to receive
                                                                            real-time notifications.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row notification-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b9/Slack_Technologies_Logo.svg/1200px-Slack_Technologies_Logo.svg.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Slack</h3>
                                                                                <small>Slack is a leading team collaboration
                                                                                    tool.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a0/Hipchat_Atlassian_logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>HipChat</h3>
                                                                                <small>HipChat is a team collaboration and
                                                                                    chat tool.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 row">
                                                        <div class="col-sm-12 col-lg-6">
                                                            <div class="card border p-3">
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-10">
                                                                        <h2>
                                                                            Orders & Fraud Apps
                                                                        </h2>
                                                                        <p class="lead">Apps that allow you to perform risk
                                                                            analysis.</p>
                                                                    </div>
                                                                    <div class="col-lg-2 d-flex align-items-center">
                                                                        <button class="btn btn-outline-dark ml-auto">View
                                                                            All</button>
                                                                    </div>
                                                                </div>
                                                                <div class="row order-frauds-apps">
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://fraudlabspro.hexa-soft.com/images/mediakit/flp-trans.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>FraudLabs Pro</h3>
                                                                                <small>Fraud detection to prevent fraud and
                                                                                    minimize chargebacks.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://upload.wikimedia.org/wikipedia/en/b/b2/MaxMind_logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>MaxMind</h3>
                                                                                <small>Detect Online Fraud and Locate Online
                                                                                    Visitors.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Browse Tab --}}
                                            <div class="tab-pane fade" id="pills-browse" role="tabpanel"
                                                aria-labelledby="pills-browse-tab">
                                                <div class="browse-section">
                                                    <div class="row">
                                                        <div class="col-lg-2 col-sm-12">
                                                            <h6>Categories</h6>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <div class="categories-list">
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-star-fill mr-2"></i>
                                                                            New & Noteworthy
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-database-fill mr-2"></i>
                                                                            Web Hosting
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-cloud-fill mr-2"></i>
                                                                            VPS/Cloud
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-computer-fill mr-2"></i>
                                                                            Digital Services
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i
                                                                                class="ri-checkbox-multiple-blank-fill mr-2"></i>
                                                                            Provisioning
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-bank-card-fill mr-2"></i>
                                                                            Payments
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-global-fill mr-2"></i>
                                                                            Domains
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-pencil-ruler-2-fill mr-2"></i>
                                                                            Productivity
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-login-box-fill mr-2"></i>
                                                                            Sign-In/Social Auth
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-line-chart-fill mr-2"></i>
                                                                            Marketing & Analytics
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-notification-3-fill mr-2"></i>
                                                                            Notification
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-shield-keyhole-fill mr-2"></i>
                                                                            Orders & Fraud
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-lock-2-fill mr-2"></i>
                                                                            Security
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-bank-fill mr-2"></i>
                                                                            Finance
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-tools-fill mr-2"></i>
                                                                            Utilities
                                                                        </button>
                                                                        <button
                                                                            class="btn btn-link d-flex align-items-center">
                                                                            <i class="ri-more-fill mr-2"></i>
                                                                            Other
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-10">
                                                            <h2>New & Noteworthy Apps</h2>
                                                            <p class="lead">Apps that have been updated recently</p>
                                                            <div class="card p-3">
                                                                <h3 class="mb-4">Recommended for you</h3>
                                                                <div class="row updated-apps">
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://www.serverpronto.com/spu/wp-content/uploads/cpanel-logo.jpg"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>cPanel & WHM™</h3>
                                                                                <small>The world's most popular web hosting
                                                                                    control panel</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-arrow-down-s-line float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://www.marketgoo.com/wp-content/uploads/2019/07/marketgoo_color.svg"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>marketgoo</h3>
                                                                                <small>Resellable SEO Tools for Web Hosting
                                                                                    Providers.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://fraudlabspro.hexa-soft.com/images/mediakit/flp-trans.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>FraudLabs Pro</h3>
                                                                                <small>Fraud detection to prevent fraud and
                                                                                    minimize chargebacks.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b9/Slack_Technologies_Logo.svg/1200px-Slack_Technologies_Logo.svg.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>

                                                                            <div class="app-description">
                                                                                <h3>Slack</h3>
                                                                                <small>Slack is a leading team collaboration
                                                                                    tool.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://annarbor.wordcamp.org/2016/files/2016/04/plesk-logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Plesk</h3>
                                                                                <small>The leading Server, Website &
                                                                                    WordPress management
                                                                                    platform.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://cdn.pixabay.com/photo/2015/05/26/09/37/paypal-784404_1280.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>PayPal</h3>
                                                                                <small>PayPal is the #1 most recognised and
                                                                                    widely used payment gateway.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://fair.digital/wp-content/uploads/2020/10/ox.svg"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>OX App Suite</h3>
                                                                                <small>Offer feature-rich, secure and
                                                                                    reliable professional email.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4 col-sm-12">
                                                                        <div class="card border px-3 pb-1">
                                                                            <div class="img-title text-center">
                                                                                <img src="https://logos-download.com/wp-content/uploads/2019/11/Authorize.net_Logo.png"
                                                                                    alt="cpanel-logo.jpg" width="200">
                                                                            </div>
                                                                            <div class="app-description">
                                                                                <h3>Authorize.net</h3>
                                                                                <small>The leading credit card processing
                                                                                    gateway with fee matching.</small>
                                                                            </div>
                                                                            <div class="mt-auto">
                                                                                <a href="#">
                                                                                    <i
                                                                                        class="ri-star-fill float-right font-size-22"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Active Tab --}}
                                            <div class="tab-pane fade" id="pills-active" role="tabpanel"
                                                aria-labelledby="pills-active-tab">
                                                <div class="active-section">
                                                    <div class="card p-3">
                                                        <h2>Active Apps</h2>
                                                        <p class="lead">The following apps are active in your WHMCS
                                                            installation.</p>
                                                    </div>
                                                    <div class="card p-3">

                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Search Tab --}}
                                            <div class="tab-pane fade" id="pills-search" role="tabpanel"
                                                aria-labelledby="pills-search-tab">
                                                <div class="search-section">
                                                    <div class="card p-3">
                                                        <h2>Search Results</h2>
                                                        <p class="lead">0 Matches Found</p>
                                                    </div>
                                                    <div class="card p-3"></div>
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
