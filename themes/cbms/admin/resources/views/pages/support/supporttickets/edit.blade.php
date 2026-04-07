@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Support Tickets</title>
@endsection

@section('content')
    <style>
        .private-note {
            background-color: #ffebee;
        }

        .staff-note {
            background-color: #fffff0;
        }

        /* Tambahan agar paragraf pada pesan ticket tidak berdempetan */
        .msgwrap p {
            margin-bottom: 1em !important;
            margin-top: 0 !important;
        }
        /* Agar teks di dalam msgwrap tidak scroll horizontal dan font normal */
        .msgwrap, .tab-pane .msgwrap {
            white-space: normal !important;
            overflow-x: auto;
            word-break: break-word;
            font-family: inherit !important;
            line-height: 1.7;
        }
        .msgwrap br {
            display: block;
            margin-bottom: 1em !important;
            content: "";
        }
        /* Atasi jika ada tag <pre> atau <code> di dalam pesan */
        .msgwrap pre, .msgwrap code {
            white-space: pre-wrap !important;
            word-break: break-word !important;
            font-family: inherit !important;
            background: none !important;
            color: inherit !important;
            font-size: inherit !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
        }
        .msgwrap p:empty,
        .msgwrap p:only-child:empty,
        .msgwrap p:only-child br:only-child,
        .msgwrap p > br:only-child {
            display: block;
            min-height: 1.5em;
            margin-bottom: 1em !important;
            content: "";
        }
    </style>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="view-client-wrapper">
                    <div class="card-title mb-3">
                        <h4 class="mb-3">Support Tickets</h4>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-8">
                                    <ul class="list-inline">
                                        <li class="list-inline-item">#{{ $tiket->tid }} - {{ $tiket->title }}</li>
                                        <li class="list-inline-item">
                                            <select name="status" id="priority"
                                                class="form-control select2-search-disable" style="width: 100%;">
                                                @foreach ($status as $r)
                                                    <option value="{{ $r->title }}" style="color:{{ $r->color }}"
                                                        {{ $r->title == $tiket->status ? 'selected' : '' }}>
                                                        {{ $r->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </li>
                                        <li class="list-inline-item"><a href="#">Close</a></li>
                                    </ul>
                                </div>
                                <div class="col-sm-4">
                                    <div class="float-right bg-dark text-light p-2 rounded">
                                        {{ \App\Helpers\HelperTickets::getLastReplyTime($tiket->lastreply) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                            <h5>Successfully Updated!</h5>
                            <small>{{ $message }}</small>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if ($message = Session::get('info'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                            <h5>Successfully Updated!</h5>
                            <small>{{ $message }}</small>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="danger-alert">
                            <h5>Error:</h5>
                            <small>{{ $message }}</small>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!--
                                        <div class="alert alert-primary mb-4" role="alert" >
                                            Panggilan yang Dipilih Client<br>English: <br>Indonesia:
                                        </div>

                                        <div class="alert alert-primary mb-4" role="alert" >
                                            Data Tambahan Pelanggan<br>Kelamin: <br>Umur: <br>Pekerjaan: <br>Pemrograman yg dikuasai: <br>Mengerti Jaringan:
                                        </div>
                                        -->
                    <div class="tiketcontent">
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <a class="nav-link nav-item active" id="nav-addreplay-tab" data-toggle="tab"
                                    href="#nav-addreplay" role="tab" aria-controls="nav-addreplay"
                                    aria-selected="true">Add Reply</a>
                                <a class="nav-link nav-item" id="nav-addnote-tab" data-toggle="tab" href="#nav-addnote"
                                    role="tab" aria-controls="nav-addnote" aria-selected="false">Add Note</a>
                                <a class="nav-link nav-item" id="nav-custom-fields-tab" data-toggle="tab"
                                    href="#nav-custom-fields" role="tab" aria-controls="nav-custom-fields"
                                    aria-selected="false">Custom Fields</a>
                                <a class="nav-link nav-item" id="nav-other-tickets-tab" data-toggle="tab"
                                    href="#nav-other-tickets" role="tab" aria-controls="nav-other-tickets"
                                    aria-selected="false">Other Tickets</a>
                                <a class="nav-link nav-item" id="nav-clientlog-tab" data-toggle="tab" href="#nav-clientlog"
                                    role="tab" aria-controls="nav-clientlog" aria-selected="false">Client Log</a>
                                <a class="nav-link nav-item" id="nav-options-tab" data-toggle="tab" href="#nav-options"
                                    role="tab" aria-controls="nav-options" aria-selected="false">Options</a>
                                <a class="nav-link nav-item" id="nav-log-tab" data-toggle="tab" href="#nav-log"
                                    role="tab" aria-controls="nav-log" aria-selected="false">Log</a>
                            </div>
                        </nav>

                        <div class="card">
                            <div class="card-body">
                                <div class="tab-content" id="nav-tabContent">
                                    <!-- replay ticket -->
                                    <div class="tab-pane fade show active" id="nav-addreplay" role="tabpanel"
                                        aria-labelledby="nav-addreplay-tab">
                                        <div>
                                            <form
                                                action="{{ url(Request::segment(1) . '/support/supporttickets/update') }}"
                                                method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                @method('PUT')
                                                <div>
                                                    <div class="form-group">
                                                        <textarea class="summernote2 form-control" name="message" id="replymessage" rows="14"></textarea>


                                                    </div>

                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <select name="deptid"
                                                                class="form-control select2-search-disable"
                                                                style="width: 100%;">
                                                                <option value="nochange" selected="selected">- Set
                                                                    Department -</option>
                                                                @foreach ($dep as $r)
                                                                    <option value="{{ $r->id }}">
                                                                        {{ $r->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <select name="flagto" id="flagto"
                                                                class="form-control select2-search-disable"
                                                                style="width: 100%;">
                                                                <option value="nochange" selected="selected">- Set
                                                                    Assigment -</option>
                                                                @foreach ($admin as $r)
                                                                    <option value="{{ $r->id }}">
                                                                        {{ $r->firstname . ' ' . $r->lastname }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <select name="priority" id="priority"
                                                                class="form-control select2-search-disable"
                                                                style="width: 100%;">
                                                                <option value="nochange" selected="selected">- Set
                                                                    Priority -</option>
                                                                <option value="High">High</option>
                                                                <option value="Medium">Medium</option>
                                                                <option value="Low">Low</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <select name="status" id="statusaaa" class="form-control"
                                                                style="width: 100%;">
                                                                <option value="nochange" selected="selected">- Set Status
                                                                    -</option>
                                                                @foreach ($status as $r)
                                                                    <option value="{{ $r->id }}">
                                                                        {{ $r->title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="row">
                                                            <div class="col-sm-3 col-md-6 my-2">
                                                                <div class="float-left">
                                                                    <button type="button" id="bttattachfile"
                                                                        class="btn btn-info m-2">Attach
                                                                        Files</button>
                                                                </div>
                                                                <div class="float-left">
                                                                    <button type="button"
                                                                        class="ml-md-2 btn btn-info m-2" id="insertpredef"
                                                                        onclick="loadpredef('0'); return false"><i
                                                                            class="fas fa-pencil-alt mr-2"></i>Insert
                                                                        Predefined
                                                                        Replies</button>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-9 col-md-6 mt-2">
                                                                <div class="float-right">
                                                                    <div class="d-flex mt-2">
                                                                        <div class="return-to-ticket-list mt-2 mr-2">
                                                                            <label class="checkbox">
                                                                                <input type="checkbox" name="returntolist"
                                                                                    value="1" checked="">
                                                                                Return to Ticket List
                                                                            </label>
                                                                        </div>
                                                                        <input type="hidden" name="action"
                                                                            value="postreply">
                                                                        <input type="hidden" name="id"
                                                                            value="{{ $tiket->id }}">
                                                                        <input type="hidden" name="did"
                                                                            value="{{ $tiket->did }}">
                                                                        <button type="submit"
                                                                            class="btn btn-success pull-right px-5"
                                                                            name="postreply" id="btnAddNote">
                                                                            <i class="fas fa-reply mr-2"></i>
                                                                            Reply
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="booxupload" class="formattachfile pt-3 mt-5 "
                                                            style="display: none">
                                                            <div class="form-group row">
                                                                <label for="attachment"
                                                                    class="col-sm-2 col-form-label">Attachments</label>
                                                                <div class="col-sm-12 col-lg-8">
                                                                    <div class="input-group mb-3">
                                                                        <div class="custom-file">
                                                                            <input type="file"
                                                                                class="custom-file-input"
                                                                                name="attachments[]" id="inputGroupFile01"
                                                                                aria-describedby="inputGroupFileAddon01">
                                                                            <label class="custom-file-label"
                                                                                for="inputGroupFile01">Choose
                                                                                file</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-12 col-lg-2">
                                                                    <button id="add-more"
                                                                        class="btn btn-primary btn-block">
                                                                        Add More
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div id="addform">


                                                            </div>
                                                        </div>
                                                        <div id="prerepliescontainer" class="bg-light"
                                                            style="display: none;">
                                                            {{-- <div class="col-sm-4 col-md-2 mb-1"> --}}
                                                            {{-- </div> --}}
                                                            <div class="p-1">
                                                                <input type="text" id="predefq" value=""
                                                                    placeholder="Search"
                                                                    class="form-control form-control-sm">
                                                            </div>
                                                            <div id="prerepliescontent" class="row p-3"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                    <!--  endreplay ticket -->


                                    <!-- note tab -->
                                    <div class="tab-pane fade" id="nav-addnote" role="tabpanel"
                                        aria-labelledby="nav-addnote-tab">
                                        <div>
                                            <form
                                                action="{{ url(Request::segment(1) . '/support/supporttickets/update') }}"
                                                method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                @method('PUT')
                                                <div>
                                                    <div class="form-group">
                                                        <textarea class="summernote form-control" name="message" id="replymessage" rows="14"></textarea>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <select name="dept"
                                                                class="form-control select2-search-disable"
                                                                style="width: 100%;">
                                                                <option value="">- Set Department -</option>
                                                                @foreach ($dep as $r)
                                                                    <option value="{{ $r->id }}">
                                                                        {{ $r->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <select name="flagto" id="flagto"
                                                                class="form-control select2-search-disable"
                                                                style="width: 100%;">
                                                                <option value="">- Set Assigment -</option>
                                                                @foreach ($admin as $r)
                                                                    <option value="{{ $r->id }}">
                                                                        {{ $r->firstname . ' ' . $r->lastname }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <select name="priority" id="priority"
                                                                class="form-control select2-search-disable"
                                                                style="width: 100%;">
                                                                <option value="">- Set Priority -</option>
                                                                <option value="High">High</option>
                                                                <option value="Medium">Medium</option>
                                                                <option value="Low">Low</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <select name="status" id="status"
                                                                class="form-control select2-search-disable"
                                                                style="width: 100%;">
                                                                <option value="">- Set Status -</option>
                                                                @foreach ($status as $r)
                                                                    <option value="{{ $r->id }}">
                                                                        {{ $r->title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-6 mt-2">
                                                            <div class="float-left">
                                                                <button id="bttattachfile2" class="btn btn-info">Attach
                                                                    Files</button>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-md-6 mt-2">
                                                            <div class="float-right">
                                                                <div class="d-flex">
                                                                    <div class="return-to-ticket-list mt-2 mr-2">
                                                                        <label class="checkbox">
                                                                            <input type="checkbox" name="returntolist"
                                                                                value="1" checked="">
                                                                            Return to Ticket List
                                                                        </label>
                                                                    </div>
                                                                    <input type="hidden" name="action" value="addnote">
                                                                    <input type="hidden" name="id"
                                                                        value="{{ $tiket->id }}">
                                                                    <button type="submit"
                                                                        class="btn btn-primary pull-right "
                                                                        name="postreply" id="btnAddNote">
                                                                        <i class="fas fa-reply mr-2"></i>
                                                                        Add Note
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12">

                                                            <div id="booxupload2" class="formattachfile pt-3 mt-5 "
                                                                style="display: none">
                                                                <div class="form-group row">
                                                                    <label for="attachment"
                                                                        class="col-sm-2 col-form-label">Attachments</label>
                                                                    <div class="col-sm-12 col-lg-8">
                                                                        <div class="input-group mb-3">
                                                                            <div class="custom-file">
                                                                                <input type="file"
                                                                                    class="custom-file-input"
                                                                                    name="attachments[]"
                                                                                    id="inputGroupFile01"
                                                                                    aria-describedby="inputGroupFileAddon01">
                                                                                <label class="custom-file-label"
                                                                                    for="inputGroupFile01">Choose
                                                                                    file</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12 col-lg-2">
                                                                        <button type="button" id="add-more-note"
                                                                            class="btn btn-primary btn-block">
                                                                            Add More
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div id="addform-noted">
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- end note tiket -->

                                    <div class="tab-pane fade" id="nav-custom-fields" role="tabpanel"
                                        aria-labelledby="nav-custom-fields-tab">
                                        <form action="{{ url(Request::segment(1) . '/support/supporttickets/update') }}"
                                            method="post" enctype="multipart/form-data">
                                            {{ csrf_field() }}
                                            @method('PUT')
                                            <div class="card shadow-none">
                                                <div class="card-body ">
                                                    @foreach ($customFields as $c)
                                                        {{ csrf_field() }}
                                                        <div class="form-group row">
                                                            <label for="department-name"
                                                                class="col-sm-4 col-form-label">{{ $c['name'] }}</label>
                                                            <div class="col-sm-8">
                                                                @php echo $c['input']; @endphp
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <input type="hidden" name="action" value="customFields">
                                                    <input type="hidden" name="id" value="{{ $tiket->id }}">
                                                    <input type="hidden" name="did" value="{{ $tiket->did }}">
                                                    <div class="text-center">
                                                        <button type="submit" class="btn btn-primary pull-right "
                                                            id="addcustomFields">Save Changes</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="nav-other-tickets" role="tabpanel"
                                        aria-labelledby="nav-other-tickets-tab">
                                        <div class="table-responsive">
                                            <table class="table table-bordered dt-responsive w-100" id="tiketother">
                                                <thead>
                                                    <tr>
                                                        <th>Date Submitted</th>
                                                        <th>Department</th>
                                                        <th>Subject</th>
                                                        <th>Status</th>
                                                        <th>Last Reply</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="nav-clientlog" role="tabpanel"
                                        aria-labelledby="nav-clientlog-tab">
                                        <div class="table-responsive">
                                            <table class="table table-bordered dt-responsive w-100" id="userlog">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Description</th>
                                                        <th>Username</th>
                                                        <th>Ip Addres</th>

                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="nav-options" role="tabpanel"
                                        aria-labelledby="nav-options-tab">
                                        <form action="" method="post" id="frmTicketOptions">
                                            @csrf
                                            <input type="hidden" name="action" value="saveoption">
                                            <div class="card shadow-none">
                                                <div class="card-body ">
                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <div class="form-group row">
                                                                <label
                                                                    class="col-sm-4 col-form-label text-right">Department</label>
                                                                <div class="col-sm-8">
                                                                    <select class="form-control" name="deptid">
                                                                        @foreach ($dep as $r)
                                                                            <option value="{{ $r->id }}"
                                                                                {{ $r->id == $tiket->did ? 'selected' : '' }}>
                                                                                {{ $r->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <label
                                                                    class="col-sm-4 col-form-label text-right">Subject</label>
                                                                <div class="col-sm-8">
                                                                    <input value="{{ $tiket->title }}" type="text"
                                                                        name="subject" class="form-control">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <label
                                                                    class="col-sm-4 col-form-label text-right">Status</label>
                                                                <div class="col-sm-8">
                                                                    <select class="form-control" name="status">
                                                                        @foreach ($status as $r)
                                                                            <option style="color: {{ $r->color }}"
                                                                                {{ $r->title == $tiket->status ? 'selected' : '' }}>
                                                                                {{ $r->title }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <label class="col-sm-4 col-form-label text-right">CC
                                                                    Recipients</label>
                                                                <div class="col-sm-8">
                                                                    <input type="text" name="cc"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <label class="col-sm-4 col-form-label text-right">Client
                                                                    Name</label>
                                                                <div class="col-sm-8">
                                                                    {{-- <select class="form-control" id="clientname" name="userid">
   
                                                   </select> --}}
                                                                    <select name="userid" id="search_client"
                                                                        class="form-control select2-limiting"
                                                                        style="width: 100%">
                                                                        @if ($tiket->userid)
                                                                            @if ($tiket->client)
                                                                                <option value="{{ $tiket->userid }}"
                                                                                    selected="selected">
                                                                                    <span class="d-block">
                                                                                        {{ $tiket->client->firstname . ' ' . $tiket->client->lastname . ' ' . $tiket->client->companyname }}
                                                                                        #{{ $tiket->client->id }}</span>
                                                                                    <span class="d-block">
                                                                                        <small>{{ $tiket->client->email }}</small></span>
                                                                                </option>
                                                                            @endif
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <label class="col-sm-4 col-form-label text-right">Assigned
                                                                    To</label>
                                                                <div class="col-sm-8">
                                                                    <select class="form-control" name="flagto">
                                                                        <option value="0">None</option>
                                                                        @foreach ($admin as $users)
                                                                            <option value="{{ $users->id }}"
                                                                                {{ $users->id == $tiket->flag ? 'selected' : '' }}>
                                                                                {{ $users->firstname . ' ' . $users->lastname }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <label
                                                                    class="col-sm-4 col-form-label text-right">Priority</label>
                                                                <div class="col-sm-8">
                                                                    <select class="form-control" name="priority">
                                                                        <option value="High"
                                                                            {{ $tiket->urgency == 'High' ? 'selected' : '' }}>
                                                                            High</option>
                                                                        <option value="Medium"
                                                                            {{ $tiket->urgency == 'Medium' ? 'selected' : '' }}>
                                                                            Medium</option>
                                                                        <option value="Low"
                                                                            {{ $tiket->urgency == 'Low' ? 'selected' : '' }}>
                                                                            Low</option>

                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-4 col-form-label text-right">Merge
                                                                    Ticket</label>
                                                                <div class="col-sm-4">
                                                                    <input type="text" name="mergetid"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    (# to combine)
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="col-md-12 text-center pt-3">
                                                            <button id="btnSaveChanges" type="submit"
                                                                class="btn btn-primary" value="save">
                                                                <i class="fas fa-save"></i>
                                                                {{ Lang::get('admin.savechanges') }}
                                                            </button>
                                                            <input type="reset"
                                                                value="{{ Lang::get('admin.cancelchanges') }}"
                                                                class="btn btn-light" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="nav-log" role="tabpanel"
                                        aria-labelledby="nav-log-tab">
                                        <div class="table-responsive">
                                            <table class="table table-bordered dt-responsive w-100">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Request Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($tiketlog as $r)
                                                        <tr>
                                                            <td>{{ $r->date }}</td>
                                                            <td>{{ $r->action }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <div class="card pt-3">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered dt-responsive w-100" id="datatabletiket">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Product/Service</th>
                                                <th>Amount</th>
                                                <th>Billing Cycle</th>
                                                <th>Signup Date</th>
                                                <th>Next Due Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                        <div id="ticketreplies">

                            @foreach ($replay['replies'] as $r)
                                @if (@!empty($r['admin'] && !$r['note']))
                                    <div class="card mb-3">
                                        <div class="card-body reply staff">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-3">
                                                    <div class="submitter p-3">
                                                        <h4 class="text-qw-admin">{{ $r['admin'] }}</h4>
                                                        <p class="title"> Staff</p>
                                                        @if (!empty($r['rating']))
                                                            <div class="rating mb-5">
                                                                @foreach ($r['rating'] as $img)
                                                                    <img src="{{ Theme::asset('img/' . $img) }}"
                                                                        border="0" alt="+" align="absmiddle">
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        <div class="taction">

                                                            <form id="fd{{ $r['id'] }}"
                                                                action="{{ url(Request::segment(1) . '/support/supporttickets/replay/destroy') }}"
                                                                method="POST">
                                                                <input type="hidden" name="_token"
                                                                    value="{{ csrf_token() }}">
                                                                <input type="hidden" name="_method" value="DELETE">
                                                                <input type="hidden" name="id"
                                                                    value="{{ $r['id'] }}">
                                                                <input type="hidden" name="tid"
                                                                    value="{{ $tiket->id }}">
                                                                @if ($r['id'] != 0)
                                                                    <div id="buttonaction{{ $r['id'] }}">
                                                                        <button type="button"
                                                                            data-id="{{ $r['id'] }}"
                                                                            class="btn btn-sm btn-outline-light editreply">Edit</button>
                                                                        <button title="delete" type="button"
                                                                            data-id="{{ $r['id'] }}"
                                                                            class="delete btn btn-sm btn-outline-danger">Delete</button>
                                                                    </div>
                                                                    <div id="buttonactionsave{{ $r['id'] }}"
                                                                        style="display: none;">
                                                                        <button data-id="{{ $r['id'] }}"
                                                                            data-type="reply" type="button"
                                                                            class="btn btn-sm btn-success actionsave ">Save</button>
                                                                        <button data-id="{{ $r['id'] }}"
                                                                            type="button"
                                                                            class="btn btn-sm btn-outline-light cancelreply">Cancel</button>
                                                                    </div>
                                                                @else
                                                                    <div id="buttontiket">
                                                                        <button type="button"
                                                                            data-id="{{ $tiket->id }}"
                                                                            class="btn btn-sm btn-outline-light editiket">Edit</button>
                                                                        <button title="delete" type="button"
                                                                            data-id="{{ $r['id'] }}"
                                                                            class="delete btn btn-sm btn-outline-danger">Delete</button>
                                                                    </div>
                                                                    <div id="tiketactionsave" style="display: none;">
                                                                        <button data-id="{{ $tiket->id }}"
                                                                            data-type="reply" type="button"
                                                                            class="btn btn-sm btn-success actiotiket ">Save</button>
                                                                        <button data-id="{{ $tiket->id }}"
                                                                            type="button"
                                                                            class="btn btn-sm btn-outline-light canceltiket">Cancel</button>
                                                                    </div>
                                                                @endif
                                                            </form>




                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-9 staff-note">
                                                    <div class="p-3 relative border rounded">

                                                        @if ($r['id'])
                                                            <div class="quoteicon d-flex ">
                                                                <input type="checkbox" class="rids" name="rids[]"
                                                                    value="{{ $r['id'] }}">
                                                                <h6 class="text-dark ml-auto">Posted on
                                                                    {{ $r['friendlydate'] }} at
                                                                    {{ $r['friendlytime'] }}</h6>
                                                            </div>
                                                        @endif
                                                        <hr>
                                                        @if ($r['id'] != 0)
                                                            <div class="msgwrap" id="replymessage{{ $r['id'] }}">
                                                                {!! $r['message'] !!}
                                                            </div>

                                                            <div id="textreplay{{ $r['id'] }}" class="form-group"
                                                                style="display: none;">
                                                                <textarea class="form-control" name="message">{!! $r['message'] !!}</textarea>
                                                            </div>
                                                        @else
                                                            <div class="msgwrap" id="ticketmessage">
                                                                {!! $r['message'] !!}
                                                            </div>

                                                            <div id="textareaticket" class="form-group"
                                                                style="display: none;">
                                                                <textarea class="form-control" name="message">{!! $r['message'] !!}</textarea>
                                                            </div>
                                                        @endif
                                                        @if (!empty($r['attachments']))
                                                            <strong>Attachments</strong>
                                                            <div class="ticketattachment ">
                                                                <div class="row">
                                                                    @foreach ($r['attachments'] as $f)
                                                                        @php
                                                                            // Replace the specific base URL with the new domain
                                                                            $fileUrl = str_replace(
                                                                                'https://my.hostingnvme.id/attachments/',
                                                                                'https://hostingnvme.id/',
                                                                                $f['filename'],
                                                                            );
                                                                            $fileUrl = str_replace(
                                                                                'https://client.gudangssl.id/attachments/',
                                                                                'https://client.gudangssl.id/',
                                                                                $f['filename'],
                                                                            );
                                                                            $fileUrl = str_replace(
                                                                                'https://client.bikin.website/attachments/',
                                                                                'https://client.bikin.website/',
                                                                                $f['filename'],
                                                                            );
                                                                            // Determine if the file is an image based on its extension
                                                                            $isImage = in_array(
                                                                                pathinfo(
                                                                                    $f['filename'],
                                                                                    PATHINFO_EXTENSION,
                                                                                ),
                                                                                ['gif', 'png', 'jpg', 'jpeg'],
                                                                            );
                                                                        @endphp
                                                                        <div class="col-sm-3 text-center">
                                                                            <div id="att{{ $r['id'] }}-{{ $loop->index }}"
                                                                                class="mb-3 p-3">
                                                                                @if ($isImage)
                                                                                    <div class="mb-2">
                                                                                        <a href="{{ $fileUrl }}"
                                                                                            class="image-popup-link">
                                                                                            <div
                                                                                                class="ticketattachmentthumbcontainer">
                                                                                                <img src="{{ $fileUrl }}"
                                                                                                    class="ticketattachmentthumb"
                                                                                                    style="width: 100px; height: auto;">
                                                                                            </div>
                                                                                        </a>
                                                                                        <div
                                                                                            class="ticketattachmentlinks pt-2 text-center">
                                                                                            <div
                                                                                                class="ticketattachmentinfo text-center">
                                                                                                <i
                                                                                                    class="fas fa-file-archive"></i>
                                                                                            </div>
                                                                                            <small>
                                                                                                <a download
                                                                                                    href="{{ $fileUrl }}">download</a>
                                                                                                |
                                                                                                <a href="javascript: void(0)"
                                                                                                    class="deleteattachments"
                                                                                                    data-id="{{ $r['id'] }}"
                                                                                                    data-i="{{ $loop->index }}"
                                                                                                    data-type="{{ $r['id'] == 0 ? 'ticket' : 'replay' }}">delete</a>
                                                                                            </small>
                                                                                        </div>
                                                                                    </div>
                                                                                @else
                                                                                    <div
                                                                                        class="ticketattachmentlinks pt-2 text-center">
                                                                                        <div
                                                                                            class="ticketattachmentinfo text-center">
                                                                                            <i
                                                                                                class="fas fa-file-archive"></i>
                                                                                        </div>
                                                                                        <small>
                                                                                            <a
                                                                                                href="{{ $fileUrl }}">download</a>
                                                                                            |
                                                                                            <a href="javascript: void(0)"
                                                                                                class="deleteattachments"
                                                                                                data-id="{{ $r['id'] }}"
                                                                                                data-i="{{ $loop->index }}"
                                                                                                data-type="{{ $r['id'] == 0 ? 'ticket' : 'replay' }}">delete</a>
                                                                                        </small>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif


                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @else
                                    @if ($r['note'] && $r['admin'])
                                        <div class="card mb-3">
                                            <div class="card-body note">
                                                <div class="">
                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="submitter p-3">
                                                                <h4>{{ $r['admin'] }}</h4>
                                                                <p class="title">Private Note </p>
                                                                <div class="taction">
                                                                    <form id="noteform{{ $r['id'] }}"
                                                                        action="{{ url(Request::segment(1) . '/support/supporttickets/replay/delnote') }}"
                                                                        method="POST">
                                                                        <input type="hidden" name="_token"
                                                                            value="{{ csrf_token() }}">
                                                                        <input type="hidden" name="_method"
                                                                            value="DELETE">
                                                                        <input type="hidden" name="id"
                                                                            value="{{ $r['id'] }}">
                                                                        <input type="hidden" name="tid"
                                                                            value="{{ $tiket->id }}">
                                                                        <button title="deletenote" type="button"
                                                                            data-id="{{ $r['id'] }}"
                                                                            class="deletenote btn btn-sm btn-outline-danger">Delete</button>
                                                                    </form>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-10 private-note">
                                                            <div class="p-3">
                                                                <div class="message markdown-content">

                                                                </div>
                                                                <div class="postedon">{{ $r['admin'] }} Posted a note on
                                                                    {{ $r['friendlydate'] }} at {{ $r['friendlytime'] }}
                                                                </div>
                                                                <div class="msgwrap" id="contentr1305">
                                                                    <div class="message markdown-content">
                                                                        @php echo  nl2br($r['message']) @endphp
                                                                    </div>
                                                                    @if (!empty($r['attachments']))
                                                                        <strong>Attachments</strong>
                                                                        <div class="ticketattachmentcontainer">
                                                                            @foreach ($r['attachments'] as $f)
                                                                                @php
                                                                                    // Replace the specific base URL with the new domain
                                                                                    $fileUrl = str_replace(
                                                                                        'https://my.hostingnvme.id/attachments/',
                                                                                        'https://hostingnvme.id/',
                                                                                        $f['filename'],
                                                                                    );
                                                                                    $fileUrl = str_replace(
                                                                                        'https://client.gudangssl.id/attachments/',
                                                                                        'https://client.gudangssl.id/',
                                                                                        $f['filename'],
                                                                                    );
                                                                                    $fileUrl = str_replace(
                                                                                        'https://client.bikin.website/attachments/',
                                                                                        'https://client.bikin.website/',
                                                                                        $f['filename'],
                                                                                    );

                                                                                    // Determine if the file is an image based on its extension
                                                                                    $isImage = in_array(
                                                                                        pathinfo(
                                                                                            $f['filename'],
                                                                                            PATHINFO_EXTENSION,
                                                                                        ),
                                                                                        ['gif', 'png', 'jpg', 'jpeg'],
                                                                                    );
                                                                                @endphp
                                                                                <div
                                                                                    id="att{{ $r['id'] }}-{{ $loop->index }}">
                                                                                    @if ($isImage)
                                                                                        <div class="mb-2">
                                                                                            <a href="{{ $fileUrl }}"
                                                                                                data-lightbox="image">
                                                                                                <div
                                                                                                    class="ticketattachmentthumbcontainer">
                                                                                                    <img src="{{ $fileUrl }}"
                                                                                                        class="ticketattachmentthumb"
                                                                                                        style="width: 100px; height: auto;">
                                                                                                </div>
                                                                                                <div
                                                                                                    class="ticketattachmentinfo">
                                                                                                    <i
                                                                                                        class="fas fa-file-archive"></i>
                                                                                                </div>
                                                                                            </a>
                                                                                            <div
                                                                                                class="ticketattachmentlinks pt-2">
                                                                                                <small>
                                                                                                    <a download
                                                                                                        href="{{ $fileUrl }}">download</a>
                                                                                                    |
                                                                                                    <a href="javascript: void(0)"
                                                                                                        class="deleteattachments"
                                                                                                        data-id="{{ $r['id'] }}"
                                                                                                        data-i="{{ $loop->index }}"
                                                                                                        data-type="noted">delete</a>
                                                                                                </small>
                                                                                            </div>
                                                                                        </div>
                                                                                    @else
                                                                                        <span class="ticketattachmentinfo">
                                                                                            <i
                                                                                                class="fas fa-file-archive"></i>
                                                                                        </span>
                                                                                        <div
                                                                                            class="ticketattachmentlinks pt-2">
                                                                                            <small>
                                                                                                <a download
                                                                                                    href="{{ $fileUrl }}">download</a>
                                                                                                |
                                                                                                <a href="javascript: void(0)"
                                                                                                    class="deleteattachments"
                                                                                                    data-id="{{ $r['id'] }}"
                                                                                                    data-i="{{ $loop->index }}"
                                                                                                    data-type="noted">delete</a>
                                                                                            </small>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="card mb-3">
                                            <div class="card-body reply">
                                                <div class="">
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-3">
                                                            <div class="submitter p-3">

                                                                <h4>@php echo  @$r['clientname'] @endphp</h4>
                                                                <p class="title">Client</p>
                                                                <div class="taction">
                                                                    <form id="fd{{ $r['id'] }}"
                                                                        action="{{ url(Request::segment(1) . '/support/supporttickets/replay/destroy') }}"
                                                                        method="POST">
                                                                        <input type="hidden" name="_token"
                                                                            value="{{ csrf_token() }}">
                                                                        <input type="hidden" name="_method"
                                                                            value="DELETE">
                                                                        <input type="hidden" name="id"
                                                                            value="{{ $r['id'] }}">
                                                                        <input type="hidden" name="tid"
                                                                            value="{{ $tiket->id }}">
                                                                        <a title="Edit" href="#"
                                                                            class="btn btn-sm btn-outline-light">Edit</a>
                                                                        <button title="delete" type="button"
                                                                            data-id="{{ $r['id'] }}"
                                                                            class="delete btn btn-sm btn-outline-danger">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-md-9">

                                                            <div class="p-3 border rounded bg-light">
                                                                <h6 class="text-dark text-lg-right mb-3">Posted on
                                                                    {{ $r['friendlydate'] }} at {{ $r['friendlytime'] }}
                                                                    </h3>
                                                                    <div class="message markdown-content">
                                                                        @php echo  nl2br($r['message']) @endphp
                                                                    </div>
                                                                    @if (!empty($r['attachments']))
                                                                        <strong>Attachments</strong>
                                                                        <div class="ticketattachmentcontainer">
                                                                            @foreach ($r['attachments'] as $f)
                                                                                @php
                                                                                    // Replace the specific base URL with the new domain
                                                                                    $fileUrl = str_replace(
                                                                                        'https://my.hostingnvme.id/attachments/',
                                                                                        'https://hostingnvme.id/',
                                                                                        $f['filename'],
                                                                                    );
                                                                                    $fileUrl = str_replace(
                                                                                        'https://client.gudangssl.id/attachments/',
                                                                                        'https://client.gudangssl.id/',
                                                                                        $f['filename'],
                                                                                    );
                                                                                    $fileUrl = str_replace(
                                                                                        'https://client.bikin.website/attachments/',
                                                                                        'https://client.bikin.website/',
                                                                                        $f['filename'],
                                                                                    );
                                                                                    // Determine if the file is an image based on its extension
                                                                                    $isImage = in_array(
                                                                                        pathinfo(
                                                                                            $f['filename'],
                                                                                            PATHINFO_EXTENSION,
                                                                                        ),
                                                                                        ['gif', 'png', 'jpg', 'jpeg'],
                                                                                    );
                                                                                @endphp
                                                                                @if ($isImage)
                                                                                    <div class="mb-2">
                                                                                        <a href="{{ $fileUrl }}"
                                                                                            data-lightbox="image-r501407">
                                                                                            <div
                                                                                                class="ticketattachmentthumbcontainer">
                                                                                                <img src="{{ $fileUrl }}"
                                                                                                    class="ticketattachmentthumb">
                                                                                            </div>
                                                                                            <div
                                                                                                class="ticketattachmentinfo">
                                                                                                <i
                                                                                                    class="fas fa-file-archive mr-2"></i>{{ $fileUrl }}
                                                                                            </div>
                                                                                        </a>
                                                                                        <div
                                                                                            class="ticketattachmentlinks pt-2">
                                                                                            <small>
                                                                                                <a download
                                                                                                    href="{{ $fileUrl }}">download</a>
                                                                                                |
                                                                                                <a href="javascript: void(0)"
                                                                                                    class="deleteattachments"
                                                                                                    data-id="{{ $r['id'] }}"
                                                                                                    data-i="{{ $loop->index }}"
                                                                                                    data-type="reply">delete</a>
                                                                                            </small>
                                                                                        </div>
                                                                                    </div>
                                                                                @else
                                                                                    <span class="ticketattachmentinfo">
                                                                                        <i class="fas fa-file-archive"></i>
                                                                                    </span>
                                                                                    <div
                                                                                        class="ticketattachmentlinks pt-2">
                                                                                        <small>
                                                                                            <a download
                                                                                                href="{{ $fileUrl }}">download</a>
                                                                                            |
                                                                                            <a href="javascript: void(0)"
                                                                                                class="deleteattachments"
                                                                                                data-id="{{ $r['id'] }}"
                                                                                                data-i="{{ $loop->index }}"
                                                                                                data-type="reply">delete</a>
                                                                                        </small>
                                                                                    </div>
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                    @endif

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach

                        </div>


                        <div class="card">
                            <div class="card-body">
                                <div class="float-right">
                                    <button type="button" class="btn btn-outline-light" data-toggle="modal"
                                        data-target="#modalsplit">Split Selected Replies</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalsplit" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <form action="{{ url(Request::segment(1) . '/support/supporttickets/split') }}" method="post"
            enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Split Selected Ticket Replies</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label text-right">Department</label>
                            <div class="col-sm-8">
                                <select name="dept" id="splitdept" class="form-control" style="width: 100%;">
                                    @foreach ($dep as $r)
                                        <option value="{{ $r->id }}"
                                            {{ $tiket->did == $r->id ? 'selected' : '' }}>
                                            {{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label text-right">New Ticket Name</label>
                            <div class="col-sm-8">
                                <input type="text" id="splittitlw" class="form-control" name="titlenew"
                                    value="{{ $tiket->title }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label text-right">Priority</label>
                            <div class="col-sm-8">
                                <select name="priority" id="splitpriority" class="form-control select2-search-disable"
                                    style="width: 100%;">
                                    <option value="High" {{ $tiket->urgency == 'High' ? 'selected' : '' }}>High
                                    </option>
                                    <option value="Medium" {{ $tiket->urgency == 'Medium' ? 'selected' : '' }}>Medium
                                    </option>
                                    <option value="Low" {{ $tiket->urgency == 'Low' ? 'selected' : '' }}>Low</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label text-right">Notify Client</label>
                            <div class="col-sm-8">
                                <div class="mt-2">
                                    <input type="checkbox" class="" id="splitnotifyclientx">
                                    Tick to send notification email
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" value="{{ $tiket->id }}">

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" data-id="{{ $tiket->id }}"
                            class="btn btn-primary split">Submit</button>
                    </div>
                </div>
            </div>
            <form>
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
    <!-- BS Markdown -->
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/markdown.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/to-markdown.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/bootstrap-markdown/js/bootstrap-markdown.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/magnific-popup/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/helpers/select2-utils.js') }}"></script>

    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

    <script type="text/javascript">
        (function() {
            // search client
            searchClient($("#search_client"), "{!! route('admin.pages.clients.viewclients.clientsummary.searchClient') !!}");

            var fieldSelection = {
                addToReply: function() {
                    var url = arguments[0] || '',
                        title = arguments[1] || '';
                    (e = this.jquery ? this[0] : this), (text = '')

                    if (title != '') {
                        text = '[' + title + '](' + url + ')'
                    } else {
                        text = url
                    }

                    return (
                        ('selectionStart' in e &&
                            function() {
                                e.value = "";
                                if (e.value == '\n\n') {
                                    e.selectionStart = 0
                                    e.selectionEnd = 0
                                }
                                e.value =
                                    e.value.substr(0, e.selectionStart) +
                                    text +
                                    e.value.substr(e.selectionEnd, e.value.length)
                                e.focus()
                                return this
                            }) ||
                        (document.selection &&
                            function() {
                                e.focus()
                                document.selection.createRange().text = text
                                return this
                            }) ||
                        function() {
                            e.value += text
                            return this
                        }
                    )()
                },
            }
            jQuery.each(fieldSelection, function(i) {
                jQuery.fn[i] = this
            })
        })()

        $('#predefq').keyup(function() {
            var intellisearchlength = $('#predefq').val().length;
            if (intellisearchlength > 2) {
                $.ajax({
                    type: 'POST',
                    url: route('admin.pages.js.predefine'),
                    data: {
                        action: 'loadpredefinedreplies',
                        predefq: $('#predefq').val(),
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(res) {
                        $("#prerepliescontent").html(res.data);
                    }
                })
            } else {
                $.ajax({
                    type: 'POST',
                    url: route('admin.pages.js.predefine'),
                    data: {
                        action: 'loadpredefinedreplies',
                        catid: 0,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(res) {
                        $("#prerepliescontent").html(res.data);
                    }
                })
            }
        })

        function loadpredef(catid) {
            // $('#prerepliescontainer').removeClass('d-none');
            $("#prerepliescontainer").slideToggle();
            $("#prerepliescontent").html(`
            <div class="col-12 text-center">
               <div class="spinner-border" role="status">
                  <span class="sr-only">Loading...</span>
               </div>
               <h6 class="mt-3">Please Wait</h6>
            </div>
            `);
            $.ajax({
                type: 'POST',
                url: route('admin.pages.js.predefine'),
                data: {
                    action: "loadpredefinedreplies",
                    cat: catid,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    $("#prerepliescontent").html(res.data);
                }
            })
        }

        function selectpredefcat(catid) {
            $.ajax({
                type: 'POST',
                url: route('admin.pages.js.predefine'),
                data: {
                    action: 'loadpredefinedreplies',
                    cat: catid,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    $("#prerepliescontent").html(res.data);
                }
            })
        }

        function selectpredefreply(artid) {
            $('#replymessage').empty();
            $.ajax({
                type: 'POST',
                url: route('admin.pages.js.predefine'),
                data: {
                    action: 'getpredefinedreply',
                    id: artid,
                    _token: '{{ csrf_token() }}',
                },
                success: function(res) {
                    $('#replymessage').addToReply(res.data)
                }
            });
        }
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.image-popup-link').magnificPopup({
                type: 'image'
            });
            //datatabletiket
            $('#datatabletiket').dataTable({
                "bPaginate": false
            });
            $('#client-name').select2({
                minimumInputLength: 2,
                placeholder: 'Client',
                ajax: {
                    type: "post",
                    url: '{{ url('admin/support/getClientselect2') }}',
                    dataType: 'json',
                    /*   delay: 250, */
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.firstname + ' ' + item.lastname + ' | ' + item
                                        .email + ' | ' +
                                        item.companyname,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#client-name').on('change', function() {
                var clientID = this.value;
                $('#datatabletiket tbody').html();
                $('#name-field').val();
                $('#email-address').val();
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url('admin/support/getservice') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        client: clientID
                    },
                    success: function(data) {
                        //console.log(data);
                        $('#name-field').val(data.cleint.firstname + ' ' + data.cleint
                            .lastname + ' #' +
                            data.cleint.id).attr('disabled', 'disabled');
                        $('#email-address').val(data.cleint.email).attr('disabled', 'disabled');
                        /* $('#panel-Client-Replis h4').text(data.clientReplies); */
                        $('#datatabletiket tbody').html(data.html);

                    }
                });

            });

            $('#datatabletiket').on('change', "input[name='related_service']:checked", function() {
                var value = $(this).data('type');
                var name = '';
                if (value == 'product') {
                    name = 'serviceid';
                }
                if (value == 'domain') {
                    name = 'domainid';
                }
                $('#inputtype').val($(this).val()).attr('name', name);
            });





            //userlog
            if (!$.fn.dataTable.isDataTable('#userlog')) {
                var tbl = $('#userlog').DataTable({
                    paging: true,
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    ajax: {
                        url: '{{ url(Request::segment(1) . '/support/supporttickets/clientlog') }}',
                        type: 'POST',
                        data: {
                            userID: {{ $tiket->userid }}
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    },
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>",
                        },
                        searching: false,
                    },
                    columns: [

                        {
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'description',
                            name: 'description'
                        },
                        {
                            data: 'user',
                            name: 'user'
                        },
                        {
                            data: 'ipaddr',
                            name: 'ipaddr'
                        }
                    ],

                    drawCallback: function() {
                        $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                    },
                    //order : [[ 3, "desc" ]] 
                });

            };
            /* userlog */

            /* tiketother */
            if (!$.fn.dataTable.isDataTable('#tiketother')) {
                var tbl = $('#tiketother').DataTable({
                    paging: true,
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    ajax: {
                        url: '{{ url(Request::segment(1) . '/support/supporttickets/tiketothe') }}',
                        type: 'POST',
                        data: {
                            userID: {{ $tiket->userid }}
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    },
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>",
                        },
                        searching: false,
                    },
                    columns: [

                        {
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'lastreply',
                            name: 'lastreply'
                        }
                    ],

                    drawCallback: function() {
                        $('.dataTables_paginate > .pagination').addClass('pagination-rounded')
                    },
                    //order : [[ 3, "desc" ]] 
                });

            };


            /*tiketother */


            dataservide();

            /* DEFAULT TEXT EDITOR */
            $(".summernote").markdown({
                autofocus: false,
                hideable: false,
                savable: false,
                width: 'inherit',
                height: 'inherit',
                resize: 'vertical',
                iconlibrary: 'fa',
                language: 'en',
                fullscreen: {},
                dropZoneOptions: null,
                hiddenButtons: [],
                disabledButtons: [],
                lineBreaks: true
            });

            $('#clientname').select2({
                minimumInputLength: 2,
                placeholder: 'Client',
                ajax: {
                    type: "post",
                    url: '{{ url('admin/support/getClientselect2') }}',
                    dataType: 'json',
                    /*   delay: 250, */
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.firstname + ' ' + item.lastname + ' | ' + item
                                        .email + ' | ' +
                                        item.companyname,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });



            $("#bttattachfile").click(function() {
                $("#booxupload").toggle();
                return false;
            });
            $("#bttattachfile2").click(function() {
                $("#booxupload2").toggle();
                return false;
            });

            $('#add-more').click(function() {
                $('#addform').after(`
                                        <div class="form-group row">
                                            <label for="attachment"
                                                class="col-sm-2 col-form-label">Attachments</label>
                                            <div class="col-sm-12 col-lg-8">
                                                <div class="input-group mb-3">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="attachments[]" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01">
                                                        <label class="custom-file-label" for="inputGroupFile01">Choose file</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-2">
                                            </div>
                                        </div>
                                    `);
                return false;

            });


            $('#add-more-note').click(function() {

                $('#addform-noted').after(`
                                        <div class="form-group row">
                                            <label for="attachment"
                                                class="col-sm-2 col-form-label">Attachments</label>
                                            <div class="col-sm-12 col-lg-8">
                                                <div class="input-group mb-3">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" name="attachments[]" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01">
                                                        <label class="custom-file-label" for="inputGroupFile01">Choose file</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-lg-2">
                                            </div>
                                        </div>
                                    `);
                return false;

            });


            //editreply
            $('.editreply').click(function() {
                var id = $(this).data('id');
                //console.log(id,'aaaaaa');
                $('#replymessage' + id).hide();
                $('#buttonaction' + id).hide();
                $('#textreplay' + id).show();
                $('#buttonactionsave' + id).show();
                return false;
            });


            //ticket
            $('.editiket').click(function() {
                var id = $(this).data('id');
                //console.log(id,'aaaaaa');
                $('#buttontiket').hide();
                $('#ticketmessage').hide();
                $('#textareaticket').show();
                $('#tiketactionsave').show();
                return false;
            });

            //cancelreply
            $('.cancelreply').click(function() {
                var id = $(this).data('id');
                $('#replymessage' + id).show();
                $('#buttonaction' + id).show();
                $('#textreplay' + id).hide();
                $('#buttonactionsave' + id).hide();
                return false;
            });

            //cancel tiket
            $('.canceltiket').click(function() {
                var id = $(this).data('id');
                $('#buttontiket').show();
                $('#ticketmessage').show();
                $('#textareaticket').hide();
                $('#tiketactionsave').hide();
                return false;
            });

            //actionsave
            $('.actionsave').click(function() {
                var id = $(this).data('id');
                var type = $(this).data('type');
                var message = $('#textreplay' + id + ' textarea').val();
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url(Request::segment(1) . '/support/supporttickets/replay/update') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: id,
                        message: message,
                        type: type,
                        _method: 'PUT'
                    },
                    success: function(data) {
                        $('#replymessage' + data.id).show();
                        $('#buttonaction' + data.id).show();
                        $('#textreplay' + data.id).hide();
                        $('#buttonactionsave' + data.id).hide();
                        $('#textreplay' + data.id + ' textarea').html(data.message);
                        $('#replymessage' + data.id).html(data.message);
                        return false;
                    }
                });
                return false;
            });


            //actioan tiket
            $('.actiotiket').click(function() {
                var id = $(this).data('id');
                var type = $(this).data('type');
                var message = $('#textareaticket textarea').val();

                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url(Request::segment(1) . '/support/supporttickets/replay/update') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: id,
                        message: message,
                        type: 'ticket',
                        _method: 'PUT'
                    },
                    success: function(data) {
                        /* $('#replymessage'+data.id).show();
                        $('#buttonaction'+data.id).show();
                        $('#textreplay'+data.id).hide();
                        $('#buttonactionsave'+data.id).hide();
                        $('#textreplay'+data.id+' textarea').html(data.message);
                        $('#replymessage'+data.id).html(data.message); */
                        $('#buttontiket').show();
                        $('#ticketmessage').show();
                        $('#textareaticket').hide();
                        $('#tiketactionsave').hide();
                        $('#textareaticket textarea').html(data.message);
                        $('#ticketmessage').html(data.message);
                        return false;
                    }
                });
                return false;
            });


            //deleteattachments            
            $('.deleteattachments').click(function() {
                Swal.fire({
                        title: "Warning..!",
                        text: "Are you sure you want to delete this attachment?",
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonColor: '#d33',
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((value) => {
                        if (value.isConfirmed) {
                            var id = $(this).data('id');
                            var i = $(this).data('i');
                            var type = $(this).data('type');

                            $.ajax({
                                type: "post",
                                dataType: "json",
                                url: '{{ url(Request::segment(1) . '/support/supporttickets/replay/deleteattachments') }}',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {
                                    id: id,
                                    i: i,
                                    type: type,
                                    _method: 'DELETE'
                                },
                                success: function(data) {
                                    // /<div id="attid-lloopp">
                                    $('#att' + data.id + '-' + data.loop).remove();
                                    return false;
                                }
                            });


                        } else {
                            return false;
                        }
                    });
                return false;
            });

            $('#ticketreplies').on('click', '.delete', function() {
                // e.preventDefault();
                Swal.fire({
                        title: "Warning..!",
                        text: "Are you sure you want to delete this support ticket and all replies?",
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonColor: '#d33',
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((value) => {
                        if (value.isConfirmed) {
                            $('#fd' + $(this).data('id')).submit();
                        } else {
                            return false;
                        }
                    });
                return false;
            });

            $('#ticketreplies').on('click', '.deletenote', function() {
                // e.preventDefault();
                Swal.fire({
                        title: "Warning..!",
                        text: "Are you sure you want to delete this support ticket note?",
                        icon: "warning",
                        showCancelButton: true,
                        cancelButtonColor: '#d33',
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((value) => {
                        if (value.isConfirmed) {
                            $('#noteform' + $(this).data('id')).submit();
                        } else {
                            return false;
                        }
                    });
                return false;
            });


            /* splite */
            $('.split').click(function() {
                console.log('aaaa');
                var id = $(this).data('id');
                var dep = $('#splitdept').val();
                var title = $('#splittitlw').val();
                var priority = $('#splitpriority').val();
                var notif = $('#splitnotifyclientx').val();
                var rids = [];
                i = 0;
                $('.rids:checked').each(function() {
                    rids[i++] = $(this).val();
                });
                //rids=JSON.stringify(rids);
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url(Request::segment(1) . '/support/supporttickets/split') }}',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: id,
                        dep: dep,
                        title: title,
                        priority: priority,
                        notif: notif,
                        rids: rids
                    },
                    success: function(data) {
                        window.location.href = data.url;
                        return false;
                    }
                });
                return false;
            });
        });
        var dataservide = function() {
            var clientID = $('#client-name').val();
            $('#datatabletiket tbody').html();
            $('#datatabletiket').dataTable().fnDestroy();
            $('#name-field').val();
            $('#email-address').val();
            $.ajax({
                type: "post",
                dataType: "json",
                url: '{{ url(Request::segment(1) . '/support/getservice') }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    client: {{ $tiket->userid }},
                    service: "{{ $tiket->service }}"
                },
                success: function(data) {

                    $('#datatabletiket tbody').html(data.html);
                    $('#datatabletiket').dataTable();

                }
            });
        };

        // Add this to your existing JavaScript code section
        $(document).ready(function() {
            // Update file input label with selected file name for both attachment sections
            $(document).on('change', '.custom-file-input', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Modify the add-more-note click handler to include the file input change functionality
            $('#add-more-note').click(function() {
                var newId = 'inputGroupFile' + ($('.custom-file-input').length + 1);
                $('#addform-noted').after(`
                  <div class="form-group row">
                     <label for="attachment" class="col-sm-2 col-form-label">Attachments</label>
                     <div class="col-sm-12 col-lg-8">
                        <div class="input-group mb-3">
                              <div class="custom-file">
                                 <input type="file" class="custom-file-input" name="attachments[]" 
                                    id="${newId}" aria-describedby="inputGroupFileAddon01">
                                 <label class="custom-file-label" for="${newId}">Choose file</label>
                              </div>
                        </div>
                     </div>
                     <div class="col-sm-12 col-lg-2">
                     </div>
                  </div>
            `);
                return false;
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                try {
                    var adminSignature = "{{ auth()->guard('admin')->user()->signature ?? '' }}";
                    $('.summernote2').summernote({
                        height: 200,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link']],
                            ['view', ['fullscreen', 'codeview']]
                        ],
                        styleTags: ['p', 'h4', 'h5', 'h6'],
                        callbacks: {
                            onInit: function() {
                                // Tambahkan signature hanya jika belum ada
                                var currentContent = $(this).summernote('code');
                                if (!currentContent.includes(adminSignature)) {
                                    $(this).summernote('code', currentContent +
                                        '<br><br>--<br>' + adminSignature);
                                }
                            },
                            onChange: function(contents, $editable) {
                                // Cek jika signature hilang, tambahkan lagi tanpa menghapus baris baru user
                                if (!contents.includes(adminSignature)) {
                                    $(this).summernote('code', contents + '<br><br>--<br>' +
                                        adminSignature);
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Error initializing Summernote:', e);
                }
            }, 500);
        });

        // Jangan manipulasi <br> atau <p> secara agresif sebelum submit
        $('form').on('submit', function(e) {
            var editor = $('.summernote2');
            var content = editor.summernote('code');
            // Pastikan signature tetap ada, tidak perlu replace <br> atau <p>
            var signature = "{{ auth()->guard('admin')->user()->signature ?? '' }}";
            if (!content.includes(signature)) {
                content += '<br><br>--<br>' + signature;
            }
            editor.summernote('code', content);
        });
    </script>
