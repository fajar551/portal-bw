@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Notifications</title>
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
                                        <h4 class="mb-3">Notifications</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="content-card">
                                                    <div class="card p-3">
                                                        <div class="noti-app-image">
                                                            <img src="https://proto.qwords.com/modules/notifications/Email/logo.png"
                                                                alt="email.png">
                                                        </div>
                                                        <div class="py-2">
                                                            <button class="btn btn-light px-3" data-toggle="modal"
                                                                data-target="#emailModal">Configure</button>
                                                        </div>
                                                        <small class="badge badge-secondary float-right">INACTIVE</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="content-card">
                                                    <div class="card p-3">
                                                        <div class="noti-app-image">
                                                            <img src="https://proto.qwords.com/modules/notifications/Hipchat/logo.png"
                                                                alt="email.png">
                                                        </div>
                                                        <div class="py-2">
                                                            <button class="btn btn-light px-3" data-toggle="modal"
                                                                data-target="#hipChatModal">Configure</button>
                                                        </div>
                                                        <small class="badge badge-secondary float-right">INACTIVE</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="content-card">
                                                    <div class="card p-3">
                                                        <div class="noti-app-image">
                                                            <img src="https://proto.qwords.com/modules/notifications/Slack/logo.png"
                                                                alt="email.png">
                                                        </div>
                                                        <div class="py-2">
                                                            <button class="btn btn-light px-3" data-toggle="modal"
                                                                data-target="#slackModal">Configure</button>
                                                        </div>
                                                        <small class="badge badge-secondary float-right">INACTIVE</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Modal Section --}}

                            <!-- Modal Email-->
                            <div class="modal fade" id="emailModal" tabindex="-1" role="dialog"
                                aria-labelledby="modelTitleId" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header"
                                            style="background-color: #252b3b; border: 1px solid #FFFFFF">
                                            <h5 class="modal-title text-white">Configure Email</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="col-form-label">Sender Name</label>
                                                <input type="text" class="form-control">
                                                <small>The name the notification should come from.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Sender Email</label>
                                                <input type="text" class="form-control" placeholder="changeme@changeme.com">
                                                <small>The email address the notification should come from.</small>
                                            </div>
                                        </div>

                                        <div class="d-flex p-3">
                                            <button class="btn btn-danger mr-auto">
                                                Disable
                                            </button>
                                            <button type="button" class="btn btn-light mx-1 px-3"
                                                data-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary mx-1 px-3">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="hipChatModal" tabindex="-1" role="dialog"
                                aria-labelledby="modelTitleId" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header"
                                            style="background-color: #252b3b; border: 1px solid #FFFFFF">
                                            <h5 class="modal-title text-white">Configure HipChat</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="col-form-label">API Token</label>
                                                <input type="text" class="form-control">
                                                <small>Requires a HipChat API V2 User Token. To create one, navigate to
                                                    Account Settings > API Access. The token requires the View Room and Send
                                                    Notification scopes.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">API URL</label>
                                                <input type="text" class="form-control"
                                                    placeholder="https://api.hipchat.com/v2/">
                                                <small>If using HipChat Server Edition, enter the URL to your self-hosted
                                                    Hipchat Instance.</small>
                                            </div>
                                        </div>

                                        <div class="d-flex p-3">
                                            <button class="btn btn-danger mr-auto">
                                                Disable
                                            </button>
                                            <button type="button" class="btn btn-light mx-1 px-3"
                                                data-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary mx-1 px-3">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="slackModal" tabindex="-1" role="dialog"
                                aria-labelledby="modelTitleId" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header"
                                            style="background-color: #252b3b; border: 1px solid #FFFFFF">
                                            <h5 class="modal-title text-white">Configure Slack</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="col-form-label">OAuth Access Token</label>
                                                <input type="text" class="form-control">
                                                <small>An OAuth token for the Custom App you have installed in your Slack
                                                    workspace. Your App needs the "channels:read", "channels:join" and
                                                    "chat:write" scopes. If you wish to notify private channels, the scope
                                                    "groups:read" is also required.</small>
                                            </div>
                                        </div>
                                        <div class="d-flex p-3">
                                            <button class="btn btn-danger mr-auto">
                                                Disable
                                            </button>
                                            <button type="button" class="btn btn-light mx-1 px-3"
                                                data-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary mx-1 px-3">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- End Modal Email --}}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row mb-4">
                                            <div class="col-lg-6 col-sm-12">
                                                <h4 class="mt-2">Notifications Rules</h4>
                                            </div>
                                            <div class="col-lg-6 col-sm-12">
                                                <button
                                                    class="btn btn-outline-success px-2 d-flex align-items-center float-lg-right"><i
                                                        class="fa fa-plus mr-2" aria-hidden="true"></i>Create New
                                                    Notifications Rule</button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table id="datatable" class="table table-bordered dt-responsive w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>Description</th>
                                                                <th>Events</th>
                                                                <th>Conditions</th>
                                                                <th>Notification Method</th>
                                                                <th>Last Modified</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
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
@endsection
