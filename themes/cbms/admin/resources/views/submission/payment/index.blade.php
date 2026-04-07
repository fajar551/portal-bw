@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Payment Submission</title>
@endsection

@section('styles')
    <style>
        .nav-pills .nav-link.active h6, .nav-pills .show>.nav-link h6 {
            color: #ffffff;
        }
        .nav-pills .nav-link.active div, .nav-pills .show>.nav-link div {
            color: #ffffff!important;
        }
        .avatar {
            font-size: 1rem;
            display: inline-flex;
            width: 48px;
            height: 48px;
            color: #fff;
            border-radius: .375rem;
            background-color: #adb5bd;
            align-items: center;
            justify-content: center;
        }
        .avatar-xl {
            width: 74px;
            height: 74px;
        }
        .avatar img {
            width: 100%;
            border-radius: 0.375rem;
        }
        .avatar.rounded-circle img {
            border-radius: 50%!important;
        }
        small.is-invalid {
            color: #ff3d60;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/css/smart_wizard_all.min.css" rel="stylesheet" type="text/css" />
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
                                    <h4 class="mb-3">Pembayaran Otomatis</h4>
                                </div>

                                <div id="smartwizard">
                                    <ul class="nav">
                                       <li>
                                           <a class="nav-link" href="#step-1">
                                                <strong>Pelajari</strong>
                                                <div class="text-muted">
                                                    Selesai
                                                </div>
                                           </a>
                                       </li>
                                       <li>
                                           <a class="nav-link" href="#step-2">
                                                <strong>Detil Bisnis</strong>
                                                <div class="text-muted">
                                                    3 menit
                                                </div>
                                           </a>
                                       </li>
                                       <li>
                                           <a class="nav-link" href="#step-3">
                                                <strong>Pemilik Bisnis</strong>
                                                <div class="text-muted">
                                                    2 menit
                                                </div>
                                           </a>
                                       </li>
                                       <li>
                                           <a class="nav-link" href="#step-4">
                                                <strong>Dokumen</strong>
                                                <div class="text-muted">
                                                    2 menit
                                                </div>
                                           </a>
                                       </li>
                                    </ul>
                                 
                                    <div class="tab-content">
                                       <div id="step-1" class="tab-pane" role="tabpanel">
                                            @include('submission.payment.pelajari-tab')
                                       </div>
                                       <div id="step-2" class="tab-pane" role="tabpanel">
                                            @include('submission.payment.detail-tab')
                                       </div>
                                       <div id="step-3" class="tab-pane" role="tabpanel">
                                            @include('submission.payment.owner-tab')
                                       </div>
                                       <div id="step-4" class="tab-pane" role="tabpanel">
                                            @include('submission.payment.doc-tab')
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
</div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/js/jquery.smartWizard.min.js" type="text/javascript"></script>
    <script>
        function nextToTab(name) {
            $("a#"+name).trigger("click");
            console.log(name);
        }
        $("#nextToTab2").on("click", function() {
            $("a#v-pills-second-tab").trigger("click");
        });
        $("#nextToTab3").on("click", function() {
            $("a#v-pills-second-tab").trigger("click");
        });
    </script>
    <script>
        $(document).ready(function(){
 
            // SmartWizard initialize
            $('#smartwizard').smartWizard({
                lang: { // Language variables for button
                    next: 'Lanjut',
                    previous: 'Kembali'
                },
                toolbarSettings: {
                    showNextButton: false, // show/hide a Next button
                    showPreviousButton: false, // show/hide a Previous button
                },
                autoAdjustHeight: false, // Automatically adjust content height
                cycleSteps: true, // Allows to cycle the navigation of steps
            });

            $("input[name=weborapp]").on("change", function(e) {
                var value = $(this).val();
                if (value == 'yes') {
                    $("#section1").show();
                    $("#section2").hide();
                } else {
                    $("#section1").hide();
                    $("#section2").show();
                }
            });

            $("select[name=alternative]").on("change", function(e) {
                var value = $(this).val();
                if (value.toLowerCase().includes('web')) {
                    $("#sosmed").hide();
                    $("#website_staging").show();
                } else {
                    $("#sosmed").show();
                    $("#website_staging").hide();
                }
            });

        });

        function toggle(className, obj) {
            if ( obj.checked ) $(className).hide();
            else $(className).show();
        }

        function toggle2(className, obj) {
            if ( !obj.checked ) $(className).hide();
            else $(className).show();
        }

        var loadFile = function(event) {
            var output = document.getElementById('logo-preview');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            }
        };

        function goToStep(step) {
            $('#smartwizard').smartWizard("goToStep", step);
        }
    </script>

    {{-- form step 2 --}}
    <script>
        $(document).ready(function(){
            var $form = $('#step2-form');
            $form.validate({
                normalizer: function (value) {
                    return value ? value.trim() : value;
                },
                errorElement: 'small',
                errorClass: "is-invalid",
                errorPlacement: function(error, element) {
                    if (element.attr("name") == "plan_to_use[]") {
                        error.insertAfter("#plan_to_use-error");
                    } else if (element.attr("name") == "weborapp") {
                        // error.insertAfter("#weborapp-error");
                        error.appendTo( element.parents('#weborapp-error') );
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function(element) {
                    if ($(element).attr("name") == "plan_to_use[]") {
                        $(element).removeClass("is-invalid");
                    } else if ($(element).attr("name") == "weborapp") {
                        $(element).removeClass("is-invalid");
                    } else {
                        $(element).addClass("is-invalid");
                    }
                },
                rules: {
                    logo: {
                        required: true,
                    },
                    company: {
                        required: true,
                    },
                    description: {
                        required: true,
                    },
                    legal_address: {
                        required: true,
                    },
                    legal_address1: {
                        required: function(element) {
                            return $("#defaultCheck1").is(':checked') ? false : true;
                        },
                    },
                    "plan_to_use[]": {
                        required: true, 
                        minlength: 1,
                    } ,
                    other_plan_to_use: {
                        required: function(element) {
                            return $("#plan_to_use-last").is(':checked') ? true : false;
                        },
                    },
                    weborapp: {
                        required: true,
                    },
                    website: {
                        required: function(element) {
                            return $("input[name=weborapp]").val() == 'yes' ? true : false;
                        },
                    },
                    alternative: {
                        required: true,
                    },
                    sosial_media_url: {
                        required: function(element) {
                            var alternative = $("select[name=alternative]").val();
                            // console.log(alternative);
                            // console.log($("input[name=weborapp]:checked").val());
                            return $("input[name=weborapp]:checked").val() == 'no' && !alternative.toLowerCase().includes('web')  ? true : false;
                        },
                    },
                    website_staging: {
                        required: function(element) {
                            var alternative = $("select[name=alternative]").val();
                            return $("input[name=weborapp]:checked").val() == 'no' && alternative.toLowerCase().includes('web')  ? true : false;
                        },
                    },
                },
            });
            $form.submit(function(event) {
                event.preventDefault();
                var isFormValid = $form.valid();
                if (!isFormValid) {
                    return false;
                }
                console.log('ok');
                $form.find('.submit').prop('disabled', true);
                goToStep(2);
            });
        });
    </script>

    {{-- form step 3 --}}
    <script>
        $(document).ready(function(){
            var $form = $('#step3-form');
            $form.validate({
                normalizer: function (value) {
                    return value ? value.trim() : value;
                },
                errorElement: 'small',
                errorClass: "is-invalid",
                rules: {
                    position: {
                        required: true,
                    },
                    id_card_name: {
                        required: true,
                    },
                    email_address: {
                        required: true,
                    },
                    phone_number: {
                        required: true,
                        number: true,
                    },
                }
            });
            $form.submit(function(event) {
                event.preventDefault();
                var isFormValid = $form.valid();
                if (!isFormValid) {
                    return false;
                }
                console.log('ok lanjut');
                $form.find('.submit').prop('disabled', true);
                goToStep(3);
            });
        });
    </script>

    {{-- form step 4 --}}
    <script>
        $(document).ready(function(){
            var $form = $('#step4-form');
            $form.validate({
                normalizer: function (value) {
                    return value ? value.trim() : value;
                },
                errorElement: 'small',
                errorClass: "is-invalid",
                rules: {
                    id_card: {
                        required: true,
                        number: true,
                        minlength: 16,
                    },
                    id_card_file: {
                        required: true,
                    },
                    npwp: {
                        required: true,
                        number: true,
                    },
                    npwp_file: {
                        required: true,
                    },
                },
            });
            $form.submit(function(event) {
                event.preventDefault();
                var isFormValid = $form.valid();
                if (!isFormValid) {
                    return false;
                }
                console.log('ok lanjut');
                $form.find('.submit').prop('disabled', true);
            });
        });
    </script>
@endsection
