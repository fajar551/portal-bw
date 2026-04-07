@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Compose Message</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Send Email Message</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    @if (session('message'))
                                        <div class="alert alert-{{ session('type') }}">
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                            <strong>{!! session('message') !!}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 min-vh-100 bg-white">
                                        <form action="#" method="POST" id="form-sendmessage" enctype="multipart/form-data" onsubmit="sendMessage(); return false;" class="needs-validation" autocomplete="off" novalidate>
                                            @csrf
                                            <input type="hidden" name="action" value="send" /> 
                                            <input type="hidden" name="type" value="{{ $type }}" />
                                            {{-- <input type="hidden" name="messagename" id="messagename" value="" /> --}}

                                            @if ($massmailquery)
                                                <input type="hidden" name="massmailquery" value="{{ $massmailquery }}">
                                                <input type="hidden" name="massmail" value="true" />
                                                <input type="hidden" name="sendforeach" value="{{ $sendforeach }}" />
                                            @elseif ($multiple)
                                                <input type="hidden" name="multiple" value="true" />
                                                @foreach ($selectedclients as $selectedclient)
                                                <input type="hidden" name="selectedclients[]" value="{{ $selectedclient }}" />
                                                @endforeach
                                            @else 
                                                <input type="hidden" name="id" value="{{ $id }}" />
                                            @endif

                                            <div class="rounded border p-3 mb-3">
                                                <div class="row flex-wrap">
                                                    <div class="col-lg-12">
                                                        <div class="form-group row">
                                                            <label for="fromname" class="col-sm-2 col-form-label">From</label>
                                                            <div class="col-sm-5">
                                                                <input type="text" name="fromname" class="form-control @error('fromname') is-invalid @enderror" id="fromname" value="{{ old("fromname", $fromname) }}" placeholder="Name or Company Name" required>
                                                                @error('fromname')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="email" name="fromemail" class="form-control @error('fromemail') is-invalid @enderror" id="fromemail" value="{{ old("fromemail", $fromemail) }}" placeholder="Email" required>
                                                                @error('fromemail')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="recipientlist" class="col-sm-2 col-form-label">Recipients</label>
                                                            <div class="col-sm-5">
                                                                <input type="hidden" name="recipients" value="{{ $numRecipients }}" >
                                                                <select name="recipientlist" id="recipientlist" class="form-control" size="4" >
                                                                    <option>
                                                                        {{ $numRecipients }} recipients matched sending criteria. 
                                                                        @if (50 < $numRecipients) Showing first 50 only... @endif
                                                                    </option>
                                                                    @foreach ($todata as $i => $to)
                                                                        <option>{!! $to !!}</option>
                                                                        @if (49 < $i) 
                                                                            @php break; @endphp
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                                @error('recipent')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <label for="">
                                                                    Emails are sent individually<br/>
                                                                    so email addresses won't be seen by others
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="cc" class="col-sm-2 col-form-label">CC</label>
                                                            <div class="col-sm-5">
                                                                <input type="text" name="cc" class="form-control @error('cc') is-invalid @enderror" id="cc" value="{{ old("cc") }}" placeholder="CC">
                                                                @error('cc')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <label for="">Comma separate emails</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="bcc" class="col-sm-2 col-form-label">BCC</label>
                                                            <div class="col-sm-5">
                                                                <input type="text" name="bcc" class="form-control @error('bcc') is-invalid @enderror" id="bcc" value="{{ old("bcc") }}" placeholder="BCC">
                                                                @error('bcc')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <label for="">Comma separate emails</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="subject" class="col-sm-2 col-form-label">Subject</label>
                                                            <div class="col-sm-8">
                                                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" id="subject" value="{!! old("subject", $subject) !!}" placeholder="Subject" required>
                                                                @error('subject')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row flex-wrap" id="message_container">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <textarea name="message" id="email_msg1" rows="15" class="tinymce form-control">{!! $mailmessage !!}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card p-3">
                                                <div class="form-group row">
                                                    <label for="subject" class="col-sm-2 col-form-label text-right">Attachment</label>
                                                    <div class="col-sm-8">
                                                        <input type="file" name="attachment[]" class="form-control @error('attachment.*') is-invalid @enderror attch" id="input-attachment" >
                                                        <div id="div-attachment">

                                                        </div>
                                                        @error('attachment.*')
                                                            <div class="text-danger" >{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <button type="button" id="add-attachment" class="btn btn-primary btn-block">
                                                            Add More
                                                        </button>
                                                    </div>
                                                </div>
                                                @if ($massmailquery || $multiple)
                                                <div class="form-group row">
                                                    <label for="#" class="col-sm-2 col-form-label text-right">Marketing Email?</label>
                                                    <div class="col-sm-7">
                                                        <div class="form-check mt-2">
                                                            <input type="checkbox" name="emailoptout" class="form-check-input @error('emailoptout') is-invalid @enderror" id="emailoptout" value="1" @if (old("emailoptout")) checked @endif>
                                                            <label class="form-check-label" for="emailoptout"> Don't send this email to clients who have opted out of marketing emails </label>
                                                            @error('emailoptout')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                @if (auth()->user()->checkPermissionTo("Create/Edit Email Templates"))
                                                <div class="form-group row">
                                                    <label for="#" class="col-sm-2 col-form-label text-right">Save Message</label>
                                                    <div class="col-sm-3">
                                                        <div class="form-check mt-2">
                                                            <input type="checkbox" name="save" class="form-check-input @error('save') is-invalid @enderror" id="save" value="1" @if (old("save")) checked @endif>
                                                            <label class="form-check-label" for="save"> Tick to save and enter save name: </label>
                                                            @error('save')
                                                                <div class="text-danger" >{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <input type="text" name="savename" id="savename" class="form-control @error('savename') is-invalid @enderror" placeholder="Name for this mail" value="{{ old('savename') }}" >
                                                        @error('savename')
                                                            <div class="text-danger" >{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                @endif

                                                @if ($massmailquery)
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label text-right">Mass Mail Settings</label>
                                                    <div class="col-sm-12 col-lg-3">
                                                        <div class="d-flex justify-content-start align-items-center">
                                                            <label class="mb-0 mr-2">Send</label>
                                                            <input type="number" name="massmailamount" min="1" step="1" class="form-control" value="25" style="max-width: 100px;">
                                                            <label class="mb-0 ml-2">emails every</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-lg-5">
                                                        <div class="d-flex justify-content-start align-items-center">
                                                            <input type="number" name="massmailinterval" min="5" step="1" class="form-control" value="30" style="max-width: 100px;">
                                                            <label class="mb-0 ml-2">seconds until complete</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12 d-flex justify-content-center">
                                                    <button type="button" id="btn-preview" onclick="preview();" class="btn btn-light btn-md px-3 mx-1">
                                                        <i class="fa fa-eye"></i> Message Preview
                                                    </button>
                                                    <button type="submit" class="btn btn-success btn-md px-3 mr-2">
                                                        <i class="fa fa-paper-plane"></i> Send Message
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3 bg-white">
                                        <h4>Available Merge Fields</h4>
                                        <div class="row flex-wrap">
                                            <div class="col-lg-12">
                                                <div class="form-group" >
                                                    <div id="mergefields" style="border:1px solid #8FBCE9;background:#ffffff;color:#000000;padding:5px;height:300px;overflow:auto;font-size:0.95em;z-index:10;">
                                                        @include('includes.mergefields')
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off" id="form-loadmessage">
                                            <div class="form-group row d-flex justify-content-center align-items-center">
                                                <label for="subject" class="col-sm-2 col-form-label text-right">Load Saved Message: </label>
                                                <div class="col-sm-5">
                                                    <select name="messagename" id="messagenameselect" class="form-control">
                                                        <option value="">Choose...</option>
                                                        @foreach ($templates as $template) {
                                                            @if ($type != "general") 
                                                                <option @if (!$template->custom) style="background-color: #efefef" @endif>{{ $template->name }}</option>
                                                            @else 
                                                                <option style="background-color: #ffffff">{{ $template->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <button type="button" id="btn-loadmessage" onclick="loadmessage();" class="btn btn-light btn-block">
                                                        Load Message
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End MAIN CARD -->
                </div>
            </div>
        </div>
    </div>

    <!--  Modal Preview -->
    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modalPreviewWindow" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="myLargeModalLabel">Message Preview <img class="ml-2" src="{{ Theme::asset('img/loading.gif') }}" id="prev-loader" alt="loading" hidden><br>
                        {{-- <p id="text-loading">Loading... </p>   --}}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalPreviewWindowBody">
                    <p id="text-loading">Loading... </p>  
                    <div class="card p-3">
                        <div id="previewwndcontent">

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light waves-effect" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <!--  Modal Sendmessage -->
    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" id="modal-sendmessage" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="myLargeModalLabel">Sending Message<img class="ml-2" src="{{ Theme::asset('img/loading.gif') }}" id="send-loader" alt="loading" hidden><br>
                        <p id="loadinginfo-container">Please wait and do not close this window until complete... </p>  
                    </h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> --}}
                </div>
                <div class="modal-body" id="modalSendWindowBody">
                    <div class="card p-3">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    <h5>Mass Mail Queue</h5>
                                    <div id="progress-container">
                                        Loading...
                                        {{-- <strong>1 Emails to Send in Total - Step 1 of 1 - Estimated Time Until Completion: 0 Seconds. Please wait...</strong> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div id="result-container">
                                    {{-- <ul>
                                        <li>Email Sending Failed - SMTP Error: The following recipients failed: andy.wijang@gmail.com: SMTP AUTH is required for message submission on port 587</li>
                                    </ul> --}}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div id="complete-container">
                                    {{-- <strong> Email Queue Processing Completed</strong> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light waves-effect" id="btn-sendmessage-close" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Summernote js -->
    {{-- <script src="{{ Theme::asset('assets/libs/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script> --}}

     <!-- Parsley -->
     <script src="{{ Theme::asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
     
     <!-- Serialize Json -->
     <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

     <!--tinymce js-->
     <script src="{{ Theme::asset('assets/libs/tinymce/tinymce.min.js') }}"></script>
 
     <!-- Bootstrap default validation -->
     <script src="{{ Theme::asset('assets/js/pages/form-validation.init.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

     <script>

        let massMailbatchTimeout = 0;
        let countDownTimeout = 0;
        let timeLeft = 0;

        const tinymceSettings = {
            selector: "textarea#email_msg1",
            height: 600,
            plugins: 'print preview importcss searchreplace autolink autosave save directionality  visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists  wordcount imagetools textpattern noneditable help charmap quickbars  emoticons ',
            mobile: {
                plugins: 'print preview importcss searchreplace autolink autosave save directionality  visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists  wordcount textpattern noneditable help charmap quickbars  emoticons'
            },
            menubar: 'file edit view insert format tools table tc help',
            toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist  | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview print | insertfile image media template link anchor codesample | a11ycheck ltr rtl | showcomments addcomment',
            autosave_ask_before_unload: true,
            autosave_interval: '30s',
            autosave_prefix: '{path}{query}-{id}-',
            autosave_restore_when_empty: false,
            autosave_retention: '2m',
            image_advtab: true,
            importcss_append: true,
            templates: [
                { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
                { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
                { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
            ],
            browser_spellcheck: true,
            convert_urls : false,
            relative_urls : false,
            forced_root_block : "p",
            media_poster: false,
            image_caption: true,
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
            toolbar_mode: 'sliding',
        }

        $(() => {
            
            tinymce.init(tinymceSettings);

            let maxAllowedFields = 10;
            let counterFlag = 1;

            $("#add-attachment").click(function(e) {
                e.preventDefault();
                if(counterFlag < maxAllowedFields) {
                    counterFlag++;

                    let html = `
                        <div class="row more-attachment mt-3">
                            <div class="col-sm-10">
                                <input type="file" name="attachment[]" class="form-control @error('attachment.*') is-invalid @enderror attch" >
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-danger" id="remove-attachment">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>`;

                    $("#div-attachment").append(html);
                }
            });

            $("#div-attachment").on("click", "#remove-attachment", function(e) {
                e.preventDefault(); 
                $(this).parents(".more-attachment").remove(); 
                counterFlag--;
            });
            
        });

        const insertMergeField = (mfield) => {
            tinymce.activeEditor.insertContent('\{\{\$' + mfield + '\}\}');  
        }

        const loadmessage = async () => {
            const val = $("#messagenameselect").val();
            if (!val) {
                showNoSelectionToast();
                return;
            }

            const url = "{!! route('admin.pages.clients.massmail.loadmessage') !!}";
            const formData = $('#form-sendmessage').serializeJSON();
            const formLoadmessage = $('#form-loadmessage').serializeJSON();
            const payloads = {
                ...formData,
                ...formLoadmessage,
            }
            options.body = JSON.stringify(payloads);
            options.method = "POST";

            $("#btn-loadmessage").attr({ "disabled": true }).text("Loading...");

            const response = await cbmsPost(url, options);
            
            $("#btn-loadmessage").attr({ "disabled": false }).text("Load Message");

            if (response) {    
                const { result, message, data = null } = response;

                if (result == 'error') {
                    Toast.fire({ icon: result, title: message, });
                    return false;
                }
    
                if (data) {
                    if (data.fromname) { $("#fromname").val(data.fromname); }
                    if (data.fromemail) { $("#fromemail").val(data.fromemail); }
                    if (data.subject) { $("#subject").val(data.subject); }
                    if (data.message) { tinymce.activeEditor.setContent(data.message); }

                    $('html, body').animate({
                        scrollTop: $("#message_container").offset().top
                    }, 500)
                }

                return true;
            }

            console.log(`loadmessage: Failed to fetch data. Response: ${response}`);
        }

        const preview = async () => {
            $("#previewwndcontent").html("");
            $("#modalPreviewWindow").modal({ show: true, backdrop: "static"} );
            
            const url = "{!! route('admin.pages.clients.massmail.preview') !!}";
            const formData = $('#form-sendmessage').serializeJSON();            
            const payloads = {
                ...formData,
                message: tinymce.activeEditor.getContent(),
            }

            options.body = JSON.stringify(payloads);
            options.method = "POST";

            $("#prev-loader").removeAttr("hidden");
            $("#text-loading").html("Loading...");

            const response = await cbmsPost(url, options);
            
            $("#prev-loader").attr({ "hidden": true});
            $("#text-loading").html("");

            if (response) {    
                const { result, message, data = null } = response;

                if (result == 'error') {
                    Toast.fire({ icon: result, title: message, });
                    return false;
                }
    
                if (data) {
                    $("#previewwndcontent").html(data.html);
                }

                return true;
            }

            console.log(`loadmessage: Failed to fetch data. Response: ${response}`);
        }
        
        const sendMessage = async (params = {}) => {
            const form = $("#form-sendmessage")[0];

            if (!form.reportValidity() || !(tinymce.activeEditor.getContent()).length) {
                Toast.fire({ icon: "warning", title: "Please ensure to fill all fields correctly and re-submit the form." });
                return;
            }

            if (!Object.keys(params).length) {
                $("#btn-sendmessage-close").attr({disabled: true}).html("Loading..."); 
                $("#loadinginfo-container").html("Please wait and do not close this window until complete..."); 
                $("#progress-container").html("");
                $("#result-container").html("");
                $("#complete-container").html("");
                $("#modal-sendmessage").modal({ show: true, backdrop: "static"} );
            }

            const url = "{!! route('admin.pages.clients.massmail.send') !!}";
            const formData = new FormData(form);
            formData.append('message', tinymce.activeEditor.getContent());
            if (Object.keys(params).length) {
                formData.append('step', params.step);
            }

            const opt = {
                method: "POST",
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            }

            $("#send-loader").removeAttr("hidden");

            const response = await cbmsPost(url, opt);
            
            $("#send-loader").attr({ "hidden": true});

            if (response) {    
                const { result, message, data = null } = response;

                if (result == 'error') {
                    $("#btn-sendmessage-close").attr({disabled: false}).html("OK"); 
                    
                    let errorString = '<ul>';
                    $.each(data.errors, (key, value) => {
                        errorString += '<li>' + value + '</li>';
                    });
                    errorString += '</ul>';

                    $("#progress-container").html(message);
                    $("#result-container").append(errorString);

                    Toast.fire({ icon: result, title: message, });
                    return false;
                }
    
                if (data) {
                    if (data.progress) { $("#progress-container").html(data.progress); }
                    if (data.result) { $("#result-container").append(data.result); }
                    if (data.complete) {
                        $("#btn-sendmessage-close").attr({disabled: false}).html("OK"); 
                        $("#btn-sendmessage-close").on("click", () => {
                            window.location.reload();
                        });
                        
                        $("#complete-container").html(data.complete); 
                        $("#loadinginfo-container").html(data.textloading); 
                    }

                    if (!data.isDone) {
                        let step = data.step;
                        let interval = data.massmailinterval;

                        $("#send-loader").attr({ "hidden": true});
                        $("#complete-container").html("<div id=\"countdown\"> </div> <a href=\"javascript:void(0);\" onclick=\"forceNextBatch("+step+");\">" +"{{ __('admin.sendmessageforcenextbatch') }}" +"</a>"); 
                        
                        timeLeft = interval;
                        countDownTimeout = setTimeout(countdown(), 1000);
                        massMailbatchTimeout = setTimeout(() => {
                            forceNextBatch(step);
                            // sendMessage({ step });
                        }, (interval * 1000));
                    }
                }

                return true;
            }

            $("#btn-sendmessage-close").attr({disabled: false}).html("OK"); 
            console.log(`loadmessage: Failed to fetch data. Response: ${response}`);

        }

        const forceNextBatch = (step) => {
            $("#complete-container").html("");
            if (massMailbatchTimeout) {
                clearTimeout(massMailbatchTimeout);
                massMailbatchTimeout = 0;
            }

            if (countDownTimeout) {
                clearTimeout(countDownTimeout);
                timeLeft = 0;
                countDownTimeout = 0;
            }

            sendMessage({ step });
        }

        const countdown = () => {
            timeLeft--;
            $("#countdown").html(String(timeLeft) +" Seconds until next batch...");
            
            if (timeLeft >= 0) {
                setTimeout(countdown, 1000);
            } else {
                clearTimeout(countDownTimeout);
                timeLeft = 0;
                countDownTimeout = 0;
            }
        }
        
    </script>
    
@endsection
