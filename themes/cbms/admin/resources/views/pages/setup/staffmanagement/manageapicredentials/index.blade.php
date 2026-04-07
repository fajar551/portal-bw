@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Manage API Credentialss</title>
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
                                        <h4 class="mb-3">Manage API Credentials</h4>
                                    </div>
                                    <p class="p-3 bg-white rounded">API Credentials enable more effective and secure
                                        management of
                                        administrative
                                        access
                                        provided to external applications and devices.</p>
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
                                    @if ($message = Session::get('info'))
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
                                    <nav>
                                        <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="nav-credentials-tab" data-toggle="tab"
                                                    href="#nav-credentials" role="tab" aria-controls="nav-credentials"
                                                    aria-selected="true">API Credentials</a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="nav-roles-tab" data-toggle="tab" href="#nav-roles"
                                                    role="tab" aria-controls="nav-roles" aria-selected="false">API
                                                    Roles</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="tab-content" id="nav-tabContent">
                                            <div class="tab-pane fade show active" id="nav-credentials" role="tabpanel"
                                                aria-labelledby="nav-credentials-tab">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <button data-toggle="modal" data-target="#apicredentialModal" class="btn btn-outline-success px-3"><i
                                                                class="fa fa-plus mr-2" aria-hidden="true"></i>Generate New
                                                            API Credential</button>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-lg-12">
                                                        <div class="table-responsive">
                                                            <table id="datatable"
                                                                class="table table-bordered dt-responsive w-100">
                                                                <thead>
                                                                    <tr>
                                                                        {{-- <th>Description</th> --}}
                                                                        <th>Admin User</th>
                                                                        <th>Roles</th>
                                                                        {{-- <th>Last Access</th> --}}
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($adminroles as $admin)
                                                                        <tr>
                                                                            <td>{{$admin->full_name}} ({{$admin->username}})</td>
                                                                            <td>{{$admin->roles->where('guard_name', 'api')->pluck('name')->join(', ')}}</td>
                                                                            {{-- <td></td> --}}
                                                                            <td>
                                                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                                                    <button id="" data-id="{{$admin->id}}" type="button" class="btn btn-sm btn-outline-primary editCredentialButton">
                                                                                        <i class="fas fa-pencil-alt"></i>
                                                                                    </button>
                                                                                    <button
                                                                                        type="button"
                                                                                        class="btn btn-sm btn-outline-primary"
                                                                                        onclick="removeCredential({{$admin->id}})"
                                                                                    >
                                                                                        <i class="fas fa-trash-alt"></i>
                                                                                        <form id="delete-credential-{{$admin->id}}" action="{{route('admin.pages.setup.staffmanagement.manageapicredentials.remove')}}" method="post">
                                                                                            @csrf
                                                                                            <input type="hidden" name="id" value="{{$admin->id}}">
                                                                                        </form>
                                                                                    </button>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="nav-roles" role="tabpanel"
                                                aria-labelledby="nav-roles-tab">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <button data-toggle="modal" data-target="#roleModal" class="btn btn-outline-success px-3">
                                                            <i class="fa fa-plus mr-2" aria-hidden="true"></i>
                                                            Create API Role</button>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-lg-12">
                                                        <div class="table-responsive">
                                                            <table id="apiroles"
                                                                class="table table-hover dt-responsive w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Role Name</th>
                                                                        <th>Description</th>
                                                                        <th>Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($apiroles as $role)
                                                                        <tr>
                                                                            <td class="detail-controls"><i class="fas fa-caret-right"></i> {{$role->name}}</td>
                                                                            <td class="detail-controls">{!!$role->description!!}</td>
                                                                            <td>
                                                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                                                    <button id="" data-id="{{$role->id}}" type="button" class="btn btn-sm btn-outline-primary editRoleButton">
                                                                                        <i class="fas fa-pencil-alt"></i>
                                                                                    </button>
                                                                                    <button
                                                                                        type="button"
                                                                                        class="btn btn-sm btn-outline-primary"
                                                                                        onclick="deleteRole({{$role->id}})"
                                                                                    >
                                                                                        <i class="fas fa-trash-alt"></i>
                                                                                        <form id="delete-role-{{$role->id}}" action="{{route('admin.pages.setup.staffmanagement.manageapicredentials.delete.role')}}" method="post">
                                                                                            @csrf
                                                                                            <input type="hidden" name="id" value="{{$role->id}}">
                                                                                        </form>
                                                                                    </button>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="detail bg-success text-white" style="display: none;">
                                                                            <td colspan="3">
                                                                                <div>
                                                                                    <u><strong>Allowed API</strong></u>
                                                                                </div>
                                                                                <div class="row">
                                                                                    @foreach ($role->permissions->sortBy('name')->values()->all() as $permission)
                                                                                        <div class="col-md-3">
                                                                                            {{$permission->name}}
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
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
                            </div>
                        </div>
                    </div>
                    <!-- End MAIN CARD -->
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <form action="{{route('admin.pages.setup.staffmanagement.manageapicredentials.create.role')}}" method="post">
            @csrf
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title text-white" id="roleModalLabel">Role Management</h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body pb-0">
                    <input type="hidden" name="id" value="">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Role Name</label>
                        <div class="col-sm-10">
                          <input name="name" type="text" class="form-control" placeholder="Role Name" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Description</label>
                        <div class="col-sm-10">
                          <textarea name="description" class="form-control" placeholder="Brief description for the role (Optional)"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 bg-light border-bottom p-2">
                            <h5 class="font-weight-bold mb-0">Allowed API Actions</h5>
                        </div>
                    </div>
                    <div class="row allowedapis" style="max-height: 300px;overflow: scroll">
                        <div class="col-md-12 mt-2">
                            <div class="form-check form-check-inline">
                                <input style="cursor: pointer" class="form-check-input" type="checkbox" id="checkAll" value="option1">
                                <label style="cursor: pointer" class="form-check-label text-primary" for="checkAll">Checked/Unchecked All</label>
                            </div>
                        </div>
                        @foreach ($permissions as $permission)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input style="cursor: pointer" name="permissions[]" class="form-check-input" type="checkbox" value="{{$permission->name}}" id="permission{{$permission->id}}">
                                    <label style="cursor: pointer" class="form-check-label" for="permission{{$permission->id}}">
                                        {{$permission->name}}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
          </form>
        </div>
    </div>
    <div class="modal fade" id="apicredentialModal" tabindex="-1" role="dialog" aria-labelledby="apicredentialModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <form action="{{route('admin.pages.setup.staffmanagement.manageapicredentials.generate')}}" method="post">
            @csrf
            <div class="modal-content border-0">
                <div class="modal-header bg-primary">
                  <h5 class="modal-title text-white" id="apicredentialModalLabel">Generate New API Credential</h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Admin User</label>
                        <select name="adminid" class="form-control" id="adminselect" required>
                          @foreach ($admins as $admin)
                              <option value="{{$admin->id}}">{{$admin->full_name}}</option>
                          @endforeach
                        </select>
                    </div>
                    {{-- <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" id="" placeholder="Description">
                    </div> --}}
                    <div class="form-group">
                        <label for="">API Role(s)</label>
                        <select name="roles[]" multiple class="form-control" id="rolesselect" required>
                          @foreach ($apiroles as $role)
                              <option value="{{$role->name}}">{{$role->name}}</option>
                          @endforeach
                        </select>
                        <small id="" class="form-text text-muted">Select the API Role(s) this credential set is assigned to. You may select more than one using Ctrl + Click.</small>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </div>
          </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .detail-controls {
            cursor: pointer;
        }
    </style>
@endpush

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
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".detail-controls").click(function() {
                $(this).closest('tr').nextUntil("tr:has(.detail-controls)").toggle("fast", function() {});
            });

            $("#checkAll").change(function () {
                $("input:checkbox").prop('checked', $(this).prop("checked"));
            });
            $('#roleModal').on('hidden.bs.modal', function (e) {
                var modal = $(this)
                modal.find('.modal-body input[name=id]').val('')
                modal.find('.modal-body input[name=name]').val('')
                modal.find('.modal-body textarea[name=description]').val('')
                modal.find('form').attr('action', "{{route('admin.pages.setup.staffmanagement.manageapicredentials.create.role')}}")
                $(".allowedapis input").each(function(index) {
                    $(this).prop('checked', false);
                });
            });
            $('.editRoleButton').on('click', function (event) {
                var id = $(this).data('id')
                var modal = $("#roleModal")
                modal.modal('show');
                $.ajax({
                    url: route('admin.pages.setup.staffmanagement.manageapicredentials.get.role'),
                    type: 'POST',
                    data: {_token: '{{csrf_token()}}', id: id},
                    success: function(res) {
                        console.log(res);
                        modal.find('.modal-body input[name=id]').val(res.id)
                        modal.find('.modal-body input[name=name]').val(res.name)
                        modal.find('.modal-body textarea[name=description]').val(res.description)
                        modal.find('form').attr('action', "{{route('admin.pages.setup.staffmanagement.manageapicredentials.edit.role')}}")
                        $(".allowedapis input").each(function(index) {
                            var val = $(this).val();
                            if (res.permissions.includes(val)) {
                                $(this).prop('checked', true);
                            } else {
                                $(this).prop('checked', false);
                            }
                        });
                    },
                    error: function() {
                        $("#roleModal").modal("hide");
                    },
                });
            });
            $('#apicredentialModal').on('hidden.bs.modal', function (e) {
                var modal = $(this)
                $("#adminselect").val($("#adminselect option:first").val()).change();
                $("#rolesselect").prop('selectedIndex', -1);
            });
            $(".editCredentialButton").on("click", function() {
                var id = $(this).data('id')
                var modal = $("#apicredentialModal")
                modal.modal('show');
                $.ajax({
                    url: route('admin.pages.setup.staffmanagement.manageapicredentials.get'),
                    type: 'POST',
                    data: {_token: '{{csrf_token()}}', id: id},
                    success: function(res) {
                        console.log(res);
                        $("#adminselect").val(id).change();
                        $.each(res.roles, function(index, value) {
                            $("#rolesselect option[value='" + value + "']").prop("selected", true);
                        });
                    },
                    error: function() {
                        $("#apicredentialModal").modal("hide");
                    },
                });
            });
        });

        function deleteRole(id) {
            if (confirm('Are you sure?')) {
                $("form#delete-role-"+id).submit();
            }
            return false;
        }
        function removeCredential(id) {
            if (confirm('Are you sure?')) {
                $("form#delete-credential-"+id).submit();
            }
            return false;
        }
    </script>
@endsection
