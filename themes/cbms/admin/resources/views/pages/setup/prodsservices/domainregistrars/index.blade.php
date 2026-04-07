@extends('layouts.basecbms')

@section('title')
   <title>{{ Cfg::getValue('CompanyName') }} -  Domain Registrars</title>
@endsection

@push('styles')
    <link href="{{ Theme::asset('css/app.css') }}" type="text/css" rel="stylesheet" />
@endpush

@section('content')
   <div class="main-content">
      <div class="page-content">
         <div class="container-fluid">
            <div class="row">
               <!-- MAIN CARD -->
               <div class="col-xl-12">
                  <div class="view-client-wrapper">
                     <div class="row">
                        <div class="col-12">
                           <div class="card-title mb-3">
                              <h4 class="mb-3">Domain Registrars</h4>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                           <!-- START HERE -->
                           <div class="card p-3">
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
                              <div class="row mb-3">
                                 <div class="col-lg-12">
                                    <div class="table-responsive">
                                       <table id="datatable" class="table table-bordered dt-responsive w-100">
                                          <thead>
                                             <tr class="text-center">
                                                <th>Name</th>
                                                <th width="790">Module</th>
                                                <th>Actions</th>
                                             </tr>
                                          </thead>
                                          <tbody>
                                              @foreach ($registrars as $registrar)
                                              @php
                                                    $moduleactive = $registrar['moduleactive'];
                                                    $moduleconfigdata = $registrar['moduleconfigdata'];
                                                    $configarray = $registrar['configarray'];
                                                    $displayName = $registrar['displayName'];
                                                    $module = $registrar['module'];
                                              @endphp
                                                <tr>
                                                    <td width="200">
                                                        <img class="img-fluid mw-50" src="{{$registrar['logo']}}" alt="registrar" width="150">
                                                    </td>
                                                    <td class="w-50">
                                                        <strong>&nbsp;&raquo;&nbsp;{{$displayName}}</strong>
                                                        @isset($configarray["Description"]["Value"])
                                                            <p>{!!$configarray["Description"]["Value"]!!}</p>
                                                        @endisset
                                                    </td>
                                                    <td>
                                                        <div class="row">
                                                        <div class="col-lg-12 text-center">
                                                            <div class="d-flex">
                                                                @if (!$module->isEnabled())
                                                                    <form  action="{{route('admin.pages.setup.prodsservice.domainregistrars.active')}}" method="post">
                                                                        @csrf
                                                                        <input type="hidden" name="module" value="{{$module->getLowerName()}}">
                                                                        <button class="btn btn-success mx-1">Activate</button>    
                                                                    </form>
                                                                @endif
                                                                @if ($module->isEnabled())
                                                                    <form onsubmit="return confirm('Are you sure you want to deactivate this module?');" action="{{route('admin.pages.setup.prodsservice.domainregistrars.deactive')}}" method="post">
                                                                        @csrf
                                                                        <input type="hidden" name="module" value="{{$module->getLowerName()}}">
                                                                        <button class="btn btn-danger mx-1">Deactivate</button>    
                                                                    </form>    
                                                                    <button class="btn btn-light btn-shower" data-module="{{$module->getLowerName()}}">Configure</button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if ($module->isEnabled())
                                                    <tr class="child-{{$module->getLowerName()}}" style="display: none">
                                                        <td class="text-center" colspan="3">
                                                            @if (is_array($configarray))
                                                                <form class="form" action="{{route('apiconsumer.admin.setup.saveRegistrarModuleConfig')}}" method="post">
                                                                    <input type="hidden" name="module" value="{{$module->getLowerName()}}">
                                                                    <table class="table w-100">
                                                                        <tbody>
                                                                            @foreach ($configarray as $key => $values)
                                                                                @if ($values["Type"] != "System")
                                                                                    @php
                                                                                        if (!isset($values["FriendlyName"])) {
                                                                                            $values["FriendlyName"] = $key;
                                                                                        }
                                                                                        $values["Name"] = $key;
                                                                                        $values["Value"] = array_key_exists($key, $moduleconfigdata) ? $moduleconfigdata[$key] : null;
                                                                                    @endphp
                                                                                    <tr>
                                                                                        <td class="text-right">{{$values["FriendlyName"]}}</td>
                                                                                        <td class="text-left bg-light">{!!\App\Helpers\Module::moduleConfigFieldOutput($values)!!}</td>
                                                                                    </tr>
                                                                                @endif
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                    <button class="btn btn-secondary mt-3">Save Changes</button>
                                                                </form>
                                                            @else
                                                                <div class="alert alert-warning" role="alert">
                                                                    {{$configarray}}
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                              @endforeach
                                          </tbody>
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
   <script src="{{ Theme::asset('assets/libs/bootstrap-switch-custom/bootstrap4-toggle.min.js') }}"></script>
    <script src="{{ Theme::asset('js/notify.min.js') }}"></script>
    <script>
        $(".btn-shower").click(function() {
            var moduleName = $(this).data('module');
            // $(this).closest('tr').nextUntil("tr.child-"+moduleName+":has(.btn-shower)").toggle("slow", function() {});
            $("tr.child-"+moduleName).toggle("fast", function() {});
        });
        $('form.form').on('submit', function (e) {
            e.preventDefault();
            var url = $(this).attr('action');
            $.ajax({
                type: 'post',
                url: url,
                data: $(this).serialize(),
                success: function (res) {
                    console.log(res)
                    if (res.result == 'success') {
                        $.notify(res.message, "success");
                    } else {
                        $.notify(res.message, "error");
                    }
                },
                error: function(xhr, status, error) {
                    var e = JSON.parse(xhr.responseText);
                    $.notify(e.message, "error");
                }
            });
        });
   </script>
@endsection
