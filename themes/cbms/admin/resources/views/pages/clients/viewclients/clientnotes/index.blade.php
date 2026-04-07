@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Notes</title>
@endsection

@section('styles')
    {{-- <style> 
        .note-editor.note-frame .note-editing-area .note-editable, .note-editor.note-frame .note-editing-area .note-codable {
            color: rgb(34, 34, 34);
        }
    </style> --}}

    <!-- BS Markdown -->
    <link href="{{ Theme::asset('assets/libs/bootstrap-markdown/css/bootstrap-markdown.min.css') }}" rel="stylesheet" type="text/css">
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
                                    @if (isset($invalidClientId))
                                    <div class="card d-flex align-items-center justify-content-center p-3" style="min-height: 70vh;">
                                        <div class="col-lg-6">
                                            <div class="alert alert-warning p-3" role="alert">
                                                <h4 class="alert-heading">Invalid Client ID</h4>
                                                <hr>
                                                <p class="mb-0">
                                                    Please <a href="{{ route('admin.pages.clients.viewclients.index') }}">Click here</a> 
                                                    to find correct Client ID 
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @if (isset($clientsdetails))
                            <div class="row">
                                <form action="" method="POST" id="form-filters" enctype="multipart/form-data" hidden>
                                    @csrf
                                    <input type="number" name="userid" value="{{ $userid }}" hidden>
                                </form>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="dt-notes" class="table table-bordered dt-responsive nowrap w-100">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">NO</th>
                                                                <th class="text-center">Created</th>
                                                                <th class="text-center">Note</th>
                                                                <th class="text-center">Admin</th>
                                                                <th class="text-center">Last Modified</th>
                                                                <th class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>

                                        @if (!$note)
                                        {{-- Add Note --}}
                                        <div class="row">
                                            <div class="col-lg-10">
                                                <form action="{{ route('admin.pages.clients.viewclients.clientnotes.store') }}" method="POST" id="form-filters" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="number" name="userid" value="{{ $userid }}" hidden>
                                                    <textarea type="textarea" name="note" class="summernote" rows="12">{{ old('note') }}</textarea>
                                                    @error('note')
                                                        <div class="text-danger mt-2" >{{ $message }}</div>
                                                    @enderror
                                                    <button type="submit" class="btn btn-success d-inline-block mt-1"> Add New </button>
                                                    <div class="form-check m-2 d-inline-block">
                                                        <input type="checkbox" name="sticky" id="defaultCheck1" class="form-check-input" value="1" {{ old('sticky') ? "checked" : "" }}>
                                                        <label class="form-check-label" for="defaultCheck1">Make Sticky (Important)</label>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @else
                                        {{-- Update Note --}}
                                        <div class="row">
                                            <div class="col-lg-10">
                                                <form action="{{ route('admin.pages.clients.viewclients.clientnotes.update') }}" method="POST" id="form-filters" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="number" name="userid" value="{{ $userid }}" hidden>
                                                    <input type="number" name="id" value="{{ $note->id }}" hidden>
                                                    <textarea type="textarea" name="note" class="summernote @error('note') is-invalid @enderror">{{ old('note') ?? $note->note }}
                                                    </textarea>
                                                    @error('note')
                                                        <div class="text-danger mt-2" >{{ $message }}</div>
                                                    @enderror
                                                    @error('id')
                                                        <div class="text-danger mt-2" >{{ $message }}</div>
                                                    @enderror
                                                    <button type="submit" class="btn btn-success d-inline-block mt-1"> Save Changes </button>
                                                    <div class="form-check m-2 d-inline-block">
                                                        <input type="checkbox" name="sticky" id="defaultCheck1" class="form-check-input" value="1" {{ old('sticky') ? "checked" : ($note->sticky ? "checked" : "") }}>
                                                        <label class="form-check-label" for="defaultCheck1">Make Sticky (Important)</label>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
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
    
    <!-- Summernote js -->
    {{-- <script src="{{ Theme::asset('assets/libs/summernote/summernote-bs4.min.js') }}"></script> --}}
    {{-- <script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script> --}}

    <!-- Serialize Json -->
    <script src="{{ Theme::asset('assets/libs/serialize-json/jquery.serializejson.min.js') }}"></script>

    <!-- BS Markdown -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/markdown.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/to-markdown.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/bootstrap-markdown.js') }}"></script>

    <!-- Utils Helper -->
    <script src="{{ Theme::asset('assets/js/pages/helpers/utils.js') }}"></script>

    @if (isset($clientsdetails))

    @stack('clientsearch')
    
    <script>
        
        // Table
        let dtNotesTable;

        $(() => {
            dtNotes();
            notesEditor();
            // initSummerNote();
        });

        const dtNotes = () => {
            dtNotesTable = $('#dt-notes').DataTable({
                stateSave: true,
                processing: true,
                responsive: false,
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
                    url: "{!! route('admin.pages.clients.viewclients.clientnotes.dtClientNote') !!}",
                    type: "GET",
                    data: (data) => {
                        data.dataFiltered = $('#form-filters').serializeJSON();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', width: '2%', className:'text-center', visible: false, orderable: false, searchable: false, },
                    { data: 'created', name: 'created', width: '5%', className:'text-left' },
                    { data: 'note', name: 'note', width: '5%', defaultContent: 'N/A', },
                    { data: 'admin', name: 'admin', width: '10%', defaultContent: 'N/A', },
                    { data: 'last_modified', name: 'last_modified', className:'text-center', width: '5%', defaultContent: 'N/A', },
                    { data: 'actions', name: 'actions', width: '5%', className:'text-center', defaultContent: 'N/A', orderable: false, searchable: false, },
                ],
            });
        }

        const initSummerNote = () => {
            $('.summernote').summernote({
                placeholder: 'Write here...',
                height: 300,
                minHeight: null,
                maxHeight: null,
                focus: !0,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname', 'strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    // ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', /*'picture', 'video'*/]],
                    ['view', ['fullscreen', 'codeview', 'help']],
                ],
                odeviewFilter: false,
                codeviewIframeFilter: true
            });
        }

        const notesEditor = () => {
            // Src: 
            // https://www.jqueryscript.net/text/Easy-WYSIWYG-Markdown-Rditor-For-Bootstrap-Bootstrap-Markdown.html
            // http://github.com/toopay/bootstrap-markdown
            $(".summernote").markdown({
                // auto focus after instantiated
                autofocus: true,

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

        const actDelete = async (params) => {
            const url = "{!! route('admin.pages.clients.viewclients.clientnotes.delete') !!}";
            const payloads = {
                userid: "{{ $userid }}",
                id: $(params).attr('data-id'),
            };

            Swal.fire({
                title: "Are you sure?",
                html: `The <b>Data</b> will be deleted from database.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "OK",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: async (data) => {
                    options.method = 'DELETE';
                    options.body = JSON.stringify(payloads);

                    const response = await cbmsPost(url, options);
                    if (!response) {
                        const error = "An error occured.";
                        return Swal.showValidationMessage(`Request failed: ${error}`);
                    }

                    return response;
                },
            }).then((response) => {
                if (response.value) {
                    const { result, message } = response.value;

                    Toast.fire({ icon: result, title: message });
                    filterTable(null);
                }
            }).catch(swal.noop);
        }

        const filterTable = (form) => {
            dtNotesTable.ajax.reload();

            return false;
        }
    </script>
    @endif

@endsection
