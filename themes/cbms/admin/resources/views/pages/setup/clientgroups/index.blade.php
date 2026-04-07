@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Client Groups</title>
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
                                        <h4 class="mb-3">Client Groups</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Cum eos ea est, magnam
                                            eveniet reprehenderit id libero dolorum assumenda impedit, neque, aspernatur
                                            officia fuga amet ab molestias doloremque obcaecati ut.</p>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                {!!$infobox!!}
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Group Name</th>
                                                                <th>Group Colour</th>
                                                                <th>% Discount</th>
                                                                <th>Suspend/Terminate Exempt</th>
                                                                <th>Separate Invoices</th>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($tabledata as $item)
                                                                <tr>
                                                                    <td>{!!$item[0]!!}</td>
                                                                    <td>{!!$item[1]!!}</td>
                                                                    <td>{!!$item[2]!!}</td>
                                                                    <td>{!!$item[3]!!}</td>
                                                                    <td>{!!$item[4]!!}</td>
                                                                    <td>{!!$item[5]!!}</td>
                                                                    <td>{!!$item[6]!!}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <form action="{{request()->url()}}?action={{$setaction}}" method="post">
                                            @csrf
                                            <input type="hidden" name="groupid" value="{{$id}}">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h4 class="card-title mb-3">Add Client Group</h4>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Group Name</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input type="text" name="groupname" value="{{$groupname}}" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2 col-form-label">Group
                                                            Colour</label>
                                                        <div class="col-sm-12 col-lg-2">
                                                            <div class="input-group colorpicker-default"
                                                                title="Using format option">
                                                                <input type="text" class="form-control input-lg" name="groupcolour"
                                                                    value="{{$groupcolour}}" />
                                                                <span class="input-group-append">
                                                                    <span
                                                                        class="input-group-text colorpicker-input-addon"><i></i></span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-lg-2 col-form-label">Group Discount %</label>
                                                        <div class="col-sm-12 col-lg-5">
                                                            <input type="text" name="discountpercent" value="{{$discountpercent}}" class="form-control w-25">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="includeInAwaitingReply" class="col-sm-12 col-lg-2 col-form-label">Exempt from Suspend & Terminate</label>
                                                        <div class="col-sm-12 col-lg-2 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input name="susptermexempt" {{$susptermexempt ? 'checked' : ''}} type="checkbox" class="custom-control-input"
                                                                    id="includeInAwaitingReply">
                                                                <label class="custom-control-label"
                                                                    for="includeInAwaitingReply"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="autoClose" class="col-sm-12 col-lg-2 col-form-label">Separate Invoices for Services</label>
                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input name="separateinvoices" {{$separateinvoices ? 'checked' : ''}} type="checkbox" class="custom-control-input"
                                                                    id="autoClose">
                                                                <label class="custom-control-label" for="autoClose"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2 col-form-label">Include in
                                                            Active Tickets</label>
                                                        <div class="col-sm-12 col-lg-2 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="includeActiveTickets">
                                                                <label class="custom-control-label"
                                                                    for="includeActiveTickets"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2 col-form-label">Include in
                                                            Awaiting Reply</label>
                                                        <div class="col-sm-12 col-lg-2 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="includeInAwaitingReply">
                                                                <label class="custom-control-label"
                                                                    for="includeInAwaitingReply"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label for="" class="col-sm-12 col-lg-2 col-form-label">Auto
                                                            Close?</label>
                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="autoClose">
                                                                <label class="custom-control-label" for="autoClose"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-12 col-lg-2 col-form-label">Sort Order</div>
                                                        <div class="col-sm-12 col-lg-5 pt-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    id="sortOrder">
                                                                <label class="custom-control-label" for="sortOrder"></label>
                                                            </div>
                                                        </div>
                                                    </div> --}}
                                                </div>
                                                <div class="col-lg-12 text-center">
                                                    <button class="btn btn-success px-3">Save Changes</button>
                                                </div>
                                            </div>
                                        </form>
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
    <script src="{{ Theme::asset('assets/js/colorpicker.js') }}"></script>
    <script>
        {!!$jscode!!}
    </script>
@endsection
