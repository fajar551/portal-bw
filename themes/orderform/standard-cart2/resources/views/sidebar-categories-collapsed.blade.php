@if (isset($productgroups))
    <div class="categories-collapsed visible-xs visible-sm clearfix mb-4">

        <div class="pull-left form-inline">
            <form method="get" action="">
                <select name="gid" class="form-control" onchange="if(this.value.startsWith('http')) { window.location.href = this.value; return false; } else { this.form.submit(); }">
                    <optgroup label="Product Categories">
                        @foreach ($productgroups as $productgroup)
                            <option value="{{$productgroup['gid']}}"
                                @if ($gid == $productgroup['gid'])
                                    selected="selected"
                                @endif>
                                {{$productgroup['name']}}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Actions">
                        @auth('web')
                            <option value="addons"{{$gid == "addons" ? ' selected' : ''}}>{{Lang::get('client.cartproductaddons')}}</option>
                            {{--@if ($renewalsenabled)
                                <option value="renewals"{{$gid == "renewals" ? ' selected':''}}>{{Lang::get('client.domainrenewals')}}</option>
                            @endif--}}
                        @endauth
                        @if ($registerdomainenabled)
                            <optgroup label="Register a New Domain">
                                <option value="https://client.bikin.website/cart?a=add&pid=2">Corporate</option>
                                <option value="https://client.bikin.website/cart?a=add&pid=9">DIY</option>
                                <option value="https://client.bikin.website/cart?a=add&pid=3">Ecommerce</option>
                                <option value="https://client.bikin.website/cart?a=add&pid=6">Entry</option>
                                <option value="https://client.bikin.website/cart?a=add&pid=7">Online</option>
                                <option value="https://client.bikin.website/cart?a=add&pid=8">Pilkada</option>
                                <option value="https://client.bikin.website/cart?a=add&pid=1">UKM</option>
                                <option value="https://client.bikin.website/cart?a=add&pid=5">Wedding</option>
                            </optgroup>
                        @endif
                        {{--@if ($transferdomainenabled)
                            <option value="transferdomain"{{$domain == "transfer" ? ' selected':''}}>{{Lang::get('client.transferinadomain')}}</option>
                        @endif--}}
                        {{-- <option value="viewcart"{{$action == "view" ? ' selected':''}}>{{Lang::get('client.viewcart')}}</option> --}}
                        <option value="viewcart" {{($action ?? '') == "view" ? ' selected' : ''}}>{{Lang::get('client.viewcart')}}</option>

                    </optgroup>
                </select>
            </form>
        </div>

        @if (!Auth::guard('web')->check() && (isset($currencies) && $currencies))
            <div class="pull-right form-inline">
                <form
                    method="post"
                    {{-- Ganti bagian ini --}}
        @if ($action)
        action="{{"?a={$action}"}}"
    @elseif ($gid)
        action="{{"?gid={$gid}"}}"
    @endif
    {{-- Dengan yang baru --}}
    @if (isset($action) && $action)
        action="{{"?a={$action}"}}"
    @elseif (isset($gid) && $gid)
        action="{{"?gid={$gid}"}}"
    @endif
                >
                <select name="currency" onchange="submit()" class="form-control">
                  <option value="">{{Lang::get('client.choosecurrency')}}</option>
                  @foreach ($currencies as $listcurr)
                      <option value="{{$listcurr['id']}}"{{$listcurr['id'] == $currency['id'] ? ' selected':''}}>{{$listcurr['code']}}</option>
                  @endforeach
              </select>
                </form>
            </div>
        @endif

    </div>
@endif
