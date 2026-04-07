@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Support Overview</title>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                     


                    <div class="col-xl-12">
                        <div class="view-client-wrapper">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-title mb-3">
                                        <h4 class="mb-3">Support Overview</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card p-3">
                                        <div class="row">
                                            <div class="col-lg-2 d-flex align-items-center">
                                                <h6>Displaying Overview For:</h6>
                                            </div>
                                            <div class="col-lg-4">
                                                <select name="dispOverview" id="dispOverview" class="form-control">
                                                    <option value="today">Today</option>
                                                    <option value="yesterday">Yesterday</option>
                                                    <option value="ThisWeek">This Week</option>
                                                    <option value="ThisMonth">This Month</option>
                                                    <option value="LastMonth">Last Month</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-5 justify-content-center">
                                            <div class="col-lg-2 col-sm-12">
                                                <div id="panel-newTicket"  class="alert alert-warning" role="alert">
                                                    <h5>New Tickets</h5>
                                                    <h4>0</h4>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-sm-12">
                                                <div id="panel-Client-Replis" class="alert alert-warning">
                                                    <h5>Client Replies</h5>
                                                    <h4>0</h4>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-sm-12">
                                                <div id="panel-staffReplish" class="alert alert-warning">
                                                    <h5>Staff Replies</h5>
                                                    <h4>0</h4>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-sm-12">
                                                <div id="panel-TicketWithoutReplay" class="alert alert-warning">
                                                    <h6>Tickets Without Reply</h6>
                                                    <h4>0</h4>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-sm-12">
                                                <div id="panel-FirstResponse" class="alert alert-warning">
                                                    <h6>Average First Response</h6>
                                                    <h4>N/A</h4>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- garafik -->
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="card p-3">
                                                    <h4 class="card-title">Average First Reply Time</h4>
                                                    <div id="flotcontainer" class="flot-charts flot-charts-height">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="card p-3">
                                                    <h4 class="card-title">
                                                        Tickets Submitted by Hour
                                                    </h4>
                                                    <div id="flotRealTimeNEW" class="flot-charts flot-charts-height"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- garafik -->
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
    <script src="{{ Theme::asset('assets/libs/flot-charts/jquery.flot.js') }} "></script>
    <script src="{{ Theme::asset('assets/libs/flot-charts/jquery.flot.time.js') }} "></script>
    <script src="{{ Theme::asset('assets/libs/jquery.flot.tooltip/js/jquery.flot.tooltip.min.js') }} "></script>
    <script src="{{ Theme::asset('assets/libs/flot-charts/jquery.flot.resize.js') }} "></script>
    <script src="{{ Theme::asset('assets/libs/flot-charts/jquery.flot.pie.js') }} "></script>
    <script src="{{ Theme::asset('assets/libs/flot-charts/jquery.flot.selection.js') }} "></script>
    <script src="{{ Theme::asset('assets/libs/flot-charts/jquery.flot.stack.js') }} "></script>
    <script src="{{ Theme::asset('assets/libs/flot.curvedLines/curvedLines.js') }} "></script>
    <script src="{{ Theme::asset('assets/libs/flot-charts/jquery.flot.crosshair.js') }} "></script>
    <script src="{{ Theme::asset('assets/js/pages/support-overview.js') }} "></script>
    <script src="{{ Theme::asset('assets/js/app.js') }} "></script>
    <script type="text/javascript">
        var loadPanel=function(display='today'){
            $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url('admin/support/supportoverview') }}',
                    headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: { type:'panel', display: display },
                    success: function (data) {
                        $('#panel-newTicket h4').text(data.newTiket);
                        $('#panel-Client-Replis h4').text(data.clientReplies);
                        $('#staffReplies h4').text(data.staffReplies);
                        $('#panel-TicketWithoutReplay h4').text(data.ticketsWithoutReply);
                        $('#panel-FirstResponse h4').text(data.AverageFirstResponse);

                    }
                });
                return false;
            };
        
        var chart_pie=function(data){
            var data = data;
            var options = {
                    series: {
                        pie: {show: true}
                            }
                };

            $.plot($("#flotcontainer"), data, options).draw; 
        };


        var chartLINE=function(data=[]){
            /* var opt= {
				colors: '#1cbb8c',
				series: {
					lines: {
						show: !0,
						fill: !0,
						lineWidth: 2,
						fillColor: { colors: [{ opacity: 0.45 }, { opacity: 0.45 }] },
					},
					points: { show: !1 },
					shadowSize: 0,
				},
				grid: {
					show: !0,
					aboveData: !1,
					color: '#dcdcdc',
					labelMargin: 15,
					axisMargin: 0,
					borderWidth: 0,
					borderColor: null,
					minBorderMargin: 5,
					clickable: !0,
					hoverable: !0,
					autoHighlight: !1,
					mouseActiveRadius: 20,
				},
				tooltip: !0,
				tooltipOpts: {
					content: 'Value is : %y.0%',
					shifts: { x: -30, y: -50 },
				},
				yaxis: {
					min: 0,
					max: 100,
					tickColor: 'rgba(166, 176, 207, 0.1)',
					font: { color: '#8791af' },
				},
				xaxis: { show: !1 },
			}



            $.plot($('#flotRealTimeNEW'),data, opt).draw; */

          /*   var bar_data = {
                    data : [[0,10], [2,8], [3,4], [4,13], [5,17], [6,9]],
                    bars: { show: true }
                    } */
                    $.plot('#flotRealTimeNEW', [data], {
                    
                    grid  : {
                        show: !0,
                        aboveData: !1,
                        color: '#dcdcdc',
                        labelMargin: 15,
                        axisMargin: 0,
                        borderWidth: 0,
                        borderColor: null,
                        minBorderMargin: 5,
                        clickable: !0,
                        hoverable: !0,
                        autoHighlight: !1,
                        mouseActiveRadius: 20,
                    },
                    series: {
                        bars: {
                             show: true, barWidth: 0.5, align: 'center',
                        },
                    },
                    colors: ['#3c8dbc'],
                        yaxis: {
                        tickColor: 'rgba(166, 176, 207, 0.1)',
                        font: { color: '#8791af' },
				    },
                    xaxis : {
                        rotateTicks:90,
                        ticks: [
                                [0,'00'],
                                [1,'01'],
                                [2,'02'],
                                [3,'03'],
                                [4,'04'],
                                [5,'05'],
                                [6,'06'],
                                [7,'07'],
                                [8,'08'],
                                [9,'09'],
                                [10,'15'],
                                [11,'11'],
                                [12,'12'],
                                [13,'13'],
                                [14,'14'],
                                [15,'15'],
                                [16,'16'],
                                [17,'17'],
                                [18,'18'],
                                [19,'19'],
                                [20,'20'],
                                [21,'21'],
                                [22,'22'],
                                [23,'23'],
                                [24,'24'],
                            ]
                    },
                    })

        };
       
                      
        var chart_pieajax=function(display='today'){
            $.ajax({
                    type: "post",
                    dataType: "json",
                    url: '{{ url('admin/support/supportoverview_pie') }}',
                    headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: { type:'panel', display: display },
                    success: function (data) {
                        chart_pie(data.pie);
                        chartLINE(data.line);
                    }
                });
                return false;
        };

        $(document).ready(function () {
            
            loadPanel();

            chart_pieajax();
            
            $('#dispOverview').change(function(){
                var action=$(this).val();
                chart_pieajax(action);
                loadPanel(action);

                return false;
            });
        });
    </script> 

@endsection
