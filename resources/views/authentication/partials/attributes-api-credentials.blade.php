<div class="form-group">
    <label for="inputDescription"> {{Lang::get("admin.description")}} </label>
    <input type="text" class="form-control" id="inputDescription" name="description" placeholder="{{Lang::get("admin.description")}}" value="{{isset($device) ? $device->description : ""}}">
</div>
<div class="form-group">
    <label for="selectRoles">{{Lang::get("admin.apicredsapiRoles")}}</label>
    <select multiple class="form-control" id="selectRoles" name="roleIds[]">
    @php
        if (!empty($roles)) {
            if (isset($device)) {
                $currentRoles = $device->rolesCollection();
            } else {
                $currentRoles = array();
            }
            foreach ($roles as $role) {
                echo sprintf("<option value=\"%s\" %s>%s</option>", $role->id, array_key_exists($role->id, $currentRoles) ? "selected" : "", $role->name);
            }
        } else {
            echo sprintf("<option value=\" disabled\">%s</option>", Lang::get("admin.apirolenoRolesDefined"));
        }
    @endphp
    </select>
    <p class="help-block">{{Lang::get("admin.apicredsroleSelectionHelper")}}</p>
</div>
