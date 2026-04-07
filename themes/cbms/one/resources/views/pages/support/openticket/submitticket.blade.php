@extends('layouts.clientbase')

@section('title')
   Submit Ticket
@endsection

@section('page-title')
   Submit Ticket
@endsection

@section('content')
   <div class="page-content" id="open-new-ticket">
      <div class="coantainer-fluid">
         <div class="row pb-3">
            <div class="col-xl-8 col-lg-8">
               <div class="header-breadcumb">
                  <h6 class="header-pretitle d-none d-md-block mt-2"><a href="{{ route('home') }}">Dashboard</a>
                     <a href="{{ route('pages.support.openticket.index') }}"><span> /
                           Open New Ticket</span></a>
                     <span class="text-muted"> / Submit Ticket</span>
                  </h6>
               </div>
            </div>
         </div>
         @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
               <h5>Your Ticket is Submitted</h5>
               <small>{{ $message }}</small>
               <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
         @endif
         @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
               <h5>Something Wrong!</h5>
               <small>{{ $message }}</small>
               <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
         @endif
         <div class="card card-ticket">
            <div class="row">
               <div class="col-lg-12">
                  <div class="ticket-container-header">
                     <h1>Open Ticket</h1>
                  </div>
               </div>
               <div class="col-lg-12">
                  <form action="{{ route('pages.support.openticket.postticket') }}" class="w-100" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="col-sm-12 col-lg-6">
                           <div class="form-group">
                              <label for="name">Name</label>
                              <input type="text" class="form-control" name="name"
                                 value="{{ ucfirst(Auth::user()->firstname . ' ' . Auth::user()->lastname) }}" readonly>
                           </div>
                        </div>
                        <div class="col-sm-12 col-lg-6">
                           <div class="form-group">
                              <label for="email">Email</label>
                              <input type="text" class="form-control" name="email" value="{!! Auth::user()->email !!}"
                                 readonly>
                           </div>
                        </div>
                        <div class="col-sm-12 col-lg-12">
                           <div class="form-group">
                              <label for="subject">Subject</label>
                              <input type="text" class="form-control" name="subject">
                           </div>
                        </div>
                        <div class="col-sm-12 col-lg-4">
                           <div class="form-group">
                              <label for="dept">Department</label>
                              <select name="deptid" class="form-control" name="department">
                                 @foreach ($deptId as $dept)
                                    <option value="{{ $dept->id }}"
                                       {{ $dept->id == $clickedDept ? 'selected' : '' }}>
                                       {{ $dept->name }}</option>
                                 @endforeach
                              </select>
                           </div>
                        </div>
                        <div class="col-sm-12 col-lg-4">
                           <div class="form-group">
                              <label for="relservice">Related Service</label>
                              <select class="form-control" name="relatedservice">
                                 <option value="0" selected>None</option>
                                 @foreach ($relService as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}
                                       ({{ $service->domainstatus }})
                                    </option>
                                 @endforeach
                              </select>
                           </div>
                        </div>
                        <div class="col-sm-12 col-lg-4">
                           <div class="form-group">
                              <label for="prior">Priority</label>
                              <select class="form-control" name="urgency">
                                 <option value="Low">Low</option>
                                 <option value="Medium">Medium</option>
                                 <option value="High">High</option>
                              </select>
                           </div>
                        </div>
                        <div class="col-sm-12 col-lg-12">
                           <div class="form-group">
                              <label for="message">Message</label>
                              <textarea type="textarea" name="message" class="summernote form-control" rows="12">{{ old('note') }}
                                                                                           </textarea>
                           </div>
                        </div>
                        <div class="col-lg-10">
                           <div class="form-group" id="attachmentFile">
                              <label>Attachment File</label>
                              <div class="custom-file mb-1">
                                 <input type="file" class="custom-file-input" id="inputGroupFile01" name="attachments[]">
                                 <label class="custom-file-label" for="inputGroupFile01">Choose file</label>
                              </div>
                           </div>
                           <small>File Allowed: .jpg, .gif, .jpeg, .png, .pdf, .doc, .docx
                           </small>
                        </div>
                        <div class="col-lg-2 pt-4">
                           <div class="form-group my-1">
                              <button type="button" class="btn btn-outline-success btn-block btn-add-files"><i
                                    class="feather-plus"></i>
                                 Add More
                                 File</button>
                           </div>
                        </div>
                        <div class="col-lg-12 text-center">
                           <a href="">
                              <button type="submit" class="btn btn-success-qw"> <i class="feather-send"></i> Send Request
                              </button>
                           </a>
                        </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   </div>
@endsection

@section('scripts')
   <!-- BS Markdown -->
   <script src="{{ Theme::asset('assets/plugins/bootstrap-markdown/js/markdown.js') }}"></script>
   <script src="{{ Theme::asset('assets/plugins/bootstrap-markdown/js/to-markdown.js') }}"></script>
   <script src="{{ Theme::asset('assets/plugins/bootstrap-markdown/js/bootstrap-markdown.js') }}"></script>
   <script src="{{ Theme::asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
   <script src="{{ Theme::asset('assets/js/file-upload.js') }}"></script>

   <script>
      $(() => {
         notesEditor();
      })
      const notesEditor = () => {
         // Src: 
         // https://www.jqueryscript.net/text/Easy-WYSIWYG-Markdown-Rditor-For-Bootstrap-Bootstrap-Markdown.html
         // http://github.com/toopay/bootstrap-markdown
         $(".summernote").markdown({
            // auto focus after instantiated
            autofocus: false,

            // is hideable
            hideable: false,

            // is savable
            savable: false,

            // width/height
            width: 'inherit',
            height: 'inherit',

            // none,both,horizontal,vertical
            resize: 'vertical',

            // custom icon
            iconlibrary: 'fa',

            // default language
            language: 'en',

            // Contains enable (bool) and icons (object) keys
            fullscreen: {},

            // Enables integration with DropZone for allowing file upload/linking via drag&drop
            dropZoneOptions: null,

            // Array or string of button names to be hidden. 
            hiddenButtons: [],

            // Array or string of button names to be disabled. Default is empty string
            disabledButtons: []
         });
      }
   </script>
@endsection
