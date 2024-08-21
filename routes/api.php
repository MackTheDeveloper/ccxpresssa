<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::match(['get', 'post'], '/filemanager', function (Request $request) {
    require_once base_path('public/connectors/php/events.php');

    function fm_authenticate()
    {
        // Customize this code as desired.
        return true;
    }

    function fm_has_read_permission($filepath)
    {
        // Customize this code as desired.
        return true;
    }

    function fm_has_write_permission($filepath)
    {
        // Customize this code as desired.
        return true;
    }

    $app = new \RFM\Application();
	$s3 = new \RFM\Repository\S3\Storage();
	$s3->setRoot('Files', true, true);
	$app->setStorage($s3);
	$app->api = new RFM\Api\AwsS3Api();
	$app->run();
});