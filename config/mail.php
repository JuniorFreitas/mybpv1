<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | messages sent by your application. Alternative mailers may be setup
    | and used as needed; however, this mailer will be used by default.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers to be used while
    | sending an e-mail. You will specify which one you are using for your
    | mailers below. You are free to add additional mailers as required.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses",
    |            "postmark", "log", "array"
    |
    */

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => '/usr/sbin/sendmail -bs',
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirecionamento em APP_ENV=local
    |--------------------------------------------------------------------------
    |
    | Quando APP_ENV=local, Mail::alwaysTo() força todos os envios para este
    | endereço (útil para testar SES sem atingir usuários reais).
    |
    */

    'local_redirect_to' => env('MAIL_LOCAL_REDIRECT_TO') ?: env('MAIL_LOCAL_ADDRESS') ?: 'josedejesusjunior@gmail.com',

    /*
    |--------------------------------------------------------------------------
    | CC / BCC em local + SES
    |--------------------------------------------------------------------------
    |
    | Somente quando APP_ENV=local e MAIL_MAILER=ses: qualquer CC ou BCC é
    | substituído por um único BCC para este endereço (teste sem cópias reais).
    |
    */

    'local_redirect_cc_bcc' => env('MAIL_LOCAL_REDIRECT_CC_BCC') ?: 'juniorfreitas@dynamusti.com.br',

    /*
    |--------------------------------------------------------------------------
    | Destinatários suprimidos (nunca recebem envio)
    |--------------------------------------------------------------------------
    |
    | Lista de endereços removidos de To/Cc/Bcc antes do envio. Se não restar
    | nenhum destinatário, a mensagem não é enviada. Comparado sem diferenciar
    | maiúsculas/minúsculas. No .env use MAIL_SUPPRESS_RECIPIENTS separado por vírgula.
    |
    */

    'suppress_recipients' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('MAIL_SUPPRESS_RECIPIENTS', 'sistema@mybp.com.br'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
