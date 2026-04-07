@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Emails</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Client Profile</h4>
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
                            
                             {{-- Row client select --}}
                             @include('includes.clientsearch')

                            @include('includes.tabnavclient')
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card p-3 min-vh-100 bg-white">
                                                <form action="{{ route('admin.pages.clients.viewclients.clientemails.doResend') }}" method="POST" enctype="multipart/form-data" autocomplete="off" class="needs-validation" novalidate>
                                                    @csrf
                                                    <input type="number" name="userid" value="{{ $userid }}" hidden>
                                                    <input type="number" name="emailid" value="{{ $emailid }}" hidden>
                                                    <input type="hidden" name="action" value="send" /> 
                                                    <input type="hidden" name="type" value="general" />
                                                    <div class="rounded border p-3 mb-3">
                                                        <div class="row flex-wrap">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row">
                                                                    <label for="fromname" class="col-sm-2 col-form-label">From</label>
                                                                    <div class="col-sm-5">
                                                                        <input type="text" name="fromname" class="form-control @error('fromname') is-invalid @enderror" id="fromname" value="{{ old("fromname") ?? $fromname }}" placeholder="Name or Company Name" required>
                                                                        @error('fromname')
                                                                            <div class="text-danger" >{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        <input type="email" name="fromemail" class="form-control @error('fromemail') is-invalid @enderror" id="fromemail" value="{{ old("fromemail") ?? $fromemail }}" placeholder="Email" required>
                                                                        @error('fromemail')
                                                                            <div class="text-danger" >{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label for="recipent" class="col-sm-2 col-form-label">Recipients</label>
                                                                    <div class="col-sm-5">
                                                                        <select name="recipent" class="form-control" size="4" >
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
                                                                        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" id="subject" value="{!! old("subject") ?? $subject !!}" placeholder="Subject">
                                                                        @error('subject')
                                                                            <div class="text-danger" >{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row flex-wrap">
                                                        <div class="col-lg-12">
                                                            <div class="form-group">
                                                                <textarea name="message" id="email_msg1" rows="25" class="tinymce form-control">{!! $message !!}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card p-3">
                                                        <div class="form-group row">
                                                            <label for="subject" class="col-sm-2 col-form-label text-right">Attachment</label>
                                                            <div class="col-sm-8">
                                                                <input type="file" name="attachment[]" class="form-control @error('attachment.*') is-invalid @enderror" id="input-attachment" >
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
                                                                <input type="text" name="savename" id="savename" class="form-control @error('savename') is-invalid @enderror" placeholder="Name for this mail" value="{{ old('savename') }}" autocomplete="off">
                                                                @error('savename')
                                                                    <div class="text-danger" >{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-12 d-flex justify-content-center">
                                                            <button type="submit" class="btn btn-success btn-md px-3 mr-2">
                                                                <i class="fa fa-paper-plane"></i> Send
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Required datatable js -->
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    {{-- <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script> --}}

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!--tinymce js-->
    <script src="{{ Theme::asset('assets/libs/tinymce/tinymce.min.js') }}"></script>

    <!-- Bootstrap default validation -->
    <script src="{{ Theme::asset('assets/js/pages/form-validation.init.js') }}"></script>

    @stack('clientsearch')
    
    <script>

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            timerProgressBar: true,
            timer: 3000,
        });

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
                                <input type="file" name="attachment[]" class="form-control @error('attachment.*') is-invalid @enderror" id="input-attachment" >
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

    </script>
@endsection
