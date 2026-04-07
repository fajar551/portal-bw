@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} - Dashboard</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    {{-- This Sidebar --}}
                    @if ($message = Session::get('success'))
                        <div class="col-lg-12">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading">Your profile is updated!</h4>
                                <small>The profile data has been updated, you can change that anytime.</small>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if (isset($error))
                        <div class="col-lg-12">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading">Access Denied</h4>
                                <p>{{ $error }}</p>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- MAIN CARD -->
                        <div class="col-xl-12" id="main-card">
                            <section class="dashboard-wrapper">

                                <div class="card-title mb-3">
                                    <h4>{{ __('admin.hometitle') }} </h4>
                                </div>

                                <section class="card-status">
                                    <div class="row">

                                        <div class="col-md-4 col-lg-4">
                                            <a href="{{ url('admin/orders/list-allorder?orderstatus=pending') }}">
                                                <div class="card hover-card" id="">
                                                    <div class="card-body text-white rounded pending-order">
                                                        <div class="info-data">
                                                            <i class="ri-shopping-cart-fill"></i>
                                                            <h1 id="pendingOrderCount" class="text-white mr-2">
                                                                {{ $getPendingOrder ?? '' }}</h1>
                                                        </div>
                                                        <small>{{ __('admin.orderslistpending') }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                        <div class="col-md-4 col-lg-4">
                                            <a href="{{ url('admin/support/supporttickets?status[]=open') }}">
                                                <div class="card hover-card" id="">
                                                    <div class="card-body text-white rounded tickets-waiting">
                                                        <div class="info-data">
                                                            <i class="ri-chat-3-fill"></i>
                                                            <h1 id="supportAwaitingReplyCount" class="text-white mr-2">
                                                                {{ $getSupportAwaitingReply ?? '' }}</h1>
                                                        </div>
                                                        <small>{{ __('admin.supportawaitingreply') }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                        <div class="col-md-4 col-lg-4">
                                            <a href="{{ url('admin/clients/cancellationrequests') }}">
                                                <div class="card hover-card" id="">
                                                    <div class="card-body text-white rounded pending-cancellation">
                                                        <div class="info-data">
                                                            <i class="ri-forbid-2-line"></i>
                                                            <h1 id="cancellationRequestCount" class="text-white mr-2">
                                                                {{ $getCancellationRequest ?? '' }}</h1>
                                                        </div>
                                                        <small>{{ __('admin.statspendingcancellations') }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                        {{-- <div class="col-md-6 col-lg-3">
                                        <div class="card hover-card" id="">
                                            <div class="card-body text-white rounded pending-module">
                                                <div class="info-data">
                                                    <i class="ri-error-warning-fill"></i>
                                                    <h1 class="text-white mr-2">17</h1>
                                                </div>
                                                <small>{{ __('admin.statpendingmodule') }}</small>
                                            </div>
                                        </div>
                                    </div> --}}

                                    </div>
                                </section>

                                <div class="chart-overview">
                                    <div class="row">

                                        <div class="col-lg-10 col-md-10 sm-mb-3">
                                            <div class="card p-3">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h4 class="card-title mb-0">{{ __('admin.homesysoverview') }}</h4>
                                                    <div class="btn-group" role="group"
                                                        aria-label="Time Period Selection">
                                                        <button id="btnLastYear" class="btn btn-primary active">Last
                                                            Year</button>
                                                        <button id="btnLastMonth" class="btn btn-secondary">Last
                                                            Month</button>
                                                        <button id="btnToday" class="btn btn-secondary">Today</button>
                                                    </div>
                                                </div>
                                                <hr class="p-0 mt-2" />
                                                <div>
                                                    <div id="spline_area" class="apex-charts mt-3" dir="ltr"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-2">
                                            <div class="card p-3">
                                                <h4 class="card-title m-0 p-0">{{ __('admin.billingtitle') }}</h4>
                                                <hr />
                                                <div class="row">
                                                    <div class="col-12 pb-3">
                                                        <h5 class="m-0 text-success">Rp
                                                            {{ number_format($billingToday, 0, ',', '.') }}</h5>
                                                        <small>{{ __('admin.billingincometoday') }}</small>
                                                    </div>
                                                    <div class="col-12 pb-3">
                                                        <h5 class="m-0 text-warning">Rp
                                                            {{ number_format($billingThisMonth, 0, ',', '.') }}</h5>
                                                        <small>{{ __('admin.calendarthisMonth') }}</small>
                                                    </div>
                                                    <div class="col-12 pb-3">
                                                        <h5 class="m-0 text-danger">Rp
                                                            {{ number_format($billingThisYear, 0, ',', '.') }}</h5>
                                                        <small>{{ __('admin.calendarthisYear') }}</small>
                                                    </div>
                                                    <div class="col-12">
                                                        <h5 class="m-0">Rp
                                                            {{ number_format($billingAllTime, 0, ',', '.') }}
                                                        </h5>
                                                        <small>{{ __('admin.calendaralltime') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="col-lg-4 col-md-12">
                                        <div class="card p-3">
                                            <h4 class="card-title mb-3">{{ __('admin.homeautomationoverview') }}</h4>
                                            <hr class="p-0 mt-2" />
                                            <div class="table-responsive thin-scrollbar mt-4 border rounded">
                                                <table class="table table-hover mb-0 table-centered table-nowrap">
                                                    <tbody>
                                                        @php
                                                            $automationItems = [
                                                                'Invoice Created' => 'spak-chart1',
                                                                'Overdue Suspensions' => 'spak-chart2',
                                                                'Overdue Reminders' => 'spak-chart3',
                                                                'Credit Card Captures' => 'spak-chart4',
                                                                'Inactive Ticket Closed' => 'spak-chart5',
                                                                'Cancellations Processed' => 'spak-chart6',
                                                            ];
                                                        @endphp

                                                        @foreach ($automationItems as $label => $chartId)
                                                            <tr>
                                                                <td>
                                                                    <p class="font-size-12 mb-0">{{ $label }}</p>
                                                                </td>
                                                                <td>
                                                                    <div id="{{ $chartId }}"></div>
                                                                </td>
                                                                <td>
                                                                    <h3>0</h3>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div> --}}

                                    </div>
                                </div>

                                <section class="section-3">
                                    <div class="row">

                                        <div class="col-md-8 col-12">
                                            <div class="card p-3">
                                                <h4 class="card-title">Support</h4>
                                                <hr class="p-0 mt-2" />
                                                <div class="row supp-section mb-3">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <i class="ri-price-tag-3-fill mr-3"
                                                                style="color: #77cacd; font-size: 24px;"></i>
                                                            <div>
                                                                <p class="mb-0">Awaiting Reply</p>
                                                                <p class="mb-0">
                                                                    <span
                                                                        style="color: #77cacd; font-size: 20px;">{{ $getSupportAwaitingReply ?? '' }}</span>
                                                                    Tickets
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <i class="ri-flag-2-fill mr-3"
                                                                style="color: #ef7895; font-size: 24px;"></i>
                                                            <div>
                                                                <p class="mb-0">Assigned To You</p>
                                                                <p class="mb-0">
                                                                    <span
                                                                        style="color: #ef7895; font-size: 20px;">{{ $getSupportAssignedToYou ?? '' }}</span>
                                                                    Tickets
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover w-100">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Ticket ID</th>
                                                                <th scope="col">Name</th>
                                                                <th scope="col">Email</th>
                                                                <th scope="col">Quick Reply</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($tickets as $ticket)
                                                                <tr>
                                                                    <th scope="row">#{{ $ticket->tid }}</th>
                                                                    <td>{{ $ticket->name }}</td>
                                                                    <td>{{ $ticket->email }}</td>
                                                                    <td>
                                                                        <a href="{{ url('admin/support/supporttickets/' . $ticket->id . '/view') }}"
                                                                            class="btn btn-primary btn-sm">
                                                                            <i class="ri-reply-fill"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <hr class="p-0 m-1" />
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <div
                                                            class="option-tickets d-flex justify-content-between text-center">
                                                            <a class="mr-2"
                                                                href="{{ url('admin/support/supporttickets') }}">View All
                                                                Tickets</a>
                                                            <a class="mr-2"
                                                                href="{{ url('admin/support/supporttickets?status[]=open') }}">View
                                                                My Tickets</a>
                                                            <a class="mr-2"
                                                                href="{{ url('admin/support/opennewtickets') }}">Open New
                                                                Tickets</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 col-12">
                                            <div class="card p-3">
                                                <h4 class="card-title">Staff Online</h4>
                                                <hr class="p-0 mt-2" />
                                                <div class="profile-online" style="max-height: 300px; overflow-y: auto;">
                                                    <div class="row">
                                                        @foreach ($adminList as $admin)
                                                            <div
                                                                class="col-12 col-sm-6 col-lg-4 d-flex flex-column align-items-center mb-3">
                                                                <i class="mdi mdi-account" style="font-size: 2rem;"></i>
                                                                <h6 class="text-center mt-2">{{ $admin['name'] }}</h6>
                                                                <small
                                                                    class="{{ $admin['status'] == 'Online' ? 'text-success' : 'text-danger' }}">
                                                                    {{ $admin['status'] }}
                                                                </small>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </section>

                                <section class="section-4">
                                    <div class="row">

                                        <div class="col-lg-6">
                                            <div class="card p-3">
                                                <h4 class="card-title">Client Activity</h4>
                                                <hr class="p-0 mt-2" />
                                                <div class="mb-5 mb-lg-0">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="client-activity">
                                                                <div class="d-lg-flex align-items-center">
                                                                    <i class="ri-user-fill mr-3"
                                                                        style="color: #eaae88; font-size: 2rem;"></i>
                                                                    <div>
                                                                        <p class="mb-0">Active Clients</p>
                                                                        <p class="mb-0">
                                                                            <span
                                                                                style="color: #eaae88; font-size: 20px;">{{ $activeClientsCount }}</span>
                                                                            Active
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="user-activity">
                                                                <div class="d-lg-flex align-items-center">
                                                                    <i class="ri-emotion-happy-fill mr-3"
                                                                        style="color: #3fa72a; font-size: 2rem;"></i>
                                                                    <div>
                                                                        <p class="mb-0">Users Online</p>
                                                                        <p class="mb-0">
                                                                            <span
                                                                                style="color: #3fa72a; font-size: 20px;">{{ $usersOnlineCount }}</span>
                                                                            Last Hour
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="table-scroll thin-scrollbar" id="tabs-bar"
                                                            style="max-height: 200px; overflow-y: auto;">
                                                            <table class="table table-sm table-striped bg-white">
                                                                <thead>
                                                                    <tr>
                                                                        {{-- <th scope="col">#</th> --}}
                                                                        <th scope="col" class="text-center">Name</th>
                                                                        <th scope="col" class="text-center">Last Login
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @php
                                                                        $onlineClients = $clients->filter(function (
                                                                            $client
                                                                        ) {
                                                                            return $client->recentlyLoggedIn;
                                                                        });
                                                                    @endphp

                                                                    @if ($onlineClients->isEmpty())
                                                                        <tr>
                                                                            <td colspan="3" class="text-center">No
                                                                                clients logged in in the last hour</td>
                                                                        </tr>
                                                                    @else
                                                                        @foreach ($onlineClients as $client)
                                                                            <tr class="table-success">
                                                                                {{-- <th scope="row">{{ $client->id }}</th> --}}
                                                                                <td>
                                                                                    {{ $client->name }}
                                                                                    <br>
                                                                                    <small>{{ $client->ip }}</small>
                                                                                </td>
                                                                                <td>
                                                                                    <small
                                                                                        class="float-right">{{ $client->lastloggin }}</small>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="card p-3">
                                                <h4 class="card-title">Activity</h4>
                                                <hr class="p-0 mt-2" />
                                                <div class="activity-list thin-scrollbar" id="activity-bar">
                                                    @foreach ($activities as $activity)
                                                        <div class="activity-item border-bottom">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <h6>{{ $activity->firstname }}
                                                                        {{ $activity->lastname }}
                                                                    </h6>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small
                                                                        class="float-right">{{ $activity->date }}</small>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    {{ $activity->description }}
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <small>{{ $activity->ipaddr }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="col-lg-6">
                                        <div class="card p-3">
                                            <h4 class="card-title">To-Do List</h4>
                                            <hr class="p-0 mt-2" />
                                            <div class="todo-wrap thin-scrollbar" id="todo-bar">
                                                @php
                                                    $todoItems = [
                                                        'Manual Domain Registration',
                                                        'Update Website Content',
                                                        'Review Security Logs',
                                                        'Backup Database',
                                                        'Prepare Monthly Report',
                                                    ];
                                                @endphp

                                                @foreach ($todoItems as $index => $description)
                                                    <div class="todo-item col-12 mb-3">
                                                        <div class="d-flex justify-content-between align-items-center">

                                                            <div class="d-flex align-items-center">
                                                                <i class="mdi mdi-account-circle mr-2"></i>
                                                                <span>Admin</span>
                                                            </div>

                                                            <div class="d-flex align-items-center">
                                                                <p class="text-right months mb-0">Due 6 months ago</p>

                                                            </div>
                                                        </div>
                                                        <div class="form-group row mt-2">
                                                            <div class="col-sm-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="gridCheck{{ $index }}" />
                                                                    <label class="form-check-label w-100"
                                                                        for="gridCheck{{ $index }}">
                                                                        <input type="text"
                                                                            class="form-control todo-description mb-2"
                                                                            value="{{ $description }}" />
                                                                        <span
                                                                            class="badge badge-warning status-badge">Pending</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div> --}}

                                    </div>
                                </section>

                            </section>
                        </div>
                        <!-- END MAIN -->
                </div> <!-- END ROW -->
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
    <!-- Simplebar init -->
    <script src="{{ Theme::asset('assets/js/simplebarexec.js') }}"></script>
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
    <!-- apexcharts -->
    <script src="{{ Theme::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <!-- swiper slide -->
    <script src="{{ Theme::asset('assets/libs/swiper-slider/swiper-bundle.min.js') }}"></script>
    <!-- apexcharts init -->
    <script src="{{ Theme::asset('assets/js/pages/apexcharts.init.js') }}"></script>
    <!-- Required datatable js -->
    <script src="{{ Theme::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Responsive examples -->
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ Theme::asset('assets/js/pages/dashboard.init.js') }}"></script>

    <script>
        setInterval(() => {
            fetch('/admin/dashboard/get-pending-order-count')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('pendingOrderCount').innerText = data.pendingOrderCount;
                });
        }, 300000); // Memeriksa setiap 5 menit
    </script>

    <script>
        setInterval(() => {
            fetch('/admin/dashboard/get-support-awaiting-reply')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('supportAwaitingReplyCount').innerText = data
                        .supportAwaitingReplyCount;
                });
        }, 300000); // Memeriksa setiap 5 menit
    </script>

    <script>
        setInterval(() => {
            fetch('/admin/dashboard/get-cancellation-request')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('cancellationRequestCount').innerText = data
                        .cancellationRequestCount;
                });
        }, 300000); // Memeriksa setiap 5 menit
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize chart
            var options = {
                series: [{
                    name: 'Order (All Status)',
                    data: [{{ $orderAllStatusDataString }}],
                    color: '#808080' // Grey for all orders
                }, {
                    name: 'Order (Active Status)',
                    data: [{{ $orderActiveStatusDataString }}],
                    color: '#0000FF' // Blue for active orders
                }, {
                    name: 'Total Paid Invoices',
                    data: [{{ $totalPaidInvoicesDataString }}],
                    color: '#008000' // Green for paid invoices
                }],
                chart: {
                    height: 350,
                    type: 'area'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    categories: {!! json_encode($months) !!}
                },
                tooltip: {
                    x: {
                        format: 'MM/yyyy'
                    },
                },
            };

            var chart = new ApexCharts(document.querySelector("#spline_area"), options);
            chart.render();

            // Function to handle button click and toggle active class
            function setActiveButton(activeButtonId) {
                const buttons = ['btnLastYear', 'btnLastMonth', 'btnToday'];
                buttons.forEach(buttonId => {
                    const button = document.getElementById(buttonId);
                    if (buttonId === activeButtonId) {
                        button.classList.add('btn-primary');
                        button.classList.remove('btn-secondary');
                    } else {
                        button.classList.add('btn-secondary');
                        button.classList.remove('btn-primary');
                    }
                });
            }

            // Function to update chart data
            function updateChartData(allStatusData, activeStatusData, paidInvoicesData, categories) {
                chart.updateOptions({
                    xaxis: {
                        categories: categories
                    }
                });
                chart.updateSeries([{
                    name: 'Order (All Status)',
                    data: allStatusData
                }, {
                    name: 'Order (Active Status)',
                    data: activeStatusData
                }, {
                    name: 'Total Paid Invoices',
                    data: paidInvoicesData
                }]);
            }

            // Initial active button and chart data
            setActiveButton('btnLastYear');
            updateChartData(
                [{{ $orderAllStatusDataString }}],
                [{{ $orderActiveStatusDataString }}],
                [{{ $totalPaidInvoicesDataString }}],
                {!! json_encode($months) !!}
            );

            // Button click event listeners
            document.getElementById('btnLastYear').addEventListener('click', function() {
                setActiveButton('btnLastYear');
                updateChartData(
                    [{{ $orderAllStatusDataString }}],
                    [{{ $orderActiveStatusDataString }}],
                    [{{ $totalPaidInvoicesDataString }}],
                    {!! json_encode($months) !!}
                );
            });

            document.getElementById('btnLastMonth').addEventListener('click', function() {
                setActiveButton('btnLastMonth');
                updateChartData(
                    JSON.parse('{{ $orderAllStatusLastMonth }}'),
                    JSON.parse('{{ $orderActiveStatusLastMonth }}'),
                    JSON.parse('{{ $totalPaidInvoicesLastMonth }}'),
                    {!! json_encode($days) !!}
                );
            });

            document.getElementById('btnToday').addEventListener('click', function() {
                setActiveButton('btnToday');
                updateChartData(
                    JSON.parse('{{ $orderAllStatusToday }}'),
                    JSON.parse('{{ $orderActiveStatusToday }}'),
                    JSON.parse('{{ $totalPaidInvoicesToday }}'),
                    Array.from({
                        length: 24
                    }, (_, i) => i + ':00')
                );
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.form-check-input');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const badge = this.closest('.form-check').querySelector('.status-badge');
                    if (this.checked) {
                        badge.classList.remove('badge-warning');
                        badge.classList.add('badge-success');
                        badge.textContent = 'Completed';
                    } else {
                        badge.classList.remove('badge-success');
                        badge.classList.add('badge-warning');
                        badge.textContent = 'Pending';
                    }
                });
            });

            const descriptions = document.querySelectorAll('.todo-description');
            descriptions.forEach(input => {
                input.addEventListener('change', function() {
                    // Handle the change event if you want to save the updated text
                    console.log('Updated description:', this.value);
                });
            });
        });
    </script>
@endsection
