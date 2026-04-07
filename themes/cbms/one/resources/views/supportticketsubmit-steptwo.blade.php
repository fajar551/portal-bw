@extends('layouts.clientbase')

@section('page-title')
   Open Ticket
@endsection

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         @if ($errormessage)
            @include('includes.alert', [
            'type' => 'error',
            'errorshtml' => $errormessage,
            ])
         @endif
         <div class="card p-3">

            <form method="post" action="{{ route('pages.support.openticket.index') }}?step=3" enctype="multipart/form-data"
               role="form">
               @csrf
               <div class="row">
                  <div class="form-group col-sm-6">
                     <label for="inputName">{{ Lang::get('client.supportticketsclientname') }}</label>
                     <input type="text" name="name" id="inputName" value="{{ $loggedin ? $clientname : $name }}"
                        class="form-control {{ $loggedin ? 'disabled' : '' }}" @if ($loggedin) disabled="disabled" @endif />
                  </div>
                  <div class="form-group col-sm-6">
                     <label for="inputEmail">{{ Lang::get('client.supportticketsclientemail') }}</label>
                     <input type="email" name="email" id="inputEmail" value="{{ $email }}"
                        class="form-control {{ $loggedin ? 'disabled' : '' }}" @if ($loggedin) disabled="disabled" @endif />
                  </div>
               </div>
               <div class="row">
                  <div class="form-group col-sm-12">
                     <label for="inputSubject">{{ Lang::get('client.supportticketsticketsubject') }}</label>
                     <input type="text" name="subject" id="inputSubject" value="{{ $subject }}"
                        class="form-control" />
                  </div>
               </div>
               <div class="row">
                  <div class="form-group col-sm-3">
                     <label for="inputDepartment">{{ Lang::get('client.supportticketsdepartment') }}</label>
                     <select name="deptid" id="inputDepartment" class="form-control"
                        onchange="refreshCustomFields(this)">
                        @foreach ($departments as $department)
                           <option value="{{ $department['id'] }}" @if ($department['id'] == $deptid)
                              selected="selected"
                        @endif>
                        {{ $department['name'] }}
                        </option>
                        @endforeach
                     </select>
                  </div>
                  @if ($relatedservices)
                     <div class="form-group col-sm-6">
                        <label for="inputRelatedService">{{ Lang::get('client.relatedservice') }}</label>
                        <select name="relatedservice" id="inputRelatedService" class="form-control">
                           <option value="">{{ Lang::get('client.none') }}</option>
                           @foreach ($relatedservices as $relatedservice)
                              <option value="{{ $relatedservice['id'] }}">
                                 {{ $relatedservice['name'] }} ({{ $relatedservice['status'] }})
                              </option>
                           @endforeach
                        </select>
                     </div>
                  @endif
                  <div class="form-group col-sm-3">
                     <label for="inputPriority">{{ Lang::get('client.supportticketspriority') }}</label>
                     <select name="urgency" id="inputPriority" class="form-control">
                        <option value="High" @if ($urgency == 'High') selected="selected" @endif>
                           {{ Lang::get('client.supportticketsticketurgencyhigh') }}
                        </option>
                        <option value="Medium" @if ($urgency == 'Medium' || !$urgency) selected="selected" @endif>
                           {{ Lang::get('client.supportticketsticketurgencymedium') }}
                        </option>
                        <option value="Low" @if ($urgency == 'Low') selected="selected" @endif>
                           {{ Lang::get('client.supportticketsticketurgencylow') }}
                        </option>
                     </select>
                  </div>
               </div>
               <div class="form-group">
                  <label for="inputMessage">{{ Lang::get('client.contactmessage') }}</label>
                  {{-- <textarea name="message" id="inputMessage" rows="12" class="form-control markdown-editor" data-auto-save-name="client_ticket_open">{{$message}}</textarea> --}}
                  <textarea name="message" id="inputMessage" rows="12" class="form-control summernote"
                     data-auto-save-name="client_ticket_open">{{ $message }}</textarea>
               </div>

               <div class="row form-group">
                  <div class="col-sm-12">
                     <label for="inputAttachments">{{ Lang::get('client.supportticketsticketattachments') }}</label>
                  </div>
                  <div class="col-sm-9">
                     <input type="file" name="attachments[]" id="inputAttachments" class="form-control" />
                     <div id="fileUploadsContainer"></div>
                  </div>
                  <div class="col-sm-3">
                     <button type="button" class="btn btn-default btn-block" onclick="extraTicketAttachment()">
                        <i class="fas fa-plus"></i> {{ Lang::get('client.addmore') }}
                     </button>
                  </div>
                  <div class="col-xs-12 ticket-attachments-message text-muted pl-3">
                     {{ Lang::get('client.supportticketsallowedextensions') }}: {{ $allowedfiletypes }}
                  </div>
               </div>

               <div id="customFieldsContainer">
                  @include('supportticketsubmit-customfields')
               </div>

               <div id="autoAnswerSuggestions" class="well hidden"></div>

               <div class="text-center margin-bottom">
                  {{-- TODO: --}}
                  {{-- {include file="$template/includes/captcha.tpl"} --}}
               </div>

               <p class="text-center">
                  {{-- TODO: <input type="submit" id="openTicketSubmit" value="{{Lang::get('client.supportticketsticketsubmit')}}" class="btn btn-primary{$captcha->getButtonClass($captchaForm)}" /> --}}
                  <input type="submit" id="openTicketSubmit" value="{{ Lang::get('client.supportticketsticketsubmit') }}"
                     class="btn btn-success-qw px-5" />
                  <a href="{{ route('pages.support.mytickets.index') }}"
                     class="btn btn-secondary">{{ Lang::get('client.cancel') }}</a>
               </p>

            </form>
         </div>

         {{-- @if ($kbsuggestions)
            <script>
               jQuery(document).ready(function() {
                  getTicketSuggestions();
               });
            </script>
         @endif --}}
      </div>
   </div>
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
