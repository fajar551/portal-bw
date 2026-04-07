<form
   action="{{ route('admin.pages.setup.prodsservices.productservices.createproduct.edit_freedomains', $newProds->id) }}"
   method="POST">
   @csrf
   <input type="hidden" name="tab" value="{{ $tab }}">
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">
         Free Domains
      </label>
      <div class="col-sm-12 col-lg-10">
         <div class="custom-control custom-radio">
            <input type="radio" id="customRadio1" name="freedomain" class="custom-control-input" value="none"
               {{ !$newProds->freedomain ? 'checked' : '' }}>
            <label class="custom-control-label" for="customRadio1">None</label>
         </div>
         <div class="custom-control custom-radio">
            <input type="radio" id="customRadio2" name="freedomain" class="custom-control-input" value="once"
               {{ $newProds->freedomain == 'once' ? 'checked' : '' }}>
            <label class="custom-control-label" for="customRadio2">
               Offer a free domain registration/transfer only (renew as normal)</label>
         </div>
         <div class="custom-control custom-radio">
            <input type="radio" id="customRadio3" name="freedomain" class="custom-control-input" value="on"
               {{ $newProds->freedomain == 'on' ? 'checked' : '' }}>
            <label class="custom-control-label" for="customRadio3">
               Offer a free domain registration/transfer and free renewal (if product is renewed)</label>
         </div>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">
         Free Domain Payment Terms
      </label>
      <div class="col-sm-12 col-lg-6">
         <select name="freedomainpaymentterms[]" id="freedomainpaymentterms" class="form-control" multiple>
            @foreach ($cyclesPricing as $key => $cycle)
               <option value="{{ $cycle }}" @foreach ($paymentTerms as $termId => $paymentTerm)
                  {{ $cycle == $paymentTerm ? 'selected' : '' }}
            @endforeach>{{ ucfirst($cycle) }}</option>
            @endforeach
         </select>
         <small>Select the payment term(s) the product must be paid with to receive a free domain</small>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-lg-2 col-form-label">
         Free Domain TLD's
      </label>
      <div class="col-sm-12 col-lg-6">
         <select name="freedomaintlds[]" id="freedomaintlds" class="form-control" multiple>
            @foreach ($tlds as $tld)
               <option value="{{ $tld->extension }}" @foreach ($tldInProduct as $key => $tldProd)
                  {{ $tld->extension == $tldProd ? 'selected' : '' }}
            @endforeach
            >{{ $tld->extension }}</option>
            @endforeach
         </select>
         <small>Use Ctrl + Click to select multiple payment terms and TLD's</small>
      </div>
   </div>
   <div class="text-center">
      <button type="submit" class="btn btn-success px-3">Save Changes</button>
      <a href="{{ route('admin.pages.setup.prodsservices.productservices.index') }}">
         <button type="button" class="btn btn-light px-3">Cancel Changes</button>
      </a>
   </div>
</form>
