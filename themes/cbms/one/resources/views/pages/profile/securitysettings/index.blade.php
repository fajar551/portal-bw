@extends('layouts.clientbase')

@section('title')
   Security Settings
@endsection

@section('page-title')
   Security Settings
@endsection

<!-- Di awal view, tambahkan ini sementara untuk debugging -->
{{-- @php
    dd([
        'twoFactorAuthEnabled' => $twoFactorAuthEnabled ?? null,
        'twoFactorAuthAvailable' => $twoFactorAuthAvailable ?? null
    ]);
@endphp --}}

{{-- Debugging --}}
{{-- @if($twoFactorAuthAvailable)
    <div>2FA Available</div>
    @if(!$twoFactorAuthEnabled)
        <div>2FA Not Enabled - Should show Enable button</div>
    @else
        <div>2FA Enabled - Should show Disable button</div>
    @endif
@else
    <div>2FA Not Available</div>
@endif --}}

@section('content')
   <div class="page-content">
      <div class="container-fluid">
         <div class="card p-3">
            @if ($twoFactorAuthAvailable)
            <div class="row">
               <div class="col-sm-12 col-md-10">
                  <h2>{{Lang::get('client.twofactorauth')}}</h2>
                  <p class="twofa-config-link disable{{!$twoFactorAuthEnabled? ' d-none':''}}">
                     {{Lang::get('client.twofacurrently')}} <strong>{{strtolower(Lang::get('admin.enabled'))}}</strong>
                  </p>
                  <p class="twofa-config-link enable{{$twoFactorAuthEnabled? ' d-none':''}}">
                     {{Lang::get('client.twofacurrently')}} <strong>{{strtolower(Lang::get('admin.disabled'))}}</strong>
                  </p>
                  @include('includes.alert', [
                     'type' => 'warning',
                     'msg' => Lang::get('client.clientAreaSecurityTwoFactorAuthRecommendation'),
                  ])
                  <p class="mt-3">
                     Two-Factor Authentication adds an extra layer of protection to logins. Once enabled & configured,
                     each
                     time you sign in you will be asked to enter both your username & password as well as a second
                     factor
                     such as a security code.
                  </p>
                  @if($twoFactorAuthAvailable)
    <!-- konten two factor -->
                    <div class="text-center">
                        @if(!$twoFactorAuthEnabled)
                            <a href="{{ route('account-security-two-factor-enable') }}" class="btn btn-success">
                                {{ Lang::get('client.twofaenableclickhere') }}
                            </a>
                        @else
                            <a href="{{ route('account-security-two-factor-disable') }}" class="btn btn-danger">
                                {{ Lang::get('client.twofadisableclickhere') }}
                            </a>
                        @endif
                    </div>
                @endif
                </div>
                  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                     aria-labelledby="exampleModalLabel" aria-hidden="true">
                     <div class="modal-dialog" role="document">
                        <div class="modal-content">
                           <div class="modal-header" style="background: #252B3B;">
                              <h5 class="text-white modal-title" id="exampleModalLabel">{{ __('client.twofasetup') }}</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                 <span aria-hidden="true">&times;</span>
                              </button>
                           </div>
                           <div class="modal-body">
                              <p>Two-Factor Authentication adds an extra layer of protection to logins. Once enabled &
                                 configured, each time you sign in you will be asked to enter both your username & password
                                 as well as a second factor such as a security code.</p>
                                 <div class="text-center">
                                    <button class="px-3 my-3 btn btn-primary">Get Started</button>
                                 </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            @endif
            {{-- @if ($showSsoSetting && !$twofaactivation) --}}
            <div class="row mt-5 d-none">
               <div class="col-sm-12 col-md-10">
                  <h3 class="mb-3">Single Sign-On</h3>
                  <div class="alert alert-success" role="alert">
                     Third party applications leverage the Single Sign-On functionality to provide direct access to your
                     billing account without you having to re-authenticate.
                  </div>

                  <form id="frmSingleSingOn">
                     <input type="hidden" name="token" value="" />
                     <input type="hidden" name="action" value="security" />
                     <input type="hidden" name="toggle_sso" value="1" />
                     <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="customSwitch1">
                        <label class="custom-control-label" for="customSwitch1">Single Sign-On is currently permitted for
                           your account.</label>
                     </div>
                     <p>
                        You may wish to disable this functionality if you provide access to any of your third party
                        applications to users who you do not wish to be able to access your billing account.
                     </p>
                  </form>
               </div>
            </div>
            {{-- @endif --}}
         </div>
      </div> <!-- container-fluid -->
   </div>
@endsection

@section('scripts')
   <script type="text/javascript" src="{{Theme::asset('js/scripts.js')}}"></script>
@endsection
