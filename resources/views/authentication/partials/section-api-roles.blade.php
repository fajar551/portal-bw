<p>
    <a id="btnNewAPIRole"
       href="{{route('admin.admin-setup-authz-api-roles-manage')}}"
       data-modal-title="{{Lang::get("admin.apiroleroleManagement")}}"
       data-modal-size="modal-lg"
       data-modal-class="modal-manage-api-role"
       data-btn-submit-id="btnSaveApiRole"
       data-datatable-reload-success="tblApiRoles"
       data-btn-submit-label="{{Lang::get("admin.save")}}"
       onclick="return false;"
       class="btn btn-success open-modal">
        <i class="fas fa-plus"></i>&nbsp;{{Lang::get("admin.apirolecreate")}}
    </a>
    {{-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalNewAPIRole">
        <i class="fas fa-plus"></i>&nbsp;{{Lang::get("admin.apirolecreate")}}
    </button> --}}
</p>

<table id="tblApiRoles" class="table display data-driven table-themed tbl-api-roles table-striped table-hover"
    data-ajax='{"url": "{{route('admin.admin-setup-authz-api-roles-list')}}"}'
    data-on-draw-rebind-confirmation="true"
    data-lang-empty-table="{{Lang::get("admin.apirolenoRolesDefined")}}"
    data-auto-width="false"
    data-order='[[ 1, "asc" ]]'
    data-columns='{{json_encode(array(array("data" => "btnExpand", "className" => "details-control text-center", "orderable" => 0, "width" => "3%"), array("data" => "name", "className" => "details-control", "width" => "25%"), array("data" => "description", "className" => "details-control", "width" => "64%"), array("data" => "btnGroup", "orderable" => 0, "width" => "8%")))}}'
>
    <thead>
        <tr class="text-center">
            <th></th>
            <th>{{Lang::get("admin.apiroleroleName")}}</th>
            <th>{{Lang::get("admin.fieldsdescription")}}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="4" class="text-center">{{Lang::get("admin.apirolenoRolesDefined")}}</td>
        </tr>
    </tbody>
</table>
