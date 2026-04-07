@extends('layouts.basecbms')

@section('title')
    <title>CBMS Auto - Theme Manager</title>
@endsection

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mb-0">Theme Manager</h2>
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
                            <h5>Upload Theme</h5>
                            <form action="{{url('cbmsthememanager/upload')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="dir">Choose Type</label>
                                    <select name="type" class="form-control" id="dir" required>
                                        <option value="">Choose</option>
                                        <option value="{{\App\Helpers\ThemeManager::orderformTheme()}}">Order Form Template</option>
                                        <option value="{{config('themes-manager.composer.vendor')}}">Client Area Template</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="statusx">Default Status</label>
                                    <select name="status" class="form-control" id="statusx" required>
                                        <option value="">Choose</option>
                                        <option value="active" {{old('status') == 'active' ? 'selected':'selected'}}>Active</option>
                                        <option value="disabled" {{old('status') == 'disabled' ? 'selected':''}}>Disabled</option>
                                    </select>
                                    <small id="" class="form-text text-muted">
                                        After the file uploaded and extracted, the module will be active or disabled depends on this status
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="file">File (.zip)</label>
                                    <input name="file" type="file" class="form-control-file" id="file" required>
                                    <small id="" class="form-text text-muted">
                                        <i>Note: the file name must be the same as the folder name (theme)</i>
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
                            <h5>Add New Theme</h5>
                            <form action="{{url('cbmsthememanager/addNew')}}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label for="">Type</label>
                                    <select name="type" class="form-control" id="" required>
                                        <option value="">Choose</option>
                                        <option value="{{config('themes-manager.composer.vendor')}}" {{old('type') == config('themes-manager.composer.vendor') ? 'selected':''}}>Client Area Template</option>
                                        <option value="{{\App\Helpers\ThemeManager::orderformTheme()}}" {{old('type') == \App\Helpers\ThemeManager::orderformTheme() ? 'selected':''}}>Order Form Template</option>
                                    </select>
                                    <small id="" class="form-text text-muted">
                                        Theme vendor
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="">Parent Theme (Optional)</label>
                                    <select name="parent" class="form-control" id="">
                                        <option value="">Choose</option>
                                        @foreach ($themes as $theme)
                                            <option value="{{$theme['name']}}" {{old('parent') == $theme['name'] ? 'selected':''}}>{{$theme['vendor']}}/{{$theme['name']}} ({{$theme['version']}})</option>
                                        @endforeach
                                    </select>
                                    <small id="" class="form-text text-muted">
                                        Parent theme. If type is orderform, choose <code>{{config('themes-manager.composer.vendor')}}/theme_name</code> as parent
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input value="{{old('name')}}" type="text" class="form-control" name="name" placeholder="e.g: mynewtheme" required>
                                    <small id="" class="form-text text-muted">
                                        <i>Note: the name must be lower case, like: mynewtheme</i>
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="">Description</label>
                                    <input value="{{old('description')}}" type="text" class="form-control" name="description" placeholder="Describe your theme">
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
                            <h5>List all theme directories</h5>
                            <table class="table table-hover table-striped" id="modules">
                                <thead>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Name</th>
                                        <th>Version</th>
                                        <th>Theme Directory/Path</th>
                                        <th>Parent Theme</th>
                                        <th>Status</th>
                                        <th class="text-center">Sync</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($themes as $theme)
                                        <tr>
                                            <td>{{$theme['vendor']}}</td>
                                            <td>
                                                {{$theme['name']}}
                                                <div>
                                                    <small class="text-muted">{{$theme['description']}}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">{{$theme['version']}}</span></td>
                                            <td>{{$theme['path']}}</td>
                                            <td>
                                                @if ($theme['extends'])
                                                    <i style="border-bottom: 1px dashed #ccc;cursor: help;">{{$theme['extends']['vendor']}}/{{$theme['extends']['name']}}</i>
                                                @else
                                                    none
                                                @endif
                                            </td>
                                            <td>
                                                @if ($theme['active'])
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Disabled</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if (!in_array($theme['name'], \App\Helpers\ThemeManager::defaultThemes()))
                                                    @if ($theme['active'])
                                                        <button onclick="syncTheme('{{$theme['vendor']}}/{{$theme['name']}}', 0)" class="btn btn-danger">Disable</button>
                                                    @else
                                                        <button onclick="syncTheme('{{$theme['vendor']}}/{{$theme['name']}}', 1)" class="btn btn-success">Enable</button>
                                                    @endif
                                                @else
                                                    <i>Default</i>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if (!in_array($theme['name'], \App\Helpers\ThemeManager::defaultThemes()))
                                                    <button onclick="deleteTheme('{{$theme['vendor']}}/{{$theme['name']}}')" class="btn btn-danger btn-sm" type="button" role="button">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif

                                                <button onclick="downloadTheme('{{$theme['vendor']}}/{{$theme['name']}}')" class="btn btn-primary btn-sm" type="button" role="button">
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

@section('scripts')
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('js/notify.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#modules").DataTable();
        });
        function downloadTheme(name) {
            $.ajax({
                url: "{{url('cbmsthememanager/download')}}",
                type: 'post',
                data: {
                    _token: "{{csrf_token()}}",
                    name: name,
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
        function deleteTheme(name) {
            if (confirm("WARNING! this operation cannot be undone. theme "+name+" will be deleted permanently. Continue?")) {
                $.ajax({
                    url: "{{url('cbmsthememanager/delete')}}",
                    type: 'post',
                    data: {
                        _token: "{{csrf_token()}}",
                        name: name,
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
        function syncTheme(name, status) {
            $.ajax({
                url: "{{url('cbmsthememanager/sync')}}",
                type: 'post',
                data: {
                    _token: "{{csrf_token()}}",
                    name: name,
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
