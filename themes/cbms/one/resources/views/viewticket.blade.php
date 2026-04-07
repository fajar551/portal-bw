@extends('layouts.clientbase')

@section('page-title')
   View Ticket
@endsection

@section('styles')
   <link rel="stylesheet" href="{{ Theme::asset('assets/plugins/fancybox/fancybox.css') }}">
   <link rel="stylesheet" href="{{ Theme::asset('assets/plugins/fancybox/panzoom.css') }}">
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         @if ($invalidTicketId)
            @include('includes.alert', [
                'type' => 'danger',
                'title' => Lang::get('client.thereisaproblem'),
                'msg' => Lang::get('client.supportticketinvalid'),
                'textcenter' => true,
            ])
         @else
            @if ($closedticket)
               @include('includes.alert', [
                   'type' => 'warning',
                   'msg' => Lang::get('client.supportticketclosedmsg'),
                   'textcenter' => true,
               ])
            @endif

            @if ($errormessage)
               @include('includes.alert', [
                   'type' => 'error',
                   'errorshtml' => $errormessage,
               ])
            @endif
         @endif

         @if (!$invalidTicketId)
            <div class="card card-info card-collapsable {{ !$postingReply ? 'card-collapsed' : '' }} hidden-print">
               <div class="card-header bg-primary" id="ticketReply" data-toggle="collapse"
                  data-target="#ticketReplyCollapse" style="cursor: pointer;">
                  <div class="collapse-icon pull-right text-white">
                     <i class="fas fa-{{ !$postingReply ? 'plus' : 'minus' }}"></i>
                  </div>
                  <h3 class="card-title text-white">
                     <i class="fas fa-pencil-alt text-white"></i> &nbsp; {{ Lang::get('client.supportticketsreply') }}
                  </h3>
               </div>
               <div class="card-body collapse {{ !$postingReply ? 'card-body-collapsed' : '' }}" id="ticketReplyCollapse">

                  <form method="post"
                     action="{{ route('pages.support.mytickets.ticketdetails') }}?tid={{ $tid }}&amp;c={{ $c }}&amp;postreply=true"
                     enctype="multipart/form-data" role="form" id="frmReply">
                     @csrf
                     <div class="row">
                        <div class="form-group col-sm-4">
                           <label for="inputName">{{ Lang::get('client.supportticketsclientname') }}</label>
                           @if ($loggedin)
                              <input class="form-control disabled" type="text" id="inputName" value="{{ $clientname }}"
                                 disabled="disabled" />
                           @else
                              <input class="form-control" type="text" name="replyname" id="inputName"
                                 value="{{ $replyname }}" />
                           @endif
                        </div>
                        <div class="form-group col-sm-5">
                           <label for="inputEmail">{{ Lang::get('client.supportticketsclientemail') }}</label>
                           @if ($loggedin)
                              <input class="form-control disabled" type="text" id="inputEmail" value="{{ $email }}"
                                 disabled="disabled" />
                           @else
                              <input class="form-control" type="text" name="replyemail" id="inputEmail"
                                 value="{{ $replyemail }}" />
                           @endif
                        </div>
                     </div>

                     <div class="form-group">
                        <label for="inputMessage">{{ Lang::get('client.contactmessage') }}</label>

                        {{-- <textarea name="replymessage" id="inputMessage" rows="12" class="form-control markdown-editor" data-auto-save-name="client_ticket_reply_{{$tid}}">{!!$replymessage!!}</textarea> --}}
                        <textarea name="replymessage" id="inputMessage" rows="12" class="form-control summernote"
                           data-auto-save-name="client_ticket_reply_{{ $tid }}">{!! $replymessage !!}</textarea>
                     </div>

                     <div class="row form-group">
                        <div class="col-sm-12">
                           <label
                              for="inputAttachments">{{ Lang::get('client.supportticketsticketattachments') }}</label>
                        </div>
                        <div class="col-sm-9">
                           <input type="file" name="attachments[]" id="inputAttachments" class="form-control" />
                           <div id="fileUploadsContainer"></div>
                        </div>
                        <div class="col-sm-3">
                           <button type="button" class="btn btn-secondary btn-block" onclick="extraTicketAttachment()">
                              <i class="fas fa-plus"></i> {{ Lang::get('client.addmore') }}
                           </button>
                        </div>
                        <div class="col-xs-12 ticket-attachments-message text-muted">
                           {{ Lang::get('client.supportticketsallowedextensions') }}: {{ $allowedfiletypes }}
                        </div>
                     </div>

                     <div class="form-group text-center">
                        <input class="btn btn-success-qw px-3" type="submit" name="save"
                           value="{{ Lang::get('client.supportticketsticketsubmit') }}" />
                        <input class="btn btn-secondary" type="reset" value="{{ Lang::get('client.cancel') }}"
                           onclick="jQuery('#ticketReply').click()" />
                     </div>

                  </form>

               </div>
            </div>
            <div class="card card-info visible-print-block" id="ticket-info">

               <h5 class="text-qw m-0">
                  {{ Lang::get('client.ticketinfo') }}
               </h5>
               <hr>
               <div class="card-body container-fluid">
                  <div class="row text-qw">
                     <div class="col-md-3 col-sm-6 col-xs-6 ">
                        <b
                           class="text-secondary">{{ Lang::get('client.supportticketsticketid') }}</b><br />{{ $tid }}
                     </div>
                     <div class="col-md-3 col-sm-6 col-xs-6 ">
                        <b
                           class="text-secondary">{{ Lang::get('client.supportticketsticketsubject') }}</b><br />{{ $subject }}
                     </div>
                     <div class="col-md-3 col-sm-6 col-xs-6 ">
                        <b
                           class="text-secondary">{{ Lang::get('client.supportticketspriority') }}</b><br />{{ $urgency }}
                     </div>
                     <div class="col-md-3 col-sm-6 col-xs-6 ">
                        <b
                           class="text-secondary">{{ Lang::get('client.supportticketsdepartment') }}</b><br />{{ $department }}
                     </div>
                  </div>
               </div>
            </div>

            @foreach ($descreplies as $num => $reply)
               {{-- {{ dd($reply) }} --}}
               <div class="card card-message p-3">
                  <div class="ticket-reply markdown-content{{ $reply['admin'] ? 'staff' : '' }}">
                     <div class="row flex-row-reverse mb-3 card-zeus">
                        <div class="col-md-6">
                           <div class="date">
                              {{ $reply['date'] }}
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="user">
                              <div class="row align-items-center">
                                 <div class="col-sm-2 col-md-2">
                                    @if ($reply['admin'])
                                       <div
                                          class="mx-md-auto mb-2 border border-info rounded-circle bg-light d-flex align-items-center justify-content-center"
                                          style="height: 50px; width: 50px">
                                          <i class="fas fa-user-shield text-qw"></i>
                                       </div>
                                    @else
                                       <div
                                          class="mx-md-auto mb-2 rounded-circle bg-light d-flex align-items-center justify-content-center"
                                          style="height: 50px; width: 50px">
                                          <i class="fas fa-user"></i>
                                       </div>
                                    @endif
                                 </div>
                                 <div class="col-sm-3 col-md-10">
                                    <h6 class="name mt-md-n2 mb-1 {{ $reply['admin'] ? 'text-qw' : '' }}">
                                       {{ $reply['name'] }}
                                    </h6>
                                    <div class="type">
                                       @if ($reply['admin'])
                                          <div class="badge badge-info"><i
                                                class="fas fa-check-circle mr-1"></i>{{ Lang::get('client.supportticketsstaff') }}
                                          </div>
                                       @elseif ($reply['contactid'])
                                          <div class="badge badge-light">{{ Lang::get('client.supportticketscontact') }}
                                          </div>
                                       @elseif ($reply['userid'])
                                          <div class="badge badge-secondary">
                                             {{ Lang::get('client.supportticketsclient') }}
                                          </div>
                                       @else
                                          {{ $reply['email'] }}
                                       @endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="message">
                        {!! $reply['message'] !!}
                        @if ($reply['attachments'])
                           @foreach ($reply['attachments'] as $f)
                              <a class="ticketattachmentthumbcontainer" href="{{ url('attachments/' . $f) }}"
                                 data-fancybox="gallery">
                                 <img src="{{ url('attachments/' . $f) }}" onerror="this.style.display='none'">
                              </a>
                           @endforeach
                        @endif
                        @if ($reply['id'] && $reply['admin'] && $ratingenabled)
                           <div class="clearfix">
                              @if ($reply['rating'])
                                 <div class="rating-done">
                                    @for ($rating = 1; $rating < 5; $rating++)
                                       <span class="star{{ 5 - $reply['rating'] < $rating ? 'active' : '' }}"></span>
                                    @endfor
                                    <div class="rated">{{ Lang::get('client.ticketreatinggiven') }}</div>
                                 </div>
                              @else
                                 <div class="rating" ticketid="{{ $tid }}"
                                    ticketkey="{{ $c }}" ticketreplyid="{{ $reply['id'] }}">
                                    <span class="star" rate="5"></span>
                                    <span class="star" rate="4"></span>
                                    <span class="star" rate="3"></span>
                                    <span class="star" rate="2"></span>
                                    <span class="star" rate="1"></span>
                                 </div>
                              @endif
                           </div>
                        @endif
                     </div>
                     @if ($reply['attachments'])
                        <div class="attachments">
                           <strong>{{ Lang::get('client.supportticketsticketattachments') }}
                              ({{ count($reply['attachments']) }})
                           </strong>
                           @if ($reply['attachments_removed'])
                              ({{ Lang::get('client.support.attachmentsRemoved') }})
                           @endif
                           <ul class="list-unstyled">
                              @foreach ($reply['attachments'] as $num => $attachment)
                                 @if ($reply['attachments_removed'])
                                    <li>
                                       <i class="far fa-file-minus"></i>
                                       {{ $attachment }}
                                    </li>
                                 @else
                                    <li>
                                       <i class="far fa-file"></i>
                                       <a
                                          href="{{ url("https://client.bikin.website/$attachment") }}">
                                          {{ $attachment }}
                                       </a>
                                       {{-- <a href="">
                                          {{ $attachment }}
                                       </a> --}}
                                    </li>
                                 @endif
                              @endforeach
                           </ul>
                        </div>
                     @endif
                  </div>
               </div>
            @endforeach
         @endif

      </div>
   </div>
@endsection

@section('styles')
   {{-- <link href="{{Theme::asset('css/all.css')}}" rel="stylesheet"> --}}
   <link href="{{ Theme::asset('css/custom.css') }}" rel="stylesheet">
@endsection
@section('scripts')
   <script type="text/javascript">
      var csrfToken = '{{ csrf_token() }}',
         markdownGuide = '{{ Lang::get('client.markdown.title') }}',
         locale = '{{ !empty($mdeLocale) ? $mdeLocale : 'en' }}',
         saved = '{{ Lang::get('client.markdown.saved') }}',
         saving = '{{ Lang::get('client.markdown.saving') }}',
         whmcsBaseUrl = "{{ url('/') }}",
         requiredText = '{{ Lang::get('orderForm.required') }}';
   </script>
   <script src="{{ Theme::asset('js/scripts.js') }}"></script>
   <script src="{{ Theme::asset('assets/plugins/bootstrap-markdown/js/markdown.js') }}"></script>
   <script src="{{ Theme::asset('assets/plugins/bootstrap-markdown/js/to-markdown.js') }}"></script>
   <script src="{{ Theme::asset('assets/plugins/bootstrap-markdown/js/bootstrap-markdown.js') }}"></script>
   <script src="{{ Theme::asset('assets/plugins/fancybox/fancybox.umd.js') }}"></script>
   <script>
      $(() => {
         notesEditor();

         Fancybox.bind('[data-fancybox="gallery"]', {
            Toolbar: {
               display: [{
                     id: "prev",
                     position: "center"
                  },
                  {
                     id: "counter",
                     position: "center"
                  },
                  {
                     id: "next",
                     position: "center"
                  },
                  "zoom",
                  "fullscreen",
                  "download",
                  "thumbs",
                  "close",
               ],
            },
         });
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
