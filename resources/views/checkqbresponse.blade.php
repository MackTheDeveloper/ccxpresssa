<?php


use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Purchase;
use QuickBooksOnline\API\Data\IPPPurchase;
use QuickBooksOnline\API\Data\IPPVendor;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\Data\IPPAccount;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Data\IPPItem;
use QuickBooksOnline\API\Facades\Item;

$config = Config::get('app.QB');

session_start();
//session_destroy();
$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $config['client_id'],
    'ClientSecret' =>  $config['client_secret'],
    'RedirectURI' => $config['oauth_redirect_uri'],
    'scope' => $config['oauth_scope'],
    'baseUrl' => "development"
));

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();


// Store the url in PHP Session Object;
$_SESSION['authUrl'] = $authUrl;
//pre($_SESSION['sessionAccessToken']);
//set the access token using the auth object
if (isset($_SESSION['sessionAccessToken'])) {

    $accessToken = $_SESSION['sessionAccessToken'];
    $accessTokenJson = array('token_type' => 'bearer',
        'access_token' => $accessToken->getAccessToken(),
        'refresh_token' => $accessToken->getRefreshToken(),
        'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
        'expires_in' => $accessToken->getAccessTokenExpiresAt()
    );
    $dataService->updateOAuth2Token($accessToken);
    $oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
    $companyInfo = $dataService->getCompanyInfo();
    
   //pre($dataService->getLastError());
    
    /*
    Code to create vendor

    $vendorObj = new IPPVendor();
    $vendorObj->Id = 58;
    $vendorData = $dataService->FindById($vendorObj);
    $allVendor = $dataService->FindAll('Customer');
    pre($allVendor);
    $companyExist = 0;
    
    // for($i=0;$i<count($allVendor);$i++){
    //     if($i==1){
    //         $allVendor[$i]->CompanyName;
    //     }
    // }
    //pre($allVendor);
    //pre($dataService->getLastError());

    $newVendor = [
        'BillAddr' => [
            'Line1' => '1202 Landmark',
            'City' => 'Ahm',
            'Country' => 'IND',
            'CountrySubDivisionCode' => 'Guj',
            'PostalCode' => '382418'
        ],

        'Vendor1099' => 'false',
        'CurrencyRef' => 'USD',
        'CompanyName' => 'New Magneto',
        'DisplayName' => 'New Magneto',
        'PrintOnCheckName' => 'New Magneto',
        'PrimaryPhone' => [

            'FreeFormNumber' => '123456789'


        ],

        'PrimaryEmailAddr' => [

            'Address' => 'abc@gmail.com'

        ]

    ];

    //pre($newVendor);
    $addVendor = Vendor::create($newVendor);
    $createdVendor = $dataService->Add($addVendor);
    //pre($createdVendor);
    */ 
    //End Vendor
    



    /* -- Quickbooks Account --  
    
    $accountObj = new IPPAccount();
    //pre($accountObj);
    $accountObj->Id = 95;
    $accountData = $dataService->FindById($accountObj);
    $allVendor = $dataService->FindAll('Customer');
    // pre($accountData);
    // pre($accountObj);

    $accountArr = [
        'Name' => 'Cash Credit Account',
        'Description' => '',
        'FullyQualifiedName' => 'Cash on hand',
        'Classification' => 'Asset',
        'AccountType' => 'Bank',
        'AccountSubType' => 'CashOnHand',
        'OpeningBalance' => 10000,
        'CurrentBalance' => 9000,
        'OpeningBalanceDate' => date('Y-m-d'),
        'CurrencyRef' => 'USD'
    ];
    //$addAccount = Account::create($accountArr);
    //pre($addAccount);
    //$createdAccount = $dataService->Add($addAccount);
    
    $updatedAccount = Account::update($accountData,$accountArr);
    pre($updatedAccount);
    $updated = $dataService->Update($updatedAccount);
    pre($dataService->getLastError());
    //pre($createdAccount);
    pre($updated);


    // And Quickbooks Account
    */ 


    /* --Quickbooks Items-- */
    
    /*

    $itemObj = new IPPItem();
    $itemObj->Id  = 26;
    $itemData = $dataService->FindById($itemObj);
    $allItem = $dataService->FindAll('Item');
    // pre($allItem);
    $IncomeAccountRef = $dataService->Query("select * from Account where Name = 'Sales of Product Income'");
    $ExpenseAccountRef = $dataService->Query("select * from Account where Name = 'Cost of Goods Sold'");
    $AssetAccountRef = $dataService->Query("select * from Account where Name = 'Inventory Asset'");
    //pre($AssetAccountRef);


    $itemArr = [
        
                'Name' => 'Sprinkler',
                'Active' => true,
                'FullyQualifiedName' => 'pipes',
                'Taxable' => true,
                'UnitPrice' => 200,
                'Type' => 'Inventory',
                'PurchaseCost' => 100,
                'TrackQtyOnHand' => true,
                'QtyOnHand' => 1,
                'InvStartDate' => date('Y-m-d'),
                'IncomeAccountRef' => [
                    'Name' => $IncomeAccountRef[0]->Name,
                    'Value' => $IncomeAccountRef[0]->Id
                ],
                'ExpenseAccountRef' => [
                    'Name' => $ExpenseAccountRef[0]->Name,
                    'Value' => $ExpenseAccountRef[0]->Id
                ],
                'AssetAccountRef' => [
                    'Name' => $AssetAccountRef[0]->Name,
                    'Value' => $AssetAccountRef[0]->Id
                ]
            ];


            
    $addItem = Item::create($itemArr);
    //pre($addItem);
    $createdItem = $dataService->Add($addItem);
    //pre($dataService->getLastError());
    pre($addItem);
    */
    
    /*End Quickbooks Item*/




    






    

    /*$allCustomers = $dataService->FindAll("Customer");
    pre("ddd",1);
    pre($allCustomers);*/

    /*$purchaseObj = new IPPPurchase();
    $purchaseObj->Id= 89;
    $companyInfo = $dataService->FindById($purchaseObj);
    pre($companyInfo);*/

    /*$invoiceToCreate = Invoice::create([
          "DocNumber" => "101",
          "Line" => [
            [
              "Description" => "Sewing Service for Alex",
              "Amount" => 150.00,
              "DetailType" => "SalesItemLineDetail",
              "SalesItemLineDetail" => [
                "ItemRef" => [
                  "value" => 1,
                  "name" => "Services"
                ]
              ]
            ]
          ],
          "CustomerRef" => [
              "value" => "1",
              "name" => "Alex"
          ]
        ]);
     pre($invoiceToCreate);
     $resultObj = $dataService->Add($invoiceToCreate);
     $error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}else {
    echo "Created Id={$resultObj->Id}. Reconstructed response body:\n\n";
}*/
    /*$invoice = Invoice::create([
        "Id" => "147",
        "SyncToken" => "0"
    ]);
    $CompanyInfo = $dataService->Delete($invoice);
    if ($CompanyInfo) {
    echo "Delete the purchase object that we just created.\n";
    } else {
        echo "Did not delete the purchase object that we just created.\n";
    }*/
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="apple-touch-icon icon shortcut" type="image/png" href="https://plugin.intuitcdn.net/sbg-web-shell-ui/6.3.0/shell/harmony/images/QBOlogo.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="views/common.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script>

        var url = '<?php echo $authUrl; ?>';

        var OAuthCode = function(url) {

            this.loginPopup = function (parameter) {
                this.loginPopupUri(parameter);
            }

            this.loginPopupUri = function (parameter) {

                // Launch Popup
                var parameters = "location=1,width=800,height=650";
                parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;
                console.log('check qb response parameters',parameters)
                var win = window.open(url, 'connectPopup', parameters);
                console.log('check qb response',win)
                var pollOAuth = window.setInterval(function () {
                    try {

                        if (win.document.URL.indexOf("code") != -1) {
                            window.clearInterval(pollOAuth);
                            win.close();
                            location.reload();
                        }
                    } catch (e) {
                        console.log(e)
                    }
                }, 100);
            }
        }


        var apiCall = function() {
            var urlztnn = '<?php echo url("qb/apiCall"); ?>';
            this.getCompanyInfo = function() {
                /*
                AJAX Request to retrieve getCompanyInfo
                 */
                $.ajax({
                    type: "GET",
                    url: urlztnn,
                }).done(function( msg ) {
                    $( '#apiCall' ).html( msg );
                }).fail(function( msg ) {
                    console.log(msg);
                });
            }

            this.refreshToken = function() {
                $.ajax({
                    type: "POST",
                    url: "refreshToken.php",
                }).done(function( msg ) {

                });
            }
        }

        var oauth = new OAuthCode(url);
        var apiCall = new apiCall();
    </script>
</head>
<body>

<div class="container">

    <h1>
        <a href="http://developer.intuit.com">
            <img src="views/quickbooks_logo_horz.png" id="headerLogo">
        </a>

    </h1>

    <hr>

    <div class="well text-center">

        <h1>QuickBooks HelloWorld sample application</h1>
        <h2>Demonstrate Connect to QuickBooks flow and API Request</h2>

        <br>

    </div>

    <p>If there is no access token or the access token is invalid, click the <b>Connect to QuickBooks</b> button below.</p>
    <pre id="accessToken">
        <style="background-color:#efefef;overflow-x:scroll"><?php
    $displayString = isset($accessTokenJson) ? $accessTokenJson : "No Access Token Generated Yet";
    echo json_encode($displayString, JSON_PRETTY_PRINT); ?>
    </pre>
    <a class="imgLink" href="#" onclick="oauth.loginPopup()">{{ Form::image('images/C2QB_green_btn_lg_default.png', 'alt text', array('class' => 'css-class')) }}</a>
    <hr />


    <h2>Make an API call</h2>
    <p>If there is no access token or the access token is invalid, click either the <b>Connect to QucikBooks</b> button above.</p>
    <pre id="apiCall"></pre>
    <button  type="button" class="btn btn-success" onclick="apiCall.getCompanyInfo()">Get Company Info</button>

    <hr />

</div>
</body>
</html>