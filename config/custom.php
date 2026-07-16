<?php
return [
    'APP'            => [
        'APP_URL'           => env('APP_URL'),
        'API_KEY'           => env('API_KEY'),
        'DEBUG_KEY'         => env('DEBUG_KEY'),
        'WEBHOOK_SECRET'     => env('WEBHOOK_SECRET'),
        'VERIFICATION_LINK' => env('VERIFICATION_LINK', 'https://nation.africa/kenya/account/email-verification'),
        'IOS_VERIFICATION'  => env('IOS_VERIFICATION', 'https://nmg-apple-transactions-manager-clh4z76qra-ew.a.run.app/api/v1/get-receipt-history')
    ],
    'CUSTOMER'       => [
        'CONCURRENT_LOGINS'     => env('CONCURRENT_LOGINS', 3),
        'COVERED_REGIONS'       => env('COVERED_REGIONS'),
        'TOKEN_EXPIRY'          => env('TOKEN_EXPIRY', 30),
        'REFRESH_TOKEN_EXPIRY'  => env('REFRESH_TOKEN_EXPIRY', 30),
        'PERSONAL_TOKEN_EXPIRY' => env('PERSONAL_TOKEN_EXPIRY', 30),
        'CUSTOMERCARE'          => env('CUSTOMERCARE', 'email : customercare@ke.nationmedia.com , Phone : 020-3288000, 0719-038000, 0732-038000'),
        //
    ],
    'AUTHENTICATION' => [
        'PASSWORD_MINIMUM_LENGTH'   => env('PASSWORD_MINIMUM_LENGTH'),
        'PASSWORD_COMPLEXITY_REGEX' => env('PASSWORD_COMPLEXITY_REGEX'),
        'PASSWORD_EXPIRY'           => env('PASSWORD_EXPIRY'),
        'LOGIN_EXPIRY'              => env('LOGIN_EXPIRY'),
    ],
    'MAIL'           => [
        'MAIL_HOST'         => env('MAIL_HOST'),
        'MAIL_PORT'         => env('MAIL_PORT'),
        'MAIL_USERNAME'     => env('MAIL_USERNAME'),
        'MAIL_PASSWORD'     => env('MAIL_PASSWORD'),
        'MAIL_ENCRYPTION'   => env('MAIL_ENCRYPTION'),
        'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
        'MAIL_FROM_NAME'    => env('MAIL_FROM_NAME'),
        'MAILGUN_API_KEY'   => env('MAILGUN_API_KEY'),
    ],
    'SOCIAL_LOGIN'   => [
        'GOOGLE_CLIENT_ID'       => env('GOOGLE_CLIENT_ID'),
        'GOOGLE_CLIENT_SECRET'   => env('GOOGLE_CLIENT_SECRET'),
        'GOOGLE_RETURN_URL'      => env('GOOGLE_RETURN_URL'),
        'FACEBOOK_CLIENT_ID'     => env('FACEBOOK_CLIENT_ID'),
        'FACEBOOK_CLIENT_SECRET' => env('FACEBOOK_CLIENT_SECRET'),
        'FACEBOOK_RETURN_URL'    => env('FACEBOOK_RETURN_URL'),
        'TWITTER_CLIENT_ID'      => env('TWITTER_CLIENT_ID'),
        'TWITTER_CLIENT_SECRET'  => env('TWITTER_CLIENT_SECRET'),
        'TWITTER_RETURN_URL'     => env('TWITTER_RETURN_URL'),
    ],
    'BILLING'        => [
        'RESERVED_CURRENCY' => env('RESERVED_CURRENCY', 'USD'),
        'DATA_WALL_COUNT'   => env('DATA_WALL_COUNT', false),
        'PAYWALL_COUNT'     => env('PAYWALL_COUNT', false),
        'STORY_EXPIRY'      => env('STORY_EXPIRY', 30)
    ],
    'MPESA'          => [
        'DEBUG_SHORTCODE'            => env('MPESA_DEBUG_SHORTCODE'),
        'DEBUG_INITIATOR'            => env('MPESA_DEBUG_INITIATOR'),
        'DEBUG_SECURITY_CREDENTIAL'  => env('MPESA_DEBUG_SECURITY_CREDENTIAL'),
        'DEBUG_CALLBACK_URL'         => env('MPESA_DEBUG_CALLBACK_URL'),
        'WHITELISTED_IP'             => env('WHITELISTED_IP', '127.0.0.1,196.201.214.200,196.201.214.206,196.201.213.114,196.201.214.207,196.201.214.208,196.201.213.44,196.201.212.127,196.201.212.138,196.201.212.129,196.201.212.136,196.201.212.74,196.201.212.69'),
        'BLACKLISTED_IP'             => env('BLACKLISTED_IP'),
        'BLACKLISTED_IP_ACTION'      => env('BLACKLISTED_IP_ACTION', 'block'),
        'ANY_OTHER_IP_ACTION'        => env('ANY_OTHER_IP_ACTION', 'notify'),
        'PAYMENT_NOTIFICATION_EMAIL' => env('PAYMENT_NOTIFICATION_EMAIL'),
    ],
    'DPO'            => [
        'PAYMENT_URL' => env('PAYMENT_URL', 'https://secure.3gdirectpay.com/payv3.php?ID=')
    ]

];
