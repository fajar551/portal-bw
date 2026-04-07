@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Mass Mail</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h4>Mass Mail</h4>
                                    <div class="card p-3">
                                        <form action="{{ route('admin.pages.clients.massmail.sendmessage') }}" method="POST" enctype="multipart/form-data" autocapitalize="off">
                                            @csrf
                                            <input type="hidden" name="type" value="massmail">
                                            <div class="row">
                                                <div class="col-12">
                                                    @if (session('message'))
                                                        <div class="alert alert-{{ session('type') }}">
                                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                                            <strong>{!! session('message') !!}</strong>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <p>
                                                        This mass mail tool allows you to send emails to selective groups of
                                                        your clients. The type of email you choose to send will determine
                                                        what merge fields you can include within it. For example, sending a
                                                        product/service related email allows you to include product specific
                                                        items like domain, usernames, server, next due date, etc... Use
                                                        Ctrl+Click to make multiple selections
                                                    </p>
                                                </div>
                                                <div class="col-lg-12">
                                                    <h5>Message Type</h5>
                                                    <div class="form-group row">
                                                        <label for="withdrawn" class="col-sm-2 col-form-label">Email Type</label>
                                                        <div class="col-sm-4 d-flex align-items-center">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="emailtype" id="typegen" value="General">
                                                                <label class="form-check-label" for="typegen">General</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="emailtype" id="typeprod" value="Product/Service">
                                                                <label class="form-check-label" for="typeprod">Product/Service</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="emailtype" id="typedom" value="Domain">
                                                                <label class="form-check-label" for="typedom">Domain</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="collapse" id="generalRadio">
                                                        <h4 class="card-title">
                                                            Client Criteria
                                                        </h4>
                                                        <div class="card p-3">
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Client Group</label>
                                                                <div class="col-sm-12 col-lg-10">
                                                                    <select name="clientgroup[]" size="4" class="select2 form-control select2-multiple " multiple="multiple" data-placeholder="Choose one or more...">
                                                                        @foreach ($clientgroups as $groupid => $data)
                                                                            <option value="{{ $groupid }}"> {{ $data["name"] }} </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            @foreach ($customfields as $customfield)
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">{!! $customfield["name"] !!}</label>
                                                                @php
                                                                    $input = "";
                                                                    if ($customfield["type"] == "tickbox") {
                                                                        // $input = "<input type=\"radio\" name=\"customfield[" . $customfield["id"] . "]\" value=\"\" checked /> No Filter <input type=\"radio\" name=\"customfield[" . $customfield["id"] . "]\" value=\"cfon\" /> Checked Only <input type=\"radio\" name=\"customfield[" . $customfield["id"] . "]\" value=\"cfoff\" /> Unchecked Only";
                                                                        $input .= "<div class=\"form-check form-check-inline\">
                                                                                    <input class=\"form-check-input\" type=\"radio\" name=\"customfield[" . $customfield["id"] . "]\" id=\"nf\" value=\"\">
                                                                                    <label class=\"form-check-label\" for=\"nf\">No Filter</label>
                                                                                </div>";
                                                                        $input .= "<div class=\"form-check form-check-inline\">
                                                                                    <input class=\"form-check-input\" type=\"radio\" name=\"customfield[" . $customfield["id"] . "]\" id=\"cfon\" value=\"cfon\">
                                                                                    <label class=\"form-check-label\" for=\"cfon\"> Checked Only</label>
                                                                                </div>";
                                                                        $input .= "<div class=\"form-check form-check-inline\">
                                                                                    <input class=\"form-check-input\" type=\"radio\" name=\"customfield[" . $customfield["id"] . "]\" id=\"cfoff\" value=\"cfoff\">
                                                                                    <label class=\"form-check-label\" for=\"cfoff\"> Unchecked Only</label>
                                                                                </div>";
                                                                    } else {
                                                                        $input = str_replace("\"><option value=\"", "\"><option value=\"\">" . __("admin.any") . "</option><option value=\"", $customfield["input"]);
                                                                    }
                                                                @endphp
                                                                
                                                                <div class="col-sm-12 col-lg-10">
                                                                    {!! $input !!}
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Country</label>
                                                                <div class="col-sm-12 col-lg-10">
                                                                    <select name="clientcountry[]" size="4" multiple="true" class="select2 form-control select2-multiple " data-placeholder="Choose one or more...">
                                                                        @foreach ($countries as $countryCode => $country)
                                                                            <option value="{{ $countryCode }}" selected>{{ $country }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Languages</label>
                                                                <div class="col-sm-12 col-lg-10">
                                                                    <select name="clientlanguage[]" size="4" multiple="true" class="select2 form-control select2-multiple " data-placeholder="Choose one or more...">
                                                                        @foreach ($languages as $langCode => $languages)
                                                                            <option value="{{ $langCode }}" selected>{{ $languages }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Client Status</label>
                                                                <div class="col-sm-12 col-lg-10">
                                                                    <select name="clientstatus[]" size="4" multiple="true" class="select2 form-control select2-multiple " data-placeholder="Choose one or more...">
                                                                        <option value="Active" selected>Active</option>
                                                                        <option value="Inactive" selected>Inactive</option>
                                                                        <option value="Closed" selected>Closed</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="collapse" id="productServiceRadio">
                                                        <h4 class="card-title">Product/Service Criteria</h4>
                                                        <div class="card p-3">
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label"> Product/Service </label>
                                                                <div class="col-sm-12 col-lg-10">
                                                                    <select name="productids[]" size="10" multiple="true" class="select2 form-control select2-multiple " data-placeholder="Choose one or more...">
                                                                        @foreach ($productsList as $data) 
                                                                            <option value="{{ $data["id"] }}">{{ "{$data["groupname"]} - {$data["name"]}" }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Product/Service Status</label>
                                                                <div class="col-sm-12 col-lg-10">
                                                                    <select name="productstatus[]" size="4" multiple="true" class="select2 form-control select2-multiple"  data-placeholder="Choose one or more...">
                                                                        <option value="Pending">Pending</option>
                                                                        <option value="Active">Active</option>
                                                                        <option value="Suspended">Suspended</option>
                                                                        <option value="Terminated">Terminated</option>
                                                                        <option value="Cancelled">Cancelled</option>
                                                                        <option value="Fraud">Fraud</option>
                                                                        <option value="Completed">Completed</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label">Assigned Server</label>
                                                                <div class="col-sm-12 col-lg-10">
                                                                    <select name="server[]" size="4" multiple="true" class="select2 form-control select2-multiple " data-placeholder="Choose one or more...">
                                                                        @foreach ($serverList as $data) 
                                                                            <option value="{{ $data["id"] }}">{{ "{$data["name"]}" }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label class="col-sm-12 col-lg-2 col-form-label"> Send for Each Domain </label>
                                                                <div class="col-sm-12 col-lg-10">
                                                                    <div class="custom-control custom-checkbox mt-2">
                                                                        <input type="checkbox" name="sendforeach" class="custom-control-input" id="sendforeach">
                                                                        <label class="custom-control-label" for="sendforeach">Tick this box to send an email for every matching domain *</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="collapse" id="domainRadio">
                                                    <h4 class="card-title">Domain Criteria</h4>
                                                    <div class="card p-3">
                                                        <div class="form-group row">
                                                            <label class="col-sm-12 col-lg-2 col-form-label">Domain Status</label>
                                                            <div class="col-sm-12 col-lg-10">
                                                                <select name="domainstatus[]" size="5" multiple="true" class="select2 form-control select2-multiple " data-placeholder="Choose one or more...">
                                                                    {!! $domainStatuses !!}
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 d-flex justify-content-center">
                                                <button type="submit" class="btn btn-primary px-5">Compose Message</button>
                                            </div>
                                            <div class="col-lg-12">
                                                <p class="mt-3">
                                                    * By default, a customer will receive only one copy of the mailing
                                                    containing merge data for the first matching product found in their account.
                                                    However, ticking this box will mean an email is sent for each item that
                                                    matches the given criteria and therefore a single client may receive the email
                                                    multiple times - once for each qualifying product they have.
                                                </p>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>

        $(() => {
            $("input[name='emailtype']").click(function() {
                let checked = $("input[name='emailtype']:checked").val();
                $("#generalRadio").collapse("show");
                if (checked == "General") {
                    $("#productServiceRadio").collapse("hide");
                    $("#domainRadio").collapse("hide");
                } else if (checked == "Product/Service") {
                    $("#productServiceRadio").collapse("show");
                    $("#domainRadio").collapse("hide");
                } else if (checked == "Domain") {
                    $("#productServiceRadio").collapse("hide");
                    $("#domainRadio").collapse("show");
                }
            });
        });
        
    </script>
@endsection
