<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Portal database settings
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default database table prefix that should be used
    | by the model.
    |
    */

    'database' => [
      'table_prefix' => env('TABLE_PREFIX', 'tbl'),
    ],
    'hash' => [
       'cc_encryption_hash' => env('CC_ENCRYPTION_HASH','PhlOWw5BeG2EsHjZDGKBgSJboKt5Z5Ydd6HFwbG0Sx5ceZlLWYHXgZpFoDWaG8tC'),
    ],
    'config' => [
      'api_access_key'      => env('api_access_key',''),
      'api_enable_logging'  => env('api_enable_logging',''),
      'autoauthkey'         => env('autoauthkey',''),
      'outbound_http_proxy' => env('outbound_http_proxy',''),
      'outbound_http_ssl_verifyhost' => env('outbound_http_ssl_verifyhost',''),
      'outbound_http_ssl_verifypeer' => env('outbound_http_ssl_verifypeer',''),
      'serialize_input_max_length' => env('serialize_input_max_length',''),
      'serialize_array_max_length' => env('serialize_array_max_length',''),
      'serialize_array_max_depth' => env('serialize_array_max_depth',''),
      'use_legacy_client_ip_logic' => env('use_legacy_client_ip_logic', ''),
    ],
   'outbound_http_proxy' => '',
   'outbound_http_ssl_verifyhost' => '',
   'outbound_http_ssl_verifypeer' => '',
   'disable_whmcs_domain_lookup' => false,
   'domain_lookup_url' => '',
   'domain_lookup_key' => '',
];
