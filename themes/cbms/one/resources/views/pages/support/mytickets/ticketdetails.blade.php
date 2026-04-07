@extends('layouts.clientbase')

@section('title')
   Ticket Details #{{ $tid }}
@endsection

@section('page-title')
   Ticket Details #{{ $tid }}
@endsection

@section('content')
   <div class="page-content" id="ticket-details">
      <div class="container-fluid">
         <div class="row pb-3">
            <div class="col-xl-8 col-lg-8">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ route('home') }}">Dashboard</a> <a
                        href={{ route('pages.support.mytickets.index') }}> / My Ticket</a> <span class="text-muted"> /
                        Ticket Details </span></h6>
               </div>
            </div>
            <div class="col-xl-4 col-lg-4">
               <div class="pull-right">
                  <a href="#form-balas" data-toggle="modal" class="btn btn-success-qw"><i class="feather-edit"></i>
                     Reply</a>
               </div>
            </div>
         </div>

         <div class="card" id="message-card">
            <div class="card-body">
               <div class="row">
                  <div class="col-xs-6 col-lg-12">
                     <div class="media">
                        <i class="fas fa-user-circle"></i>
                        <div class="media-body">
                           <h5 class="mt-0 mb-0">{{ $fullname }}</h5>
                           <small class="text-muted">Client</small>
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-12">
                     <div>
                        <div class="float-lg-right">
                           <div class="text-muted">{{ $date }}</div>
                        </div>
                        <div class="ticket-container">
                           <h5 class="mb-3">{{ $title }}</h5>
                           <p>
                              {{ $message }}
                           </p>
                           <div class="attachment-list">
                              <h6>Attachment List</h6>
                              @if ($attachment)
                                 @foreach ($attachment as $file)
                                 <div class="attachment my-1">
                                    <a href="{{ Storage::disk('attachments')->path($file) }}" class="text-qw" download><i class="fas fa-file mr-2"></i>{{ $file }}</a>
                                 </div>
                                 @endforeach
                              @else   
                                 <div class="text-muted font-size-10">None</div>
                              @endif
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- end row-->
      </div> <!-- container-fluid -->
   </div>
   <div class="modal fade" id="form-balas" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
      aria-hidden="true" data-keyboard="false" data-backdrop="static">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalCenterTitle">Buka Tiket</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-lg-6">
                     <div class="form-group">
                        <label for="simpleinput">Nama</label>
                        <input type="text" id="nama" class="form-control" value=" ">
                     </div>
                  </div>
                  <div class="col-lg-6">
                     <div class="form-group">
                        <label for="simpleinput">Alamat email</label>
                        <input type="email" id="nama" class="form-control" value=" ">
                     </div>
                  </div>
                  <div class="col-lg-12">
                     <div class="form-group">
                        <label for="simpleinput">Pesan</label>
                        <div id="snow-editor" style="height: 200px;"></div>
                        <!-- end Snow-editor-->
                     </div>
                  </div>
                  <div class="col-lg-10">
                     <div class="form-group">
                        <label for="simpleinput">Lampiran</label>
                        <div class="form-control">
                           <input type="file" name="attachments[]" id="inputAttachments">
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-2">
                     <div class="form-group pt-1">
                        <a href="#" class="btn btn-primary btn-block mt-4"><i class="feather-plus"></i> Lampiran</a>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
               <button type="button" class="btn btn-primary">Submit</button>
            </div>
         </div>
      </div>
   </div>
@endsection
