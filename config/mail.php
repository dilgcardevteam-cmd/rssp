<?php

$normalizeEnvString = static function ($value, string $default = ''): string {
    $normalized = trim((string) $value);

    if ($normalized === '' || strtolower($normalized) === 'null') {
        return $default;
    }

    return $normalized;
};

$fromAddress = $normalizeEnvString(env('MAIL_FROM_ADDRESS', 'hello@example.com'), 'hello@example.com');
$fallbackLocalDomain = str_contains($fromAddress, '@')
    ? ltrim(strrchr($fromAddress, '@'), '@')
    : (parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost');
$configuredScheme = strtolower($normalizeEnvString(env('MAIL_SCHEME', '')));
$configuredEncryption = strtolower($normalizeEnvString(env('MAIL_ENCRYPTION', '')));

if ($configuredScheme === 'tls') {
    $configuredScheme = 'smtp';
}

if ($configuredScheme === 'ssl') {
    $configuredScheme = 'smtps';
}

$smtpScheme = $configuredScheme !== ''
    ? $configuredScheme
    : ($configuredEncryption === 'ssl' ? 'smtps' : 'smtp');

if (!in_array($smtpScheme, ['smtp', 'smtps'], true)) {
    $smtpScheme = $configuredEncryption === 'ssl' ? 'smtps' : 'smtp';
}

$autoTls = filter_var((string) env('MAIL_AUTO_TLS', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
if ($autoTls === null) {
    $autoTls = true;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            // Valid schemes are smtp / smtps. TLS should be controlled via encryption/auto_tls.
            'scheme' => $smtpScheme,
            'encryption' => $configuredEncryption !== '' ? $configuredEncryption : null,
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => env('MAIL_TIMEOUT', 30),
            'auto_tls' => $autoTls,
            'local_domain' => env('MAIL_EHLO_DOMAIN', $fallbackLocalDomain),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

];
