@if (isset($linkableProviders) && ($linkableProviders || $hasLinkedProvidersEnabled) && $linkContext == 'linktable')
    <table id="tableLinkedAccounts" class="table display data-driven"
           data-ajax-url="{{$linkedAccountsUrl}}"
           data-on-draw-rebind-confirmation="true"
           data-lang-empty-table="{{Lang::get('client.remoteAuthn.noLinkedAccounts')}}"
    >
        <thead>
        <tr class="text-center">
            <th>{{Lang::get('client.remoteAuthn.provider')}}</th>
            <th>{{Lang::get('client.remoteAuthn.name')}}</th>
            <th>{{Lang::get('client.remoteAuthn.emailAddress')}}</th>
            <th>{{Lang::get('client.remoteAuthn.actions')}}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="4" class="text-center">{{Lang::get('client.remoteAuthn.noLinkedAccounts')}}</td>
        </tr>
        </tbody>
    </table>
@elseif (isset($linkableProviders))
    <div id="providerLinkingMessages" class="hidden">
        <p class="providerLinkingMsg-preLink-init_failed">
            <span class="provider-name"></span> {{Lang::get('client.remoteAuthn.unavailable')}}
        </p>
        <p class="providerLinkingMsg-preLink-connect_error">
            <strong>{{Lang::get('client.remoteAuthn.error')}}</strong> {{Lang::get('client.remoteAuthn.connectError')}}
        </p>
        <p class="providerLinkingMsg-preLink-complete_sign_in">
            {{Lang::get('client.remoteAuthn.completeSignIn')}}
        </p>
        <p class="providerLinkingMsg-preLink-2fa_needed">
            {{Lang::get('client.remoteAuthn.redirecting')}}
        </p>
        <p class="providerLinkingMsg-preLink-linking_complete">
            <strong>{{Lang::get('client.remoteAuthn.success')}}</strong> {{Lang::get('client.remoteAuthn.accountNowLinked')}}
        </p>
        <p class="providerLinkingMsg-preLink-login_to_link-signin-required">
            <strong>{{Lang::get('client.remoteAuthn.linkInitiated')}}</strong> {{Lang::get('client.remoteAuthn.oneTimeAuthRequired')}}
        </p>
        <p class="providerLinkingMsg-preLink-login_to_link-registration-required">
            <strong>{{Lang::get('client.remoteAuthn.linkInitiated')}}</strong> {{Lang::get('client.remoteAuthn.completeRegistrationForm')}}
        </p>
        <p class="providerLinkingMsg-preLink-checkout-new">
            <strong>{{Lang::get('client.remoteAuthn.linkInitiated')}}</strong> {{Lang::get('client.remoteAuthn.completeNewAccountForm')}}
        </p>
        <p class="providerLinkingMsg-preLink-other_user_exists">
            <strong>{{Lang::get('client.remoteAuthn.error')}}</strong> {{Lang::get('client.remoteAuthn.linkedToAnotherClient')}}
        </p>
        <p class="providerLinkingMsg-preLink-already_linked">
            <strong>{{Lang::get('client.remoteAuthn.error')}}</strong> {{Lang::get('client.remoteAuthn.alreadyLinkedToYou')}}
        </p>
        <p class="providerLinkingMsg-preLink-default">
            <strong>{{Lang::get('client.remoteAuthn.error')}}</strong> {{Lang::get('client.remoteAuthn.connectError')}}
        </p>
    </div>

    @if ($linkContext == 'registration')
        <div class="sub-heading">
            <span>{{Lang::get('client.remoteAuthn.titleSignUpVerb')}}</span>
        </div>
    @elseif ($linkContext == 'checkout-existing')
        <div class="sub-heading-borderless">
            <span>{{Lang::get('client.remoteAuthn.titleOr')}}</span>
        </div>
        <p class="small text-center text-muted">{{Lang::get('client.remoteAuthn.saveTimeByLinking')}}</p>
    @elseif ($linkContext == 'checkout-new')
        <div class="sub-heading">
            <span>{{Lang::get('client.remoteAuthn.titleSignUpVerb')}}</span>
        </div>
        <p class="small text-center text-muted">{{Lang::get('client.remoteAuthn.saveTimeByLinking')}}</p>
    @elseif ($linkContext == 'clientsecurity')
        <p>{{Lang::get('client.remoteAuthn.mayHaveMultipleLinks')}}</p>
    @endif

    <div class="providerPreLinking" data-link-context="{{$linkContext}}"
        data-hide-on-prelink="{{in_array($linkContext, ['clientsecurity','login']) ? '0':'1'}}"
        data-disable-on-prelink=0>
        <div class="social-signin-btns">
            @foreach ($linkableProviders as $provider)
                @if (in_array($linkContext, ['checkout-existing']))
                    {{$provider['login_button']}}
                @else
                    {{$provider['code']}}
                @endif
            @endforeach
        </div>
    </div>

    @if (!isset($customFeedback) || !$customFeedback)
        <div class="providerLinkingFeedback"></div>
    @endif
@endif
