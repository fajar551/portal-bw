@extends('layouts.clientbase')

@section('title')
   {{ $params['checkAffAccount'] ? $params['affiliatesTitle'] : $params['activateTitle'] }}
@endsection

@section('page-title')
   {{ $params['checkAffAccount'] ? $params['affiliatesTitle'] : $params['activateTitle'] }}
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         <div class="row pb-3">
            <div class="col-xl-8 col-lg-8">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ url('home') }}">Dashboard</a> <span
                        class="text-muted"> / Affiliate</span></h6>
               </div>
            </div>
         </div>
         @if (session('success'))
            <div class="alert alert-success">
               <button type="button" class="close" data-dismiss="alert">×</button>
               <h5>{!! __('client.clientHomePanelsaffiliateProgram') !!}</h5>
               <p class="m-0">{!! session('success') !!}</p>
            </div>
         @endif
         @if (session('error_withdraw'))
            <div class="alert alert-danger">
               <button type="button" class="close" data-dismiss="alert">×</button>
               <h5>{!! __('client.clientHomePanelsaffiliateProgram') !!}</h5>
               <p class="m-0">{!! session('error_withdraw') !!}</p>
            </div>
         @endif
         @if (!$params['checkAffAccount'])
            <div class="row">
               <div class="col-xl-12 col-lg-12">
                  <div class="card">
                     <div class="card-body">
                        <h4 class="card-title">{{ __('client.affiliatesactivate') }}</h4>
                        <div class="alert alert-info" id="activateAffInfo">
                           <div class="intro text-center">
                              <h2>{{ __('client.affiliatesignuptitle') }}</h2>
                              <p>{{ __('client.affiliatesignupintro') }}</p>
                           </div>
                        </div>
                        <div class="activate-info">
                           <ul>
                              <li>{{ __('client.affiliatesignupinfo1') }}</li>
                              <li>{{ __('client.affiliatesignupinfo2') }}</li>
                              <li>{{ __('client.affiliatesignupinfo3') }}</li>
                           </ul>
                        </div>
                        <div class="text-center">
                           <form action="{{ route('pages.affiliate.activateaccount') }}" method="POST">
                              @csrf
                              <input type="hidden" name="activate" value="true">
                              <button type="submit" class="btn btn-success-qw">Activate Affiliate Account</button>
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- end row-->
         @else
            <div class="row">
               <div class="col-xl-4 col-lg-6">
                  <div class="card">
                     <div class="card-body">
                        <div class="row align-items-center">
                           <div class="col">
                              <h6 class="text-uppercase font-size-12 text-muted mb-3">Klik</h6>
                              <span class="h3 counter-number mb-0">{{ $params['visitors'] }}</span>
                           </div>
                           <div class="col-auto ic-card">
                              <i class="feather-users"></i>
                           </div>
                        </div> <!-- end row -->

                        <div id="sparkline1" class="mt-3"></div>
                     </div> <!-- end card-body-->
                  </div> <!-- end card-->
               </div> <!-- end col-->

               <div class="col-xl-4 col-lg-6">
                  <div class="card">
                     <div class="card-body">
                        <div class="row align-items-center">
                           <div class="col">
                              <h6 class="text-uppercase font-size-12 text-muted mb-3">Jumlah Pendaftaran</h6>
                              <span class="h3 counter-number mb-0"> {{ $params['signups'] }} </span>
                           </div>
                           <div class="col-auto ic-card">
                              <i class="feather-shopping-cart"></i>
                           </div>
                        </div> <!-- end row -->

                        <div id="sparkline1" class="mt-3"></div>
                     </div> <!-- end card-body-->
                  </div> <!-- end card-->
               </div> <!-- end col-->

               <div class="col-xl-4 col-lg-6">
                  <div class="card">
                     <div class="card-body">
                        <div class="row align-items-center">
                           <div class="col">
                              <h6 class="text-uppercase font-size-12 text-muted mb-3">Rata-rata konversi</h6>
                              <span class="h3 counter-number mb-0"> {{ $params['conversionrate'] }}% </span>
                           </div>
                           <div class="col-auto ic-card">
                              <i class="feather-bar-chart"></i>
                           </div>
                        </div> <!-- end row -->

                        <div id="sparkline1" class="mt-3"></div>
                     </div> <!-- end card-body-->
                  </div> <!-- end card-->
               </div> <!-- end col-->
            </div>

            <div class="row">
               <div class="col-xl-12 col-lg-12">
                  <div class="card">
                     <div class="card-body">
                        <div class="row">
                           <div class="col-md-3 col-sm-12 mb-3">
                              <p class="text-uppercase font-size-12 text-muted mb-0"><span
                                    class="feather-link-2 mr-2"></span>
                                 Kode
                                 Refferal unik anda</p>
                              <div class="d-flex align-items-center ref-link-holder h-100">
                                 <a href="{{ $params['referrallink'] }}">{{ $params['referrallink'] }}</a>
                              </div>
                           </div>
                           <div class="col-md-5 col-sm-12">
                              <div class="table-responsive">
                                 <table class="table table-bordered mb-lg-0 mb-sm-3">
                                    <tbody>
                                       <tr>
                                          <td>Komisi yang masih ditunda :</td>
                                          <td>{{ $params['commissionpending'] }}</td>
                                       </tr>
                                       <tr>
                                          <td>Saldo Komisi yang ada :</td>
                                          <td>{{ $params['payamount'] }}</td>
                                       </tr>
                                       <tr>
                                          <td>Jumlah pemindahan :</td>
                                          <td>{{ $params['withdraw'] }}</td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                           <div class="col-md-4 col-sm-12 text-center">
                              <div class="d-flex align-items-center justify-content-center flex-column h-100">
                                 <p class="text-muted mt-2">You will be able to request a withdrawal as soon as your
                                    balance reaches the minimum required amount of {{ $params['affpayoutmin'] }}</p>
                                 <form action="{{ route('pages.affiliate.withdrawrequest') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="affpayoutmin" value="{{ Cfg::get('AffiliatePayout') }}">
                                    <button class="btn btn-success">
                                        <!--<span class="feather-repeat"></span>-->
                                       Pindahkan dana
                                       ke Deposit</button>
                                 </form>
                              </div>
                           </div>
                        </div> <!-- end row -->
                     </div> <!-- end card-body-->
                  </div> <!-- end card-->
               </div> <!-- end col-->
            </div>

            <div class="row">
               <div class="col-xl-12 col-lg-12">
                  <div class="card">
                     <div class="card-body">
                        <h4 class="card-title">{{__('client.affiliatesreferals')}}</h4>
                        <div class="table-responsive">
                           <table id="affTable" class="table">
                              <thead>
                                 <tr>
                                    <th>No</th>
                                    <th>{{__('client.affiliatessignupdate')}}</th>
                                    <th>{{__('client.orderproduct')}}</th>
                                    <th>{{__('client.affiliatesamount')}}</th>
                                    <th>{{__('client.affiliatescommission')}}</th>
                                    <th>{{__('client.orderbillingcycle')}}</th>
                                    <th>{{__('client.affiliatesstatus')}}</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- end row-->
         @endif

      </div> <!-- container-fluid -->
   </div>
@endsection

@section('scripts')
<script type="text/javascript">
    let dtTable;

    $(() => {
       dtIndex();
      
    })

    const dtIndex = () => {
       dtTable = $('#affTable').DataTable({
          columnDefs: [{
             "width": "1%",
             "targets": 0
          }],
          stateSave: true,
          processing: true,
          responsive: true,
          serverSide: true,
          autoWidth: false,
          searching: false,
          destroy: true,
          language: {
             paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>",
             },
          },
          drawCallback: () => {
             $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
          },
          ajax: {
             url: "{!! route('dtAffiliate.json') !!}",
             type: "GET",
          },
          columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                width: '1%',
                className: 'text-center align-middle',
                orderable: false,
                searchable: false,
                visible: true
             },
             {
                data: 'regdate',
                name: 'regdate',
                width: '1%',
                className: 'text-left align-middle',
                defaultContent: 'N/A',
             },
             {
                data: 'name',
                name: 'name',
                width: '2%',
                className: 'text-center align-middle',
                defaultContent: 'N/A',
             },
             {
                data: 'amount',
                name: 'amount',
                width: '1%',
                className: 'text-center align-middle',
                defaultContent: 'N/A',
             },
             {
                data: 'commision',
                name: 'commision',
                width: '1%',
                className: 'text-center align-middle',
                searchable: false,
                defaultContent: 'Off',
             },
             {
                data: 'billingcycle',
                name: 'billingcycle',
                width: '2%',
                className: 'text-center align-middle',
                orderable: false,
                searchable: false,
                defaultContent: 'N/A',
             },
             {
                data: 'domainstatus',
                name: 'domainstatus',
                width: '2%',
                className: 'text-center align-middle',
                orderable: false,
                searchable: false,
                defaultContent: 'N/A',
             },
          ]
       })
    }
 </script>
@endsection
    

