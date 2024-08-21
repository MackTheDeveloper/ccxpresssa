<?php

return [

    'dept' => [
        '1' => 'Admin',
        '2' => 'Finance',
        '3' => 'Packaging',
        '4' => 'Expense',
        ],
    'expenseType' => [
        '1' => 'Expense 1',
        '2' => 'Expense 2',
        '3' => 'Expense 3',
        ],
    'userStatus' => [
        '1' => 'Active',
        '0' => 'In active',
    ],
    'productTaxType' => [
        '0' => 'None',
        '1' => 'TCA',
    ],
    'currency' => [
        '1' => 'USD',
        '2' => 'GDES',
    ],
    'cargoOperationType' => [
        '1' => 'IMPORT',
        '2' => 'EXPORT',
        '3' => 'LOCALE',
    ],
    'adminEmail' => 'mphp.magneto@gmail.com',
    'contactType' => ['1' => 'Mobile', '2' => 'Phone', '3' => 'Direct Phone', '4' => 'WhatsApp', '5' => 'Skype', '6' => 'QQ Chat', '7' => 'Other'],
    
    'shipmentStatus' => [
        '1' => 'Completed',
        '2' => 'Incomplete',
        '3' => 'Short Shipped'
    ],
    'deliveryStatus' => [
        '1' => 'Delivered',
        '2' => 'Returned',
        '3' => 'Out for Delivery',
    ],
    'reasonOfReturn' => [
        '1' => 'Wrong Address',
        '2' => 'Decline by consignee',
        '3' => 'Consignee absent',
        '100' => 'Other',
    ],
    'warehouseStatus' => [
        '1' => 'Shipment Received',
        '2' => 'Shipment Missing',
        '3' => 'Shipment Delivered'
    ],
    'upsStatus' => [
        '1' => 'In Review',
        '2' => 'In Progress',
        '3' => 'Destination Scan',
        '4' => 'Warehouse Scan',

    ],
    'verifyFileWarehouse' => [
        '0' => 'Pending',
        '1' => 'Done',
    ],
    'customInspectionFileStatus' => [
        '0' => 'Held by customs',
        '1' => 'Inspected',
        '2' => 'Inspection free',
    ],
    'inspectionFileWarehouse' => [
        '0' => 'Pending',
        '1' => 'Done',
        '2' => 'In progress',
    ],
    'NonBoundedWarehouseConfirmation' => [
        '0' => 'Pending',
        '1' => 'Done',
        '2' => 'In progress',
    ],
    'storageCharges' => [
        '1' => 'Day',
        '2' => 'Weekly',
        '3' => 'Monthly',
    ],
    'rackDepartment' => [
        'S' => 'S (Storage)',
        'P' => 'P (Production)',
    ],
    'measureMass' => [
        'k' => 'Kg',
        'p' => 'Pound',
    ],

    'measureDimension' => [
        'm' => 'Cubic meter',
        'f' => 'Cubic feet',
    ],
    'months'=>[
        '1'=>'1',
        '2'=>'2',
        '3'=>'3',
        '4'=>'4',
        '5'=>'5',
        '6'=>'6',
        '7'=>'7',
        '8'=>'8',
        '9'=>'9',
        '10'=>'10',
        '11'=>'11',
        '12'=>'12',
    ],
    'monthsPrefixZero'=>[
        '01'=>1,
        '02'=>2,
        '03'=>3,
        '04'=>4,
        '05'=>5,
        '06'=>6,
        '07'=>7,
        '08'=>8,
        '09'=>9,
        '10'=>10,
        '11'=>11,
        '12'=>12,
    ],
    'warehouseFor' => [
        'Courier' => 'Courier',
        'Cargo' => 'Cargo',
    ],

    'exportPrepaid' => [
      //in percentage
        'lt&doc'=>15,
        'package'=>20,
    ],

    'exportCollect' => [
        //in doller
        'lt' => 6,
        'doc' => 6.5,
        'singlePkg' => 7,
        'mulPkg' => 2.50,
    ],

    'importPrepaid' => [
        //
        'lt' => 7.25,
        'doc' => 7.50,
        'singlePkg' => 7,
        'multiPkg' => 2.50
    ],

    'importCollect' => [
        'lt' => 7.50,
        'doc' => 8,
        'singlePkg' => 9.25,
        'multiPkg' => 2.50
    ],

    'productType' => [
        'LTR' => 'Letter',
        'DOC' => 'Document',
        'PKG' => 'Package',
    ],

    'fileType' =>[
        'i' => 'Import',
        'e' => 'Export',
    ],

    'scanArr' => [
        '2' => 'All Files',
        '3' => 'Import Scan',
        '4' => 'Warehouse Scan',
        '5' => 'Physical Scan',
        '6' => 'Delivery Scan',
    ],

    //Scan Status for CCpack and Aeropost
    'scan_status' => [
        1 => 'Visual confirmation',
        2 => 'Warehouse scan',
    ],

    'ups_new_scan_status' => [
        //''  => '',
        '1' => 'Move to none bounded warehouse',
        '2' => 'In Progress',
        '3' => 'Import Scan Done',
        '4' => 'Warehouse Scan Done',
        '5' => 'Physical Scan Done',
        '6' => 'Delivered',
        '7' => 'Returned',
        '8' => 'Out for Delivery',
        '9' => 'Documents submitted to client',
        '10' => 'Livraison au comptoir',
        '11' => 'Certificat de fret remis au consignataire',
        '12' => 'En attente de docs additionnels du client',
        '13' => 'Return to Sender',
        '14' => 'Document submitted to client'
    ],

    'adminManagers' => [
        '0' => 'Manager',
        '1' => 'Admin',
        '2' => 'Administration/ Utilisateur',
    ],

    'durationForAccountPayable' => [
        '0' => 'All',
        '1' => '15 days or less',
        '2' => '30 days or less',
        '3' => '30+ days',
    ],

    //'nonBoundedWHName' => 'Courier Non-Bounded Warehouse',
    'nonBoundedWHName' => 'Dépot Courrier Hors Douane',
    //'boundedWHName' => 'Bounded Warehouse',
    'boundedWHName' => 'Dépot Courrier sous Douane',

    'cargoNonBoundedWHName' => 'Dépot Cargo hors Douane',
    'cargoBoundedWHName' => 'Dépot Cargo Sous Douane',

    'upsMasterImport' => [
        'consignee' => 'CHATELAIN CARGO SERVICE S.A USD',
        'shipper' => 'UPS Miami USD',
    ],
    'upsMasterExport' => [
        'consignee' => 'UPS Miami USD',
        'shipper' => 'CHATELAIN CARGO SERVICE S.A USD',
    ],
    'aeropostMasterImport' => [
        'consignee' => 'CHATELAIN CARGO SERVICE S.A USD',
        'shipper' => 'Aeropost Miami USD',
    ],
    'ccpackMasterImport' => [
        'consignee' => 'CHATELAIN CARGO SERVICE S.A USD',
        'shipper' => 'CHATELAIN CARGO SERVICES INC USD',
    ],

    'QB' => [
        'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
        'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',

        // Manish App
        // 'client_id' => 'Q0SUBGyzNGylm3OddXp2xXUGxVVKejc1KlDN8X5VZsvdg7mdO4',
        // 'client_secret' => 'Sbx8zuDKQRmyZFdqYXG558uU3jXXaSLQXeeOxNhm',



        //Harsh App
        /*'client_id' => 'Q00FAhflTtK2OXNlwIzibltVyY750JaOHDWi3mMrflo1dHfKIc',
        'client_secret' => '5Z0tzlxpziYLzIQwLr2QxQs44A7DeNv2a5yBR5BA',*/

        // Client Account
        /*'client_id' => 'L0oF6YzVp1LB5D9OhcBgdbF4s83K7FRqM7MH7Dg7hRaw5Z7TRC',
        'client_secret' => 'zROf4xNzzbbGlmQENYA1YWhUyMjNqMBx42oC1hwy',*/

        // New Live App
        /* 'client_id' => 'BBH1ckeGpLvndDwavHEmL3NvGuDGpq5HY7G6GmKRlQnZBUgm7h',
        'client_secret' => 'byQVm0IZucLjd5wP1jQwbzxlSzPOEoR7niQ4lL52', 

        'oauth_scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
        
        //'oauth_redirect_uri' => 'http://localhost/cargo_new/qb/callback',
        //'oauth_redirect_uri' => 'http://localhost/cargo/qb/callback',
        //'oauth_redirect_uri' => 'http://quickbook.ccxpresssa.com/qb/callback',
        'oauth_redirect_uri' => 'https://ccxpresssa.com/qb/callback', */
        //'oauth_redirect_uri' => 'https://quickbook.ccxpresssa.com/qb/callback',

        // New test account (sunny@magnetoitsolutions.com)
        //'client_id' => 'ABkzA0SyeavXmPntExhfwxLDAuNuvyzd98EmCTtqUyJUKhetXN',
        //'client_secret' => 'l3HcEKt5f5464DNJkwAK7hyE56EdZ7QqytYmHWS8',

        // Client Live account
        'client_id' => 'ABwtBIJXlozhJkyIO7SLfjTT4SkwVM9NXkTXiKTjVZwvVVFTdm',
        'client_secret' => 'j9aJbXQHnoWyePPpYg3l5AP7D1FWLdxZyUmDA8Zj',

        'oauth_scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
        'oauth_redirect_uri' => 'https://ccxpresssa.com/qb/callback',
        
    ],
    
    'fileTypes' => [
        '1' => 'Custom Document',
        '2' => 'Custom Verification Document'
    ],

    'quickbook_modules' => [
        '0'  =>  'Cost',
        '1'  =>  'Vendor',
        '2'  =>  'Cash/Bank Account',
        '3'  =>  'Billing Item',
        '4'  =>  'UPS House Expense',
        '5'  =>  'Cargo Expense',
        '6'  =>  'Cargo Invoice',
        '7'  =>  'UPS Invoice',
        '8'  =>  'CCapck Invoice',
        '9'  =>  'Aeropost Invoice',
        '10' =>  'Expense',
        '11' =>  'Client',
        '12' =>  'Currency',
        '13' =>  'House File Invoice',
        '14' =>  'Aeropost House Expense',
        '15' =>  'CCPack House Expense',
        '16' =>  'House File Expense',
        '17' =>  'Other Accounts',
        '18' =>  'UPS Master Invoice',
        '19' =>  'Aropost Master Invoice',
        '20' =>  'CCPack Master Invoice',
        '21' =>  'UPS Master Expense',
        '22' =>  'Aropost Master Expense',
        '23' =>  'CCPack Master Expense',
    ],
    'paymentMethod' => [
        'Cash'  =>  'Cash',
        'Credit Card' => 'Credit Card',
        'Bank Transfer' => 'Bank Transfer',
        'Cheque' => 'Cheque',
        'American Express' => 'American Express',
        'CARTE DE CREDIT' => 'CARTE DE CREDIT',
        'COMPESATION' => 'COMPESATION',
        'Debit Card' => 'Debit Card',
        'DEPOSIT' => 'DEPOSIT',
        'Discover' => 'Discover',
        'E-Cheque' => 'E-Cheque',
        'Gift Card' => 'Gift Card',
        'MasterCard' => 'MasterCard',
        'SPIH' => 'SPIH',
        'Visa' => 'Visa',
        'Web Payment' => 'Web Payment'
    ],

    'googleReCaptchaKeys' =>
    [
        // original live captcha
        'siteKey' => '6LebQdoaAAAAAH-2zGJKRogaoz4SF_z9fTABfbnx',
        'secretKey' => '6LebQdoaAAAAAM_JkppOQy9peeP2W30P5qKKfY-4',

        // // DIlpesh account captcha
        // 'siteKey' => '6Lc27tAlAAAAALCtUORczCg1tdPEfc9ZPBrOGwi2',
        // 'secretKey' => '6Lc27tAlAAAAAFKlN_ktODUuA49Eoy9qUptDmeLs',
    ],



    


    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    */

    //'name' => env('APP_NAME', 'Laravel'),
    //'name' => 'Courier and Cargo Management',
    'name' => 'Cargo Management',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => env('APP_LOG', 'single'),

    'log_level' => env('APP_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        
        /*
         * Package Service Providers...
         */
        Laravel\Tinker\TinkerServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        // Collective\Html\HtmlServiceProvider::class,
        'Maatwebsite\Excel\ExcelServiceProvider',
        // niklasravnsborg\LaravelPdf\PdfServiceProvider::class,
        Mccarlosen\LaravelMpdf\LaravelMpdfServiceProvider::class


    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        // 'Form' => Collective\Html\FormFacade::class,
        // 'Html' => Collective\Html\HtmlFacade::class,
        'Excel' => 'Maatwebsite\Excel\Facades\Excel',
        // 'PDF' => niklasravnsborg\LaravelPdf\Facades\Pdf::class,
        'PDF' => Mccarlosen\LaravelMpdf\Facades\LaravelMpdf::class,
        'Form' => App\Facades\FormFacade::class,
        'Html' => Spatie\Html\Facades\Html::class,
    ],

];
