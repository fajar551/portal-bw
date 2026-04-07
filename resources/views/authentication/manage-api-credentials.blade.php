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
                                        <h4 class="mb-3">Manage API Credentials   </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <p>
                                        {{Lang::get("admin.apicredsintroduction")}}
                                    </p>
                                    <div role="tabpanel">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="nav-item">
                                                    <a class="nav-link active" href="#tabManageCredentials" id="btnManageCredentials" aria-controls="tabManageCredentials" role="tab" data-toggle="tab">
                                                        <i class="fas fa-sign-in-alt"></i>
                                                        {{Lang::get("admin.apicredstitle")}}
                                                    </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                    <a class="nav-link" href="#tabManageRoles" id="btnManageRoles" aria-controls="tabManageRoles" role="tab" data-toggle="tab">
                                                        <i class="fas fa-cubes"></i>
                                                        {{Lang::get("admin.apiroletitle")}}
                                                    </a>
                                            </li>
                                        </ul>
                                        <br />
                                        <div class="tab-content">
                                                <div role="tabpanel" class="tab-pane fade in show active" id="tabManageCredentials">
                                                    @include('authentication.partials.section-api-credentials')
                                                </div>
                                                <div role="tabpanel" class="tab-pane fade" id="tabManageRoles">
                                                    @include('authentication.partials.section-api-roles')
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
    @include('includes.modal-ajax')
    {!!$modalRole!!}
@endsection

@section('styles')
    <style>
        .details-control {
            cursor: pointer;
        }
    </style>
@endsection
@section('scripts')
<script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ Theme::asset('assets/libs/jquery-editable/jquery.editable.min.js') }}"></script>
<script src="{{ Theme::asset('js/notify.min.js') }}"></script>
<script src="{{ Theme::asset('js/AjaxModal.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.2.0/clipboard.min.js" integrity="sha512-3KvXY2mMAbsB7VLiEKFaMc9K//kclUkopGR8WIVYc0+UHc4cSeaanb1yGsMvVwco7PHsYxluH1xcronseXLyaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    showError = function(xhr) {
            jQuery.growl.error({ title: 'Error', message: xhr.responseJSON ?  xhr.responseJSON.data : 'Internal Error' });
    };

    window.tblDevice = $("#tblDevice").DataTable({"searching": true})
    jQuery(document).ready(function() {
        jQuery('#btnNewAPICredentials').click(function (e) {
                // ensure not previous content
            jQuery('#inputDescription').val('');

            // ensure generate button can be seen and clicked
            jQuery('#NewAPICredentials-Generate').show().removeClass('disabled d-none');
        });

        // When dataTable object receives content, (re)define button group binds
        // that are expressed in the (new) table data
        tblDevice.on('draw.dt', function() {
            jQuery('.inline-editable').editable({
                mode: 'inline',
                params: function(params) {
                    params.action = 'savefield';
                    return params;
                },
                error: showError
            });
        });
    });
</script>

<script>
    window.table = $('#tblApiRoles').DataTable();
    $(document).ready(function () {


        // Add event listener for opening and closing details
        $('#tblApiRoles tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var tdi = tr.find("i.fas");
            var row = table.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                tdi.first().removeClass('fa-caret-down');
                tdi.first().addClass('fa-caret-right');
            }
            else {
                // Open this row
                row.child(formatAllowedActions(row.data())).show();

                if (!row.child().hasClass('allowed-permissions')) {
                    row.child().addClass('allowed-permissions');
                }
                tr.addClass('shown');
                tdi.first().removeClass('fa-caret-right');
                tdi.first().addClass('fa-caret-down');
            }
        });

        table.on("user-select", function (e, dt, type, cell, originalEvent) {
                if ($(cell.node()).hasClass("details-control")) {
                    e.preventDefault();
            }
        });
    });

    function formatAllowedActions(d) {
            var actions = '';
        for (var i=0; i<d.allowedActions.length; i++) {
                actions = actions +
                '<div class="col-sm-3">' +
                    d.allowedActions[i] +
                '</div>'
        }

        return '<div class="container-fluid">' +
                    '<div class="title">{{Lang::get("admin.apiroleallowedApiActions")}}</div>' +
                    '<div class="row row-detail">' +
                            actions +
                        '</div>' +
                '</div>';
    }
    function deleteApiRole(e) {
        var url = $(e).data('target-url');
        var title = $(e).data('title');
        var content = $(e).data('content');
        if (confirm(title+"\n"+content)) {
            $.ajax({
                url: url,
                type: 'post',
                data: {
                    _token: "{{csrf_token()}}",
                },
                success: function(res) {
                    // console.log(res);
                    if (res.status == 'error') {
                        alert(res.errorMessage);
                    } else {
                        table.ajax.reload();
                    }
                }
            });
        }
        return false;
    }
    function deleteDeveice(e) {
        var url = $(e).data('target-url');
        var title = $(e).data('title');
        var content = $(e).data('content');
        if (confirm(title+"\n"+content)) {
            $.ajax({
                url: url,
                type: 'post',
                data: {
                    _token: "{{csrf_token()}}",
                },
                success: function(res) {
                    // console.log(res);
                    if (res.status == 'error') {
                        alert(res.errorMessage);
                    } else {
                        tblDevice.ajax.reload();
                    }
                }
            });
        }
        return false;
    }
</script>
@endsection
