@extends('layouts.clientbase')

@section('tab-title')
    My Services
@endsection

<style>
    .custom-dropdown {
        position: relative;
    }
    .dropdown-content {
        position: absolute;
        top: -100%;
        left: 0;
        background-color: #f9f9f9;
        min-width: 100%;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 9999;
    }
    .dropdown-item {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }
    .dropdown-item:hover {
        background-color: #f1f1f1;
    }
</style>

@section('content')
    <div class="page-content mb-5" id="my-service">
        <div class="container-fluid">
            {{-- Alert untuk pesan error --}}
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card p-4 mb-4">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <h1 class="font-weight-bold">Welcome, {{ $user->firstname }}</h1>
                                    <span>On this page you can find out what services you have and details about our products</span>
                                </div>
                                <button class="btn btn-success px-3">Learn More</button>
                            </div>
                            <div class="col-md-5 text-center">
                                <img src="{{ "https://my.hostingnvme.id/assets/images/relabs/service3.png" }}" class="img-fluid" alt="service.png">
                            </div>
                        </div>
                    </div>
                    <!-- Services ROW -->
                    <div class="">
                        <h5 class="font-weight-bold mb-3">{{ __('client.yourservices') }}</h5>
                        <div class="row">
                            @if (!$serviceProd->isEmpty())
                                @foreach ($serviceProd as $prod)
                                    <div class="col-md-4 mb-4">
                                        <div class="card product-card p-3 h-100">
                                            <div class="row justify-content-center">
                                                <div class="col-md-10">
                                                    <div class="text-center mt-3">
                                                        <h5 class="text-relabs-orange d-inline">{{ $prod->name }}</h5>
                                                        <div class="mt-2">
                                                            @switch($prod->domainstatus)
                                                                @case('Terminated')
                                                                    <div class="badge badge-danger">{{ $prod->domainstatus }}</div>
                                                                @break

                                                                @case('Pending')
                                                                    <div class="badge badge-warning">{{ $prod->domainstatus }}</div>
                                                                @break

                                                                @case('Cancelled')
                                                                    <div class="badge badge-secondary">{{ $prod->domainstatus }}</div>
                                                                @break

                                                                @case('Suspended')
                                                                    <div class="badge badge-info">{{ $prod->domainstatus }}</div>
                                                                @break

                                                                @default
                                                                    <div class="badge badge-success">{{ $prod->domainstatus }}</div>
                                                            @endswitch
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Domain:</span>
                                                            <span class="font-weight-bold">{{ $prod->domain ?? '-' }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mt-2">
                                                            <span class="text-muted">{{ __('client.clientareahostingnextduedate') }}:</span>
                                                            <span class="font-weight-bold">{{ \Carbon\Carbon::parse($prod->nextduedate)->translatedFormat('j F Y') }}</span>
                                                        </div>
                                                        <div class="h3 font-weight-bold mt-3">
                                                            {{ \App\Helpers\Format::price($prod->amount) }}
                                                        </div>
                                                        <div class="description text-muted mt-2">
                                                            {{ $prod->description }}
                                                        </div>
                                                        <div class="mt-3">
                                                            <a class="btn btn-success w-100" 
                                                                href="{{ url('services/servicedetails/' . $prod->id) }}">{{ __('client.clientareaviewdetails') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <p class="text-muted text-center">{!! __('client.clientHomePanelsactiveProductsServicesNone') !!}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    function toggleDropdown(id) {
        var dropdown = document.getElementById(id);
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }

    const serviceProd = @json($serviceProd);
    //console.log(serviceProd);
</script>

