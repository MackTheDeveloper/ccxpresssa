<?php


use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Purchase;
use QuickBooksOnline\API\Data\IPPPurchase;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
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

?>

<!DOCTYPE html>
<html>
<head>
    
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

                var win = window.open(url, 'connectPopup', parameters);
                var pollOAuth = window.setInterval(function () {
                    try {

                        if (win.document.URL.indexOf("code") != -1) {
                            window.clearInterval(pollOAuth);
                            win.close();
                            <?php if(checkloggedinuserdata() == 'Cashier') { ?>
                                window.location.href = "<?php echo route('homecashier'); ?>";
                            <?php } else { ?>
                                window.location.href = "<?php echo route('home'); ?>";
                            <?php } ?>
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


<div style="background: #d43838;
    padding: 10px;
    margin-bottom: 20px;
    font-size: 20px;
    font-weight: bold;color:#fff">
    You are not connected with the QuickBooks.
</div>
    
    <a class="imgLink" href="#" onclick="oauth.loginPopup()"><?php echo e(Html::image('images/C2QB_green_btn_lg_default.png', 'alt text', array('class' => 'css-class','style'=>'height:50px'))); ?></a>
    


    

</body>
</html>