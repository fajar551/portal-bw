@extends('layouts.clientbase')

@section('page-title')
   Open Ticket
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">

         
         <div class="card p-3" id="open-ticket-default">
            <h4 class="text-qw">Choose a Department</h4>
            <p>{{ Lang::get('client.supportticketsheader') }}</p>
            <div class="row">
               <div class="col-sm-12">
                  <div class="row px-3">
                     @forelse ($departments  as $num => $department)
                        <div class="col-md-3">
                           <a
                              href="{{ route('pages.support.openticket.index') }}?step=2&amp;deptid={{ $department['id'] }}">
                              <div class="card dept-card p-3">
                                    <strong>
                                       <i class="fas fa-envelope"></i> &nbsp;{{ $department['name'] }}
                                    </strong>
                                 @if ($department['description'])
                                    <p>{!! $department['description'] !!}</p>
                                 @endif
                              </div>
                           </a>
                        </div>
                        @if ($num % 2 == true)
                           <div class="clearfix"></div>
                        @endif
                     @empty
                        @include('includes.alert', [
                        'type' => 'info',
                        'msg' => Lang::get('client.nosupportdepartments'),
                        'textcenter' => true,
                        ])
                     @endforelse
                  </div>
               </div>
            </div>
         </div>

      </div>
   </div>
@endsection
