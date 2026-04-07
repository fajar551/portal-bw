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
                    <h2 class="mb-0">Module Manager</h2>
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
                            <h5>Upload Module</h5>
                            <form action="{{route('cbmsmodulemanager.save')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="dir">Choose Type</label>
                                    <select name="type" class="form-control" id="dir" required>
                                        <option value="">Choose</option>
                                        @foreach ($moduledirectories as $dir => $subdir)
                                            <option value="{{$dir}}" {{old('type') == $dir ? 'selected':''}}>{{$dir}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="statusx">Default Status</label>
                                    <select name="status" class="form-control" id="statusx" required>
                                        <option value="">Choose</option>
                                        <option value="active" {{old('status') == 'active' ? 'selected':''}}>Active</option>
                                        <option value="disabled" {{old('status') == 'disabled' ? 'selected':'selected'}}>Disabled</option>
                                    </select>
                                    <small id="" class="form-text text-muted">
                                        After the file uploaded and extracted, the module will be active or disabled depends on this status
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="file">File (.zip)</label>
                                    <input name="file" type="file" class="form-control-file" id="file" required>
                                    <small id="" class="form-text text-muted">
                                        <i>Note: the file name must be the same as the folder name (module)</i>
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
                            <h5>Add New Module</h5>
                            <form action="{{route('cbmsmodulemanager.addNew')}}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label for="">Type</label>
                                    <select name="command" class="form-control" id="" required>
                                        <option value="">Choose</option>
                                        <option value="addons:make">Addons Module</option>
                                        <option value="gateways:make">Payment Gateway</option>
                                        <option value="servers:make">Server/Provisioning</option>
                                        <option value="registrar:make">Registrar</option>
                                        <option value="security:make">Security</option>
                                        <option value="widgets:make">Admin Dashboard Widget</option>
                                    </select>
                                    <small id="" class="form-text text-muted">
                                        Module command
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="e.g: Cbmsnewmodule" required>
                                    <small id="" class="form-text text-muted">
                                        <i>Note: the name must be camel case, like: MyNewModule or Mynewmodule</i>
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
                            <h5>List all module directories</h5>
                            <table class="table table-responsive table-hover table-striped" id="modules">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Module Directory/Name</th>
                                        <th>Base Path</th>
                                        <th>Status</th>
                                        <th class="text-center">Sync Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($moduledirectories as $dir => $subdirs)
                                        @foreach ($subdirs as $subdir)
                                            @php
                                                $module = Module::find($subdir);
                                            @endphp
                                            @if ($module)
                                                @if (!in_array($module->getName(), [config('cbmsmodulemanager.name'), config('cbmsthememanager.name')]))
                                                    <tr>
                                                        <td>{{$dir}}</td>
                                                        <td>{{$subdir}}</td>
                                                        <td>{{base_path("Modules/$dir")}}</td>
                                                        <td>
                                                            @if ($module->isEnabled())
                                                                <span class="badge badge-success">Active</span>
                                                            @else
                                                                <span class="badge badge-secondary">Disabled</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($module->isEnabled())
                                                                <button onclick="syncstatus('{{$module->getName()}}', 0)" class="btn btn-danger">Disable</button>
                                                            @else
                                                                <button onclick="syncstatus('{{$module->getName()}}', 1)" class="btn btn-success">Enable</button>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <button onclick="removeModule('{{$module->getName()}}')" class="btn btn-danger btn-sm" type="button" role="button">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            <button onclick="downloadModule('{{$module->getName()}}', '{{$dir}}')" class="btn btn-primary btn-sm" type="button" role="button">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif
                                        @endforeach
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

@section('scripts')
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('js/notify.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#modules").DataTable();
        });

        function downloadModule(module, path) {
            $.ajax({
                    url: route('cbmsmodulemanager.downloadModule'),
                    type: 'post',
                    data: {
                        _token: "{{csrf_token()}}",
                        module: module,
                        path: path,
                    },
                    success: function(res) {
                        console.log(res);
                        if (res.result == 'success') {
                            $.notify(res.message, "success");
                            setTimeout(() => {
                                location.href = res.download_url;
                            }, 1000);
                        } else {
                            $.notify(res.message, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        var e = JSON.parse(xhr.responseText);
                        $.notify(e.message, "error");
                    },
                });
        }

        function removeModule(module) {
            if (confirm("WARNING! this operation cannot be undone. module "+module+" will be deleted permanently. Continue?")) {
                $.ajax({
                    url: route('cbmsmodulemanager.removeModule'),
                    type: 'post',
                    data: {
                        _token: "{{csrf_token()}}",
                        module: module,
                    },
                    success: function(res) {
                        console.log(res);
                        if (res.result == 'success') {
                            $.notify(res.message, "success");
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            $.notify(res.message, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        var e = JSON.parse(xhr.responseText);
                        $.notify(e.message, "error");
                    },
                });
            }
            return false;
        }

        function syncstatus(module, status) {
            $.ajax({
                url: route('cbmsmodulemanager.syncStatus'),
                // url: route('admin.pages.setup.payments.paymentgateways.activate'),
                
                type: 'post',
                data: {
                    _token: "{{csrf_token()}}",
                    module: module,
                    status: status,
                },
                success: function(res) {
                    console.log(res);
                    if (res.result == 'success') {
                        $.notify(res.message, "success");
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        $.notify(res.message, "error");
                    }
                },
                error: function(xhr, status, error) {
                    var e = JSON.parse(xhr.responseText);
                    $.notify(e.message, "error");
                },
            });
        }
    </script>
@endsection
