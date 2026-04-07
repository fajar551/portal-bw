<form
   action="{{ route('admin.pages.setup.prodsservices.productservices.createproduct.edit_pricing', $newProds->id) }}"
   method="POST" id="pricingTabEditProduct">
   @csrf
   <input type="hidden" name="tab" value="{{ $tab }}">
   <input type="hidden" name="name" value="{{ $newProds->name }}">
   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Payment Type
      </label>
      <div class="col-sm-12 col-md-10 pt-2">
         <div class="custom-control custom-radio custom-control-inline">
            <input value="free" name="paytype" type="radio" id="free" class="custom-control-input"
               {{ $newProds->paytype == 'free' ? 'checked' : '' }}>
            <label class="custom-control-label" for="free">Free</label>
         </div>
         <div class="custom-control custom-radio custom-control-inline">
            <input value="onetime" name="paytype" type="radio" id="oneTimeRadio" class="custom-control-input"
               {{ $newProds->paytype == 'onetime' ? 'checked' : '' }}>
            <label class="custom-control-label" for="oneTimeRadio">One
               Time</label>
         </div>
         <div class="custom-control custom-radio custom-control-inline">
            <input value="recurring" name="paytype" type="radio" id="recurringRadio" class="custom-control-input"
               {{ $newProds->paytype == 'recurring' ? 'checked' : '' }}>
            <label class="custom-control-label" for="recurringRadio">Recurring</label>
         </div>
      </div>
   </div>
   {{-- <div class="collapse" id="onetime-section">
      <div id="table-one-time" class="d-flex justify-content-center">
         <table id="pricingtbl" class="table table-bordered w-50">
            <thead>
               <tr>
                  <th>Currency</th>
                  <th></th>
                  <th>One Time/Monthly</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($params['currencies'] as $currency)
                  <tr id="onetime{{ $currency->id }}">
                     <td rowspan="3" width="100" style="vertical-align: middle;">
                        <strong>{{ $currency->code }}</strong>
                     </td>
                     <td width="100">Setup Fee</td>
                     <td width="120" class='p-1'>
                        <input type="text" name="currency[{{ $currency->id }}][msetupfee]"
                           value="{{ @$price['onetime'][$currency->id]->msetupfee }}"
                           style="display:{{ $newProds->paytype == 'onetime' ? 'block' : 'none' }}"
                           id="setup_{{ $currency->code }}_monthlys" class="form-control collapse">
                     </td>
                  </tr>
                  <tr>
                     <td width="100">Price</td>
                     <td width="120" class='p-1'>
                        <input type="text" name="currency[{{ $currency->id }}][monthly]"
                           value="{{ @$price['onetime'][$currency->id]->monthly }} "
                           style="display:{{ $newProds->paytype == 'onetime' ? 'block' : 'none' }}"
                           id="pricing_{{ $currency->code }}_monthlys"
                           class="form-control collapse">
                     </td>
                  </tr>
                  <tr>
                     <td width="100">Enable</td>
                     <td class="text-center">
                        <div class="custom-control custom-checkbox">
                           <input type="checkbox" class="custom-control-input onlyOneTime pricingtgl"
                              data-id="{{ $currency->id }}" currency="{{ $currency->code }}" cycle="monthlys"
                              id="onlyOneTime{{ $currency->id }}" value="false"
                              {{ @$price['onetime'][$currency->id]->monthly != '' ? 'checked' : '' }}>
                           <label class="custom-control-label" for="onlyOneTime{{ $currency->id }}"></label>
                        </div>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </div> --}}
   <div class="collapse show" id="recurring-section">
      <div class="row">
         <div class="col-lg-12">
            <div class="table-responsive d-flex justify-content-center mb-3">
               <table class="table table-bordered w-50" id="tblpricing">
                  <thead>
                     <tr>
                        <th style="width: 80px">Currency</th>
                        <th> </th>
                        <th>One Time/Monthly</th>
                        <th class="recurring-cycles onetime-mode">Quarterly</th>
                        <th class="recurring-cycles onetime-mode">Semi-Annually</th>
                        <th class="recurring-cycles onetime-mode">Annually</th>
                        <th class="recurring-cycles onetime-mode">Biennially</th>
                        <th class="recurring-cycles onetime-mode">Triennially</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($params['currencies'] as $r)
                        <tr>
                           <td rowspan="3" class="align-middle font-weight-bold" width="100">{{ $r->code }} </td>
                           <td width="100">Setup fee</td>
                           @foreach ($params['cycles'] as $d)
                              <td width="100" class="{{ $d !== 'monthly' ? 'setup-column onetime-mode'  : '' }}">
                                 <input type="text"
                                    name="currency[{{ $r->id }}][{{ $params['setup'][$loop->index] }}]"
                                    id="setup_{{ $r->code }}_{{ $d }}"
                                    @if ($price['onetime'])
                                    value="{{ $price['onetime'][$r->id]->{$params['setup'][$loop->index]} ?? 0.0 }}"
                                    @elseif ($price['recurring'])
                                    value="{{ $price['recurring'][$r->id]->{$params['setup'][$loop->index]} ?? 0.0 }}"
                                    @else
                                    value="0.0"
                                    @endif
                                    style="display: @if (!empty(@$price['onetime'][$r->id]->{$params['setup'][$loop->index]}) || !empty(@$price['recurring'][$r->id]->{$params['setup'][$loop->index]})) block @else none @endif;" class="form-control  text-center">
                              </td>
                           @endforeach
                        </tr>
                        <tr>
                           <td width="100">Price</td>
                           @foreach ($params['cycles'] as $d)
                              <td class="{{ $d !== 'monthly' ? 'price-column onetime-mode' : '' }}">
                                 <input type="text" name="currency[{{ $r->id }}][{{ $d }}]"
                                    id="pricing_{{ $r->code }}_{{ $d }}" size="10"
                                    @if ($price['onetime'])
                                    value="{{ $price['onetime'][$r->id]->{$d} ?? -1.00 }}"
                                    @elseif ($price['recurring'])
                                    value="{{ $price['recurring'][$r->id]->{$d} ?? -1.00 }}"
                                    @else
                                    value="-1.00"
                                    @endif
                                    style="display: @if (!empty(@$price['onetime'][$r->id]->{$d}) || !empty(@$price['recurring'][$r->id]->{$d})) block @else none @endif ;" class="form-control text-center">
                              </td>
                           @endforeach
                        </tr>
                        <tr>
                           <td width="100">Enable</td>
                           @foreach ($params['cycles'] as $d)
                              <td class="text-center {{ $d !== 'monthly' ? 'checkbox-column onetime-mode' : '' }}">
                                 <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="pricingtgl custom-control-input"
                                       currency="{{ $r->code }}" cycle="{{ $d }}"
                                       @if (!empty(@$price['onetime'][$r->id]->{$d}) || !empty(@$price['recurring'][$r->id]->{$d}) || !empty(@$price['onetime'][$r->id]->{$params['setup'][$loop->index]}) || !empty(@$price['onetime'][$r->id]->{$params['setup'][$loop->index]})) checked @endif @if ($price['onetime']) id="onetime{{ $d }}{{ $r->code }}" @else id="recurring{{ $d }}{{ $r->code }}" @endif>
                                    <label class="custom-control-label"
                                    @if ($price['onetime']) for="onetime{{ $d }}{{ $r->code }}" @else for="recurring{{ $d }}{{ $r->code }}" @endif></label>
                                 </div>
                              </td>
                           @endforeach
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>

   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Allow Multiple Quantities
      </label>
      <div class="col-sm-12 col-md-10 pt-2">
         <div class="custom-control custom-checkbox">
            <input type="checkbox" name="allowqty" class="custom-control-input" id="allowQTY" value="1"
               {{ $newProds->allowqty == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="allowQTY">Tick this box to
               allow customers to specify if they want more than 1 of this item when
               ordering (must not require separate config)</label>
         </div>
      </div>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Recurring Cycles Limit
      </label>
      <div class="col-sm-12 col-md-1">
         <input type="text" name="recurringcycles" class="form-control" value="{{ $newProds->recurringcycles }}">
      </div>
      <label class="col-sm-12 col-md-9 col-form-label">
         To limit this product to only recur a fixed number of times, enter the
         total number of times to invoice (0 = Unlimited)
      </label>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Auto Terminate/Fixed Term
      </label>
      <div class="col-sm-12 col-md-1">
         <input type="text" name="autoterminatedays" class="form-control"
            value="{{ $newProds->autoterminatedays }}">
      </div>
      <label class="col-sm-12 col-md-9 col-form-label">
         Enter the number of days after activation to automatically terminate (eg.
         free trials, time limited products, etc...)
      </label>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Termination Email
      </label>
      <div class="col-sm-12 col-md-3">
         <select name="autoterminateemail" id="" class="form-control">
            <option value="0" {{ !$newProds->autoterminateemail ? 'selected' : '' }}>None</option>
            @foreach ($customProductEmail as $mail)
               <option value="{{ $mail->id }}"
                  {{ $mail->id == $newProds->autoterminateemail ? 'selected' : '' }}>{{ $mail->name }}</option>
            @endforeach
         </select>
      </div>
      <label class="col-sm-12 col-md-7 col-form-label">
         Choose the email template to send when the fixed term comes to an end
      </label>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Prorata Billing
      </label>
      <div class="col-sm-12 col-md-10 col-form-label">
         <div class="custom-control custom-checkbox">
            <input type="checkbox" name="proratabilling" class="custom-control-input" value="1" id="prorata"
               {{ $newProds->proratabilling == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="prorata">Tick this box to
               enable</label>
         </div>
      </div>
   </div>
   {{-- <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
        Charge at the end of the month
      </label>
      <div class="col-sm-12 col-md-10 col-form-label">
         <div class="custom-control custom-checkbox">
            <input type="checkbox" name="proratabillingendmonth" class="custom-control-input" value="1" id="prorataX"
               {{ $newProds->proratabillingendmonth == 1 ? 'checked' : '' }}>
            <label class="custom-control-label" for="prorataX">Tick this box to
               enable</label>
         </div>
         <div class="text-info"> <small>Note: Prorata Billing must be enabled</small> </div>
         <div class="text-muted"> <small>Note: Prorata Date & Charge Next Month option will be ignored</small> </div>
      </div>
   </div> --}}
   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Prorata Date
      </label>
      <div class="col-sm-12 col-md-1">
         <input type="text" name="proratadate" class="form-control" value="{{ $newProds->proratadate }}">
      </div>
      <label class="col-sm-12 col-md-9 col-form-label">
         Enter the day of the month you want to charge on
      </label>
   </div>
   <div class="form-group row">
      <label for="" class="col-sm-12 col-md-2 col-form-label">
         Charge Next Month
      </label>
      <div class="col-sm-12 col-md-1">
         <input type="text" name="proratachargenextmonth" class="form-control"
            value="{{ $newProds->proratachargenextmonth }}">
      </div>
      <label class="col-sm-12 col-md-7 col-form-label">
         Enter the day of the month after which the following month will also be
         included on the first invoice
      </label>
   </div>
   <div class="text-center">
      <button type="submit" class="btn btn-success px-3">Save Changes</button>
      <a href="{{ route('admin.pages.setup.prodsservices.productservices.index') }}">
         <button type="button" class="btn btn-light px-3">Cancel Changes</button>
      </a>
   </div>
</form>
