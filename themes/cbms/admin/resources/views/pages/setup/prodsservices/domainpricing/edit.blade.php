@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Edit Domain Pricing</title>
@endsection

@section('styles')
    <style>
        table tr.domain-pricing-row td {
            height: 43px!important;
        }
        table th.domain-pricing-head {
            width: 130px;
            min-width: 80px;
        }
    </style>
@endsection

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="view-client-wrapper">
                        <form action="?id={{$id}}&selectedcugroupid={{$selectedcugroupid}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">
                                            Domain Pricing for {{$extension}}
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <p>
                                        Check the Enable box to enable the pricing for that currency and term. Set Transfer/Renew pricing to -1 to disable transfers and renewals for that term.
                                    </p>
                                </div>
                                <div class="col-12">
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
                                </div>
                                <div class="col-12">
                                    @if (!$noslabpricing)
                                        <table class=" table-hover w-50">
                                            <tr class="bg-primary text-white">
                                                <th></th>
                                                <th>Currency</th>
                                                <th>Enable</th>
                                                <th class="domain-pricing-head">Register</th>
                                                <th class="domain-pricing-head">Transfer</th>
                                                <th class="domain-pricing-head">Renewal</th>
                                            </tr>
                                            @php
                                                $years = 1;
                                            @endphp
                                            @while ($years <= 10)
                                                <tr class="domain-pricing-row border-bottom" >
                                                    <td rowspan="{{$totalcurrencies}}" class="text-center align-middle bg-light border-bottom" style="border-bottom-color: #ccc!important;">
                                                        <strong class="font-weight-bold">{{$years}} Years</strong>
                                                    </td>
                                                    @php
                                                        $i = 0;
                                                    @endphp
                                                    @foreach ($currenciesarray as $curr_id => $curr_code)
                                                        @php
                                                            $result2_baseslab = \App\Models\Pricing::where(array("type" => "domainregister", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id));
                                                            $regdata_baseslab = $result2_baseslab;
                                                            $register[$selectedcugroupid][$curr_id] = array(1 => $regdata_baseslab->value("msetupfee"), 2 => $regdata_baseslab->value("qsetupfee"), 3 => $regdata_baseslab->value("ssetupfee"), 4 => $regdata_baseslab->value("asetupfee"), 5 => $regdata_baseslab->value("bsetupfee"), 6 => $regdata_baseslab->value("monthly"), 7 => $regdata_baseslab->value("quarterly"), 8 => $regdata_baseslab->value("semiannually"), 9 => $regdata_baseslab->value("annually"), 10 => $regdata_baseslab->value("biennially"));
                                                            $transresult2_baseslab = \App\Models\Pricing::where(array("type" => "domaintransfer", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id));
                                                            $transdata_baseslab = $transresult2_baseslab;
                                                            $transfer[$selectedcugroupid][$curr_id] = array(1 => $transdata_baseslab->value("msetupfee"), 2 => $transdata_baseslab->value("qsetupfee"), 3 => $transdata_baseslab->value("ssetupfee"), 4 => $transdata_baseslab->value("asetupfee"), 5 => $transdata_baseslab->value("bsetupfee"), 6 => $transdata_baseslab->value("monthly"), 7 => $transdata_baseslab->value("quarterly"), 8 => $transdata_baseslab->value("semiannually"), 9 => $transdata_baseslab->value("annually"), 10 => $transdata_baseslab->value("biennially"));
                                                            $result2_baseslab = \App\Models\Pricing::where(array("type" => "domainrenew", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id));
                                                            $rendata_baseslab = $result2_baseslab;
                                                            $renew[$selectedcugroupid][$curr_id] = array(1 => $rendata_baseslab->value("msetupfee"), 2 => $rendata_baseslab->value("qsetupfee"), 3 => $rendata_baseslab->value("ssetupfee"), 4 => $rendata_baseslab->value("asetupfee"), 5 => $rendata_baseslab->value("bsetupfee"), 6 => $rendata_baseslab->value("monthly"), 7 => $rendata_baseslab->value("quarterly"), 8 => $rendata_baseslab->value("semiannually"), 9 => $rendata_baseslab->value("annually"), 10 => $rendata_baseslab->value("biennially"));
                                                        @endphp
                                                        @if (0 < $i)
                                                            </tr><tr class="domain-pricing-row border-bottom" style="border-bottom-color: #ccc!important;">
                                                        @endif
                                                        @php
                                                            $enableName = "enable[" . $selectedcugroupid . "][" . $curr_id . "][" . $years . "]";
                                                            $registerName = "register[" . $selectedcugroupid . "][" . $curr_id . "][" . $years . "]";
                                                            $registerValue = $register[$selectedcugroupid][$curr_id][$years];
                                                            $transferName = "transfer[" . $selectedcugroupid . "][" . $curr_id . "][" . $years . "]";
                                                            $transferValue = $transfer[$selectedcugroupid][$curr_id][$years];
                                                            $renewName = "renew[" . $selectedcugroupid . "][" . $curr_id . "][" . $years . "]";
                                                            $renewValue = $renew[$selectedcugroupid][$curr_id][$years];
                                                            $toggleCheck = $register[$selectedcugroupid][$curr_id][$years] == "-1" ? "" : " checked=checked";
                                                            $toggleData = "[" . $selectedcugroupid . "][" . $curr_id . "][" . $years . "]";
                                                            $hideInput = $register[$selectedcugroupid][$curr_id][$years] == "-1" ? 'style=display:none;' : "";
                                                        @endphp
                                                        <td class="text-center align-middle">
                                                            {{$curr_code}}
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <input type="checkbox" name="{{$enableName}}" class="pricingToggle" data="{{$toggleData}}"{{$toggleCheck}} class="form-control" />
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <input type="text" name="{{$registerName}}" value="{{$registerValue}}" size="10"{{$hideInput}} class="form-control" />
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <input type="text" name="{{$transferName}}" value="{{$transferValue}}" size="10"{{$hideInput}} class="form-control" />
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <input type="text" name="{{$renewName}}" value="{{$renewValue}}" size="10"{{$hideInput}} class="form-control" />
                                                        </td>
                                                        @php
                                                            $i++;
                                                        @endphp
                                                    @endforeach
                                                </tr>
                                                @php
                                                    $years += 1;
                                                @endphp
                                            @endwhile
                                        </table>
                                    @endif
                                </div>
                                <div class="col-12 text-center pt-3">
                                    <button type="submit" class="btn btn-primary">Save Change</button>
                                    <button onclick="window.close()" class="btn btn-light">Close Window</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(function() {
            $("body").addClass("sidebar-enable vertical-collpsed");

            $(".pricingToggle").click(function() {
                var data = $(this).attr("data");

                if ($(this).is(":checked")) {
                    $("input[name='register" + data + "']").val("0.00").show();
                    $("input[name='transfer" + data + "']").val("0.00").show();
                    $("input[name='renew" + data + "']").val("0.00").show();
                } else {
                    $("input[name='register" + data + "']").val("-1.00").hide();
                    $("input[name='transfer" + data + "']").val("-1.00").hide();
                    $("input[name='renew" + data + "']").val("-1.00").hide();
                }
            });
        });
    </script>
@endsection
