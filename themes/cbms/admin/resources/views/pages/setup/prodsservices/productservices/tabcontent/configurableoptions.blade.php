<form
   action="{{ route('admin.pages.setup.prodsservices.productservices.createproduct.edit_configoptions', $newProds->id) }}"
   method="POST">
   @csrf
   <input type="hidden" name="tab" value="{{ $tab }}">
   <input type="hidden" name="gid" value="{{ $newProds->gid }}">
   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Assigned Option Groups
      </label>
      <div class="col-sm-12 col-lg-4">
         <select name="configoptionlinks[]" id="configOption" class="form-control" multiple style="height: 200px">
            @foreach ($configList as $config)
               <option value="{{ $config->id }}" @if ($configOptionLinks)
                  @foreach ($configOptionLinks as $key => $gid)
                     {{ $gid == $config->id ? 'selected' : '' }}
                  @endforeach
            @endif
            >{{ $config->name }} - {{ $config->description }}</option>
            @endforeach
         </select>
      </div>
   </div>
   <div class="col-lg-12 text-center">
      <button class="btn btn-success px-3">Save Changes</button>
      <a href="{{ route('admin.pages.setup.prodsservices.productservices.index') }}" class="btn btn-light px-3">Cancel
         Changes</a>
   </div>
</form>
