@extends('layouts.clientbase')

@section('title')
    Network Status
@endsection

@section('page-title')
    Network Status
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row pb-3">
                <div class="col-xl-8 col-lg-8">
                    <div class="header-breadcumb">
                        <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <span
                                class="text-muted"> / Network status</span></h6>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Transfer domain ke GFN</h4>
                            <p>All the latest from Goldenfast Networks</p>

                            <div class="article-list mt-4">
                                <a href="">
                                    <div class="media mb-3">
                                        <img class="d-flex align-self-start rounded mr-3"
                                            src="{{ Theme::asset('assets/images/media/thumb-promo.jpg') }}" alt="Generic
                                                placeholder image" height="48">
                                        <div class="media-body">
                                            <h5 class="mt-0 mb-1">Special Price Colocation Server</h5>
                                            <small class="text-muted">Mon, 7 June 2021</small>
                                            <p class="mb-1 mt-1">Berikan "rumah" bagi server Anda di Data Center Qwords.
                                                Server dimonitor 24/7, akses cepat di seluruh dunia</p>
                                        </div>
                                    </div>
                                </a>
                                <hr>
                                <a href="">
                                    <div class="media mb-3">
                                        <img class="d-flex align-self-start rounded mr-3"
                                            src="{{ Theme::asset('assets/images/media/thumb-promo-2.jpg') }}"
                                            alt="Generic placeholder image" height="48">
                                        <div class="media-body">
                                            <h5 class="mt-0 mb-1">Special Price Colocation Server</h5>
                                            <small class="text-muted">Mon, 6 June 2021</small>
                                            <p class="mb-1 mt-1">Berikan "rumah" bagi server Anda di Data Center Qwords.
                                                Server dimonitor 24/7, akses cepat di seluruh dunia</p>
                                        </div>
                                    </div>
                                </a>
                                <hr>
                                <a href="">
                                    <div class="media mb-3">
                                        <img class="d-flex align-self-start rounded mr-3"
                                            src="{{ Theme::asset('assets/images/media/thumb-promo.jpg') }}" alt="Generic
                                                placeholder image" height="48">
                                        <div class="media-body">
                                            <h5 class="mt-0 mb-1">Cloud VPS All Item Discount</h5>
                                            <small class="text-muted">Mon, 5 June 2021</small>
                                            <p class="mb-1 mt-1">Berikan "rumah" bagi server Anda di Data Center Qwords.
                                                Server dimonitor 24/7, akses cepat di seluruh dunia</p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- end row-->
        </div> <!-- container-fluid -->
    </div>
@endsection
