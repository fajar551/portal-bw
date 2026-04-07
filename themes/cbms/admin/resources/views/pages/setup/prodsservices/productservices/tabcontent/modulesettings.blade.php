<form action="{{route('admin.pages.setup.prodsservices.productservices.createproduct.edit.modulesettings')}}" method="post">
    @csrf
    <input type="hidden" name="id" value="{{$id}}">
    <input type="hidden" name="tab" value="{{$tab}}">
    <div class="row align-items-center border rounded-lg mb-3">
        <div class="col text-right">
            Module Name
        </div>
        <div class="col-4 py-1 bg-light d-flex align-items-center">
            <select name="servertype" id="inputModule" class="form-control w-50" onchange="fetchModuleSettings('{{$id}}', 'simple', 'configproducts')">
                <option value="">None</option>
                @foreach ($serverModules as $moduleName => $displayName)
                    <option value="{{$moduleName}}" {{$moduleName == $product->servertype ? 'selected' : ''}}>{{$displayName}}</option>
                @endforeach
            </select>
            <img class="ml-2" src="{{Theme::asset('img/loading.gif')}}" id="moduleSettingsLoader" alt="loading" style="display: none">
        </div>
        <div class="col text-right">
            Server Group
        </div>
        <div class="col-4 py-1 bg-light">
            <select name="servergroup" class="form-control w-50" id="inputServerGroup" onchange="fetchModuleSettings('{{$id}}', 'simple', 'configproducts')">
                <option value="0" data-server-types="">None</option>
                @foreach ($serverGroups as $group)
                    <option value="{{$group->id}}" data-server-types="{{$group->server_types}}" {{$group->id == $product->servergroup ? 'selected':''}}>{{$group->name}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 px-0">
            <div id="serverReturnedError" class="alert alert-warning d-none">
                <span id="serverReturnedErrorText"></span>
            </div>
        </div>
    </div>
    
    <div class="row border rounded-lg justify-content-center">
        <div class="col-md-12 text-center py-3">
            {{-- <p class="mb-0">Choose a module to load configuration settings</p> --}}
            <table id="tblModuleSettings" class="table mb-0">
                <tr id="noModuleSelectedRow">
                    <td class="border-top-0">
                        <div class="no-module-selected">
                            Choose a module to load configuration settings
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-right">
            <div class="module-settings-mode d-none" id="mode-switch" data-mode="simple">
                <a class="btn btn-sm btn-link">
                    <span class="text-simple d-none">
                        {{\Lang::get('admin.products.switchSimple')}}
                    </span>
                    <span class="text-advanced d-none">
                        {{\Lang::get('admin.products.switchAdvanced')}}
                    </span>
                </a>
            </div>
        </div>
    </div>

    <div class="row border rounded-lg mt-3 justify-content-center">
        @php
            $autosetup = $product->autosetup;
        @endphp
        <div class="col-md-12 px-0">
            <table id="tblModuleAutomationSettings" class="table mb-0 module-settings-automation module-settings-loading">
                <tr>
                    <td width="20" class="align-middle">
                        <input type="radio" name="autosetup" value="order" id="order" {{$autosetup == "order" ? 'checked' : ''}}>
                    </td>
                    <td class="bg-light align-middle">
                        <label for="order" class="mb-0 cursor-pointer">Automatically setup the product as soon as an order is placed</label>
                    </td>
                </tr>
                <tr>
                    <td class="align-middle">
                        <input type="radio" name="autosetup" value="payment" id="payment" {{$autosetup == "payment" ? 'checked' : ''}}>
                    </td>
                    <td class="bg-light align-middle">
                        <label for="payment" class="mb-0 cursor-pointer">Automatically setup the product as soon as the first payment is received</label>
                    </td>
                </tr>
                <tr>
                    <td class="align-middle">
                        <input type="radio" name="autosetup" value="on" id="autosetup_on" {{$autosetup == "on" ? 'checked' : ''}}>
                    </td>
                    <td class="bg-light align-middle">
                        <label for="autosetup_on" class="mb-0 cursor-pointer">Automatically setup the product when you manually accept a pending order</label>
                    </td>
                </tr>
                <tr>
                    <td class="align-middle">
                        <input type="radio" name="autosetup" value="" id="autosetup_no" {{$autosetup == "" ? 'checked' : ''}}>
                    </td>
                    <td class="bg-light align-middle">
                        <label for="autosetup_no" class="mb-0 cursor-pointer">Automatically setup the product when you manually accept a pending order</label>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-12 text-center">
            <button class="btn btn-success px-3">Save Changes</button>
            <a href="{{ route('admin.pages.setup.prodsservices.productservices.index') }}" class="btn btn-light px-3">Cancel Changes</a>
        </div>
    </div>
    
</form>
