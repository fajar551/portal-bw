@extends('layouts.clientbase')

@section('page-title')
   Open Ticket
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         <br />

         <div class="row">
            <div class="col-sm-12">

               <h6 class="text-center">{{ Lang::get('client.supportticketsticketcreateddesc') }}</h6>

               <div class="alert alert-success text-center">
                  <strong>
                     {{ Lang::get('client.supportticketsticketcreated') }}
                     <a id="ticket-number"
                        href="{{ route('pages.support.mytickets.ticketdetails') }}?tid={{ $tid }}&amp;c={{ $c }}"
                        class="alert-link">#{{ $tid }}</a>
                  </strong>
               </div>



               <br />

               <div class="text-center">
                  <a href="{{ route('pages.support.mytickets.ticketdetails') }}?tid={{ $tid }}&amp;c={{ $c }}"
                     class="btn btn-success-qw">
                     <div class="btn btn-success-qw px-5">
                        {{ Lang::get('client.continue') }}
                        <i class="fas fa-arrow-circle-right"></i>
                     </div>
                  </a>
               </div>

               <br />
               <br />
               <br />

            </div>
         </div>

      </div>
   </div>
@endsection
