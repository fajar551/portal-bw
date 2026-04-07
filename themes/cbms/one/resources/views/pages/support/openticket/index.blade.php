@extends('layouts.clientbase')

@section('title')
   Open New Ticket
@endsection

@section('page-title')
   Open New Ticket
@endsection

@section('content')
   <div class="page-content" id="open-new-ticket">
      <div class="coantainer-fluid">
         <div class="row pb-3">
            <div class="col-xl-8 col-lg-8">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ route('home') }}">Dashboard</a> <span
                        class="text-muted"> /
                        Open New Ticket</span></h6>
               </div>
            </div>
         </div>
         <div class="card card-ticket">
            <div class="row">
               <div class="col-lg-12">
                  <div class="ticket-container-header">
                     <h1>Choose Department Ticket</h1>
                  </div>
               </div>
               @foreach ($departments as $num => $department)
                  <div class="col-lg-4">
                     <a href="{{ route('pages.support.openticket.submitticket', $department['id']) }}">
                        <div class="card-department">
                           <div class="icon-col text-center">
                              <i class="fas fa-envelope"></i>
                           </div>
                           <div class="text-desc-col">
                              <h5>{{ $department['name'] }}</h5>
                              <p class="text-muted mb-0">{{ $department['description'] }}</p>
                           </div>
                        </div>
                     </a>
                  </div>
               @endforeach
               {{-- @foreach ($depts as $dept)
                  <div class="col-lg-4">
                     <a href="{{ route('pages.support.openticket.submitticket', $dept->id) }}">
                        <div class="card-department">
                           <div class="icon-col text-center">
                              @switch($dept->id)
                                 @case(1)
                                    <i class="fas fa-wrench"></i>
                                 @break
                                 @case(2)
                                    <i class="fas fa-user-edit"></i>
                                 @break
                                 @case(3)
                                    <i class="fas fa-hand-holding-usd"></i>
                                 @break
                                 @case(4)
                                    <i class="fas fa-user-tie"></i>
                                 @break
                                 @case(5)
                                    <i class="fas fa-undo-alt"></i>
                                 @break
                                 @default
                                    <p>wkwk</p>
                              @endswitch

                           </div>
                           <div class="text-desc-col">
                              <h5>{{ $dept->name }}</h5>
                              <p class="text-muted mb-0">{{ $dept->description }}</p>

                           </div>
                        </div>
                     </a>
                  </div>
               @endforeach --}}
               {{-- <div class="col-lg-4">
                  <div class="card card-department">
                     <div class="icon-col text-center">
                        <i class="fas fa-file-invoice-dollar"></i>
                     </div>
                     <div class="text-desc-col">
                        <h5 class="text-muted">Billing Support</h5>
                        <p class="text-muted mb-0" id="billing-desc">Senin 07.30 WIB 24 Jam Non Stop Sampai Sabtu 21.30 WIB
                           dan Minggu / Hari Libur Nasional 08.30 - 17.30 WIB
                           Untuk Tiket yang sifatnya SEGERA, mohon untuk membuat tiket ke Technical Support</p>
                     </div>
                  </div>
               </div>
               <div class="col-lg-4">
                  <div class="card card-department">
                     <div class="icon-col text-center">
                        <i class="fas fa-user-tie"></i>
                     </div>
                     <div class="text-desc-col">
                        <h5 class="text-muted">Sales Support</h5>
                        <p class="text-muted mb-0" id="sales-desc">Senin - Jumat 09.00 WIB - 16.30 WIB dan Sabtu 09.00
                           WIB - 12.00 WIB
                           Untuk Tiket yang sifatnya SEGERA, mohon untuk membuat tiket ke Technical Support</p>
                     </div>
                  </div>
               </div> --}}
            </div>
         </div>
      </div>
   </div>
@endsection
