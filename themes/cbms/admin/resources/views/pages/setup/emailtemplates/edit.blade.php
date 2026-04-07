@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Edit Email Templates</title>
@endsection


@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Sidebar Shortcut -->

                    <!-- End Sidebar -->

                    <!-- MAIN CARD -->
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Email Templates</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    @if ($message = Session::get('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                                            id="success-alert">
                                            <h5>Successfully Updated!</h5>
                                            <small>{{ $message }}</small>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    @if ($message = Session::get('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                            id="danger-alert">
                                            <h5>Error:</h5>
                                            <small>{{ $message }}</small>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    {{-- @foreach ($templates as $template) --}}
                                        <div class="card p-3">
                                            <h5 class="my-3">{{ $template->name }}</h5>
                                            <div class="row">
                                                <form class="w-100"
                                                    enctype="multipart/form-data"
                                                    action="{{ route('admin.pages.setup.emailtemplates.update', $template->id) }}"
                                                    method="POST">
                                                    @method('PUT')
                                                    @csrf
                                                    <div class="col-lg-12">
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">From</label>
                                                            <div class="col-sm-12 col-lg-5">
                                                                <input value="{{$template->fromname}}" type="text" name="fromname" class="form-control"
                                                                    placeholder="Company Name">
                                                            </div>
                                                            <div class="col-sm-12 col-lg-5">
                                                                <input value="{{$template->fromemail}}" type="text" name="fromemail" class="form-control"
                                                                    placeholder="mail@mail.com">
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Copy To</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <input value="{{$template->copyto}}" type="text" name="copyto" class="form-control">
                                                            </div>
                                                            <div class="col-sm-12 col-lg-4 pt-2">
                                                                <p>Enter email addresses separated by a comma</p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Blind Copy
                                                                To</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <input value="{{$template->blind_copy_to}}" type="text" name="blind_copy_to"
                                                                    class="form-control">
                                                            </div>
                                                            <div class="col-sm-12 col-lg-4 pt-2">
                                                                <p>Enter email addresses separated by a comma</p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for=""
                                                                class="col-sm-12 col-lg-2 col-form-label">Attachments</label>
                                                            <div class="col-sm-12 col-lg-10">
                                                                {{-- <div class="file-column">
                                                                    <div class="input-group mb-3">
                                                                        <div class="custom-file">
                                                                            <input type="file" name="attachments"
                                                                                class="custom-file-input"
                                                                                id="inputGroupFile01">
                                                                            <label class="custom-file-label"
                                                                                for="inputGroupFile01"
                                                                                aria-describedby="inputGroupFileAddon01">Choose
                                                                                file</label>
                                                                        </div>
                                                                    </div>
                                                                </div> --}}
                                                                @foreach ($template->attachments as $attachment)
                                                                    <div class="form-control mb-3 d-flex align-items-center">
                                                                        <span>{{$attachment}}</span>
                                                                        <button type="button" onclick="deleteAttachment({{$template->id}}, '{{$attachment}}')" data-id="{{$template->id}}" data-name="{{$attachment}}" class="btn btn-sm btn-danger ml-2">
                                                                            &times;
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                                <div class="attachmentsfiles">
                                                                    <input class="form-control mb-3" type="file" name="attachments[]">
                                                                </div>
                                                                <button type="button" class="btn btn-light btn-sm mt-2"
                                                                    id="anotherFileInput">
                                                                    <i class="fa fa-plus" aria-hidden="true"></i> Add
                                                                    Another
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                                                Plain-Text
                                                            </label>
                                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" name="plaintext" class="custom-control-input"
                                                                        id="plainTextCheck" {{$template->plaintext == 1 ? 'checked' : ''}}>
                                                                    <label class="custom-control-label"
                                                                        for="plainTextCheck">Tick
                                                                        this box to send this email in Plain-Text format
                                                                        only</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label for="" class="col-sm-12 col-lg-2 col-form-label">
                                                                Disable
                                                            </label>
                                                            <div class="col-sm-12 col-lg-10 pt-2">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" name="disabled" class="custom-control-input"
                                                                        id="disableEmailSent" {{$template->disabled == 1 ? 'checked' : ''}}>
                                                                    <label class="custom-control-label"
                                                                        for="disableEmailSent">Tick
                                                                        this box to disable this email from being
                                                                        sent</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                    <div class="col-lg-12" id="compose">
                                                        <div class="mb-3"><span class="font-weight-bold">Default
                                                                Version</span>
                                                            -
                                                            Used for the
                                                            English language and any languages where email template
                                                            translations
                                                            are
                                                            not defined</div>
                                                        <div class="form-group row">
                                                            <label for="subject"
                                                                class="col-sm-12 col-lg-1 col-form-label">Subject</label>
                                                            <div class="col-sm-12 col-lg-6">
                                                                <input type="text" value="{{ $template->subject }}"
                                                                    name="subject" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <textarea name="message"
                                                            class="summernote">{{ $template->message }}</textarea>
                                                    </div>
                                                    <div class="col-lg-12 mt-3 text-center">
                                                        <button type="submit" class="btn btn-success px-3">Save
                                                            Changes</button>
                                                        <a href="{{ route('admin.pages.setup.emailtemplates.index') }}">
                                                            <button type="button" class="btn btn-light px-3">Cancel
                                                                Changes</button>
                                                        </a>
                                                    </div>
                                                </form>
                                    {{-- @endforeach --}}
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
                        </div>
                    </div>
                </div>
            </div>
            <!-- End MAIN CARD -->
        </div>
    </div>
    </div>
    </div>
@endsection

@section('scripts')
    {{-- <script src="{{ Theme::asset('assets/js/pages/edit-email-template.js') }}"></script> --}}
    {!! Theme::script('assets/js/pages/edit-email-template.js') !!}
    <script src="{{ Theme::asset('assets/libs/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/showEditor.js') }}"></script>
    <script>
        function deleteAttachment(id, name) {
            $.ajax({
                url: "{{route('apiconsumer.admin.setup.removeEmailTemplateAttachment')}}",
                type: 'POST',
                data: {name, id},
                beforeSend: function() {},
                success: function(res) {
                    if (res.result == 'success') {
                        location.reload();
                    }
                },
                error: function(error) {
                    console.log(error);
                },
            });
        }
        function insertMergeField(mfield) {
            // $("#compose").animate({scrollTop: 0}, 300);
            $([document.documentElement, document.body]).animate({
                scrollTop: $("#compose").offset().top
            }, 200);
            $('.summernote').summernote('insertText', '\{\{\$' + mfield + '\}\}');
        }
    </script>
@endsection
