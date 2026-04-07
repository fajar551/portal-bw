@php
$i = 0;
$sidebarList = array();
$tabContent = array();
$roleName = $roleDescription = $roleId = "";
if (isset($role)) {
    $roleName = sprintf("value=\"%s\"", $role->name);
    $roleDescription = $role->description ?? "";
    $roleId = $role->id;
}
$btnTextNone = Lang::get("admin.adminrolescheckall");
$btnTextAll = Lang::get("admin.adminrolesuncheckall");
// foreach ($apiCatalog as $group => $groupDetails) {
//     $tabId = "tab" . ucfirst($group);
//     $sidebarList[] = sprintf("<li class=\"%s\"><a href=\"#%s\" data-toggle=\"tab\">%s</a></li>", $i ? "" : "active", $tabId, $groupDetails["name"]);
//     $tabItems = array();
//     $checkedInGroup = 0;
//     foreach ($groupDetails["actions"] as $action => $actionDetails) {
//         if ($action == "setconfigurationvalue") {
//             continue;
//         }
//         if (isset($role)) {
//             $checked = $role->isAllowed($action) ? "checked" : "";
//         } else {
//             $checked = $actionDetails["default"] ? "checked" : "";
//         }
//         if ($checked) {
//             $checkedInGroup++;
//         }
//         $name = $actionDetails["name"];
//         $tabItems[] = "<div class=\"col-sm-6\">
//                 <label class=\"checkbox-inline\" >
//                     <input id=\"" . $action . "\" name=\"allow[" . $action . "]\" type=\"checkbox\" " . $checked . "> " . $name . "
//                     <a href=\"https://developers.whmcs.com/api-reference/" . $action . "/\" target=\"_blank\">
//                         <i class=\"fas fa-book\"></i>
//                     </a>
//                 </label>
//             </div>";
//     }
//     $allInGroupSelected = $tabItems && count($tabItems) === $checkedInGroup;
//     if ($allInGroupSelected) {
//         $btnClassActive = "toggle-active";
//     } else {
//         $btnClassActive = "";
//     }
//     $btnSelectAll = sprintf("<div class=\"btn-check-all btn btn-sm btn-link %s\"
//                 data-checkbox-container=\"%s\"
//                 data-btn-toggle-on=\"1\"
//                 id=\"btnSelectAll-%s\">%s</div>", $btnClassActive, $tabId, $tabId, $btnTextNone);
//     $btnDeselectAll = sprintf("<div class=\"btn-check-all btn btn-sm btn-link %s\"
//                 data-checkbox-container=\"%s\"
//                 id=\"btnSelectAll-%s\">%s</div>", $btnClassActive, $tabId, $tabId, $btnTextAll);
//     $tabContent[] = sprintf("<div class=\"tab-pane %s\" id=\"%s\">
//                     <h2>%s</h2>
//                     <div class=\"scroll-container\">
//                         <div class=\"row\">%s</div>
//                 </div>
//                 <br>
//                 %s %s
//             </div>", $i ? "" : "active", $tabId, $groupDetails["name"], implode("
//     ", $tabItems), $btnSelectAll, $btnDeselectAll);
//     $i++;
// }
@endphp

<script>
    jQuery(document).ready(function() {
        // WHMCS.form.register();
    });
    $("#checkAll").change(function () {
        $("input:checkbox").prop('checked', $(this).prop("checked"));
    });
</script>
<form class="form-horizontal" name="frmApiRoleManage" action="{{route('admin.admin-setup-authz-api-roles-create')}}">
    @csrf
    <input type="hidden" name="roleId" value="{{$roleId}}">
    <div class="form-group">
        <label for="inputName" class="col-sm-2 control-label">{{Lang::get("admin.apiroleroleName")}}</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="inputName" name="roleName" placeholder="{{Lang::get("admin.apiroleroleName")}}" {!!$roleName!!}>
        </div>
    </div>
    <div class="form-group">
        <label for="inputDescription" class="col-sm-2 control-label">{{Lang::get("admin.fieldsdescription")}}</label>
        <div class="col-sm-10">
            <textarea class="form-control" id="inputDescription" name="roleDescription" placeholder="{{Lang::get("admin.apiroledescriptionPlaceholder")}}">{{$roleDescription}}</textarea>
        </div>
    </div>
    <h2 class="api-permissions-heading">
        {{Lang::get("admin.apiroleallowedApiActions")}}
    </h2>
    <div class="form-check form-check-inline">
        <input style="cursor: pointer" class="form-check-input" type="checkbox" id="checkAll" value="option1">
        <label style="cursor: pointer" class="form-check-label text-primary" for="checkAll">Checked/Unchecked All</label>
    </div>
    <div class="container-fluid">
        <div class="row api-permissions">
            @foreach ($apiCatalog as $action)
                @php
                    $checked = "";
                    if (isset($role)) {
                        $checked = $role->permissions->contains('name', $action) ? "checked" : "";
                    }
                @endphp
                <div class="col-sm-3">
                    <label class="checkbox-inline" >
                        <input id="{{$action}}" name="allow[{{$action}}]" type="checkbox" {!!$checked!!}> {{$action}}
                        <a href="https://developers.whmcs.com/api-reference/{{strtolower($action)}}/" target="_blank">
                            <i class="fas fa-book"></i>
                        </a>
                    </label>
                </div>
            @endforeach
            <!-- sidebar nav -->
            {{-- <div class="col-sm-3">
                <nav class="nav-sidebar">
                    <ul class="nav">
                        {!!implode("\n", $sidebarList)!!}
                    </ul>
                </nav>
            </div> --}}
            <!-- tab content -->
            {{-- <div class="col-sm-9">
                <div class="tab-content">
                    {!!implode("\n", $tabContent)!!}
                </div>
            </div> --}}
        </div>
    </div>
</form>
