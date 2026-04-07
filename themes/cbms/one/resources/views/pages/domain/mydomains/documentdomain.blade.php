@extends('layouts.clientbase')

@section('title')
Domain Contact Info
@endsection

@section('page-title')
{{Lang::get('client.domaincontactinfo')}}
@endsection

@section('content')

{{-- <link href="{{ Theme::asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" /> --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

{{-- <link rel="stylesheet" href="{{ asset('themes/cbms/admin/public/assets/libs/dropzone/min/dropzone.min.css') }}"> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">




<style>
    #documentUpload {
        border: 2px dashed #ccc;
        border-radius: 5px;
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
        background-color: #f9f9f9;
    }

    .custom-alert {
        border-left: 4px solid #ccc;
        background-color: #f8f8f8;
        color: #333;
        padding: 10px 15px;
        border-radius: 0.25rem;
    }

    .file-thumbnail {
        margin-bottom: 15px;
        padding: 5px;
    }

    .thumbnail-wrapper {
        border: 1px solid #ddd;
        padding: 5px;
        background-color: #fff;
    }

    .img-fluid {
        width: 100%;
        height: auto;
        display: block;
    }

    /* Hide the 'X' overlay icon in the center */
    /* .dropzone .dz-preview.dz-error .dz-error-message {
            display: none;
        } */
    about:blank#blocked

    /* Remove tooltip (title attribute) */
    .dz-filename span {
        pointer-events: none;
    }

    .dz-progress {
        background: #28a745 !important;
    }

    .dz-progress .dz-upload {
        background: #28a745 !important;
        height: 10px;
        border-radius: 5px;
    }
</style>
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-xl-8 col-lg-8">
                <div class="header-breadcumb">
                    <h6 class="header-pretitle d-none d-md-block mt-2"><a href="index.html">Dashboard</a> <a
                            href="{{ route('pages.domain.mydomains.index') }}"> / My Domains</a> <span
                            class="text-muted"> / Domain Document</span></h6>
                </div>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- Header -->
        <h3>Domain Document</h3>
        <br>
        <!-- Buttons -->
        <div class="mb-4">
            <a href="{{ route('pages.domain.mydomains.details.document', array_merge(request()->query(), ['module' => 'PrivateNsRegistrar', 'page' => 'upload'])) }}" class="btn btn-primary">Upload Document</a>
            <a href="{{ route('pages.domain.mydomains.details.requirement', array_merge(request()->query(), ['module' => 'PrivateNsRegistrar', 'page' => 'requirement'])) }}" class="btn btn-outline-primary">Domain Document</a>
        </div>

        <!-- Upload Info -->
        <div class="custom-alert">
            Here you can upload multiple file
        </div>
        <br>
        <!-- Dropzone Area -->
        <form action="{{route('pages.domain.mydomains.details.upload')}}" class="dropzone" id="documentUpload">
            @csrf
            <input class="form-control" name="clientid" value="{{ $domain_data['userid'] ?? ''}}" hidden>
            <input class="form-control" name="title" value="domain_document" hidden>
            <div class="dz-message" data-dz-message>
                <i class="fas fa-upload"></i> <!-- Add this line for the icon -->
                <span>Drop files here to upload</span>
            </div>
        </form>

        <hr class="my-5">

        <!-- Document List Header -->
        <h5>Your Files</h5>
        <div class="custom-alert">
            This all document is your document, and it can be used for all domain without reupload any document
        </div>
        <br>
        <!-- Warning -->
        <div class="alert alert-warning">
            <strong>Warning!</strong> You must set the document after uploading it. Click the file, then choose a domain, and click set.
        </div>

        <!-- File Thumbnails -->
        <br>
        <div id="fileListContainer" class="row">
            @if(empty($document))
            <div class="col-12">
                <p class="text-center font-italic">No files available</p>
            </div>
            @else
            @foreach($document as $row)
            <div class="col-2 mb-3">
                <div class="card" style="width: 100%;">
                     <!--<img src="{{ asset('storage/modules/addons/PrivateNsRegistrar/Files/' . $row['file']) }}"-->
                     <!--   class="card-img-top image-preview"-->
                     <!--   alt="Document Image"-->
                     <!--   style="object-fit: cover; width: 100%; height: 100px;"-->
                     <!--   data-toggle="modal" data-target="#imageModal" data-file="{{ asset('storage/modules/addons/PrivateNsRegistrar/Files/' . $row['file']) }}" data-name="{{ $row['file'] }}"> -->
                        
                        
                        <img src="{{ 'https://' . $_SERVER['SERVER_NAME'] . '/Files/' . $row['file'] }}"
                            class="card-img-top image-preview"
                            alt="Document Image"
                            style="object-fit: cover; width: 100%; height: 100px;"
                        data-toggle="modal" data-target="#imageModal"
                        data-file="{{ 'https://' . $_SERVER['SERVER_NAME'] . '/Files/' . $row['file'] }}" data-name="{{ $row['file'] }}">
                        
                       

                    <div class="card-body">
                        <a href="#" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteModal" data-file="{{ $row['file'] }}">Delete</a>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="deleteForm" method="POST">
                @csrf
                @method('POST')
                <input type="hidden" name="fileName" id="fileName" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Confirmation</h5>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this item?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelButton">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteButton">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <form id="setForm" method="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Set File</h5>
            </div>
            <div class="modal-body text-center">
                <img id="imagePreview" src="" alt="Full Image" class="img-fluid" style="max-height: 500px; max-width: 100%; object-fit: contain;">
                <br>
                    <br>
                    @php
                        $domainArray = explode(', ', $domains);
                    @endphp
                    @csrf
                    <div class="form-group">
                        <label for="select_domain" class="text-left d-block">Domain Name</label>
                        <select name="domain" class="form-control" id="select_domain" required>
                            <option value="">Choose Domain</option>
                            @foreach ($domainArray as $row)
                                <option value="{{ $row }}">{{ $row }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="doc_domain" class="text-left d-block">Document Type</label>
                        <select name='type' class="form-control" id="doc_domain" required disabled>
                            <option value="">Choose Type</option>
                        </select>
                    </div>
                    <br>
                    <div class="custom-alert" id="alertContainer" hidden>
                        <div id="keterangan">
                            This Document Need : 
                        </div>
                    </div>
                    <br>
                    <div class="form-check">
                        <input name="set_all" type="checkbox" class="form-check-input" id="set_all_checkbox"/> 
                        <label class="form-check-label" for="exampleCheck1">Set this document for all domain</label>
                    </div>
                    <input type="hidden" name="file" id="file_set">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelSetButton">Cancel</button>
                <button type="submit" class="btn btn-primary" id="setButton" disabled>Set</button>
            </div>
        </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
{{-- <script src="{{ Theme::asset('assets/plugins/select2/js/select2.min.js') }}"></script> --}}
{{-- <script src="{{ Theme::asset('assets/plugins/select2/js/select2.full.min.js') }}"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- BS Markdown -->
<script>
    Dropzone.options.documentUpload = {
        paramName: 'upload_file', 
        maxFilesize: 5,
        parallelUploads: 5, 
        addRemoveLinks: true,
        dictRemoveFile: 'Delete',
        acceptedFiles: ".jpg,.png,.pdf",
        init: function() {
            this.on("addedfile", function(file) {
                let removeButton = file.previewElement.querySelector('.dz-remove');
                removeButton.addEventListener('click', () => {
                    this.removeFile(file);
                });
            });

            this.on("success", function(file) {
                let removeButton = file.previewElement.querySelector('.dz-remove');
                if (removeButton) {
                    removeButton.parentNode.removeChild(removeButton); 
                }
                let successMessage = document.createElement('div');
                successMessage.innerHTML = '<i class="fas fa-check-circle" style="color: green; margin-right: 5px;"></i> Upload Success';
                successMessage.style.color = 'green';
                successMessage.style.marginTop = '10px';

                file.previewElement.appendChild(successMessage);
            });

            this.on("queuecomplete", function() {
                if (this.getQueuedFiles().length === 0 && this.getUploadingFiles().length === 0) {
                    window.location.reload();
                }
            });
        }
    };

    $('#deleteModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var file = button.data('file'); // Extract file ID
        var modal = $(this);
        modal.find('#fileName').val(file);
        modal.find('.modal-body').text('Are you sure you want to delete the file: ' + file + '?');
    });

    document.addEventListener('DOMContentLoaded', function() {
        const deleteForm = document.getElementById('deleteForm');
        const setForm = document.querySelector('#setForm');
        const deleteButton = document.getElementById('deleteButton'); 
        const cancelButton = document.getElementById('cancelButton');
        const imageElements = document.querySelectorAll('.image-preview');

        const setButton = document.getElementById('setButton'); 
        const cancelSetButton = document.getElementById('cancelSetButton');

        deleteForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const fileName = $('#fileName').val();
            const token = document.querySelector('input[name="_token"]').value;

            deleteButton.disabled = true;
            cancelButton.disabled = true;

            deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';

            try {
                const response = await fetch('/domain/mydomains/details/document/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        fileName: fileName,
                        _token: token
                    })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                $('#deleteModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Item deleted successfully!',
                    position: 'top-end',
                    toast: true, 
                    timer: 3000, 
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error,
                    position: 'top-end', 
                    toast: true, 
                    timer: 3000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            } finally {
                deleteButton.disabled = false;
                cancelButton.disabled = false;
                deleteButton.innerHTML = 'Delete';
            }
        });

        imageElements.forEach(img => {
            img.addEventListener('click', function() {
                const filePath = img.getAttribute('data-file');
                const filename = img.getAttribute('data-name');
                const modalImage = document.getElementById('imagePreview');
                $("#file_set").val(filename);

                if (modalImage) {
                    modalImage.src = filePath;
                }
            });
        });

        document.getElementById('select_domain').addEventListener('change', async function() {
            const domain = this.value;
            const token = document.querySelector('input[name="_token"]').value;
            const userid = "{{ $userid }}";

            $('#alertContainer').attr('hidden',true);
            $('#doc_domain').attr('disabled',true);
            $('#setButton').attr('disabled',true);

            try {
                const response = await fetch('/domain/mydomains/details/document/tldlookup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        domain: domain,
                        userid : userid, 
                        _token: token
                    })
                });
                
                const data = await response.json();

                const docDomainSelect = document.getElementById('doc_domain');
                docDomainSelect.innerHTML = ''; 

                const keteranganParagraph = document.getElementById('keterangan');
                keteranganParagraph.innerHTML = '';

                for (const [i, v] of Object.entries(data)) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = v;
                    docDomainSelect.appendChild(option);
                }

                const keteranganText = Object.values(data).join(', '); 
                const additionalText = "This Document Need : ";
                const fullText = additionalText + '<strong>' + keteranganText + '</strong>'; 
                const keteranganPara = document.createElement('p');

                keteranganPara.innerHTML = fullText;
                keteranganParagraph.appendChild(keteranganPara);

            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                $('#alertContainer').removeAttr('hidden',true);
                $('#doc_domain').removeAttr('disabled',true);
                $('#setButton').removeAttr('disabled',true);
            }
        });

        $('#imageModal').on('hidden.bs.modal', function () {
            document.getElementById('select_domain').value = '';
            document.getElementById('doc_domain').disabled = true;
            document.getElementById('doc_domain').innerHTML = '<option value="">Choose Type</option>';
            document.getElementById('alertContainer').hidden = true;
            document.getElementById('keterangan').innerHTML = 'This Document Need:';
            document.getElementById('setButton').disabled = true;
            document.getElementsByName('set_all')[0].checked = false;
        });

        setForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const domain = document.querySelector('select[name="domain"]').value;
            const file = document.querySelector('input[name="file"]').value;
            const type = document.querySelector('select[name="type"]').value;
            const setAll = document.querySelector('input[name="set_all"]').checked ? 1 : 0;
            const _token = document.querySelector('input[name="_token"]').value;

            setButton.disabled = true;
            cancelSetButton.disabled = true;
            setButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Set...';

            try {
                const response = await fetch('/domain/mydomains/details/document/setdocument', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _token,
                    },
                    body: JSON.stringify({
                        domain: domain,
                        file: file,
                        type: type,
                        setAll: setAll,
                    })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                $('#imageModal').modal('hide');

                Swal.fire(
                    {
                    icon: 'success',
                    title: 'Success!',
                    text: 'Success set your document!',
                    position: 'top-end',
                    toast: true, 
                    timer: 3000, 
                    showConfirmButton: false,
                    timerProgressBar: true
                    }
                );
               
            } catch (error) {
                $('#imageModal').modal('hide');

                Swal.fire(
                    {
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to set your document!',
                    position: 'top-end',
                    toast: true, 
                    timer: 3000, 
                    showConfirmButton: false,
                    timerProgressBar: true
                    }
                );   
            } finally {
                setButton.disabled = false;
                cancelSetButton.disabled = false;
                setButton.innerHTML = 'Delete';
            }
        });
    });
</script>

@endsection