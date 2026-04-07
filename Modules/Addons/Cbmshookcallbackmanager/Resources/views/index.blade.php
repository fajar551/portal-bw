@extends('layouts.basecbms')

@section('title')
    <title>CBMS Auto - Module Manager</title>
@endsection

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mb-0">Hooks & Callback Manager</h2>
                    <small class="text-muted">By CBMS</small>
                </div>
                <div class="col-md-12">
                    @if (Session::get('alert-message'))
                        <div class="alert alert-{{Session::get('alert-type')}}" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {!!nl2br(Session::get('alert-message'))!!}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <b>Error:</b>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="col-md-6 mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Upload Script</h5>
                            <form action="{{url('cbmshookcallbackmanager/upload')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="dir">Choose Type</label>
                                    <select name="type" class="form-control" id="dir" required>
                                        <option value="">Choose</option>
                                        <option value="hook">Hook</option>
                                        <option value="callback">Gateway Callback</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="file">File (.zip)</label>
                                    <input name="file" type="file" class="form-control-file" id="file" required>
                                    <small id="" class="form-text text-muted">
                                        {{-- <i>Note: the file name must be the same as the folder name (module)</i> --}}
                                    </small>
                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">Upload & Extract</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Add New Script</h5>
                            <form action="{{url('cbmshookcallbackmanager/addNew')}}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label for="">Type</label>
                                    <select name="command" class="form-control" id="" required>
                                        <option value="">Choose</option>
                                        <option value="hook:make">Hook</option>
                                        <option value="callback:make">Gateway Callback</option>
                                    </select>
                                    <small id="" class="form-text text-muted">
                                        Module command
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="">Hooks Event (Optional for type hook)</label>
                                    <select name="event" class="form-control select2" id="">
                                        <option value="">Choose</option>
                                        @foreach ($events as $event)
                                            <option value="{{$event}}">{{$event}}</option>
                                        @endforeach
                                    </select>
                                    <small id="" class="form-text text-muted">
                                        Just for hook type
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="e.g: Cbmsnewscript" required>
                                    <small id="" class="form-text text-muted">
                                        <i>Note: the name must be camel case, like: MyNewScript or Mynewscript</i>
                                    </small>
                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>List all scripts</h5>
                            <table class="table table-hover table-striped" id="modules">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Script Name</th>
                                        <th>Script File</th>
                                        <th>Url (Callback)</th>
                                        <th>Base Path</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($scripts as $script)
                                        <tr>
                                            <td>{{$script['type']}}</td>
                                            <td>{{$script['name']}}</td>
                                            <td>{{$script['file']}}</td>
                                            <td class="text-muted">
                                                @if ($script['type'] == 'Callback')
                                                    {{route('modules.gateways.callback', [$script['name'], 'your_method'])}}
                                                @else
                                                    <i>None</i>
                                                @endif
                                            </td>
                                            <td>{{$script['path']}}</td>
                                            <td class="text-center">
                                                <button onclick="removeFile('{{$script['full_path']}}')" class="btn btn-danger btn-sm" type="button" role="button">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button onclick="downloadFile('{{$script['full_path']}}')" class="btn btn-primary btn-sm" type="button" role="button">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" integrity="sha256-nbyata2PJRjImhByQzik2ot6gSHSU4Cqdz5bNYL2zcU=" crossorigin="anonymous"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <style>
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            top: -1%!important;
        }
    </style>
@endpush
@section('scripts')
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('js/notify.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#modules").DataTable({
                responsive: true,
            });
            $('.select2').select2({
                theme: 'bootstrap4',
            });
        });
        function downloadFile(route) {
            location.href = "{{url('cbmshookcallbackmanager/download')}}?file="+route;
        }
        function removeFile(route) {
            if (confirm("WARNING! this operation cannot be undone. script "+route+" will be deleted permanently. Continue?")) {
                location.href = "{{url('cbmshookcallbackmanager/remove')}}?file="+route;
            }
            return false;
        }
    </script>
@endsection
