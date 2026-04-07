@extends('layouts.clientbase')

@section('title')
    Dashboard
@endsection

@section('page-title')
    Welcome, {{ ucfirst($user->firstname) }}
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="pb-3">
                <h5 class="header-pretitle d-md-block font-weight-bold">Dashboards</h5>
            </div>
            <div class="row mb-1 mb-md-0">
                <div class="col-12 col-md-8">
                    <div class="row">
                        <div class="col-6 col-md-6">
                            <div class="card" id="user-statistic">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <h6 class="text-uppercase font-size-12 text-muted mb-3">Services</h6>
                                            <span class="h2 counter-number font-weight-bold mb-0"> {{ $getServices->count() ?? 0 }}
                                            </span>
                                        </div>
                                        <div class="col-md-5 text-right">
                                            <div class="col-auto ic-card">
                                                <i class="feather-server"></i>
                                            </div>
                                        </div>
                                    </div> <!-- end row -->

                                    <div id="sparkline1" class="mt-3"></div>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <!--<div class="col-6 col-md-6">-->
                        <!--    <div class="card" id="user-statistic">-->
                        <!--        <div class="card-body">-->
                        <!--            <div class="row align-items-center">-->
                        <!--                <div class="col-md-7">-->
                        <!--                    <h6 class="text-uppercase font-size-12 text-muted mb-3">Domain</h6>-->
                        <!--                    <span class="h2 counter-number font-weight-bold mb-0 counter" data-count="{{ $getDomain->count() }}">-->
                        <!--                        {{ $getDomain->count() ?? 0 }} </span>-->
                        <!--                </div>-->
                        <!--                <div class="col-md-5 text-right">-->
                        <!--                    <div class="col-auto ic-card">-->
                        <!--                        <i class="feather-globe"></i>-->
                        <!--                    </div>-->
                        <!--                </div>-->
                        <!--            </div> <!-- end row -->

                        <!--            <div id="sparkline1" class="mt-3"></div>-->
                        <!--        </div> <!-- end card-body-->
                        <!--    </div>-->
                        <!--</div> <!-- end col-->

                        <div class="col-6 col-md-6">
                            <div class="card" id="user-statistic">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <h6 class="text-uppercase font-size-12 text-muted mb-3">Tickets</h6>
                                            <span class="h2 counter-number font-weight-bold mb-0"> {{ $getTicket->count() ?? 0 }} </span>
                                        </div>
                                        <div class="col-md-5 text-right">
                                            <div class="col-auto ic-card">
                                                <i class="feather-mail"></i>
                                            </div>
                                        </div>
                                    </div> <!-- end row -->

                                    <div id="sparkline1" class="mt-3"></div>
                                </div> <!-- end card-body-->
                            </div>
                        </div> <!-- end col-->

                        <div class="col-6 col-md-6">
                            <div class="card" id="user-statistic">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <h6 class="text-uppercase font-size-12 text-muted mb-3">Invoices</h6>
                                            <span class="h2 counter-number font-weight-bold counter mb-0" data-count="{!! $getInvoice->count() !!}">
                                                {{ $getInvoice->count() ?? 0 }}</span>
                                        </div>
                                        <div class="col-md-5 text-right">
                                            <div class="col-auto ic-card">
                                                <i class="feather-file-text"></i>
                                            </div>
                                        </div>
                                    </div> <!-- end row -->

                                    <div id="sparkline1" class="mt-3"></div>
                                </div> <!-- end card-body-->
                            </div>
                        </div> <!-- end col-->
                    </div>
                </div>
                <div class="col-12 col-md-4 pb-4">
                    <div class="card" id="user-deposit">
                        <div class="card-body">
                            <div class="row align-items-center justify-content-center">
                                <div class="col-12">
                                    <h6 class="text-uppercase font-size-12 text-muted">
                                        <img src=" {{ Theme::asset('assets/images/icons/ic_coin.svg') }} " alt=" Header Language" height="16">
                                        My
                                        Deposit
                                    </h6>

                                </div>
                                <div class="col-12">
                                    <span class="h4 counter-number font-weight-bold mb-0">
                                        {{ $userCredit }}
                                    </span>
                                </div>
                                <div class="col-md-12 col-sm-12 text-lg-center">
                                    <hr>

                                </div>
                            </div>
                        </div> <!-- end row -->
                        <a href="{{ route('deposit.add') }}" style="border-top-left-radius:1px; border-top-right-radius:1px;" class="btn btn-success-qw btn-block waves-rippling"><i class="fas fa-wallet mr-2"></i>
                            Add
                            deposit </a>
                        {{-- <div id="sparkline4" class="mt-3"></div> --}}
                    </div>
                </div>
            </div> <!-- end col-->
        </div>
        <!-- end row-->
        <div class="row px-3">
            <div class="col-xl-12">
                <form action="/">
                    <input type="text" class="form-control" placeholder="Enter a question here to search our knowledgebase for answers" />
                </form>
            </div>
        </div>
        <div class="row p-3" id="details-card">
            <div class="col-xl-12 col-lg-12">
                <div class="row">
                    <div class="col-xl-6 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title font-weight-bold mb-3">
                                    {{ __('client.clientHomePanelsactiveProductsServices') }}</h4>
                                @php
                                    $servicesDetailsActive = $getServices->where('status', 'Active')->toArray();
                                @endphp
                                @if ($servicesDetailsActive)
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($servicesDetailsActive as $service)
                                                <tr>
                                                    <td><a
                                                            href={{ route('pages.services.myservices.servicedetails', ['id' => $service['id']]) }}>{{ $service['id'] }}</a>
                                                    </td>
                                                    <td><a
                                                            href={{ route('pages.services.myservices.servicedetails', ['id' => $service['id']]) }}>{{ $service['name'] }}</a>
                                                    </td>
                                                    {{-- <td>{{ $service['domainstatus'] }}</td> --}}
                                                    <td>
                                                        @switch($service['domainstatus'])
                                                            @case('Active')
                                                                <div class="badge badge-success">{{ $service['domainstatus'] }}</div>
                                                            @break

                                                            @case('Pending')
                                                                <div class="badge badge-warning">{{ $service['domainstatus'] }}</div>
                                                            @break

                                                            @case('Suspended')
                                                                <div class="badge badge-danger">{{ $service['domainstatus'] }}</div>
                                                            @break

                                                            @case('Terminated')
                                                                <div class="badge badge-secondary">{{ $service['domainstatus'] }}</div>
                                                            @break

                                                            @case('Cancelled')
                                                                <div class="badge badge-info">{{ $service['domainstatus'] }}</div>
                                                            @break

                                                            @default
                                                                <div class="badge badge-dark">Unknown</div>
                                                        @endswitch
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-center card-empty-section">
                                        <h3 class="feather-inbox text-muted"></h3>
                                        <h5 class="text-muted">Empty</h5>
                                        <p class="text-muted">You haven't got any <strong>ACTIVE</strong> services</p>
                                        <a href="{{ route('pages.services.myservices.index') }}"
                                            class="btn btn-success-qw waves-rippling darkmode-ignore"><i class="feather-plus"></i>
                                            Order
                                            Services</a>
                                    </div>
                                @endif
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                    <!-- card domain was here -->
                    <div class="col-xl-6 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-1">
                                    <h4 class="card-title font-weight-bold mb-0">
                                        {{ __('client.clientHomePanelsrecentSupportTickets') }}</h4>
                                    @if ($ticketDetails)
                                        <a href="{{ route('pages.support.mytickets.index') }}" class="ml-auto">
                                            <button class="btn btn-sm btn-info" id="loadmore">More</button>
                                        </a>
                                    @endif
                                </div>
                                @if ($ticketDetails)
                                    <table class="table table-bordered table-sm" id="ticket-dash">
                                        <thead>
                                            <tr>
                                                <th scope="col">Ticket ID</th>
                                                <th scope="col">Title</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ticketDetails as $ticket)
                                                <tr>
                                                    <td style="width: 100px;"><a
                                                            href="{{ route('pages.support.mytickets.ticketdetails', ['tid' => $ticket['tid'], 'c' => $ticket['c']]) }}">{{ $ticket['tid'] }}</a>
                                                    </td>
                                                    <td><a
                                                            href="{{ route('pages.support.mytickets.ticketdetails', ['tid' => $ticket['tid'], 'c' => $ticket['c']]) }}">{{ $ticket['title'] }}</a>
                                                    </td>
                                                    <td @foreach ($getTicketStatus as $stats) @if ($stats->title === $ticket['status'])
                                             style="color: {{ $stats->color }}" @endif
                                                        @endforeach
                                                        >{{ $ticket['status'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-center card-empty-section">
                                        <h3 class="feather-inbox text-muted"></h3>
                                        <h5 class="text-muted">Empty</h5>
                                        <p class="text-muted">You haven't got any services</p>
                                        <a href="{{ route('pages.support.openticket.index') }}" class="btn btn-success-qw waves-rippling"><i class="feather-plus"></i> Create
                                            New
                                            Ticket</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div> <!-- end col-->
                </div>

                <div class="row">
                    
                    @php
                        $contactArray = $contacts->toArray();
                    @endphp
                    <div class="col-xl-6 col-lg-6 ">
                        <div class="card card-contact" id="contact-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <h4 class="card-title font-weight-bold">Contacts</h4>
                                    <a href="{{ route('pages.profile.contactsub.index') }}"
                                        class="ml-auto {{ empty($contactArray) ? 'd-none' : '' }}">
                                        <button class="btn btn-sm btn-info">More</button>
                                    </a>
                                </div>
                                @if (!empty($contactArray))
                                    <div class="row">
                                        @foreach ($contacts as $contact)
                                            <div class="card-link-contact col-sm-12 col-lg-6">
                                                <a href="{{ route('pages.profile.contactsub.details', $contact->id) }}">
                                                    <div class="contact-container d-flex">
                                                        <div class="d-inline-block mr-4" id="logo-contact-placeholder">
                                                            <i class="far fa-address-card"></i>
                                                        </div>
                                                        <div class="d-inline-block w-100">
                                                            <h5 class="counter-number">{{ $contact->firstname }}
                                                                {{ $contact->lastname }}</h5>
                                                            <hr>
                                                            <h6 class="counter-number">Company:
                                                                <span
                                                                    class="text-qw">{{ !$contact->companyname ? 'None' : "$contact->companyname" }}</span>
                                                            </h6>
                                                            <h6 class="counter-number">Phone: <span
                                                                    class="text-qw">{{ $contact->phonenumber }}</span></h6>
                                                            <h6 class="counter-number">Address: <span
                                                                    class="text-qw">{{ $contact->address1 }}</span> </h6>
                                                            <h6 class="counter-number">Email: <span
                                                                    class="text-qw">{{ $contact->email }}</span></h6>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center card-empty-section">
                                        <h3 class="feather-inbox text-muted"></h3>
                                        <h5 class="text-muted">No Contacts</h5>
                                        <p class="text-muted">You haven't any contact</p>
                                        <a href="{{ route('pages.profile.contactsub.index') }}" class="btn btn-success-qw waves-rippling"><i class="feather-plus"></i> Add
                                            Contact</a>
                                    </div>
                                @endif
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>

            </div>

            {{-- <div class="col-xl-3 col-lg-3">
            <div class="card card-promo" id="latest-promo">
               <div class="card-body">
                  <h4 class="card-title font-weight-bold mb-3">Latest Promo</h4>
                  <div class="media mb-3">
                     <img class="d-flex align-self-start rounded mr-3"
                        src="{{ Theme::asset('assets/images/media/thumb-promo.jpg') }}" alt="Generic placeholder image"
                        height="48">
                     <div class="media-body">
                        <h5 class="mt-0 mb-1">Lorem ipsum dolor sit.</h5>
                        <small class="text-muted">Mon, 7 June 2021</small>
                        <p class="mb-1 mt-1">Lorem ipsum dolor sit amet consectetur adipisicing elit. Ea dolorum
                           cumque assumenda. Consequuntur exercitationem, nam nulla amet distinctio ratione
                           repellendus deserunt? Unde voluptas enim cum reiciendis sed est. Corporis, deleniti?
                        </p>
                     </div>
                  </div>
                  <hr />
                  <div class="media mb-3">
                     <img class="d-flex align-self-start rounded mr-3"
                        src="{{ Theme::asset('assets/images/media/thumb-promo-2.jpg') }}"
                        alt="Generic placeholder image" height="48">
                     <div class="media-body">
                        <h5 class="mt-0 mb-1">Lorem ipsum dolor sit amet.</h5>
                        <small class="text-muted">Mon, 6 June 2021</small>
                        <p class="mb-1 mt-1">Lorem ipsum dolor sit amet consectetur adipisicing elit. Natus esse
                           provident molestias corrupti ipsa voluptas alias accusamus ex a nobis quasi harum
                           quibusdam, sed reprehenderit in porro, consequuntur libero laudantium!</p>
                     </div>
                  </div>
                  <hr />
                  <div class="media mb-3">
                     <img class="d-flex align-self-start rounded mr-3"
                        src="{{ Theme::asset('assets/images/media/thumb-promo.jpg') }}" alt="Generic placeholder image"
                        height="48">
                     <div class="media-body">
                        <h5 class="mt-0 mb-1">Lorem ipsum dolor sit amet.</h5>
                        <small class="text-muted">Mon, 5 June 2021</small>
                        <p class="mb-1 mt-1">Lorem ipsum dolor sit amet consectetur adipisicing elit. Optio
                           accusamus ex, dicta placeat reiciendis velit nisi expedita voluptas accusantium
                           tempore quos aut minima est sunt ab maxime fugiat. Nostrum, esse.</p>
                     </div>
                  </div>
               </div>
            </div>
         </div> --}}
        </div>
        <!-- end row-->

    </div> <!-- container-fluid -->
    </div>
@endsection

@section('scripts')
    <script src="{{ Theme::asset('assets/js/responsive-contact-card.js') }}"></script>
@endsection
