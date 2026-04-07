@extends('layouts.basecbms')

@section('title')
    <title>{{ Cfg::getValue('CompanyName') }} -  Addons</title>
@endsection

@push('styles')
    <link href="{{ Theme::asset('css/app.css') }}" type="text/css" rel="stylesheet" />
@endpush

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
                                        <h4 class="mb-3">Addons</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- START HERE -->
                                    <div class="card p-3">
                                        <p>This is where you can activate and manage addon modules in your CBMS installation. Older legacy modules will still allow you to activate/deactivate and configure access rights, but will not be able to show any configuration options, version or author information.</p>
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
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="table-responsive table-addons">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Module</th>
                                                                <th width="150">Version</th>
                                                                <th width="150">Author</th>
                                                                <th width="300"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($addonmodules as $addonmodule)
                                                                @php
                                                                    $module = $addonmodule['module']->getLowerName();
                                                                    $modulevars = $addonmodule['setting'];
                                                                @endphp
                                                                {{-- <script>
                                                                    console.log(@json($addonmodule));
                                                                </script> --}}
                                                                <tr>
                                                                    <td>
                                                                        <h5>
                                                                            @if (isset($addonmodule['config']['name']))
                                                                                {{$addonmodule['config']['name']}}
                                                                            @endif
                                                                        </h5>
                                                                        <p class="m-0 p-0">
                                                                            @if (isset($addonmodule['config']['description']))
                                                                                {{$addonmodule['config']['description']}}
                                                                            @endif
                                                                        </p>
                                                                    </td>
                                                                    <td>
                                                                        @if (isset($addonmodule['config']['version']))
                                                                            {{$addonmodule['config']['version']}}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if (isset($addonmodule['config']['author']))
                                                                            {{$addonmodule['config']['author']}}
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <div class="d-flex">
                                                                            @if (!$addonmodule['module']->isEnabled())
                                                                                <form action="{{route('admin.pages.setup.addonsmodule.active')}}" method="post">
                                                                                    @csrf
                                                                                    <input type="hidden" name="module" value="{{$addonmodule['module']->getLowerName()}}">
                                                                                    <button
                                                                                    class="btn btn-success mx-1">Activate</button>    
                                                                                </form>
                                                                            @endif
                                                                            @if ($addonmodule['module']->isEnabled())
                                                                                <form onsubmit="return confirm('Are you sure you want to deactivate this module?');" action="{{route('admin.pages.setup.addonsmodule.deactive')}}" method="post">
                                                                                    @csrf
                                                                                    <input type="hidden" name="module" value="{{$addonmodule['module']->getLowerName()}}">
                                                                                    <button
                                                                                    class="btn btn-danger mx-1">Deactivate</button>    
                                                                                </form>    
                                                                            @endif
                                                                            
                                                                            @if ($addonmodule['module']->isEnabled())
                                                                                <button
                                                                                    type="button"
                                                                                    {{-- data-toggle="collapse" data-target="#collapse{{$loop->index}}" aria-expanded="false" aria-controls="collapse{{$loop->index}}" --}}
                                                                                    class="btn btn-light mx-1 btn-shower" data-module="{{$module}}">
                                                                                    Configure
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr class="child-{{$module}}" style="display: none"> 
                                                                    <td class="text-center" colspan="4">
                                                                        <form class="form" action="{{route('apiconsumer.admin.setup.saveAddonsModuleConfig')}}" method="post">
                                                                            <table class="table w-100">
                                                                                <tbody>
                                                                                    @if (isset($addonmodule['config']['fields']))
                                                                                        @foreach ($addonmodule['config']['fields'] as $key => $values)
                                                                                            @php
                                                                                                $values["Name"] = "fields[" . $module . "][" . $key . "]";
                                                                                                $values["Value"] = array_key_exists($key, $modulevars) ? $modulevars[$key] : null;
                                                                                            @endphp
                                                                                            <tr>
                                                                                                <td class="text-right">{{$values["FriendlyName"]}}</td>
                                                                                                <td class="text-left bg-light">{!!\App\Helpers\Module::moduleConfigFieldOutput($values)!!}</td>
                                                                                            </tr>
                                                                                        @endforeach
                                                                                    @endif
                                                                                    <tr>
                                                                                        <td class="text-right">Access Control</td>
                                                                                        <td class="bg-light text-left">
                                                                                            <p>Choose the admin role groups to permit access to this module:</p>
                                                                                            @php
                                                                                                $allowedroles = explode(",", $modulevars["access"] ?? "");
                                                                                            @endphp
                                                                                            @foreach ($roles as $data)
                                                                                                @php
                                                                                                    $checked = "";
                                                                                                    if (in_array($data["id"], $allowedroles)) {
                                                                                                        // $addonmodulesperms[$data["id"]][$module] = $vals["name"];
                                                                                                        $checked = "checked";
                                                                                                    }
                                                                                                @endphp
                                                                                                <label class="checkbox-inline">
                                                                                                    <input type="checkbox" value="1" name="access[{{$module}}][{{$data['id']}}]" id="" {{$checked}}>&nbsp;{{$data['name']}}
                                                                                                </label>
                                                                                            @endforeach
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                            <button class="btn btn-secondary mt-3">Save Changes</button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
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
            var data = $(this).serialize();
            // console.log(data);
            $.ajax({
                type: 'post',
                url: url,
                
                data: data,
                success: function (res) {
                    if (res.result == 'success') {
                        $.notify(res.message, "success");
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
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
