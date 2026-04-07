<form
   action="{{ route('admin.pages.setup.prodsservices.productservices.createproduct.edit_upgrades', $newProds->id) }}"
   method="POST" id="upgradesTab">
   @csrf
   <div class="form-group row">
      <label for="packagesUpgrades" class="col-sm-12 col-lg-2 col-form-label">Packages Upgrades</label>
      <div class="col-sm-12 col-lg-6 col-form-label">
         <select class="form-control" name="upgradepackages[]" size="10" id="upgradepackages" multiple>
            @foreach ($upgradesPackageList as $package)
               @php
                  $productid = $package->id;
                  $groupname = $package->groupname;
                  $productname = $package->productname;
               @endphp
               @if ($newProds->id != $productid)
                  <option value="{{ $productid }}"
                    @foreach ($getUpgradePackageId as $upgradeId)
                        {{ $productid == $upgradeId->upgrade_product_id ? 'selected' : ''}}
                    @endforeach
                  >{{ $groupname }} - {{ $productname }}</option>
            @endif
            @endforeach
         </select>
         <small>Use Ctrl+Click to select multiple packages</small>
      </div>
   </div>
   <div class="form-group row">
      <label for="configoptions" class="col-sm-12 col-lg-2 col-form-label">Configurable Options</label>
      <div class="col-sm-12 col-lg-6 pt-2">
         <div class="custom-control custom-checkbox">
            <input name="configoptionsupgrade" type="hidden" class="custom-control-input" id="configoptionsHidden" value="0">
            <input name="configoptionsupgrade" type="checkbox" class="custom-control-input" id="configoptions" value="1"
                {{ $newProds->configoptionsupgrade == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="configoptions">Tick this box to allow Upgrading/Downgrading of
               configurable options</label>
         </div>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">
         Upgrade Email
      </label>
      <div class="col-sm-12 col-lg-4">
         <select name="upgradeemail" class="form-control" name="upgradeemail" id="upgradeemail">
             <option value="0" {{ !$newProds->upgradeemail ? 'selected' : '' }}>None</option>
             @foreach ($customProductEmail as $mail)
             <option value="{{ $mail->id }}"
                {{ $mail->id == $newProds->upgradeemail ? 'selected' : '' }}
                >{{ $mail->name }}</option>
             @endforeach
         </select>
      </div>
   </div>
   <div class="text-center">
      <button type="submit" class="btn btn-success px-3 waves-effect" id="btnUpdateSettings">Save Changes</button>
      <button class="btn btn-light px-3">Cancel Changes</button>
   </div>
</form>
