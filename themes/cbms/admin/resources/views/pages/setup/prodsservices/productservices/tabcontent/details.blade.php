<form
   action="{{ route('admin.pages.setup.prodsservices.productservices.createproduct.edit_details', $newProds->id) }}"
   method="POST" id="detailsTab">
   @csrf
   <input type="hidden" name="tab" value="{{ $tab }}">
   {{-- <input type="hidden" name="id" value="{{Request::get('id')}}"> --}}
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Product Type</label>
      <div class="col-sm-12 col-lg-3">
         <select name="type" id="type" class="form-control">
            {!! $listTypeValue !!}
         </select>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Product Group</label>
      <div class="col-sm-12 col-lg-4">
         <select name="gid" id="gid" class="form-control">
            @foreach ($prodGroup as $prod)
               <option value="{{ $prod->id }}" {{ $newProds->gid == $prod->id ? 'selected' : '' }}>
                  {{ $prod->hidden == 1 ? $prod->name . ' (Hidden)' : $prod->name }}</option>
            @endforeach
         </select>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Product Name</label>
      <div class="col-sm-12 col-lg-6">
         <input type="text" name="name" id="name" class="form-control" value="{{ $newProds->name }}">
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Product
         Description</label>
      <div class="col-sm-12 col-lg-6">
         <textarea name="description" id="description" cols="30" rows="10"
            class="form-control">{{ $newProds->description }}</textarea>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Welcome Email</label>
      <div class="col-sm-12 col-lg-4">
         <select name="welcomeemail" id="welcomeemail" class="form-control">
            <option value="0" {{ !$newProds->welcomeemail ? 'selected' : '' }}>None</option>
            @foreach ($customProductEmail as $mail)
               <option value="{{ $mail->id }}" {{ $mail->id == $newProds->welcomeemail ? 'selected' : '' }}>
                  {{ $mail->name }}</option>
            @endforeach
         </select>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Require Domain</label>
      <div class="col-sm-12 col-lg-4 pt-2">
         <div class="custom-control custom-checkbox">
            <input type="hidden" class="custom-control-input" id="HiddenCheckAlt" name="showdomainoptions" value="0">
            <input type="checkbox" class="custom-control-input" id="HiddenCheck" name="showdomainoptions" value="1"
               {{ $newProds->showdomainoptions == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="HiddenCheck">Tick to show
               domain
               registration options</label>
         </div>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Stock Control</label>
      <div class="col-sm-12 col-lg-3 pt-2">
         <div class="custom-control custom-checkbox">
            <input type="hidden" name="stockcontrol" class="custom-control-input" id="CheckStockHidden" value="0">
            <input type="checkbox" name="stockcontrol" class="custom-control-input" id="CheckStock" value="1"
               {{ $newProds->stockcontrol == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="CheckStock">Enable - Quantity
               in
               Stock:</label>
         </div>
      </div>
      <div class="col-sm-12 col-lg-1">
         <input type="number" name="qty" id="qty-input" class="form-control" value="{{ $newProds->qty }}" disabled>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Apply Tax</label>
      <div class="col-sm-12 col-lg-10 pt-2">
         <div class="custom-control custom-checkbox">
            <input type="hidden" class="custom-control-input" id="CheckTaxHidden" name="tax" value="0">
            <input type="checkbox" class="custom-control-input" id="CheckTax" name="tax" value="1"
               {{ $newProds->tax == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="CheckTax">Tick this box to
               charge tax for this product</label>
         </div>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Featured</label>
      <div class="col-sm-12 col-lg-10 pt-2">
         <div class="custom-control custom-checkbox">
            <input type="hidden" class="custom-control-input" id="IsFeaturedHidden" name="is_featured" value="0">
            <input type="checkbox" class="custom-control-input" id="IsFeatured" name="is_featured" value="1"
               {{ $newProds->is_featured == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="IsFeatured">Display this
               product
               more prominently on supported order forms</label>
         </div>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Hidden</label>
      <div class="col-sm-12 col-lg-10 pt-2">
         <div class="custom-control custom-checkbox">
            <input type="hidden" class="custom-control-input" id="CheckHiddenAlt" name="hidden" value="0">
            <input type="checkbox" class="custom-control-input" id="CheckHidden" name="hidden" value="1"
               {{ $newProds->hidden == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="CheckHidden">Tick to hide from
               order form</label>
         </div>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">Retired</label>
      <div class="col-sm-12 col-lg-10 pt-2">
         <div class="custom-control custom-checkbox">
            <input type="hidden" class="custom-control-input" id="RetiredHidden" name="retired" value="0">
            <input type="checkbox" class="custom-control-input" id="Retired" name="retired" value="1"
               {{ $newProds->retired == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="Retired">Tick to hide from
               admin area product dropdown menus (does not apply to services already
               with this product)</label>
         </div>
      </div>
   </div>
   <div class="text-center">
      <button type="submit" class="btn btn-success waves-effect px-3">Save Changes</button>
      <a href="{{ route('admin.pages.setup.prodsservices.productservices.index') }}">
         <button type="button" class="btn btn-light waves-effect px-3">Cancel Changes</button>
      </a>
   </div>
</form>
