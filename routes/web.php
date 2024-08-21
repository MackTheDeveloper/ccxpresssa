<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*App::bind('App\Component\MailComponent',function(){
	return new \App\Component\MailComponent();
});*/
/* Route::get('/', function () {
	return view('welcome');
}); */

Route::get('/', function () {
	if (auth()->check()) {
		return redirect()->route('home');
	} else {
		return view('errors/404');
	}
})->name('404error');

//Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('secureccxlogin', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('secureccxlogin', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
//Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/homeagent', 'HomeController@index')->name('homeagent');
Route::get('/homewarehouse', 'HomeController@index')->name('homewarehouse');
Route::get('/homecashier', 'HomeController@index')->name('homecashier');

Route::any('checknotificationscount', 'HomeController@checknotificationscount')->name('checknotificationscount');
Route::any('checknotifications', 'HomeController@checknotifications')->name('checknotifications');
Route::any('viewallnotifications', 'HomeController@viewallnotifications')->name('viewallnotifications');
Route::any('expensenotificationoffile/{expenseId?}/{flag?}', 'HomeController@expensenotificationoffile')->name('expensenotificationoffile');
Route::any('approveallexpense/{moduleId?}/{expenseId?}/{flag?}', 'HomeController@approveallexpense')->name('approveallexpense');
Route::any('approveallselectedexpense/{flag?}', 'HomeController@approveallselectedexpense')->name('approveallselectedexpense');
Route::any('approveallselectedadministrationexpense/{flag?}', 'HomeController@approveallselectedadministrationexpense')->name('approveallselectedadministrationexpense');




Route::group(
	['middleware' => ['auth', 'FilterDataBeforeSave']],
	function () {
		// User Management
		//Route::resource('/users','Auth\RegisterController');
		Route::get('/users', 'Auth\RegisterController@index')->name('users');
		Route::any('/user/edit/{id}', 'Auth\RegisterController@edit')->name('edituser');
		Route::any('/user/update/{id}', 'Auth\RegisterController@update');
		Route::any('/user/delete/{id}', 'Auth\RegisterController@destroy')->name('deleteuser');
		Route::post('/user/changeuserstatus', 'Auth\RegisterController@changeuserstatus');
		Route::post('/user/resetpassword', 'Auth\RegisterController@resetpassword');
		Route::any('/user/viewuserdetail/{id}', 'Auth\RegisterController@viewuserdetail');
		Route::any('/user/viewuseractivities/{id}', 'Auth\RegisterController@viewuseractivities');
		Route::any('user/checkuniqueemail', 'Auth\RegisterController@checkuniqueemail')->name('checkuniqueemail');
		//-- END-- User Management

		// Client Management
		//Route::resource('/users','Auth\RegisterController');
		Route::get('/clients/{flag?}', 'ClientsController@index')->name('clients');
		Route::get('clients/create/{flag?}', 'ClientsController@create')->name('createclient');
		Route::post('clients/store', 'ClientsController@store');
		Route::any('clients/edit/{id}/{flag?}', 'ClientsController@edit')->name('editclient');
		Route::any('clients/update/{id}', 'ClientsController@update');
		Route::any('clients/delete/{id}', 'ClientsController@destroy')->name('deleteclient');
		Route::any('clients/viewclientdetail/{id}', 'ClientsController@viewclientdetail');
		Route::post('clients/changeuserstatus', 'ClientsController@changestatus');
		Route::any('clients/resetpassword', 'ClientsController@resetpassword');
		Route::any('clients/viewclientactivities/{id}', 'ClientsController@viewclientactivities');
		Route::any('clients/getclientdata', 'ClientsController@getclientdata')->name('getclientdata');
		Route::any('clients/getstatesdata', 'ClientsController@getstatesdata')->name('getstatesdata');
		Route::get('clients/viewdetails/{id}/{flag?}', 'ClientsController@show')->name('viewdetails');
		Route::any('clients/checkuniquecompany', 'ClientsController@checkuniquecompany')->name('checkuniquecompany');
		Route::any('clients/copyclient', 'ClientsController@copyclient')->name('copyclient');
		Route::any('clientsdatatable/listbydatatableserverside', 'ClientsController@listbydatatableserverside')->name('listbydatatableserverside');

		//-- END-- Client Management

		// Permission Management
		Route::get('/permissions/{permissionFlag}', 'PermissionController@index')->name('permissions');
		Route::any('/permissions/getuser', 'PermissionController@getuser');
		Route::any('/permissions/filteroutside', 'PermissionController@filteroutside');
		//-- END-- Permission Management


		// Courier Management
		/*Route::group(
['middleware' => ['auth'],'prefix' => 'couriers'], 
function() {
	Route::get('/','CourierController@index')->name('couriers');
	Route::get('/create','CourierController@create')->name('createcourier');
	Route::post('/store','CourierController@store');
	Route::get('/import','CourierController@import')->name('importcourier');
	Route::post('/importdata','CourierController@importdata');
	Route::any('/edit/{id}','CourierController@edit')->name('editcourier');
	Route::any('/update/{id}','CourierController@update');
	Route::any('/delete/{id}','CourierController@destroy')->name('deletecourier');
});*/
		Route::get('couriers', 'CourierController@index')->name('couriers');
		Route::get('courier/create', 'CourierController@create')->name('createcourier');
		Route::post('courier/store', 'CourierController@store');
		Route::get('courier/import', 'CourierController@import')->name('importcourier');
		Route::post('courier/importdata', 'CourierController@importdata');
		Route::any('courier/edit/{id}', 'CourierController@edit')->name('editcourier');
		Route::any('courier/update/{id}', 'CourierController@update');
		Route::any('courier/delete/{id}', 'CourierController@destroy')->name('deletecourier');
		//-- END-- Courier Management


		// Ups Master Management
		Route::get('ups-master', 'UpsMasterController@index')->name('ups-master');
		Route::get('ups-master/create', 'UpsMasterController@create')->name('createupsmaster');
		Route::post('ups-master/store', 'UpsMasterController@store');
		Route::any('ups-master/edit/{id}/{fileType?}', 'UpsMasterController@edit')->name('editupsmaster');
		Route::any('ups-master/update/{id}', 'UpsMasterController@update');
		Route::any('ups-master/delete/{id}', 'UpsMasterController@destroy')->name('deleteupsmaster');
		Route::any('ups-master/checkuniqueupsmasterawbnumber', 'UpsMasterController@checkuniqueupsmasterawbnumber')->name('checkuniqueupsmasterawbnumber');
		Route::any('ups-master/view/{id}', 'UpsMasterController@show')->name('viewdetailsupsmaster');
		Route::post('ups-master/expandhousefiles', 'UpsMasterController@expandhousefiles');
		Route::any('ups-master/listingmasterups', 'UpsMasterController@listingmasterups')->name('listingmasterups');
		Route::any('ups-master/checkoperations', 'UpsMasterController@checkoperations')->name('checkoperationsupsmaster');
		Route::any('ups-master/getupsmasterdata', 'UpsMasterController@getupsmasterdata');
		Route::any('ups-master/print/{upsId}/{upsType}', 'UpsMasterController@print')->name('printupsmasterfile');
		Route::any('ups-master/exporttoexcelhousefiles/{masterUpsId?}', 'UpsMasterController@exporttoexcelhousefiles')->name('exporttoexcelhousefiles');
		// Agent
		Route::any('agent/ups-master/view/{id}', 'UpsMasterAgentController@show')->name('agentUpsMasterView');
		Route::any('agent/ups-master/assign-operations', 'UpsMasterAgentController@assignOperations')->name('agentUpsMasterAssignOperations');
		//--- Ups Master Management

		// Aeropost Master Management
		Route::get('aeropost-master', 'AeropostMasterController@index')->name('aeropost-master');
		Route::get('aeropost-master/create', 'AeropostMasterController@create')->name('createaeropostmaster');
		Route::post('aeropost-master/store', 'AeropostMasterController@store');
		Route::any('aeropost-master/edit/{id}', 'AeropostMasterController@edit')->name('editaeropostmaster');
		Route::any('aeropost-master/update/{id}', 'AeropostMasterController@update');
		Route::any('aeropost-master/delete/{id}', 'AeropostMasterController@destroy')->name('deleteaeropostmaster');
		Route::any('aeropost-master/checkuniqueaeropostmasterawbnumber', 'AeropostMasterController@checkuniqueaeropostmasterawbnumber')->name('checkuniqueaeropostmasterawbnumber');
		Route::any('aeropost-master/view/{id}', 'AeropostMasterController@show')->name('viewdetailsaeropostmaster');
		Route::post('aeropost-master/expandhousefiles', 'AeropostMasterController@expandhousefiles');
		Route::any('aeropost-master/listingmasteraeropost', 'AeropostMasterController@listingmasteraeropost')->name('listingmasteraeropost');
		Route::any('aeropost-master/checkoperations', 'AeropostMasterController@checkoperations')->name('checkoperationsaeropostmaster');
		Route::any('aeropost-master/getaeropostmasterdata', 'AeropostMasterController@getaeropostmasterdata');
		Route::any('aeropost-master/print/{aeropostId}', 'AeropostMasterController@print')->name('printaeropostmasterfile');
		// Agent
		Route::any('agent/aeropost-master/view/{id}', 'AeropostMasterAgentController@show')->name('agentAeroposMasterView');
		Route::any('agent/aeropost-master/assign-operations', 'AeropostMasterAgentController@assignOperations')->name('agentAeropostMasterAssignOperations');
		//--- Aeropost Master Management

		// CCPack Master Management
		Route::get('ccpack-master', 'CcpackMasterController@index')->name('ccpack-master');
		Route::get('ccpack-master/create', 'CcpackMasterController@create')->name('createccpackmaster');
		Route::post('ccpack-master/store', 'CcpackMasterController@store');
		Route::any('ccpack-master/edit/{id}', 'CcpackMasterController@edit')->name('editccpackmaster');
		Route::any('ccpack-master/update/{id}', 'CcpackMasterController@update');
		Route::any('ccpack-master/delete/{id}', 'CcpackMasterController@destroy')->name('deleteccpackmaster');
		Route::any('ccpack-master/checkuniqueccpackmasterawbnumber', 'CcpackMasterController@checkuniqueccpackmasterawbnumber')->name('checkuniqueccpackmasterawbnumber');
		Route::any('ccpack-master/view/{id}', 'CcpackMasterController@show')->name('viewdetailsccpackmaster');
		Route::post('ccpack-master/expandhousefiles', 'CcpackMasterController@expandhousefiles');
		Route::any('ccpack-master/listingmasterccpack', 'CcpackMasterController@listingmasterccpack')->name('listingmasterccpack');
		Route::any('ccpack-master/checkoperations', 'CcpackMasterController@checkoperations')->name('checkoperationsccpackmaster');
		Route::any('ccpack-master/getccpackmasterdata', 'CcpackMasterController@getccpackmasterdata');
		Route::any('ccpack-master/gettotalweightvolumeandpieces', 'CcpackMasterController@gettotalweightvolumeandpieces')->name('gettotalweightvolumeandpiecesccpack');
		Route::any('ccpack-master/print/{ccpackId}', 'CcpackMasterController@print')->name('printccpackmasterfile');
		// Agent
		Route::any('agent/ccpack-master/view/{id}', 'CcpackMasterAgentController@show')->name('agentCcpackMasterView');
		Route::any('agent/ccpack-master/assign-operations', 'CcpackMasterAgentController@assignOperations')->name('agentCcpackMasterAssignOperations');
		//--- CCPack Master Management

		// Ups Master Expense Management
		Route::get('ups-master/expenses', 'UpsMasterExpensesController@index')->name('upsmasterexpenses');
		Route::get('ups-master/expense/create/{upsMasterId?}/{flagFromWhere?}', 'UpsMasterExpensesController@create')->name('createupsmasterexpense');
		Route::post('ups-master/expense/store', 'UpsMasterExpensesController@store')->name('storeupsmasterexpense');
		Route::any('ups-master/expense/generateupsmastervoucheronsavenext', 'UpsMasterExpensesController@generateupsmastervoucheronsavenext')->name('generateupsmastervoucheronsavenext');
		Route::any('ups-master/expense/edit/{id}/{flagFromWhere?}', 'UpsMasterExpensesController@edit')->name('editupsmasterexpense');
		Route::post('ups-master/expense/update/{id}', 'UpsMasterExpensesController@update')->name('updateupsmasterexpense');
		Route::any('ups-master/expense/editagentupsmasterexpense/{id}/{flagFromWhere?}', 'UpsMasterExpensesController@editagentupsmasterexpense')->name('editagentupsmasterexpense');
		Route::post('ups-master/expense/updateagentupsmasterexpense/{id}', 'UpsMasterExpensesController@updateagentupsmasterexpense')->name('updateagentupsmasterexpense');
		Route::any('ups-master/expense/print/{expenseId}/{upsMasterId}/{flag?}', 'UpsMasterExpensesController@print')->name('printsingleupsmasterexpense');
		Route::any('ups-master/expense/printall/{flag?}', 'UpsMasterExpensesController@printall')->name('printallupsmasterexpense');
		Route::any('ups-master/expense/view/{id}', 'UpsMasterExpensesController@view')->name('viewdetailsupsmasterexpense');
		Route::any('ups-master/expense/listbydatatableserverside', 'UpsMasterExpensesController@listbydatatableserverside')->name('listbydatatableserversideupsmasterexpenses');
		Route::any('ups-master/expense/checkoperationfordatatableserverside', 'UpsMasterExpensesController@checkoperationfordatatableserverside')->name('checkupsmasterexpenseoperationfordatatableserverside');
		Route::any('ups-master/expense/viewupsmasterexpenseforcashier/{expenseId}/{upsMasterId}/{flag?}', 'CashierUpsMasterExpenseController@viewupsmasterexpenseforcashier')->name('viewupsmasterexpenseforcashier');
		Route::any('ups-master/expense/changeupsmasterexpensestatusbycashier', 'CashierUpsMasterExpenseController@changeupsmasterexpensestatusbycashier')->name('changeupsmasterexpensestatusbycashier');
		//--- Ups Master Expense Management

		// Aeropost Master Expense Management
		Route::get('aeropost-master/expenses', 'AeropostMasterExpensesController@index')->name('aeropostmasterexpenses');
		Route::get('aeropost-master/expense/create/{aeropostMasterId?}/{flagFromWhere?}', 'AeropostMasterExpensesController@create')->name('createaeropostmasterexpense');
		Route::post('aeropost-master/expense/store', 'AeropostMasterExpensesController@store')->name('storeaeropostmasterexpense');
		Route::any('aeropost-master/expense/generateaeropostmastervoucheronsavenext', 'AeropostMasterExpensesController@generateaeropostmastervoucheronsavenext')->name('generateaeropostmastervoucheronsavenext');
		Route::any('aeropost-master/expense/edit/{id}/{flagFromWhere?}', 'AeropostMasterExpensesController@edit')->name('editaeropostmasterexpense');
		Route::post('aeropost-master/expense/update/{id}', 'AeropostMasterExpensesController@update')->name('updateaeropostmasterexpense');
		Route::any('aeropost-master/expense/editagentaeropostmasterexpense/{id}/{flagFromWhere?}', 'AeropostMasterExpensesController@editagentaeropostmasterexpense')->name('editagentaeropostmasterexpense');
		Route::post('aeropost-master/expense/updateagentaeropostmasterexpense/{id}', 'AeropostMasterExpensesController@updateagentaeropostmasterexpense')->name('updateagentaeropostmasterexpense');
		Route::any('aeropost-master/expense/print/{expenseId}/{aeropostMasterId}/{flag?}', 'AeropostMasterExpensesController@print')->name('printsingleaeropostmasterexpense');
		Route::any('aeropost-master/expense/printall/{flag?}', 'AeropostMasterExpensesController@printall')->name('printallaeropostmasterexpense');
		Route::any('aeropost-master/expense/view/{id}', 'AeropostMasterExpensesController@view')->name('viewdetailsaeropostmasterexpense');
		Route::any('aeropost-master/expense/listbydatatableserverside', 'AeropostMasterExpensesController@listbydatatableserverside')->name('listbydatatableserversideaeropostmasterexpenses');
		Route::any('aeropost-master/expense/checkoperationfordatatableserverside', 'AeropostMasterExpensesController@checkoperationfordatatableserverside')->name('checkaeropostmasterexpenseoperationfordatatableserverside');
		Route::any('aeropost-master/expense/viewaeropostmasterexpenseforcashier/{expenseId}/{aeropostMasterId}/{flag?}', 'AeropostMasterExpensesController@viewaeropostmasterexpenseforcashier')->name('viewaeropostmasterexpenseforcashier');
		Route::any('aeropost-master/expense/changeaeropostmasterexpensestatusbycashier', 'AeropostMasterExpensesController@changeaeropostmasterexpensestatusbycashier')->name('changeaeropostmasterexpensestatusbycashier');
		//--- Aeropost Master Expense Management

		// CCPack Master Expense Management
		Route::get('ccpack-master/expenses', 'CcpackMasterExpensesController@index')->name('ccpackmasterexpenses');
		Route::get('ccpack-master/expense/create/{ccpackMasterId?}/{flagFromWhere?}', 'CcpackMasterExpensesController@create')->name('createccpackmasterexpense');
		Route::post('ccpack-master/expense/store', 'CcpackMasterExpensesController@store')->name('storeccpackmasterexpense');
		Route::any('ccpack-master/expense/generateccpackmastervoucheronsavenext', 'CcpackMasterExpensesController@generateccpackmastervoucheronsavenext')->name('generateccpackmastervoucheronsavenext');
		Route::any('ccpack-master/expense/edit/{id}/{flagFromWhere?}', 'CcpackMasterExpensesController@edit')->name('editccpackmasterexpense');
		Route::post('ccpack-master/expense/update/{id}', 'CcpackMasterExpensesController@update')->name('updateccpackmasterexpense');
		Route::any('ccpack-master/expense/editagentccpackmasterexpense/{id}/{flagFromWhere?}', 'CcpackMasterExpensesController@editagentccpackmasterexpense')->name('editagentccpackmasterexpense');
		Route::post('ccpack-master/expense/updateagentccpackmasterexpense/{id}', 'CcpackMasterExpensesController@updateagentccpackmasterexpense')->name('updateagentccpackmasterexpense');
		Route::any('ccpack-master/expense/print/{expenseId}/{ccpackMasterId}/{flag?}', 'CcpackMasterExpensesController@print')->name('printsingleccpackmasterexpense');
		Route::any('ccpack-master/expense/printall/{flag?}', 'CcpackMasterExpensesController@printall')->name('printallccpackmasterexpense');
		Route::any('ccpack-master/expense/view/{id}', 'CcpackMasterExpensesController@view')->name('viewdetailsccpackmasterexpense');
		Route::any('ccpack-master/expense/listbydatatableserverside', 'CcpackMasterExpensesController@listbydatatableserverside')->name('listbydatatableserversideccpackmasterexpenses');
		Route::any('ccpack-master/expense/checkoperationfordatatableserverside', 'CcpackMasterExpensesController@checkoperationfordatatableserverside')->name('checkccpackmasterexpenseoperationfordatatableserverside');
		Route::any('ccpack-master/expense/viewccpackmasterexpenseforcashier/{expenseId}/{ccpackMasterId}/{flag?}', 'CcpackMasterExpensesController@viewccpackmasterexpenseforcashier')->name('viewccpackmasterexpenseforcashier');
		Route::any('ccpack-master/expense/changeccpackmasterexpensestatusbycashier', 'CcpackMasterExpensesController@changeccpackmasterexpensestatusbycashier')->name('changeccpackmasterexpensestatusbycashier');
		//--- CCPack Master Expense Management

		// Ups Master Invoice Management
		Route::get('ups-master/invoices', 'UpsMasterInvoiceController@index')->name('upsmasterinvoices');
		Route::get('ups-master/invoice/create/{upsMasterId?}/{flagFromWhere?}', 'UpsMasterInvoiceController@create')->name('createupsmasterinvoice');
		Route::post('ups-master/invoice/store', 'UpsMasterInvoiceController@store');
		Route::any('ups-master/invoice/edit/{id}/{flagFromWhere?}', 'UpsMasterInvoiceController@edit')->name('editupsmasterinvoice');
		Route::any('ups-master/invoice/update/{id}', 'UpsMasterInvoiceController@update');
		Route::any('ups-master/invoice/getupsmasterdetailforinvoice', 'UpsMasterInvoiceController@getupsmasterdetailforinvoice')->name('getupsmasterdetailforinvoice');
		Route::any('ups-master/invoice/storeupsmasterinvoiceandprint', 'UpsMasterInvoiceController@storeupsmasterinvoiceandprint');
		Route::any('ups-master/invoice/deleteupsmasterinvoicefromedit/{id}', 'UpsInvoicesController@deleteupsmasterinvoicefromedit')->name('deleteupsmasterinvoicefromedit');
		Route::any('ups-master/invoice/deleteupsmasterinvoice/{id}', 'UpsMasterInvoiceController@destroy')->name('deleteupsmasterinvoice');
		Route::any('ups-master/invoice/viewandprintupsmasterinvoice/{id}', 'UpsMasterInvoiceController@viewandprintupsmasterinvoice')->name('viewandprintupsmasterinvoice');
		Route::any('ups-master/invoice/copy/{id}/{flag?}', 'UpsMasterInvoiceController@copy')->name('copyupsmasterinvoice');
		Route::any('ups-master/invoice/listbydatatableserverside', 'UpsMasterInvoiceController@listbydatatableserverside')->name('listbydatatableserversideupsmasterinvoices');
		Route::any('ups-master/invoice/checkoperationfordatatableserverside', 'UpsMasterInvoiceController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserversideupsmasterinvoices');
		Route::any('ups-master/invoice/viewupsmasterinvoicedetails/{id}', 'UpsMasterInvoiceController@show')->name('viewupsmasterinvoicedetails');
		Route::any('ups-master/getupsmasterinvoiceforinputpicker', 'UpsMasterInvoiceController@getupsmasterinvoiceforinputpicker')->name('getupsmasterinvoiceforinputpicker');
		//--- Ups Master Invoice Management

		// Aeropost Master Invoice Management
		Route::get('aeropost-master/invoices', 'AeropostMasterInvoiceController@index')->name('aeropostmasterinvoices');
		Route::get('aeropost-master/invoice/create/{aeropostMasterId?}/{flagFromWhere?}', 'AeropostMasterInvoiceController@create')->name('createaeropostmasterinvoice');
		Route::post('aeropost-master/invoice/store', 'AeropostMasterInvoiceController@store');
		Route::any('aeropost-master/invoice/edit/{id}/{flagFromWhere?}', 'AeropostMasterInvoiceController@edit')->name('editaeropostmasterinvoice');
		Route::any('aeropost-master/invoice/update/{id}', 'AeropostMasterInvoiceController@update');
		Route::any('aeropost-master/invoice/getaeropostmasterdetailforinvoice', 'AeropostMasterInvoiceController@getaeropostmasterdetailforinvoice')->name('getaeropostmasterdetailforinvoice');
		Route::any('aeropost-master/invoice/storeaeropostmasterinvoiceandprint', 'AeropostMasterInvoiceController@storeaeropostmasterinvoiceandprint');
		Route::any('aeropost-master/invoice/deleteaeropostmasterinvoicefromedit/{id}', 'AeropostMasterInvoiceController@deleteaeropostmasterinvoicefromedit')->name('deleteaeropostmasterinvoicefromedit');
		Route::any('aeropost-master/invoice/deleteaeropostmasterinvoice/{id}', 'AeropostMasterInvoiceController@destroy')->name('deleteaeropostmasterinvoice');
		Route::any('aeropost-master/invoice/viewandprintaeropostmasterinvoice/{id}', 'AeropostMasterInvoiceController@viewandprintaeropostmasterinvoice')->name('viewandprintaeropostmasterinvoice');
		Route::any('aeropost-master/invoice/copy/{id}/{flag?}', 'AeropostMasterInvoiceController@copy')->name('copyaeropostmasterinvoice');
		Route::any('aeropost-master/invoice/listbydatatableserverside', 'AeropostMasterInvoiceController@listbydatatableserverside')->name('listbydatatableserversideaeropostmasterinvoices');
		Route::any('aeropost-master/invoice/checkoperationfordatatableserverside', 'AeropostMasterInvoiceController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserversideaeropostmasterinvoices');
		Route::any('aeropost-master/invoice/viewaeropostmasterinvoicedetails/{id}', 'AeropostMasterInvoiceController@show')->name('viewaeropostmasterinvoicedetails');
		Route::any('aeropost-master/getaeropostmasterinvoiceforinputpicker', 'AeropostMasterInvoiceController@getaeropostmasterinvoiceforinputpicker')->name('getaeropostmasterinvoiceforinputpicker');
		//--- Aeropost Master Invoice Management

		// CCPack Master Invoice Management
		Route::get('ccpack-master/invoices', 'CcpackMasterInvoiceController@index')->name('ccpackmasterinvoices');
		Route::get('ccpack-master/invoice/create/{ccpackMasterId?}/{flagFromWhere?}', 'CcpackMasterInvoiceController@create')->name('createccpackmasterinvoice');
		Route::post('ccpack-master/invoice/store', 'CcpackMasterInvoiceController@store');
		Route::any('ccpack-master/invoice/edit/{id}/{flagFromWhere?}', 'CcpackMasterInvoiceController@edit')->name('editccpackmasterinvoice');
		Route::any('ccpack-master/invoice/update/{id}', 'CcpackMasterInvoiceController@update');
		Route::any('ccpack-master/invoice/getccpackmasterdetailforinvoice', 'CcpackMasterInvoiceController@getccpackmasterdetailforinvoice')->name('getccpackmasterdetailforinvoice');
		Route::any('ccpack-master/invoice/storeccpackmasterinvoiceandprint', 'CcpackMasterInvoiceController@storeccpackmasterinvoiceandprint');
		Route::any('ccpack-master/invoice/deleteccpackmasterinvoicefromedit/{id}', 'CcpackMasterInvoiceController@deleteccpackmasterinvoicefromedit')->name('deleteccpackmasterinvoicefromedit');
		Route::any('ccpack-master/invoice/deleteccpackmasterinvoice/{id}', 'CcpackMasterInvoiceController@destroy')->name('deleteccpackmasterinvoice');
		Route::any('ccpack-master/invoice/viewandprintccpackmasterinvoice/{id}', 'CcpackMasterInvoiceController@viewandprintccpackmasterinvoice')->name('viewandprintccpackmasterinvoice');
		Route::any('ccpack-master/invoice/copy/{id}/{flag?}', 'CcpackMasterInvoiceController@copy')->name('copyccpackmasterinvoice');
		Route::any('ccpack-master/invoice/listbydatatableserverside', 'CcpackMasterInvoiceController@listbydatatableserverside')->name('listbydatatableserversideccpackmasterinvoices');
		Route::any('ccpack-master/invoice/checkoperationfordatatableserverside', 'CcpackMasterInvoiceController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserversideccpackmasterinvoices');
		Route::any('ccpack-master/invoice/viewccpackmasterinvoicedetails/{id}', 'CcpackMasterInvoiceController@show')->name('viewccpackmasterinvoicedetails');
		Route::any('ccpack-master/getccpackmasterinvoiceforinputpicker', 'CcpackMasterInvoiceController@getccpackmasterinvoiceforinputpicker')->name('getccpackmasterinvoiceforinputpicker');
		//--- CCPack Master Invoice Management

		// Ups Management
		Route::get('ups', 'UpsController@index')->name('ups');
		Route::get('ups_export', 'UpsController@exportindex')->name('ups_export');
		Route::any('upsexport/delete/{id}/{type_flag}', 'UpsController@destroy')->name('deleteupsexport');
		Route::any('upsexport/edit/{id}/{file_type}', 'UpsController@edit')->name('editupsexport');

		Route::get('all', 'UpsController@viewall')->name('viewall');
		Route::get('viewlogfiles', 'UpsController@viewlogfiles')->name('viewlogfiles');
		Route::get('ups/create', 'UpsController@create')->name('createups');
		Route::post('ups/store', 'UpsController@store');
		Route::get('ups/import', 'UpsController@import')->name('importups');
		Route::post('ups/importdata', 'UpsController@importdata');
		Route::any('ups/edit/{id}/{file_type}', 'UpsController@edit')->name('editups');
		Route::any('ups/getupsclientdetail', 'UpsController@getupsclientdetail')->name('getupsclientdetail');
		Route::any('ups/update/{id}', 'UpsController@update');
		Route::any('ups/delete/{id}/{type_flag?}', 'UpsController@destroy')->name('deleteups');
		Route::post('ups/expandpackage', 'UpsController@expandpackage');
		Route::any('ups/getupsdata', 'UpsController@getupsdata');
		Route::any('ups/filderbydaterange', 'UpsController@filderbydaterange');
		Route::any('ups/fildergetalldata', 'UpsController@fildergetalldata');
		Route::any('ups/filderbyfilename', 'UpsController@filderbyfilename');
		Route::any('ups/viewdetails/{id}', 'UpsController@viewdetails')->name('viewdetailsups');
		Route::any('ups/checkuniqueawbnumber', 'UpsController@checkuniqueawbnumber')->name('checkuniqueawbnumber');
		Route::post('upsfilter', 'UpsController@filterbyfiletype')->name('upsfilter');
		Route::post('upsscanfilter', 'UpsController@filterbyscan')->name('upsscanfilter');




		// Agent Role
		Route::get('agentups', 'AgentUpsController@index')->name('agentups');
		//-- END-- Ups Management



		// Cargo Management
		Route::get('import/cargo/{id}', 'CargoController@create')->name('cargoimport');
		Route::any('autocomplete/cargo/search-client', 'ClientsController@searchClientsAutocomplete')->name('cargoautocompletesearchclient');
		Route::get('export/cargo/{id}', 'CargoController@create')->name('cargoexport');
		Route::get('locale/cargo/{id}', 'CargoController@create')->name('cargolocale');
		//Route::any('import/addnewhawbfile/{flag}','CargoController@addNewHawb')->name('cargonewhawbfile');
		Route::post('cargo/store', 'CargoController@store')->name('storecargo');

		Route::get('cargo/edit/{rid}/{id}', 'CargoController@editcargo')->name('editcargo');
		Route::any('cargo/update/{id}', 'CargoController@update');

		Route::any('cargo/delete/{rid}{id}', 'CargoController@destroy')->name('deletecargo');
		Route::get('cargo/viewcargo/{rid}/{id}/{flag?}', 'CargoController@viewcargo')->name('viewcargo');
		Route::get('cargo/cargoexpensedetail/{rid}/{id}', 'CargoController@cargoexpensedetail')->name('cargoexpensedetail');
		Route::get('cargo/invoicedetail/{rid}/{id}', 'CargoController@invoicedetail')->name('invoicedetail');
		Route::get('cargo/costdetail/{rid}/{id}', 'CargoController@costdetail')->name('costdetail');
		Route::get('cargo/reportdetail/{rid}/{id}', 'CargoController@reportdetail')->name('reportdetail');


		Route::get('cargoimports', 'CargoController@cargoimportsindex')->name('cargoimports');
		Route::get('cargoexports', 'CargoController@cargoexportsindex')->name('cargoexports');
		Route::get('cargolocales', 'CargoController@cargolocalesindex')->name('cargolocales');
		Route::get('cargoall/{flagCargo?}', 'CargoController@cargoall')->name('cargoall');

		Route::any('cargo/filterusingcargofiletype', 'CargoController@filterusingcargofiletype')->name('filterusingcargofiletype');

		Route::any('cargo/gettotalweightvolumeandpieces', 'CargoController@gettotalweightvolumeandpieces')->name('gettotalweightvolumeandpieces');
		Route::any('cargo/gettotalweightpiecesinexport', 'CargoController@gettotalweightpiecesinexport')->name('gettotalweightpiecesinexport');




		Route::post('cargoimportsajax', 'CargoController@cargoimportsindexajax')->name('cargoimportsajax');
		Route::post('cargoexportsajax', 'CargoController@cargoexportsindexajax')->name('cargoexportsajax');
		Route::post('cargolocalesajax', 'CargoController@cargolocalesindexajax')->name('cargolocalesajax');
		Route::post('cargoallajax', 'CargoController@cargoallajax')->name('cargoallajax');

		Route::post('shipmentoutsidefiltering', 'CargoController@shipmentoutsidefiltering')->name('shipmentoutsidefiltering');
		Route::any('cargo/getcargodata', 'CargoController@getcargodata');

		Route::any('/expandhawbnumber', 'CargoController@expandhawbnumber')->name('expandhawbnumber');
		Route::any('cargo/printcargofile/{cargoId}/{cargoType}', 'CargoController@printcargofile')->name('printcargofile');
		Route::any('cargo/printupsfile/{upsId}/{upsType}', 'UpsController@printupsfile')->name('printupsfile');
		Route::any('cargo/printaeropostfile/{aeropostId}', 'AeropostController@printaeropostfile')->name('printaeropostfile');
		Route::any('cargo/printccpackfile/{ccpackId}', 'ccpackController@printccpackfile')->name('printccpackfile');

		// Agent Role
		Route::post('agentcargoimportsajax', 'AgentCargoController@agentcargoimportsajax')->name('agentcargoimportsajax');
		Route::post('agentcargoexportsajax', 'AgentCargoController@agentcargoexportsajax')->name('agentcargoexportsajax');
		Route::post('agentcargolocalesajax', 'AgentCargoController@agentcargolocalesajax')->name('agentcargolocalesajax');
		Route::post('agentcargoallajax', 'AgentCargoController@agentcargoallajax')->name('agentcargoallajax');
		Route::get('agentcargoall', 'AgentCargoController@agentcargoall')->name('agentcargoall');
		Route::any('agent/cargo/listbydatatableserverside', 'AgentCargoController@listbydatatableserverside')->name('listbydatatableserverside');

		Route::get('warehousecargoall', 'WarehouseCargoController@warehousecargoall')->name('warehousecargoall');
		Route::post('warehousecargoimportsajax', 'WarehouseCargoController@warehousecargoimportsajax')->name('warehousecargoimportsajax');
		Route::post('warehousecargoexportsajax', 'WarehouseCargoController@warehousecargoexportsajax')->name('warehousecargoexportsajax');
		Route::post('warehousecargoallajax', 'WarehouseCargoController@warehousecargoallajax')->name('warehousecargoallajax');

		Route::any('warehousehawbfiles', 'WarehouseCargoController@warehousehawbfiles')->name('warehousehawbfiles');



		Route::any('addracklocationinwarehousefile/{id?}', 'WarehouseCargoController@addracklocationinwarehousefile')->name('addracklocationinwarehousefile');
		Route::post('storeracklocationinwarehousefile', 'WarehouseCargoController@storeracklocationinwarehousefile')->name('storeracklocationinwarehousefile');
		Route::any('releeaseracklocationinwarehousefile/{id?}', 'WarehouseCargoController@releeaseracklocationinwarehousefile')->name('releeaseracklocationinwarehousefile');
		Route::any('cargo/checkuniqueawbnumber', 'CargoController@checkuniqueawbnumber')->name('checkuniqueawbnumberofcargo');
		Route::any('cargo/listbydatatableserverside', 'CargoController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('cargo/checkoperationfordatatableserverside', 'CargoController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserverside');
		Route::any('cargo/viewdifferencereport/{cargoId}', 'CargoController@viewdifferencereport')->name('viewdifferencereport');

		//-- Cargo-- Ups Management
	}
);

// Below route is for testing purpose
//Route::resource('photo', 'PhotoController');


// Expenses Management
Route::group(
	['middleware' => ['auth', 'FilterDataBeforeSave'], 'prefix' => 'expense'],
	function () {
		Route::get('/', 'ExpenseController@index')->name('expenses');
		Route::get('/create/{courier_id}/{flag}', 'ExpenseController@create')->name('createexpense');
		Route::get('/createinbasiccargo/{courier_id}/{flag}', 'ExpenseController@createinbasiccargo')->name('createexpenseinbasiccargo');
		Route::post('/store', 'ExpenseController@store')->name('storeexpence');
		Route::post('/storeinbasiccargo', 'ExpenseController@storeinbasiccargo')->name('storeexpenceinbasiccargo');
		Route::post('/getexpensedata', 'ExpenseController@getexpensedata');
		Route::post('/getexpensedataoftoday', 'ExpenseController@getexpensedataoftoday');
		Route::any('/edit/{id}', 'ExpenseController@edit')->name('editexpense');
		Route::any('/delete/{id}', 'ExpenseController@destroy')->name('deleteexpense');
		Route::any('/editexpensevoucher/{id}/{flagFromWhere?}', 'ExpenseController@editexpensevoucher')->name('editexpensevoucher');
		Route::any('/editexpenserequestvoucher/{id}/{flagFromWhere?}', 'ExpenseController@editexpenserequestvoucher')->name('editexpenserequestvoucher');
		Route::any('/deleteexpensevoucher/{id}', 'ExpenseController@destroyexpensevoucher')->name('deleteexpensevoucher');
		Route::get('/createexpenseusingawl/{flag}/{cargoId?}/{flagFromWhere?}/{gauranteeId?}', 'ExpenseController@createexpenseusingawl')->name('createexpenseusingawl');
		Route::post('/storeexpenseusingawl', 'ExpenseController@storeexpenseusingawl')->name('storeexpenseusingawl');
		Route::post('/updateexpenseusingawl/{id}', 'ExpenseController@updateexpenseusingawl')->name('updateexpenseusingawl');
		Route::post('/getcargofilenumberforprint', 'ExpenseController@getcargofilenumberforprint')->name('getcargofilenumberforprint');
		Route::get('/createexpenserequest/{flag}', 'ExpenseController@createexpenserequest')->name('createexpenserequest');
		Route::post('/storeexpenserequest', 'ExpenseController@storeexpenserequest')->name('storeexpenserequest');
		Route::any('/changeexpenserequeststatus', 'ExpenseController@changeexpenserequeststatus')->name('changeexpenserequeststatus');
		Route::any('/changeexpenserequestnumberinlisting', 'ExpenseController@changeexpenserequestnumberinlisting')->name('changeexpenserequestnumberinlisting');
		Route::any('/getexpenserequestnumberinlistingall', 'ExpenseController@getexpenserequestnumberinlistingall')->name('getexpenserequestnumberinlistingall');
		Route::any('/addmoreexpense', 'ExpenseController@addmoreexpense')->name('addmoreexpense');
		Route::any('/expandexpenses', 'ExpenseController@expandexpenses')->name('expandexpenses');
		Route::any('/addmoreexpenseforrequest', 'ExpenseController@addmoreexpenseforrequest')->name('addmoreexpenseforrequest');
		Route::any('/generatevoucheronsavenext', 'ExpenseController@generatevoucheronsavenext')->name('generatevoucheronsavenext');
		Route::any('/expensereport', 'ExpenseController@expensereport')->name('expensereport');
		Route::any('/expenserequestlisting', 'ExpenseController@expenserequestlisting')->name('expenserequestlisting');
		Route::any('/makereadnotification/{expenseId}', 'ExpenseController@makereadnotification')->name('makereadnotification');
		Route::any('/getcargoreportdata', 'ExpenseController@getcargoreportdata')->name('getcargoreportdata');
		Route::any('expense/getprintsingleexpense/{expenseId}/{cargoId}/{flag?}', 'ExpenseController@getprintsingleexpense')->name('getprintsingleexpense');
		Route::any('expense/getprintallexpense/{flag?}', 'ExpenseController@getprintallexpense')->name('getprintallexpense');

		Route::any('/editagentexpensesbyadmin/{id}/{flagFromWhere?}', 'ExpenseController@editagentexpensesbyadmin')->name('editagentexpensesbyadmin');
		Route::post('/updateagentexpensesbyadmin/{id}', 'ExpenseController@updateagentexpensesbyadmin')->name('updateagentexpensesbyadmin');
		Route::any('/viewdetailscargoexpense/{id}', 'ExpenseController@viewdetailscargoexpense')->name('viewdetailscargoexpense');

		Route::any('/listbydatatableserverside', 'ExpenseController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('/checkoperationfordatatableserverside', 'ExpenseController@checkoperationfordatatableserverside')->name('checkcargoexpenseoperationfordatatableserverside');
		Route::any('/checkexistingvoucherno', 'ExpenseController@checkexistingvoucherno')->name('checkexistingvoucherno');
	}
);
//-- END-- Expenses Management

Route::group(
	['middleware' => ['auth', 'FilterDataBeforeSave']],
	function () {
		// Billing Items Management
		Route::get('/billingitems', 'BillingItemsController@index')->name('billingitems');
		Route::any('billingitems/listbillingitems', 'BillingItemsController@listbillingitems')->name('listbillingitems');
		Route::get('billingitem/create', 'BillingItemsController@create')->name('createbillingitem');
		Route::post('billingitem/store', 'BillingItemsController@store');
		Route::any('billingitem/edit/{id}', 'BillingItemsController@edit')->name('editbillingitem');
		Route::any('billingitem/update/{id}', 'BillingItemsController@update');
		Route::any('billingitem/delete/{id}', 'BillingItemsController@destroy')->name('deletebillingitem');
		Route::any('billingitem/getbillinglistdata', 'BillingItemsController@getbillinglistdata')->name('getbillinglistdata');
		Route::any('billingitem/getbillingdata', 'BillingItemsController@getbillingdata')->name('getbillingdata');
		Route::any('billingitem/checkunique/{flag?}', 'BillingItemsController@checkunique')->name('checkunique');


		//-- END-- Billing Items Management

		// Costs Management
		Route::get('/costs', 'CostsController@index')->name('costs');
		Route::any('costs/listcosts', 'CostsController@listcosts')->name('listcosts');
		Route::get('costs/create', 'CostsController@create')->name('createcost');
		Route::post('costs/store', 'CostsController@store');
		Route::any('costs/edit/{id}', 'CostsController@edit')->name('editcost');
		Route::any('costs/update/{id}', 'CostsController@update');
		Route::any('costs/delete/{id}', 'CostsController@destroy')->name('deletecost');
		Route::any('costs/getcostdata', 'CostsController@getcostdata');
		Route::any('costs/checkunique', 'CostsController@checkunique');
		//-- END-- Costs Management

		// Storage charges Management
		Route::get('/storagecharges', 'StorageChargesController@index')->name('storagecharges');
		Route::get('storagecharge/create', 'StorageChargesController@create')->name('createstoragecharge');
		Route::post('storagecharge/store', 'StorageChargesController@store');
		Route::any('storagecharge/edit/{id}', 'StorageChargesController@edit')->name('editstoragecharge');
		Route::any('storagecharge/update/{id}', 'StorageChargesController@update');
		Route::any('storagecharge/delete/{id}', 'StorageChargesController@destroy')->name('deletestoragecharge');
		Route::any('storagecharge/checkuniquemeasure', 'StorageChargesController@checkuniquemeasure')->name('checkuniquemeasure');
		//-- END-- Storage charges Management

		// Storage racks Management
		Route::get('/storageracks', 'StorageRacksController@index')->name('storageracks');
		Route::get('storagerack/create', 'StorageRacksController@create')->name('createstoragerack');
		Route::post('storagerack/store', 'StorageRacksController@store');
		Route::any('storagerack/edit/{id}', 'StorageRacksController@edit')->name('editstoragerack');
		Route::any('storagerack/update/{id}', 'StorageRacksController@update');
		Route::any('storagerack/delete/{id}', 'StorageRacksController@destroy')->name('deletestoragerack');
		Route::any('storagerack/checkuniqueracklocation', 'StorageRacksController@checkuniqueracklocation')->name('checkuniqueracklocation');
		//-- END-- Storage racks Management


		//Import & Export commission Management 
		Route::get('/upscommission', 'UpsController@createCommission')->name('upscommission');
		Route::get('/upscommissiondetails', 'UpsController@upsCommissionDetail')->name('upscommissiondetails');
		Route::post('/upscommission/store', 'UpsController@storeUpsCommission');
		Route::post('upscommission/delete/{id}', 'UpsController@deleteupscommission')->name('deleteupscommission');
		Route::get('upscommission/edit/{id}', 'UpsController@editupscommission')->name('editupscommission');
		Route::post('upscommission/update/{id}', 'UpsController@updateupscommission')->name('updateupscommission');
		Route::any('upscommission/checkuniqueupscommission', 'UpsController@checkuniqueupscommission')->name('checkuniqueupscommission');

		Route::any('ups/listbydatatableserverside', 'UpsController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('ups/checkoperationfordatatableserverside', 'UpsController@checkoperationfordatatableserverside')->name('checkupsoperationfordatatableserverside');

		Route::any('commission/aeropostcommission', 'AeropostCommissionController@index')->name('aeropostcommission');
		Route::any('commission/addaeropostcommission', 'AeropostCommissionController@create')->name('addaeropostcommission');
		Route::any('commission/storeaeropostcommission', 'AeropostCommissionController@store');
		Route::any('commission/editaeropostcommission/{id}', 'AeropostCommissionController@edit')->name('editaeropostcommission');
		Route::any('commission/updateaeropostcommission/{id}', 'AeropostCommissionController@update')->name('updateaeropostcommission');

		// End commission Management

		//In progress Status
		Route::get('filestatus', 'FileStatusController@create')->name('filestatus');
		Route::get('filestatus/index', 'FileStatusController@index')->name('filestatusindex');
		Route::post('filestatus/store', 'FileStatusController@store');
		Route::post('filestatus/delete/{id}', 'FileStatusController@destroy')->name('deletefilestatus');
		Route::get('filestatus/edit/{id}', 'FileStatusController@edit')->name('editfilestatus');
		Route::post('filestatus/update/{id}', 'FileStatusController@update')->name('updatefilestatus');
		// End progress Status

		// Old Invoices
		Route::get('/oldinvoices', 'OldInvoicesController@index')->name('oldinvoices');
		Route::any('oldinvoices/listbydatatableserverside', 'OldInvoicesController@listbydatatableserverside')->name('listbydatatableserversideoldinvoices');
		Route::any('oldinvoices/checkuniquebillnoforoldinvoice', 'OldInvoicesController@checkuniquebillnoforoldinvoice')->name('checkuniquebillnoforoldinvoice');



		// Invoice Management
		Route::get('/invoices', 'InvoicesController@index')->name('invoices');
		Route::get('invoices/paymentReport', 'InvoicesController@invoiceReportIndex')->name('invoicePaymentReport');
		Route::get('invoices/viewDetails/{id}', 'InvoicesController@viewDetail')->name('ViewDetails');
		Route::get('invoice/searchByDate', 'InvoicesController@filter')->name('searchByDate');
		Route::get('invoicesmail/send', 'InvoicesController@sendMail')->name('invoiceMail');
		Route::get('/pendinginvoices', 'InvoicesController@indexpendinginvoices')->name('pendinginvoices');
		Route::get('invoices/create/{cargoId?}/{flagInvoice?}/{flagFromWhere?}', 'InvoicesController@create')->name('createinvoice');
		Route::get('invoices/send', 'CargoController@sendMail')->name('sendinvoice');
		Route::post('invoices/store', 'InvoicesController@store');
		Route::any('invoices/edit/{id}/{flagFromWhere?}', 'InvoicesController@edit')->name('editinvoice');
		Route::any('invoices/update/{id}', 'InvoicesController@update');
		Route::any('invoices/delete/{id}', 'InvoicesController@destroy')->name('deleteinvoice');
		Route::any('invoices/deletefromedit/{id}', 'InvoicesController@destroyfromedit')->name('deleteinvoicefromedit');
		Route::any('invoices/getcargodetailforinvoice', 'InvoicesController@getcargodetailforinvoice')->name('getcargodetailforinvoice');
		Route::any('invoices/getcargohouseawbdetailforinvoice', 'InvoicesController@getcargohouseawbdetailforinvoice')->name('getcargohouseawbdetailforinvoice');
		Route::any('/changeinvoicestatus', 'InvoicesController@changeinvoicestatus')->name('changeinvoicestatus');
		Route::post('invoices/storecargoinvoiceandprint', 'InvoicesController@storecargoinvoiceandprint');

		Route::any('invoice/viewandprintcargoinvoice/{id}', 'InvoicesController@viewandprintcargoinvoice')->name('viewandprintcargoinvoice');
		Route::any('invoices/copy/{id}/{flag?}', 'InvoicesController@copy')->name('copyinvoice');
		Route::any('invoices/printpendinginvoices', 'InvoicesController@printpendinginvoices')->name('printpendinginvoices');

		Route::any('invoices/viewcargoinvoicedetails/{id}/{flag?}', 'InvoicesController@show')->name('viewcargoinvoicedetails');
		Route::any('invoices/importcargoinvoices', 'InvoicesController@importcargoinvoices')->name('importcargoinvoices');

		Route::any('invoiceoutsidefiltering', 'InvoicesController@invoiceoutsidefiltering')->name('invoiceoutsidefiltering');
		Route::any('invoices/listbydatatableserverside', 'InvoicesController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('invoices/checkoperationfordatatableserverside', 'InvoicesController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserverside');
		Route::any('invoice/printinvoice/{invoiceId}/{flag?}', 'InvoicesController@printinvoice')->name('printinvoice');
		Route::any('invoice/checkexistingbillno', 'InvoicesController@checkexistingbillno')->name('checkexistingbillno');
		Route::any('invoice/getcargoinvoiceforinputpicker', 'InvoicesController@getcargoinvoiceforinputpicker')->name('getcargoinvoiceforinputpicker');
		//-- END-- Invoice Management


		// Vendor Management
		//Route::resource('/users','Auth\RegisterController');
		Route::get('/vendors', 'VendorsController@index')->name('vendors');
		Route::any('vendors/listvendors', 'VendorsController@listvendors')->name('listvendors');
		Route::get('vendors/create', 'VendorsController@create')->name('createvendor');
		Route::post('vendors/store', 'VendorsController@store');
		Route::any('vendors/edit/{id}', 'VendorsController@edit')->name('editvendor');
		Route::any('vendors/update/{id}', 'VendorsController@update');
		Route::any('vendors/delete/{id}', 'VendorsController@destroy')->name('deletevendor');
		Route::any('vendors/viewclientdetail/{id}', 'VendorsController@viewvendordetail');
		Route::post('vendors/changeuserstatus', 'VendorsController@changestatus');
		Route::post('vendors/resetpassword', 'VendorsController@resetpassword');
		Route::any('vendors/viewclientactivities/{id}', 'VendorsController@viewvendoractivities');
		Route::any('vendors/checkunique', 'VendorsController@checkunique');
		Route::any('vendors/copyvendor', 'VendorsController@copyvendor')->name('copyvendor');
		Route::any('vendors/getvendordata', 'VendorsController@getvendordata')->name('getvendordata');
		//-- END-- Vendor Management


		// Cash/Credit Management
		//Route::resource('/users','Auth\RegisterController');
		Route::get('/cashcredit', 'CashCreditController@index')->name('cashcredit');
		Route::get('cashcredit/create', 'CashCreditController@create')->name('createcashcredit');
		Route::post('cashcredit/store', 'CashCreditController@store');
		Route::any('cashcredit/edit/{id}', 'CashCreditController@edit')->name('editcashcredit');
		Route::any('cashcredit/update/{id}', 'CashCreditController@update');
		Route::any('cashcredit/delete/{id}', 'CashCreditController@destroy')->name('deletecashcredit');
		Route::any('cashcredit/viewclientdetail/{id}', 'CashCreditController@viewcashcreditdetail');
		Route::post('cashcredit/changeuserstatus', 'CashCreditController@changestatus');
		Route::post('cashcredit/resetpassword', 'CashCreditController@resetpassword');
		Route::any('cashcredit/viewclientactivities/{id}', 'CashCreditController@viewcashcreditactivities');
		Route::any('cashcredit/getdetailtypedata', 'CashCreditController@getdetailtypedata');
		Route::any('cashcredit/getbalance', 'CashCreditController@getbalance');
		Route::any('cashcredit/checkunique', 'CashCreditController@checkunique');

		//-- END-- Cash/Credit Management


		// Cash/Credit Account Type Management
		//Route::resource('/users','Auth\RegisterController');
		Route::get('/cashcreditaccounttype', 'CashCreditAccountTypeController@index')->name('cashcreditaccounttype');
		Route::get('cashcreditaccounttype/create', 'CashCreditAccountTypeController@create')->name('createcashcreditaccounttype');
		Route::post('cashcreditaccounttype/store', 'CashCreditAccountTypeController@store');
		Route::any('cashcreditaccounttype/edit/{id}', 'CashCreditAccountTypeController@edit')->name('editcashcreditaccounttype');
		Route::any('cashcreditaccounttype/update/{id}', 'CashCreditAccountTypeController@update');
		Route::any('cashcreditaccounttype/delete/{id}', 'CashCreditAccountTypeController@destroy')->name('deletecashcreditaccounttype');
		//-- END-- Cash/Credit Account Type Management


		// Cash/Credit Detail Type Management
		//Route::resource('/users','Auth\RegisterController');
		Route::get('/cashcreditdetailtype', 'CashCreditDetailTypeController@index')->name('cashcreditdetailtype');
		Route::get('cashcreditdetailtype/create', 'CashCreditDetailTypeController@create')->name('createcashcreditdetailtype');
		Route::post('cashcreditdetailtype/store', 'CashCreditDetailTypeController@store');
		Route::any('cashcreditdetailtype/edit/{id}', 'CashCreditDetailTypeController@edit')->name('editcashcreditdetailtype');
		Route::any('cashcreditdetailtype/update/{id}', 'CashCreditDetailTypeController@update');
		Route::any('cashcreditdetailtype/delete/{id}', 'CashCreditDetailTypeController@destroy')->name('deletecashcreditdetailtype');
		Route::any('cashcreditdetailtype/getqbsubaccounts', 'CashCreditDetailTypeController@getqbsubaccounts')->name('getqbsubaccounts');
		//-- END-- Cash/Credit Detail Type Management



		// Invoice Payments Management
		Route::get('invoicepayment/create/{cargoId?}/{invoiceId?}/{billingParty?}/{fromMenu?}/{flagModule?}', 'InvoicePaymentsController@create')->name('addinvoicepayment');
		Route::post('invoicepayment/store', 'InvoicePaymentsController@store');
		Route::any('invoicepayment/edit/{receiptNumber}/{flagModule?}', 'InvoicePaymentsController@edit')->name('editinvoicepayment');
		Route::any('invoicepayment/update/{receiptNumber}', 'InvoicePaymentsController@update');
		Route::any('invoicepayment/getinvoicesusingfilenumber', 'InvoicePaymentsController@getinvoicesusingfilenumber')->name('getinvoicesusingfilenumber');
		Route::any('invoicepayment/getinvoicesofclient', 'InvoicePaymentsController@getinvoicesofclient')->name('getinvoicesofclient');
		Route::any('invoicepayment/getinvoicesofclientineditmode', 'InvoicePaymentsController@getinvoicesofclientineditmode')->name('getinvoicesofclientineditmode');
		Route::any('invoicepayment/getcargoandcourierinvoicesofclient', 'InvoicePaymentsController@getcargoandcourierinvoicesofclient')->name('getcargoandcourierinvoicesofclient');
		Route::any('invoicepayment/getselectedinvoicedata', 'InvoicePaymentsController@getselectedinvoicedata')->name('getselectedinvoicedata');
		Route::any('invoicepayment/getcurrencyratesection', 'InvoicePaymentsController@getcurrencyratesection');
		Route::any('invoicepayment/listbydatatableserverside', 'InvoicePaymentsController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('invoicepayment/checkoperations', 'InvoicePaymentsController@checkoperations')->name('checkoperations');
		Route::any('invoicepayment/getclientdataforcredit', 'InvoicePaymentsController@getclientdataforcredit')->name('getclientdataforcredit');
		Route::any('invoicepayment/getclientdataforcreditfrominvoice', 'InvoicePaymentsController@getclientdataforcreditfrominvoice')->name('getclientdataforcreditfrominvoice');



		Route::get('upsinvoicepayment/create/{upsId?}/{invoiceId?}/{billingParty?}/{fromMenu?}', 'UpsInvoicePaymentController@create')->name('addupsinvoicepayment');
		Route::post('upsinvoicepayment/store', 'UpsInvoicePaymentController@store');
		Route::any('upsinvoicepayment/update/{id}', 'UpsInvoicePaymentController@update');
		Route::any('upsinvoicepayment/getinvoicesusingfilenumber', 'UpsInvoicePaymentController@getinvoicesusingfilenumber')->name('getinvoicesusingfilenumber');
		Route::any('upsinvoicepayment/getupsinvoicesofclient', 'UpsInvoicePaymentController@getupsinvoicesofclient')->name('getupsinvoicesofclient');
		Route::any('upsinvoicepayment/getselectedupsinvoicedata', 'UpsInvoicePaymentController@getselectedupsinvoicedata')->name('getselectedupsinvoicedata');
		Route::any('upsinvoicepayment/getcurrencyratesection', 'UpsInvoicePaymentController@getcurrencyratesection');
		Route::any('upsinvoicepayment/getshipmentnumbers', 'UpsInvoicePaymentController@getshipmentnumbers');
		Route::any('upsinvoicepayment/getupsinvoices', 'UpsInvoicePaymentController@getupsinvoices');
		Route::any('upsinvoicepayment/getclients', 'UpsInvoicePaymentController@getclients');
		Route::any('invoicepayment/getclientsforallpayment', 'InvoicePaymentsController@getclientsforallpayment');
		Route::any('aeropostinvoicepayment/getclients', 'AeropostInvoicePaymentController@getclients');
		Route::any('aeropostinvoicepayment/gettrackingnumbers', 'AeropostInvoicePaymentController@gettrackingnumbers');
		Route::any('aeropostinvoicepayment/getaeropostinvoices', 'AeropostInvoicePaymentController@getaeropostinvoices');
		Route::any('ccpackinvoicepayment/getclients', 'ccpackInvoicePaymentController@getclients');
		Route::any('ccpackinvoicepayment/getccpackinvoices', 'ccpackInvoicePaymentController@getccpackinvoices');


		Route::get('invoicepayment/invoicepaymentcreateall/{clientId?}', 'InvoicePaymentsController@createforall')->name('invoicepaymentcreateall');
		Route::any('invoicepayment/getcourierorcargodata', 'InvoicePaymentsController@getcourierorcargodata')->name('getcourierorcargodata');
		Route::post('invoicepayment/storeall', 'InvoicePaymentsController@storeall');
		Route::any('invoicepayment/listbyall', 'InvoicePaymentsController@listbyall')->name('listbyall');



		Route::any('invoicepayment/invoicepaymentslisting/{flag?}', 'InvoicePaymentsController@invoicepaymentslisting')->name('invoicepaymentslisting');
		Route::any('invoicepayment/printreceiptofinvoicepayment/{id?}/{flag?}/{flagmodule?}', 'InvoicePaymentsController@printreceiptofinvoicepayment')->name('printreceiptofinvoicepayment');
		Route::any('invoicepayment/printsinglereceipt/{receiptNumber?}/{flag?}/{flagmodule?}', 'InvoicePaymentsController@printsinglereceipt')->name('printsinglereceipt');
		Route::any('invoicepayment/deleteinvoicepayment/{id?}/{receiptNumber?}', 'InvoicePaymentsController@destroy')->name('deleteinvoicepayment');


		Route::any('invoicepayment/invoicepaymentoutsidefiltering', 'InvoicePaymentsController@invoicepaymentoutsidefiltering')->name('invoicepaymentoutsidefiltering');
		//-- END-- Invoice Payments

	}
);

// Customs Management
Route::group(
	['middleware' => ['auth', 'FilterDataBeforeSave'], 'prefix' => 'customs'],
	function () {
		Route::get('/', 'CustomsController@index')->name('customs');
		Route::get('/create/{cargoId?}', 'CustomsController@create')->name('createcustoms');
		Route::post('/store', 'CustomsController@store')->name('storecustoms');
		Route::any('/checkuniquecustomfilenumber', 'CustomsController@checkuniquecustomfilenumber')->name('checkuniquecustomfilenumber');
	}
);
//-- END-- Customs Management

Route::group(
	['middleware' => ['auth', 'FilterDataBeforeSave']],
	function () {
		// Deposit Cash Credit Managment 
		Route::get('/depositcashcredit', 'DepositCashCreditController@index')->name('depositcashcredit');
		Route::get('depositcashcredit/create', 'DepositCashCreditController@create')->name('createdepositcashcredit');
		Route::post('depositcashcredit/store', 'DepositCashCreditController@store');
		Route::any('depositcashcredit/edit/{id}', 'DepositCashCreditController@edit')->name('editdepositcashcredit');
		Route::any('depositcashcredit/update/{id}', 'DepositCashCreditController@update');
		Route::any('depositcashcredit/delete/{id}', 'DepositCashCreditController@destroy')->name('deletedepositcashcredit');
		// End Deposit Cash Credit Managment 

		// Reports
		Route::any('reports/billingitemsdetailreport', 'ReportsController@billingitemsdetailreport')->name('billingitemsdetailreport');
		Route::any('reports/listbillingitemsdetailreport', 'ReportsController@listbillingitemsdetailreport')->name('listbillingitemsdetailreport');
		Route::any('reports/fetchbillingitemsdetailreport/{id}', 'ReportsController@fetchbillingitemsdetailreport')->name('fetchbillingitemsdetailreport');
		Route::any('reports/listfetchbillingitemsdetailreport', 'ReportsController@listfetchbillingitemsdetailreport')->name('listfetchbillingitemsdetailreport');
		Route::any('reports/printfetchbillingitemsdetailreport', 'ReportsController@printfetchbillingitemsdetailreport')->name('printfetchbillingitemsdetailreport');
		Route::any('reports/costitemsdetailreport', 'ReportsController@costitemsdetailreport')->name('costitemsdetailreport');
		Route::any('reports/listcostitemsdetailreport', 'ReportsController@listcostitemsdetailreport')->name('listcostitemsdetailreport');
		Route::any('reports/fetchcostitemsdetailreport/{id}', 'ReportsController@fetchcostitemsdetailreport')->name('fetchcostitemsdetailreport');
		Route::any('reports/listfetchcostitemsdetailreport', 'ReportsController@listfetchcostitemsdetailreport')->name('listfetchcostitemsdetailreport');
		Route::any('reports/printfetchcostitemsdetailreport', 'ReportsController@printfetchcostitemsdetailreport')->name('printfetchcostitemsdetailreport');
		Route::get('reports/cashcreditall', 'ReportsController@cashcreditallreport')->name('cashcreditallreport');
		Route::get('reports/cashier', 'ReportsController@cashierReport')->name('cashierReport');
		Route::get('reports/cashierAllDetail/{id}', 'ReportsController@cashierAllDetail')->name('cashierReportAllDetail');
		Route::get('reports/printCashierReport/{id?}/{date?}', "ReportsController@printCashierReport")->name('printCashierReport');
		Route::get('reports/printCashierDisbursementReport/{id?}/{date?}', 'ReportsController@printCashierDisbursementReport')->name('printCashierDisbursementReport');
		Route::get('reports/printLocalRentReport/{id?}/{date?}', 'ReportsController@printLocatRentReport')->name('printLocatRentReport');
		Route::get('reports/getcashcreditdataonclick/{accountId}/{accountName}', 'ReportsController@getcashcreditdataonclick')->name('getcashcreditdataonclick');
		Route::get('reports/cashcredit', 'ReportsController@cashcreditreport')->name('cashcreditreport');
		Route::any('reports/getcashcreditdata', 'ReportsController@getcashcreditdata')->name('getcashcreditdata');
		Route::any('reports/getcommissionReport', 'ReportsController@getCommissionReport')->name('commissionReport');
		Route::any('reports/listgetcommissionReport', 'ReportsController@listgetcommissionReport')->name('listgetcommissionReport');
		Route::any('reports/printandexportcommissionReport/{fromDate?}/{toDate?}/{file_type?}/{typeimpexp?}/{submitButtonName?}', 'ReportsController@printandexportcommissionReport')->name('printandexportcommissionReport');
		Route::any('reports/filterbycouriertype', 'ReportsController@filterbycouriertype')->name('filterbycouriertype');
		Route::any('reports/printCommissionReport/{type_flag?}', 'ReportsController@printCommissionReport')->name('printCommissionReport');
		Route::any('reports/getExcelFile/{mailflag?}/{type?}', 'ReportsController@getExcelFile')->name('getExcelFile');
		Route::any('reports/freedomicilereport', 'ReportsController@freedomicilereport')->name('freeDomicileReport');
		Route::any('reports/listfreedomicilereport', 'ReportsController@listfreedomicilereport')->name('listfreedomicilereport');
		Route::any('reports/printandexportfreedomicilereport/{fromDate?}/{toDate?}/{submitButtonName?}', 'ReportsController@printandexportfreedomicilereport')->name('printandexportfreedomicilereport');
		Route::any('reports/fdreportweekly/{mailflag?}/{fromDate?}/{toDate?}', 'ReportsController@fdreportweekly')->name('fdreportweekly');
		Route::any('reports/settodateinfdreport', 'ReportsController@settodateinfdreport')->name('settodateinfdreport');
		Route::get('reports/printfreedomicilereport/{fromDate}/{toDate}', 'ReportsController@printfreedomicilereport')->name('printfreedomicilereport');
		Route::any('reports/upsexpensepayments', 'ReportsController@upsexpensepayments')->name('upsexpensepayments');
		Route::any('reports/upsexpensepaymentsoutsidefiltering', 'ReportsController@upsexpensepaymentsoutsidefiltering')->name('upsexpensepaymentsoutsidefiltering');

		Route::any('/reports/upsMissingInvoices', 'ReportsController@upsMissingInvoiceReport')->name('upsMissingInvoiceReport');
		Route::any('/reports/listupsMissingInvoiceReport', 'ReportsController@listupsMissingInvoiceReport')->name('listupsMissingInvoiceReport');
		Route::any('/reports/upsMissingInvoicesPDF', 'ReportsController@upsMissingInoiceReportPdf')->name('upsMissingInoiceReportPdf');
		Route::any('reports/upsprofitreports', 'ReportsController@upsprofitreports')->name('upsprofitreports');
		Route::any('reports/upsprofitreports/view-detail/{flag}/{id?}', 'ReportsController@viewDetail');
		Route::any('reports/tmpReport', 'ReportsController@tmpReport')->name('templeteReport');

		Route::any('reports/tmpReportByInvoice', 'ReportsController@tmpReportByInvoice')->name('tmpReportByInvoice');


		Route::get('reports/clientcreditall', 'ReportsController@clientcreditallreport')->name('clientcreditallreport');
		Route::get('reports/listclientcreditall', 'ReportsController@listclientcreditallreport')->name('listclientcreditallreport');
		Route::get('reports/getclientcreditdataonclick/{clientId}/{clientName}', 'ReportsController@getclientcreditdataonclick')->name('getclientcreditdataonclick');
		Route::get('reports/listgetclientcreditdataonclick', 'ReportsController@listgetclientcreditdataonclick')->name('listgetclientcreditdataonclick');
		Route::get('reports/clientcredit', 'ReportsController@clientcreditreport')->name('clientcreditreport');
		Route::any('reports/getclientcreditdata', 'ReportsController@getclientcreditdata')->name('getclientcreditdata');

		Route::any('/reports/missingInvoices', 'ReportsController@missingInvoiceReport')->name('missingInvoiceReports');
		Route::any('/reports/listmissingInvoiceReport', 'ReportsController@listmissingInvoiceReport')->name('listmissingInvoiceReport');
		Route::any('/reports/missingInvoicesPDF', 'ReportsController@missingInoiceReportPdf')->name('missingInvoiceReportsPDF');


		Route::any('/reports/listcustomreport', 'ReportsController@listcustomreport')->name('listcustomreport');
		Route::any('/reports/customreport', 'ReportsController@customreport')->name('customreport');
		Route::any('reports/getcustomreportdata', 'ReportsController@getcustomreportdata')->name('getcustomreportdata');
		Route::any('reports/getallcustomreportdata', 'ReportsController@getallcustomreportdata')->name('getallcustomreportdata');
		Route::any('/reports/warehousereport', 'ReportsController@warehousereport')->name('warehousereport');
		Route::any('/reports/listwarehousereport', 'ReportsController@listwarehousereport')->name('listwarehousereport');
		Route::any('/reports/warehousereportpdf', 'ReportsController@warehousereportpdf')->name('warehousereportpdf');

		Route::any('/reports/warehousereportcourier', 'ReportsController@warehousereportcourier')->name('warehousereportcourier');
		Route::any('/reports/listwarehousereportcourier', 'ReportsController@listwarehousereportcourier')->name('listwarehousereportcourier');
		Route::any('/reports/warehousereportcourierpdf', 'ReportsController@warehousereportcourierpdf')->name('warehousereportcourierpdf');

		Route::any('/reports/nonbilledfiles', 'ReportsController@nonbilledfiles')->name('nonbilledfiles');
		Route::any('/reports/nonBilledFilesReportsPDF', 'ReportsController@nonBilledFilesReportsPDF')->name('nonBilledFilesReportsPDF');
		Route::any('/reports/listnonbilledfiles', 'ReportsController@listnonbilledfiles')->name('listnonbilledfiles');

		Route::any('/reports/nonbilledfilescourier', 'ReportsController@nonbilledfilescourier')->name('nonbilledfilescourier');
		Route::any('/reports/nonBilledFilesCourierReportsPDF', 'ReportsController@nonBilledFilesCourierReportsPDF')->name('nonBilledFilesCourierReportsPDF');
		Route::any('/reports/listnonbilledfilescourier', 'ReportsController@listnonbilledfilescourier')->name('listnonbilledfilescourier');

		Route::any('/reports/filesWithExpenseNoInvoices/{module?}', 'ReportsController@filesWithExpenseNoInvoices')->name('filesWithExpenseNoInvoices');
		Route::any('/reports/filesWithExpenseNoInvoicesPDF', 'ReportsController@filesWithExpenseNoInvoicesPDF')->name('filesWithExpenseNoInvoicesPDF');
		Route::any('/reports/listfilesWithExpenseNoInvoices', 'ReportsController@listfilesWithExpenseNoInvoices')->name('listfilesWithExpenseNoInvoices');

		Route::any('/reports/filesWithExpenseNoInvoicesCourier/{module?}', 'ReportsController@filesWithExpenseNoInvoicesCourier')->name('filesWithExpenseNoInvoicesCourier');
		Route::any('/reports/filesWithExpenseNoInvoicesCourierPDF', 'ReportsController@filesWithExpenseNoInvoicesCourierPDF')->name('filesWithExpenseNoInvoicesCourierPDF');
		Route::any('/reports/listfilesWithExpenseNoInvoicesCourier', 'ReportsController@listfilesWithExpenseNoInvoicesCourier')->name('listfilesWithExpenseNoInvoicesCourier');


		Route::any('/reports/filterreceivedpaymentbydate', 'ReportsController@filterreceivedpaymentbydate')->name('filterreceivedpaymentbydate');
		Route::any('/reports/gettotalsincurrencies', 'ReportsController@gettotalsincurrencies')->name('gettotalsincurrencies');
		Route::any('/reports/filterdisbursementbydate', 'ReportsController@filterdisbursementbydate')->name('filterdisbursementbydate');
		Route::any('/reports/gettotalsofdisbursement', 'ReportsController@gettotalsofdisbursement')->name('gettotalsofdisbursement');
		Route::any('/reports/filterlocalrentbydate', 'ReportsController@filterlocalrentbydate')->name('filterlocalrentbydate');


		Route::any('/reports/combinereport', 'ReportsController@combinedetailreportindex')->name('combineReport');
		Route::any('/reports/listcombinereport', 'ReportsController@listcombinereport')->name('listcombinereport');
		Route::post('/reports/filtercombinereport/{op?}', 'ReportsController@filtercombinereport')->name('filtercombinereport');

		Route::any('/reports/statementofaccounts', 'ReportsController@statementofaccounts')->name('statementofaccounts');
		Route::any('/reports/getdueinvoicesofclient/{clientId?}', 'ReportsController@getdueinvoicesofclient')->name('getdueinvoicesofclient');
		Route::any('/reports/listgetdueinvoicesofclient', 'ReportsController@listgetdueinvoicesofclient')->name('listgetdueinvoicesofclient');
		Route::any('/reports/getduefilteredinvoicesofclient', 'ReportsController@getduefilteredinvoicesofclient')->name('getduefilteredinvoicesofclient');
		Route::any('/reports/genericdisbursementreport/{flag?}/{fromDate?}/{toDate?}/{cashBank?}/{cashier?}', 'ReportsController@genericdisbursementreport')->name('genericdisbursementreport');
		Route::any('/reports/genericcollectionreport/{flag?}/{fromDate?}/{toDate?}/{currency?}', 'ReportsController@genericcollectionreport')->name('genericcollectionreport');
		Route::any('/reports/makedefaultcashbankofcashierinreport/{id?}', 'ReportsController@makedefaultcashbankofcashierinreport')->name('makedefaultcashbankofcashierinreport');
		Route::any('/reports/viewinvoicedetailswithcollection/{invoiceId?}', 'CommonController@viewInvoiceDetailsWithCollection')->name('viewInvoiceDetailsWithCollection');
		Route::any('/reports/checkguaranteetopayreport', 'ReportsController@checkguaranteetopayreport')->name('checkguaranteetopayreport');
		Route::any('/reports/checkguaranteetopayreport/list', 'ReportsController@checkguaranteetopayreportlist')->name('checkguaranteetopayreportlist');

		Route::any('/report/show-statement-of-accounts','ReportsV1Controller@showStatementOfAccount')->name('showStatementOfAccount');
		//Route::any('/report/export-open-inovoices/{fromDate?}/{toDate?}','ReportsV1Controller@exportStatementOfAccount')->name('exportStatementOfAccount');
		Route::any('/report/export-statement-of-accounts/{fromDate?}/{toDate?}','ReportsV1Controller@exportStatementOfAccountNew')->name('exportStatementOfAccount');
		Route::any('/report/show-pending-invoices','ReportsV1Controller@showPendingInvoices')->name('showPendingInvoices');
		Route::any('/report/export-pending-invoices/{fromDate?}/{toDate?}','ReportsV1Controller@exportPendingInvoices')->name('exportPendingInvoices');
		
		Route::any('/report/show-ar-aging','ReportsV1Controller@showArAging')->name('showArAging');
		//Route::any('/report/export-ar-aging/{fromDate?}/{toDate?}','ReportsV1Controller@exportArAging')->name('exportArAging');
		Route::any('/report/export-ar-aging/{fromDate?}/{toDate?}','ReportsV1Controller@exportArAgingNew')->name('exportArAging');




		// End Reports
	}
);

// UPS Expenses Management
Route::group(
	['middleware' => ['auth', 'FilterDataBeforeSave'], 'prefix' => 'upsexpense'],
	function () {
		Route::get('/', 'UpsExpenseController@index')->name('upsexpenses');
		Route::get('/createupsexpense/{upsId?}/{flagFromWhere?}', 'UpsExpenseController@create')->name('createupsexpense');
		Route::post('/storeupsexpense', 'UpsExpenseController@store')->name('storeupsexpense');
		Route::any('/editupsexpense/{id}/{flagFromWhere?}', 'UpsExpenseController@edit')->name('editupsexpense');
		Route::post('/updateupsexpense/{id}', 'UpsExpenseController@update')->name('updateupsexpense');

		Route::any('/generateupsvoucheronsavenext', 'UpsExpenseController@generateupsvoucheronsavenext')->name('generateupsvoucheronsavenext');
		Route::any('upsexpense/getprintsingleupsexpense/{expenseId}/{upsId}/{flag?}', 'UpsExpenseController@getprintsingleupsexpense')->name('getprintsingleupsexpense');
		Route::any('expense/getprintallupsexpense/{flag?}', 'UpsExpenseController@getprintallupsexpense')->name('getprintallupsexpense');
		Route::post('/checkCurrency', 'UpsExpenseController@checkCurrency');

		Route::any('/editagentupsexpensesbyadmin/{id}/{flagFromWhere?}', 'UpsExpenseController@editagentupsexpensesbyadmin')->name('editagentupsexpensesbyadmin');
		Route::post('/updateagentupsexpensesbyadmin/{id}', 'UpsExpenseController@updateagentupsexpensesbyadmin')->name('updateagentupsexpensesbyadmin');
		Route::any('/viewdetailsupsexpense/{id}', 'UpsExpenseController@viewdetailsupsexpense')->name('viewdetailsupsexpense');
		Route::any('/listbydatatableserverside', 'UpsExpenseController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('/checkoperationfordatatableserverside', 'UpsExpenseController@checkoperationfordatatableserverside')->name('checkupsexpenseoperationfordatatableserverside');
	}
);
//-- END-- UPS Expenses Management


// UPS Invoice Management
Route::group(
	['middleware' => ['auth', 'FilterDataBeforeSave'], 'prefix' => 'upsinvoice'],
	function () {
		Route::get('/upsinvoices', 'UpsInvoicesController@index')->name('upsinvoices');
		Route::get('/create/{upsId?}/{flagInvoice?}/{flagFromWhere?}', 'UpsInvoicesController@create')->name('createupsinvoice');
		Route::post('/store', 'UpsInvoicesController@store');
		Route::any('/edit/{id}/{flagFromWhere?}', 'UpsInvoicesController@edit')->name('editupsinvoice');
		Route::any('/update/{id}', 'UpsInvoicesController@update');
		Route::any('/getupsdetailforinvoice', 'UpsInvoicesController@getupsdetailforinvoice')->name('getupsdetailforinvoice');
		Route::any('/storeupsinvoiceandprint', 'UpsInvoicesController@storeupsinvoiceandprint');
		Route::any('/viewandprintupsinvoice/{id}', 'UpsInvoicesController@viewandprintupsinvoice')->name('viewandprintupsinvoice');
		Route::any('/copy/{id}/{flag?}', 'UpsInvoicesController@copy')->name('copyupsinvoice');

		Route::any('/deleteupsinvoicefromedit/{id}', 'UpsInvoicesController@deleteupsinvoicefromedit')->name('deleteupsinvoicefromedit');
		Route::any('/deleteupsinvoice/{id}', 'UpsInvoicesController@destroy')->name('deleteupsinvoice');

		Route::any('/viewupsinvoicedetails/{id}', 'UpsInvoicesController@show')->name('viewupsinvoicedetails');
		Route::any('/listbydatatableserverside', 'UpsInvoicesController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('/checkoperationfordatatableserverside', 'UpsInvoicesController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserverside');
		Route::any('/getupsinvoiceforinputpicker', 'UpsInvoicesController@getupsinvoiceforinputpicker')->name('getupsinvoiceforinputpicker');
		/*Route::get('/pendinginvoices','InvoicesController@indexpendinginvoices')->name('pendinginvoices');
Route::get('invoices/create/{cargoId?}','InvoicesController@create')->name('createinvoice');
Route::post('invoices/store','InvoicesController@store');
Route::any('invoices/edit/{id}','InvoicesController@edit')->name('editinvoice');
Route::any('invoices/update/{id}','InvoicesController@update');
Route::any('invoices/delete/{id}','InvoicesController@destroy')->name('deleteinvoice');
Route::any('invoices/getcargodetailforinvoice','InvoicesController@getcargodetailforinvoice')->name('getcargodetailforinvoice');
Route::any('/changeinvoicestatus','InvoicesController@changeinvoicestatus')->name('changeinvoicestatus');*/
	}
);
//-- END-- UPS Invoice Management


Route::group(
	['middleware' => ['auth', 'FilterDataBeforeSave']],
	function () {

		// Client Categories Management
		Route::get('/clientcategories', 'ClientCategoriesController@index')->name('clientcategories');
		Route::get('clientcategory/create', 'ClientCategoriesController@create')->name('createclientcategory');
		Route::post('clientcategory/store', 'ClientCategoriesController@store');
		Route::any('clientcategory/edit/{id}', 'ClientCategoriesController@edit')->name('editclientcategory');
		Route::any('clientcategory/update/{id}', 'ClientCategoriesController@update');
		Route::any('clientcategory/delete/{id}', 'ClientCategoriesController@destroy')->name('deleteclientcategory');
		//-- END-- Client Categories Management

		// Payment Term Management
		Route::get('/paymentterms', 'PaymentTermsController@index')->name('paymentterms');
		Route::get('paymentterm/create', 'PaymentTermsController@create')->name('createpaymentterm');
		Route::post('paymentterm/store', 'PaymentTermsController@store');
		Route::any('paymentterm/edit/{id}', 'PaymentTermsController@edit')->name('editpaymentterm');
		Route::any('paymentterm/update/{id}', 'PaymentTermsController@update');
		Route::any('paymentterm/delete/{id}', 'PaymentTermsController@destroy')->name('deletepaymentterm');
		//-- END-- Payment Term Management

		// Country Management
		Route::get('/countries', 'CountryController@index')->name('countries');
		Route::get('country/create', 'CountryController@create')->name('createcountry');
		Route::post('country/store', 'CountryController@store');
		Route::any('country/edit/{id}', 'CountryController@edit')->name('editcountry');
		Route::any('country/update/{id}', 'CountryController@update');
		Route::any('country/delete/{id}', 'CountryController@destroy')->name('deletecountry');
		//-- END-- Country Management


		// State Management
		Route::get('/states', 'StateController@index')->name('states');
		Route::get('state/create', 'StateController@create')->name('createstate');
		Route::post('state/store', 'StateController@store');
		Route::any('state/edit/{id}', 'StateController@edit')->name('editstate');
		Route::any('state/update/{id}', 'StateController@update');
		Route::any('state/delete/{id}', 'StateController@destroy')->name('deletestate');
		//-- END-- State Management


		// Client Contact Management
		Route::get('/clientcontacts', 'ClientContactController@index')->name('clientcontacts');
		Route::get('clientcontact/create/{clientId?}/{flagFromWhere?}', 'ClientContactController@create')->name('createclientcontact');
		Route::post('clientcontact/store', 'ClientContactController@store');
		Route::any('clientcontact/edit/{id}/{flagFromWhere?}', 'ClientContactController@edit')->name('editclientcontact');
		Route::any('clientcontact/update/{id}', 'ClientContactController@update');
		Route::any('clientcontact/delete/{id}', 'ClientContactController@destroy')->name('deleteclientcontact');
		//-- END-- Client Contact Management

		// Vendor Contact Management
		Route::get('/vendorcontacts', 'VendorContactController@index')->name('vendorcontacts');
		Route::get('vendorcontact/create/{clientId?}/{flagFromWhere?}', 'VendorContactController@create')->name('createvendorcontact');
		Route::post('vendorcontact/store', 'VendorContactController@store');
		Route::any('vendorcontact/edit/{id}/{flagFromWhere?}', 'VendorContactController@edit')->name('editvendorcontact');
		Route::any('vendorcontact/update/{id}', 'VendorContactController@update');
		Route::any('vendorcontact/delete/{id}', 'VendorContactController@destroy')->name('deletevendorcontact');
		//-- END-- Vendor Contact Management


		// Client Branch Management
		Route::get('/clientbranches', 'ClientBranchController@index')->name('clientbranches');
		Route::get('clientbranch/create/{clientId?}/{flagFromWhere?}', 'ClientBranchController@create')->name('createclientbranch');
		Route::post('clientbranch/store', 'ClientBranchController@store');
		Route::any('clientbranch/edit/{id}/{flagFromWhere?}', 'ClientBranchController@edit')->name('editclientbranch');
		Route::any('clientbranch/update/{id}', 'ClientBranchController@update');
		Route::any('clientbranch/delete/{id}', 'ClientBranchController@destroy')->name('deleteclientbranch');
		Route::any('clientbranch/getbranches', 'ClientBranchController@getbranches')->name('getbranches');
		//-- END-- Client Branch Management


		// Client Address Management
		Route::get('/clientaddresses', 'ClientAddressController@index')->name('clientaddresses');
		Route::get('clientaddress/create/{clientId?}/{flagFromWhere?}', 'ClientAddressController@create')->name('createclientaddress');
		Route::post('clientaddress/store', 'ClientAddressController@store');
		Route::any('clientaddress/edit/{id}/{flagFromWhere?}', 'ClientAddressController@edit')->name('editclientaddress');
		Route::any('clientaddress/update/{id}', 'ClientAddressController@update');
		Route::any('clientaddress/delete/{id}', 'ClientAddressController@destroy')->name('deleteclientaddress');
		Route::any('clientaddress/getaddresses', 'ClientAddressController@getaddresses')->name('getaddresses');
		//-- END-- Client Address Management


		// Nature of services Management
		Route::get('/natureofservice', 'NatureOfServicesController@index')->name('natureofservices');
		Route::get('natureofservice/create', 'NatureOfServicesController@create')->name('createnatureofservice');
		Route::post('natureofservice/store', 'NatureOfServicesController@store');
		Route::any('natureofservice/edit/{id}', 'NatureOfServicesController@edit')->name('editnatureofservice');
		Route::any('natureofservice/update/{id}', 'NatureOfServicesController@update');
		Route::any('natureofservice/delete/{id}', 'NatureOfServicesController@destroy')->name('deletenatureofservice');
		//-- END-- Nature of services Management


		// Nature of services Management
		Route::get('/hawbfiles', 'HawbFilesController@index')->name('hawbfiles');
		Route::get('hawbfile/create', 'HawbFilesController@create')->name('createhawbfile');
		Route::post('hawbfile/store', 'HawbFilesController@store')->name('storehawb');
		Route::any('hawbfile/edit/{id}', 'HawbFilesController@edit')->name('edithawbfile');
		Route::any('hawbfile/update/{id}', 'HawbFilesController@update')->name('updatehawbfile');
		Route::any('hawbfile/delete/{id}', 'HawbFilesController@destroy')->name('deletehawbfile');
		Route::any('hawbfile/print/{id}/{cargoType}', 'HawbFilesController@print')->name('printhawbfiles');
		Route::any('hawbfile/view/{id}/{flag?}', 'HawbFilesController@show')->name('viewhawbfile');
		Route::any('hawbfile/listbydatatableserverside', 'HawbFilesController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('hawbfile/checkoperationfordatatableserversidehawbfiles', 'HawbFilesController@checkoperationfordatatableserversidehawbfiles')->name('checkoperationfordatatableserversidehawbfiles');
		//-- END-- Nature of services Management

		// Currency Management
		Route::get('/currency', 'CurrencyController@index')->name('currency');
		Route::get('currency/create', 'CurrencyController@create')->name('createcurrency');
		Route::post('currency/store', 'CurrencyController@store');
		Route::any('currency/edit/{id}', 'CurrencyController@edit')->name('editcurrency');
		Route::any('currency/update/{id}', 'CurrencyController@update');
		Route::any('currency/delete/{id}', 'CurrencyController@destroy')->name('deletecurrency');
		Route::any('currency/getcurrencydd', 'CurrencyController@getcurrencydd')->name('getcurrencydd');

		//-- END-- Currency Management

		// Other Expense Items Management
		Route::get('/otherexpenseitems', 'OtherExpenseItemsController@index')->name('otherexpenseitems');
		Route::get('otherexpenseitem/create', 'OtherExpenseItemsController@create')->name('createotherexpenseitem');
		Route::post('otherexpenseitem/store', 'OtherExpenseItemsController@store');
		Route::any('otherexpenseitem/edit/{id}', 'OtherExpenseItemsController@edit')->name('editotherexpenseitem');
		Route::any('otherexpenseitem/update/{id}', 'OtherExpenseItemsController@update');
		Route::any('otherexpenseitem/delete/{id}', 'OtherExpenseItemsController@destroy')->name('deleteotherexpenseitem');
		//-- END-- Other Expense Items Management


		// Other Expenses Management
		Route::get('/otherexpenses/{flagFrom?}', 'OtherExpensesController@index')->name('otherexpenses');
		Route::get('otherexpense/create/{flagFrom?}', 'OtherExpensesController@create')->name('createotherexpense');
		Route::post('otherexpense/store', 'OtherExpensesController@store')->name('storeotherexpense');
		Route::any('otherexpense/edit/{id}/{flagFrom?}', 'OtherExpensesController@edit')->name('editotherexpense');
		Route::any('otherexpense/update/{id}', 'OtherExpensesController@update')->name('updateotherexpense');
		Route::any('otherexpense/getprintsingleotherexpense/{expenseId}', 'OtherExpensesController@getprintsingleotherexpense')->name('getprintsingleotherexpense');
		Route::any('otherexpense/printotherexpensebyfilter/{fromDate?}/{toDate?}/{expenseStatus?}/{submitButtonName?}', 'OtherExpensesController@printotherexpensebyfilter')->name('printotherexpensebyfilter');
		Route::any('otherexpense/expandotherexpenses', 'OtherExpensesController@expandotherexpenses')->name('expandotherexpenses');
		Route::any('otherexpense/deleteotherexpense/{id}', 'OtherExpensesController@deleteotherexpense')->name('deleteotherexpense');
		Route::any('otherexpense/deleteotherexpensevoucher/{id}', 'OtherExpensesController@deleteotherexpensevoucher')->name('deleteotherexpensevoucher');
		Route::any('otherexpense/generateotherexpensevoucheronsavenext', 'OtherExpensesController@generateotherexpensevoucheronsavenext')->name('generateotherexpensevoucheronsavenext');
		Route::any('otherexpense/addmoreotherexpense', 'OtherExpensesController@addmoreotherexpense')->name('addmoreotherexpense');
		Route::any('otherexpense/getprintviewsingleadministrationexpensecashier/{expenseId}/{flag?}', 'OtherExpensesController@getprintviewsingleadministrationexpensecashier')->name('getprintviewsingleadministrationexpensecashier');
		Route::any('otherexpense/changeadministrationexpensestatusbycashier', 'OtherExpensesController@changeadministrationexpensestatusbycashier')->name('changeadministrationexpensestatusbycashier');
		Route::any('otherexpense/expense/listbydatatableserverside', 'OtherExpensesController@listbydatatableserverside')->name('aeropostlistbydatatableserverside');
		Route::any('/checkoperationfordatatableserversideadministrationexpense', 'OtherExpensesController@checkoperationfordatatableserversideadministrationexpense')->name('checkoperationfordatatableserversideadministrationexpense');
		//-- END-- Other Expenses Management


		// Warehouse Management
		Route::get('/warehouses', 'WarehouseController@index')->name('warehouses');
		Route::get('warehouse/create', 'WarehouseController@create')->name('createwarehouse');
		Route::post('warehouse/store', 'WarehouseController@store');
		Route::any('warehouse/edit/{id}', 'WarehouseController@edit')->name('editwarehouse');
		Route::any('warehouse/update/{id}', 'WarehouseController@update');
		Route::any('warehouse/delete/{id}', 'WarehouseController@destroy')->name('deletewarehouse');

		Route::get('warehouse/addwarehouseinfile/{moduleId?}/{flagModule?}', 'WarehouseController@addwarehouseinfile')->name('addwarehouseinfile');
		Route::post('warehouse/storewarehouseinfile', 'WarehouseController@storewarehouseinfile');
		Route::get('warehouse/addcashcreditinfile/{cargoId}', 'WarehouseController@addcashcreditinfile')->name('addcashcreditinfile');
		Route::post('warehouse/storecashcreditinfile', 'WarehouseController@storecashcreditinfile');

		//-- END-- Warehouse Management


		Route::any('cargo/viewcargodetailforagent/{id}', 'AgentCargoController@viewcargodetailforagent')->name('viewcargodetailforagent');
		Route::any('cargo/viewhawbdetailforagent/{id}', 'AgentCargoController@viewhawbdetailforagent')->name('viewhawbdetailforagent');
		Route::any('cargo/assignonconsolidationbyagent', 'AgentCargoController@assignonconsolidationbyagent')->name('assignonconsolidationbyagent');
		Route::any('cargo/assignonconsolidationbyagenttohawb', 'AgentCargoController@assignonconsolidationbyagenttohawb')->name('assignonconsolidationbyagenttohawb');
		Route::any('courier/viewcourierdetailforagent/{id}', 'AgentUpsController@viewcourierdetailforagent')->name('viewcourierdetailforagent');
		Route::any('courier/assignonconsolidationbyagentcourier', 'AgentUpsController@assignonconsolidationbyagentcourier')->name('assignonconsolidationbyagentcourier');

		Route::any('cargo/viewcargodetailforwarehouse/{id}/{flag?}/{houseId?}', 'WarehouseCargoController@viewcargodetailforwarehouse')->name('viewcargodetailforwarehouse');
		Route::any('cargo/cargowarehouseflow/{masterId?}/{houseId?}', 'WarehouseCargoController@cargowarehouseflow')->name('cargowarehouseflow');
		Route::any('warehouseups/viewcourierdetailforwarehouse/{id}/{flag?}/{houseId?}', 'WarehouseUpsController@viewcourierdetailforwarehouse')->name('viewcourierdetailforwarehouse');
		Route::any('warehouseups/viewcourierdetailforwarehousemaster/{id}/{flag?}/{houseId?}', 'WarehouseUpsController@viewcourierdetailforwarehousemaster')->name('viewcourierdetailforwarehousemaster');
		Route::any('warehouseups/courierupswarehouseflow/{masterId?}/{houseId?}', 'WarehouseUpsController@courierupswarehouseflow')->name('courierupswarehouseflow');
		Route::any('warehouseups/assignstatusbywarehousecourier', 'WarehouseUpsController@assignstatusbywarehousecourier')->name('assignstatusbywarehousecourier');
		Route::any('warehouseups/assignstatusbywarehousecouriermaster', 'WarehouseUpsController@assignstatusbywarehousecouriermaster')->name('assignstatusbywarehousecouriermaster');
		Route::any('warehouseaeropost/assignstatusbywarehousecourieraeropost', 'WarehouseAeropostController@assignstatusbywarehousecourieraeropost')->name('assignstatusbywarehousecourieraeropost');
		Route::any('warehouseaeropost/assignstatusbywarehousecourieraeropostmaster', 'WarehouseAeropostController@assignstatusbywarehousecourieraeropostmaster')->name('assignstatusbywarehousecourieraeropostmaster');
		Route::any('warehouseccpack/assignstatusbywarehousecourierccpack', 'WarehouseCcpackController@assignstatusbywarehousecourierccpack')->name('assignstatusbywarehousecourierccpack');
		Route::any('warehouseaeropost/assignstatusbywarehousecourierccpackmaster', 'WarehouseAeropostController@assignstatusbywarehousecourierccpackmaster')->name('assignstatusbywarehousecourierccpackmaster');

		Route::any('cargo/assignwarehousestatusbywarehouseuser', 'WarehouseCargoController@assignwarehousestatusbywarehouseuser')->name('assignwarehousestatusbywarehouseuser');
		Route::any('cargo/assignstatusbywarehousehousefile', 'WarehouseCargoController@assignstatusbywarehousehousefile')->name('assignstatusbywarehousehousefile');
		Route::any('cargo/assigncargohousefilestatusbywarehouseuser', 'WarehouseCargoController@assigncargohousefilestatusbywarehouseuser')->name('assigncargohousefilestatusbywarehouseuser');
		Route::any('cargo/assigncargomasterfilestatusbyadmin', 'WarehouseCargoController@assigncargomasterfilestatusbyadmin')->name('assigncargomasterfilestatusbyadmin');
		Route::any('cargo/assigncargomasterfilestatusbywarehouse', 'WarehouseCargoController@assigncargomasterfilestatusbywarehouse')->name('assigncargomasterfilestatusbywarehouse');

		Route::any('assignwarehousestatustohousefilebywarehouseuser', 'WarehouseCargoController@assignwarehousestatustohousefilebywarehouseuser')->name('assignwarehousestatustohousefilebywarehouseuser');

		Route::any('cargo/verificationinspection', 'WarehouseCargoController@verificationinspection')->name('verificationinspection');
		Route::any('cargo/addverificationnote/{cargoHouseAWBId}/{flag?}', 'WarehouseCargoController@addverificationnote')->name('addverificationnote');
		Route::any('cargo/saveverificationnote', 'WarehouseCargoController@saveverificationnote')->name('saveverificationnote');
		Route::any('cargo/viewverificationnote/{cargoHouseAWBId}/{flag?}', 'WarehouseCargoController@viewverificationnote')->name('viewverificationnote');
		Route::any('warehouse/cargo/listbydatatableserverside', 'WarehouseCargoController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('warehouse/hawbfile/listbydatatableserverside', 'WarehouseCargoController@listbydatatableserversidehawbfile')->name('listbydatatableserversidehawbfile');
		Route::any('warehouse/ups/listbydatatableserverside', 'WarehouseUpsController@listbydatatableserverside')->name('listbydatatableserverside');

		// Add New Items block
		Route::any('items/addnewitem/{flag}', 'HomeController@addnewitem')->name('addnewitem');
		Route::any('costs/storenewitem', 'CostsController@storenewitem');
		Route::any('costs/getcostdropdowndataaftersubmit', 'CostsController@getcostdropdowndataaftersubmit');
		Route::post('billingitem/storenewitem', 'BillingItemsController@storenewitem');
		Route::any('billingitem/getbillingitemsdropdowndataaftersubmit', 'BillingItemsController@getbillingitemsdropdowndataaftersubmit');
		Route::any('clients/storenewitem', 'ClientsController@storenewitem');
		Route::any('clients/getclientdropdowndataaftersubmit', 'ClientsController@getclientdropdowndataaftersubmit');

		Route::any('vendors/storenewitem', 'VendorsController@storenewitem');
		Route::any('vendors/getvendordropdowndataaftersubmit', 'VendorsController@getvendordropdowndataaftersubmit');
		// -- END -- Add New Items block

		// Warehouse Role
		Route::get('warehouseinvoices/warehouseinvoices', 'WarehouseInvoicesController@index')->name('warehouseinvoices');
		Route::get('warehouseinvoices/create/{cargoId?}', 'WarehouseInvoicesController@create')->name('createwarehouseinvoice');
		Route::post('warehouseinvoices/store', 'WarehouseInvoicesController@store');
		Route::any('warehouseinvoices/edit/{id}/{flagFromWhere?}', 'WarehouseInvoicesController@edit')->name('editwarehouseinvoice');
		Route::any('warehouseinvoices/update/{id}', 'WarehouseInvoicesController@update');
		Route::any('warehouseinvoices/gethawboffiles', 'WarehouseInvoicesController@gethawboffiles');
		Route::get('warehouseinvoices/send', 'WarehouseInvoicesController@sendMail')->name('warehouseinvoicesmail');

		Route::get('warehouseups', 'WarehouseUpsController@index')->name('warehouseups');
		Route::get('warehouseupsmaster', 'WarehouseUpsMasterController@index')->name('warehouseupsmaster');
		Route::post('warehouse/ups-master/expandhousefiles', 'WarehouseUpsMasterController@expandhousefilesforwarehouse');
		Route::get('warehouseups/importupswarehouse', 'WarehouseUpsController@import')->name('importupswarehouse');
		Route::post('warehouseups/importupsdatawarehouse', 'WarehouseUpsController@importdatawarehouse');
		Route::any('assign-delivery-boy/{module?}', 'WarehouseUpsController@assigndeliveryboy')->name('assign-delivery-boy');
		Route::any('assign-delivery-boy-submit', 'WarehouseUpsController@assigndeliveryboysubmit')->name('assigndeliveryboysubmit');

		Route::any('fileamendmentbywarehouse/{flagModule?}', 'WarehouseCargoController@fileamendmentbywarehouse')->name('fileamendmentbywarehouse');
		Route::any('generatehousefileinvoice', 'WarehouseCargoController@generatehousefileinvoice')->name('generatehousefileinvoice');

		Route::any('warehouse/cargo/step1shipmentstatus', 'WarehouseCargoController@step1shipmentstatus')->name('cargostep1shipmentstatus');
		Route::any('warehouse/cargo/step2racklocation', 'WarehouseCargoController@step2racklocation')->name('cargostep2racklocation');
		Route::any('warehouse/cargo/step3custominspection', 'WarehouseCargoController@step3custominspection')->name('cargostep3custominspection');
		Route::any('warehouse/cargo/step4invoiceandpayment', 'WarehouseCargoController@step4invoiceandpayment')->name('cargostep4invoiceandpayment');
		Route::any('warehouse/cargo/step5shipmentrelease', 'WarehouseCargoController@step5shipmentrelease')->name('cargostep5shipmentrelease');
		Route::any('warehouse/cargo/releasereceipt', 'WarehouseCargoController@releasereceipt')->name('cargoreleasereceipt');

		Route::any('warehouse/ups/upsstep1shipmentstatus', 'WarehouseUpsController@upsstep1shipmentstatus')->name('upsstep1shipmentstatus');
		Route::any('warehouse/ups/upsstep2custominspection', 'WarehouseUpsController@upsstep2custominspection')->name('upsstep2custominspection');
		Route::any('warehouse/ups/upsstep3movetononboundedwh', 'WarehouseUpsController@upsstep3movetononboundedwh')->name('upsstep3movetononboundedwh');
		Route::any('warehouse/ups/upsstep4invoiceandpayment', 'WarehouseUpsController@upsstep4invoiceandpayment')->name('upsstep4invoiceandpayment');
		Route::any('warehouse/ups/upsstep5assigndeliveryboy', 'WarehouseUpsController@upsstep5assigndeliveryboy')->name('upsstep5assigndeliveryboy');

		Route::get('warehouseaeroposts', 'WarehouseAeropostController@index')->name('warehouseaeroposts');
		Route::get('warehouseaeropostmaster', 'WarehouseAeropostMasterController@index')->name('warehouseaeropostmaster');
		Route::post('warehouse/aeropost-master/expandhousefiles', 'WarehouseAeropostMasterController@expandhousefilesforwarehouse');
		Route::any('warehouse/aeropost/listbydatatableserverside', 'WarehouseAeropostController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('warehouseaeropost/viewcourieraeropostdetailforwarehouse/{id}/{flag?}/{houseId?}', 'WarehouseAeropostController@viewcourieraeropostdetailforwarehouse')->name('viewcourieraeropostdetailforwarehouse');
		Route::any('warehouseaeropost/viewcourieraeropostdetailforwarehousemaster/{id}/{flag?}/{houseId?}', 'WarehouseAeropostController@viewcourieraeropostdetailforwarehousemaster')->name('viewcourieraeropostdetailforwarehousemaster');
		Route::any('warehouseaeropost/courieraeropostwarehouseflow/{masterId?}/{houseId?}', 'WarehouseAeropostController@courieraeropostwarehouseflow')->name('courieraeropostwarehouseflow');
		Route::any('warehouse/aeropost/aeropoststep1shipmentstatus', 'WarehouseAeropostController@aeropoststep1shipmentstatus')->name('aeropoststep1shipmentstatus');
		Route::any('warehouse/aeropost/aeropoststep2custominspection', 'WarehouseAeropostController@aeropoststep2custominspection')->name('aeropoststep2custominspection');
		Route::any('warehouse/aeropost/aeropoststep3movetononboundedwh', 'WarehouseAeropostController@aeropoststep3movetononboundedwh')->name('aeropoststep3movetononboundedwh');
		Route::any('warehouse/aeropost/aeropoststep4invoiceandpayment', 'WarehouseAeropostController@aeropoststep4invoiceandpayment')->name('aeropoststep4invoiceandpayment');
		Route::any('warehouse/aeropost/aeropoststep5assigndeliveryboy', 'WarehouseAeropostController@aeropoststep5assigndeliveryboy')->name('aeropoststep5assigndeliveryboy');
		Route::any('non-bounded-warehouse/acceptfiles/{id?}/{flagFromWhere?}/{flagModule?}', 'CommonController@acceptfiles')->name('acceptfiles');
		Route::any('non-bounded-warehouse/acceptfilessubmit/{id?}/{flagModule?}/{tblName?}', 'CommonController@acceptfilessubmit')->name('acceptfilessubmit');





		Route::get('warehouseccpack', 'WarehouseCcpackController@index')->name('warehouseccpack');
		Route::get('warehouseccpackmaster', 'WarehouseCcpackMasterController@index')->name('warehouseccpackmaster');
		Route::post('warehouse/ccpack-master/expandhousefiles', 'WarehouseCcpackMasterController@expandhousefilesforwarehouse');
		Route::any('warehouse/ccpack/listbydatatableserverside', 'WarehouseCcpackController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('warehouseccpack/viewcourierccpackdetailforwarehouse/{id}/{flag?}/{houseId?}', 'WarehouseCcpackController@viewcourierccpackdetailforwarehouse')->name('viewcourierccpackdetailforwarehouse');
		Route::any('warehouseccpack/viewcourierccpackdetailforwarehousemaster/{id}/{flag?}/{houseId?}', 'WarehouseCcpackController@viewcourierccpackdetailforwarehousemaster')->name('viewcourierccpackdetailforwarehousemaster');
		Route::any('warehouseccpack/courierccpackwarehouseflow/{masterId?}/{houseId?}', 'WarehouseCcpackController@courierccpackwarehouseflow')->name('courierccpackwarehouseflow');
		Route::any('warehouse/ccpack/ccpackstep1shipmentstatus', 'WarehouseCcpackController@ccpackstep1shipmentstatus')->name('ccpackstep1shipmentstatus');
		Route::any('warehouse/ccpack/ccpackstep2custominspection', 'WarehouseCcpackController@ccpackstep2custominspection')->name('ccpackstep2custominspection');
		Route::any('warehouse/ccpack/ccpackstep3movetononboundedwh', 'WarehouseCcpackController@ccpackstep3movetononboundedwh')->name('ccpackstep3movetononboundedwh');
		Route::any('warehouse/ccpack/ccpackstep4invoiceandpayment', 'WarehouseCcpackController@ccpackstep4invoiceandpayment')->name('ccpackstep4invoiceandpayment');
		Route::any('warehouse/ccpack/ccpackstep5assigndeliveryboy', 'WarehouseCcpackController@ccpackstep5assigndeliveryboy')->name('ccpackstep5assigndeliveryboy');



		// -- End -- Warehouse Role

		// Manager Role
		Route::get('cargo/managerexpenses', 'ManagerExpenseController@index')->name('managerexpenses');
		Route::get('cargo/createmanagerexpenses/{flag}/{cargoId?}/{flagFromWhere?}', 'ManagerExpenseController@create')->name('createmanagerexpenses');
		Route::post('cargo/storeemanagerexpenses', 'ManagerExpenseController@store');
		Route::any('cargo/editmanagerexpenses/{id}/{flagFromWhere?}', 'ManagerExpenseController@edit')->name('editmanagerexpenses');
		Route::post('cargo/updatemanagerexpenses/{id}', 'ManagerExpenseController@update')->name('updatemanagerexpenses');

		// Agent Role
		Route::get('cargo/agentexpenses', 'AgentExpenseController@index')->name('agentexpenses');
		Route::get('cargo/createagentexpenses/{flag}/{cargoId?}/{flagFromWhere?}/{gauranteeId?}', 'AgentExpenseController@create')->name('createagentexpenses');
		Route::post('cargo/storeeagentexpenses', 'AgentExpenseController@store');
		Route::any('cargo/editeagentexpenses/{id}/{flagFromWhere?}', 'AgentExpenseController@edit')->name('editagentexpenses');
		Route::post('cargo/updateagentexpenses/{id}', 'AgentExpenseController@update')->name('updateagentexpenses');
		Route::post('cargo/getadminmanagerusers', 'AgentExpenseController@getadminmanagerusers');


		Route::get('cargo/agentupsexpenses', 'AgentUpsExpenseController@index')->name('agentupsexpenses');
		Route::get('cargo/createagentupsexpenses/{upsId?}', 'AgentUpsExpenseController@create')->name('createagentupsexpenses');
		Route::post('cargo/storeeagentupsexpenses', 'AgentUpsExpenseController@store');
		Route::any('cargo/editagentupsexpenses/{id}/{flagFromWhere?}', 'AgentUpsExpenseController@edit')->name('editagentupsexpenses');
		Route::post('cargo/updateagentupsexpenses/{id}', 'AgentUpsExpenseController@update')->name('updateagentupsexpenses');

		Route::get('agentups/importupsagent', 'AgentUpsController@import')->name('importupsagent');
		Route::post('agentups/importupsdataagent', 'AgentUpsController@importdataagent');
		Route::any('agent/ups/listbydatatableserverside', 'AgentUpsController@listbydatatableserverside')->name('listbydatatableserverside');
		// -- End Agent Role

		// Cashier Role
		Route::get('cargo/cashierexpenses', 'CashierExpenseController@index')->name('cashierexpenses');
		Route::any('cargo/expandexpensescashier', 'CashierExpenseController@expandexpensescashier')->name('expandexpensescashier');
		Route::any('expense/getprintviewsingleexpensecashier/{expenseId}/{cargoId}/{flag?}', 'CashierExpenseController@getprintviewsingleexpensecashier')->name('getprintviewsingleexpensecashier');
		Route::any('expense/getprintsingleexpensecashier/{expenseId}/{cargoId}', 'CashierExpenseController@getprintsingleexpensecashier')->name('getprintsingleexpensecashier');
		Route::any('cargo/changestatusbycashier', 'CashierExpenseController@changestatusbycashier')->name('changestatusbycashier');

		Route::any('cashier/expense/listbydatatableserverside/{flag?}', 'CashierExpenseController@listbydatatableserverside')->name('cashierexpenselistbydatatableserverside');
		Route::any('cashier/expense/checkoperationfordatatableserverside', 'CashierExpenseController@checkoperationfordatatableserverside')->name('cashierexpensecheckoperationfordatatableserverside');

		Route::get('ups/cashierupsexpenses', 'CashierUpsExpenseController@index')->name('cashierupsexpenses');
		Route::any('ups/getprintviewsingleupsexpensecashier/{expenseId}/{upsId}/{flag?}', 'CashierUpsExpenseController@getprintviewsingleupsexpensecashier')->name('getprintviewsingleupsexpensecashier');
		Route::any('ups/getprintsingleupsexpensecashier/{expenseId}/{upsId}', 'CashierUpsExpenseController@getprintsingleupsexpensecashier')->name('getprintsingleupsexpensecashier');
		Route::any('ups/changeupsstatusbycashier', 'CashierUpsExpenseController@changeupsstatusbycashier')->name('changeupsstatusbycashier');

		Route::get('cashiercargoall', 'CashierCargoController@cashiercargoall')->name('cashiercargoall');
		Route::post('cashiercargoimportsajax', 'CashierCargoController@cashiercargoimportsajax')->name('cashiercargoimportsajax');
		Route::get('cashierLocalFileListing', 'CashierCargoController@cashierlocalfilelisting')->name('cashierLocalFileListing');
		Route::get('changeCargoLocal/{status}/{id}', 'CashierCargoController@changestatusoflocalfile')->name('changeCargoLocal');
		Route::get('viewcargolocalfiledetailforcashier/{id}', 'CashierCargoController@getAllDetail')->name('viewcargolocalfiledetailforcashier');
		Route::post('cashiercargoexportsajax', 'CashierCargoController@cashiercargoexportsajax')->name('cashiercargoexportsajax');
		Route::post('cashiercargoallajax', 'CashierCargoController@cashiercargoallajax')->name('cashiercargoallajax');

		Route::any('cargo/viewcargodetailforcashier/{id}/{flag?}', 'CashierCargoController@viewcargodetailforcashier')->name('viewcargodetailforcashier');

		Route::get('cashierwarehouseinvoices', 'CashierInvoicesController@index')->name('cashierwarehouseinvoicesoffile');
		Route::get('cashierinvoices/send', 'CashierInvoicesController@sendMail')->name('cashierwarehouseinvoicesmail');
		Route::get('cashierwarehouseinvoices/create/{cargoId?}/{HawbId?}', 'CashierInvoicesController@create')->name('createcashierwarehouseinvoicesoffile');
		Route::post('cashierwarehouseinvoices/store', 'CashierInvoicesController@store');
		Route::any('cashierwarehouseinvoices/edit/{id}/{flagFromWhere?}', 'CashierInvoicesController@edit')->name('editcashierwarehouseinvoicesoffile');
		Route::any('cashierwarehouseinvoices/update/{id}', 'CashierInvoicesController@update');
		Route::any('cashierwarehouseinvoices/gethawboffiles', 'CashierInvoicesController@gethawboffiles');
		Route::post('cashierwarehouseinvoices/storecargoinvoiceandprint', 'CashierInvoicesController@storecargoinvoiceandprint');


		Route::get('cashierinvoicepayment/create/{cargoId?}/{invoiceId?}/{billingParty?}/{fromMenu?}', 'CashierInvoicePaymentsController@create')->name('addcashierinvoicepayment');
		Route::post('cashierinvoicepayment/store', 'CashierInvoicePaymentsController@store');
		Route::any('cargo/releasereceiptbycashier/{cargoId}/{cargoType}', 'CashierCargoController@releasereceiptbycashier')->name('releasereceiptbycashier');

		// -- End Cashier Role

		// Custom Expense //
		Route::get('customexpneses', 'CustomExpensesController@index')->name('customexpneses');
		Route::get('customexpnese/createcustomexpense/{upsId?}', 'CustomExpensesController@create')->name('createcustomexpense');
		Route::post('customexpnese/storecustomexpense', 'CustomExpensesController@store')->name('storecustomexpense');
		Route::any('customexpnese/editcustomexpnese/{id}', 'CustomExpensesController@edit')->name('editcustomexpnese');
		Route::post('customexpnese/updatecustomexpnese/{id}', 'CustomExpensesController@update')->name('updatecustomexpnese');
		Route::any('customexpnese/deletecustomexpnese/{id}', 'CustomExpensesController@destroy')->name('deletecustomexpnese');
		Route::any('customexpnese/deletecustomexpnesevoucher/{id}', 'CustomExpensesController@deletecustomexpnesevoucher')->name('deletecustomexpnesevoucher');
		Route::any('customexpnese/getcustomdata', 'CustomExpensesController@getcustomdata')->name('getcustomdata');
		Route::any('customexpnese/addmorecustomexpense', 'CustomExpensesController@addmorecustomexpense')->name('addmorecustomexpense');
		Route::any('customexpnese/generatecustomexpensevoucheronsavenext', 'CustomExpensesController@generatecustomexpensevoucheronsavenext')->name('generatecustomexpensevoucheronsavenext');
		Route::any('customexpnese/expandcustomexpenses', 'CustomExpensesController@expandcustomexpenses')->name('expandcustomexpenses');
		// -- End Custom Expense //




		//Route for sending mail when invoice complete its one month
		Route::get('sendinvoiceoflocalstorage', 'CargoController@sendinvoiceoflocalstorage');
		Route::get('local/getdate', 'CargoController@getDate');
		Route::get('local/changeStatus', 'CashierCargoController@changestatusoflocalsubfile');

		/* Aeropost */
		Route::get('aeroposts', 'AeropostController@index')->name('aeroposts');
		Route::get('aeropost/create', 'AeropostController@create')->name('createaeropost');
		Route::post('aeropost/store', 'AeropostController@store');
		Route::get('importaeropost', 'AeropostController@import')->name('importaeropost');
		Route::any('aeropost/delete/{id}', 'AeropostController@destroy')->name('deleteaeropost');
		Route::any('aeropost/edit/{id}', 'AeropostController@edit')->name('editaeropost');
		Route::any('aeropost/update/{id}', 'AeropostController@update');
		Route::post('aeropost/importdata', 'AeropostController@importdata');
		Route::get('aeropost/viewdetailsaeropost/{id}', 'AeropostController@viewdetails')->name('viewdetailsaeropost');
		Route::any('aeropost/checkuniqueawbnumberofaeropost', 'AeropostController@checkuniqueawbnumberofaeropost')->name('checkuniqueawbnumberofaeropost');
		Route::any('aeropost/listbydatatableserverside', 'AeropostController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('aeropost/checkoperationfordatatableserversideaeropost', 'AeropostController@checkoperationfordatatableserversideaeropost')->name('checkoperationfordatatableserversideaeropost');
		Route::any('aeropost/getaeropostdata', 'AeropostController@getaeropostdata')->name('getaeropostdata');
		Route::any('aeropost/viewaeropostdetailforagent/{id}', 'AeropostController@viewaeropostdetailforagent')->name('viewaeropostdetailforagent');
		Route::any('aeropost/assignbillingparty', 'AeropostController@assignbillingparty')->name('assignbillingpartyforaeropostfile');
		/* -- End Aeropost */

		/* Aeropost Invoices */
		Route::get('/aeropostinvoices', 'AeropostInvoicesController@index')->name('aeropostinvoices');
		Route::get('aeropostinvoices/create/{aeropostId?}/{flagInvoice?}/{flagFromWhere?}', 'AeropostInvoicesController@create')->name('createaeropostinvoice');
		Route::post('aeropostinvoices/store', 'AeropostInvoicesController@store');
		Route::any('aeropostinvoices/edit/{id}/{flagFromWhere?}', 'AeropostInvoicesController@edit')->name('editaeropostinvoice');
		Route::any('aeropostinvoices/update/{id}', 'AeropostInvoicesController@update');
		Route::any('aeropostinvoices/getaeropostdetailforinvoice', 'AeropostInvoicesController@getaeropostdetailforinvoice')->name('getaeropostdetailforinvoice');
		Route::any('aeropostinvoices/storeaeropostinvoiceandprint', 'AeropostInvoicesController@storeaeropostinvoiceandprint');
		Route::any('aeropostinvoices/viewandprintaeropostinvoice/{id}', 'AeropostInvoicesController@viewandprintaeropostinvoice')->name('viewandprintaeropostinvoice');
		Route::any('aeropostinvoices/copy/{id}/{flag?}', 'AeropostInvoicesController@copy')->name('copyaeropostinvoice');
		Route::any('aeropostinvoices/deleteaeropostinvoicefromedit/{id}', 'AeropostInvoicesController@deleteaeropostinvoicefromedit')->name('deleteaeropostinvoicefromedit');
		Route::any('aeropostinvoices/deleteaeropostinvoice/{id}', 'AeropostInvoicesController@destroy')->name('deleteaeropostinvoice');
		Route::any('aeropostinvoices/viewaeropostinvoicedetails/{id}', 'AeropostInvoicesController@show')->name('viewaeropostinvoicedetails');
		Route::any('aeropostinvoices/listbydatatableserverside', 'AeropostInvoicesController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('aeropostinvoices/checkoperationfordatatableserverside', 'AeropostInvoicesController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserverside');
		Route::any('aeropostinvoices/getaeropostinvoiceforinputpicker', 'AeropostInvoicesController@getaeropostinvoiceforinputpicker')->name('getaeropostinvoiceforinputpicker');
		/* -- End Aeropost Invoices */

		/*Aeropost Invoice Payments*/
		Route::get('aeropostinvoicepayment/create/{upsId?}/{invoiceId?}/{billingParty?}/{fromMenu?}', 'AeropostInvoicePaymentController@create')->name('addaeropostinvoicepayment');
		Route::post('aeropostinvoicepayment/store', 'AeropostInvoicePaymentController@store');
		Route::any('aeropostinvoicepayment/update/{id}', 'AeropostInvoicePaymentController@update');
		Route::any('aeropostinvoicepayment/getinvoicesusingfilenumber', 'AeropostInvoicePaymentController@getinvoicesusingfilenumber')->name('getinvoicesusingfilenumber');
		Route::any('aeropostinvoicepayment/getaeropostinvoicesofclient', 'AeropostInvoicePaymentController@getaeropostinvoicesofclient')->name('getaeropostinvoicesofclient');
		Route::any('aeropostinvoicepayment/getselectedaeropostinvoicedata', 'AeropostInvoicePaymentController@getselectedaeropostinvoicedata')->name('getselectedupsinvoicedata');
		Route::any('aeropostinvoicepayment/getcurrencyratesection', 'AeropostInvoicePaymentController@getcurrencyratesection');

		/*Aeropost Expenses*/
		Route::get('aeropost/expenses', 'AeropostExpenseController@index')->name('aerpostexpenses');
		Route::any('aeropost/expense/listbydatatableserverside', 'AeropostExpenseController@listbydatatableserverside')->name('aeropostlistbydatatableserverside');
		Route::any('aeropost/expense/create/{aeropostId?}/{flagFromWhere?}', 'AeropostExpenseController@create')->name('aeropostexpensecreate');
		Route::any('aeropost/expense/store', 'AeropostExpenseController@store')->name('aeropostexpensestore');
		Route::any('aeropost/expense/edit/{id}/{flagFromWhere?}', 'AeropostExpenseController@edit')->name('aeropostexpenseedit');
		Route::any('aeropost/expense/update/{id}', 'AeropostExpenseController@update')->name('aeropostexpenseupdate');;
		Route::any('aeropost/expense/printall/{flag?}', 'AeropostExpenseController@printall')->name('printallaeropostexpense');
		Route::any('aeropost/expense/printone/{expenseId}/{aeropostId}/{flag?}', 'AeropostExpenseController@printone')->name('printoneaeropostexpense');
		Route::any('aeropost/getprintviewsingleaeropostexpensecashier/{expenseId}/{aeropostId}/{flag?}', 'AeropostExpenseController@getprintviewsingleaeropostexpensecashier')->name('getprintviewsingleaeropostexpensecashier');
		Route::any('aeropost/changeaeropostexpensestatusbycashier', 'AeropostExpenseController@changeaeropostexpensestatusbycashier')->name('changeaeropostexpensestatusbycashier');

		/*Ccpack Expenses*/
		Route::get('ccpack/expenses', 'CcpackExpenseController@index')->name('ccpackexpenses');
		Route::any('ccpack/expense/listbydatatableserverside', 'CcpackExpenseController@listbydatatableserverside')->name('ccpacklistbydatatableserverside');
		Route::any('ccpack/expense/create/{ccpackId?}/{flagFromWhere?}', 'CcpackExpenseController@create')->name('ccpackexpensecreate');
		Route::any('ccpack/expense/store', 'CcpackExpenseController@store')->name('ccpackexpensestore');
		Route::any('ccpack/expense/edit/{id}/{flagFromWhere?}', 'CcpackExpenseController@edit')->name('ccpackexpenseedit');
		Route::any('ccpack/expense/update/{id}', 'CcpackExpenseController@update')->name('ccpackexpenseupdate');;
		Route::any('ccpack/expense/printall/{flag?}', 'CcpackExpenseController@printall')->name('printallccpackexpense');
		Route::any('ccpack/expense/printone/{expenseId}/{ccpackId}/{flag?}', 'CcpackExpenseController@printone')->name('printoneccpackexpense');
		Route::any('ccpack/getprintviewsingleccpackexpensecashier/{expenseId}/{ccpackId}/{flag?}', 'CcpackExpenseController@getprintviewsingleccpackexpensecashier')->name('getprintviewsingleccpackexpensecashier');
		Route::any('ccpack/changeccpackexpensestatusbycashier', 'CcpackExpenseController@changeccpackexpensestatusbycashier')->name('changeccpackexpensestatusbycashier');



		// Fd-Charge Management
		Route::get('/fdcharges', 'FdChargesController@index')->name('fdcharges');
		Route::get('fdcharges/create', 'FdChargesController@create')->name('createfdcharges');
		Route::post('fdcharges/store', 'FdChargesController@store');
		Route::any('fdcharges/edit/{id}', 'FdChargesController@edit')->name('editfdcharges');
		Route::any('fdcharges/update/{id}', 'FdChargesController@update');
		Route::any('fdcharges/delete/{id}', 'FdChargesController@destroy')->name('deletefdcharges');
		//-- END-- Fd-Charge Management

		/* CCpack */
		Route::get('ccpack', 'ccpackController@index')->name('ccpack');
		Route::get('ccpack/create', 'ccpackController@create')->name('createccpack');
		Route::post('ccpack/store', 'ccpackController@store')->name('storeccpack');
		Route::any('ccpack/delete/{id}', 'ccpackController@destroy')->name('deleteccpack');
		Route::any('ccpack/edit/{id}', 'ccpackController@edit')->name('editccpack');
		Route::any('ccpack/update/{id}', 'ccpackController@update');
		Route::post('ccpack/checkuniqueawbnumber', 'ccpackController@checkuniqueawbnumber');
		Route::post('ccpackfilter', 'ccpackController@filterbyfiletype')->name('ccpackfilter');
		Route::get('ccpack/viewdetailsccpack/{id}', 'ccpackController@viewdetails')->name('viewdetailsccpack');
		Route::any('ccpack/listbydatatableserverside', 'ccpackController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('ccpack/checkoperationfordatatableserversideccpack', 'ccpackController@checkoperationfordatatableserversideccpack')->name('checkoperationfordatatableserversideccpack');
		Route::any('ccpack/getccpackdata', 'ccpackController@getccpackdata')->name('getccpackdata');

		Route::any('ccpack/viewccpackdetailforagent/{id}', 'ccpackController@viewccpackdetailforagent')->name('viewccpackdetailforagent');
		Route::any('ccpack/assignbillingparty', 'ccpackController@assignbillingparty')->name('assignbillingpartyforccpackfile');


		/* CCpack Invoice*/
		Route::get('/ccpackinvoices', 'ccpackinvoiceController@index')->name('ccpackinvoices');
		Route::get('ccpackinvoice/create/{id?}/{flagInvoice?}/{flagFromWhere?}', 'ccpackinvoiceController@create')->name('createccpackinvoices');
		Route::post('ccpackinvoice/store', 'ccpackinvoiceController@store');
		Route::any('ccpackinvoice/edit/{id}/{flagFromWhere?}', 'ccpackinvoiceController@edit')->name('editccpackinvoice');
		Route::any('ccpackinvoice/update/{id}', 'ccpackinvoiceController@update');
		Route::any('ccpackinvoice/delete/{id}', 'ccpackinvoiceController@destroy')->name('deleteinvoice');

		Route::post('ccpackinvoice/getccpackdetailforinvoice', 'ccpackinvoiceController@getccpackdetailforinvoice');
		Route::any('ccpackinvoice/viewandprintccpackinvoice/{id}', 'ccpackinvoiceController@viewandprintccpackinvoice')->name('viewandprintccpackinvoice');
		Route::any('ccpackinvoice/storeccpackinvoiceandprint', 'ccpackinvoiceController@storeccpackinvoiceandprint');
		Route::any('ccpackinvoice/viewccpackinvoicedetails/{id}', 'ccpackinvoiceController@show')->name('viewccpackinvoicedetails');

		Route::any('ccpackinvoices/copy/{id}/{flag?}', 'ccpackinvoiceController@copy')->name('copyccpackinvoice');

		Route::get('printccpackinvoice/{id}', 'ccpackinvoiceController@printccpackinvoice')->name('printccpackinvoice');
		Route::any('ccpackinvoices/deleteccpackinvoicefromedit/{id}', 'ccpackinvoiceController@deleteccpackinvoicefromedit')->name('deleteccpackinvoicefromedit');

		Route::any('ccpackinvoice/listbydatatableserverside', 'ccpackinvoiceController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('ccpackinvoice/checkoperationfordatatableserverside', 'ccpackinvoiceController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserverside');
		Route::any('ccpackinvoice/getccpackinvoiceforinputpicker', 'ccpackinvoiceController@getccpackinvoiceforinputpicker')->name('getccpackinvoiceforinputpicker');

		Route::get('ccpackinvoicepayment/create/{ccpackId?}/{invoiceId?}/{billingParty?}/{fromMenu?}', 'ccpackInvoicePaymentController@create')->name('addccpackinvoicepayment');
		Route::post('ccpackinvoicepayment/store', 'ccpackInvoicePaymentController@store');

		Route::any('ccpackinvoicepayment/getselectedccpackinvoicedata', 'ccpackInvoicePaymentController@getselectedccpackinvoicedata');
		Route::any('ccpackinvoicepayment/getccpackinvoicesofclient', 'ccpackInvoicePaymentController@getccpackinvoicesofclient');
		Route::any('ccpackinvoicepayment/getcurrencyratesection', 'ccpackInvoicePaymentController@getcurrencyratesection');

		Route::get('courierinvoice/createccpackexpense/{upsId?}', 'ccpackExpencesController@create')->name('createccpackexpense');

		Route::get('qb/checkqbresponse', 'HomeController@checkqbresponse')->name('checkqbresponse');
		Route::get('qb/callback', 'HomeController@callback')->name('callback');
		Route::get('qb/apiCall', 'HomeController@apiCall')->name('apiCall');

		Route::get('qb/loginwithconnection', 'HomeController@loginwithconnection')->name('loginwithconnection');
		Route::any('qb/checkconnectedornot', 'HomeController@checkconnectedornot')->name('checkconnectedornot');

		Route::get('import/clients', 'HomeController@importClients');




		Route::any('files/upload/{flag?}/{id?}', 'fileManagerController@create');
		Route::any('file/addmorefile', 'fileManagerController@addmoreFiles');
		Route::post('file/uploadUps', 'fileManagerController@uploadUps');
		Route::post('file/uploadAeropost', 'fileManagerController@uploadAeropost');
		Route::post('file/uploadAeropostMaster', 'fileManagerController@uploadAeropostMaster');
		Route::post('file/uploadCCpack', 'fileManagerController@uploadCCpack');
		Route::post('file/uploadCargo', 'fileManagerController@uploadCargo');
		Route::post('file/uploadHouseFile', 'fileManagerController@uploadHouseFile');
		Route::any('file/delete/{flag?}/{id?}/{filename?}', 'fileManagerController@destroy')->name('deletefiles');
		Route::get('file/download/{flag?}/{id?}/{filename?}', 'fileManagerController@download')->name('downloadfiles');
		Route::get('awsdemo', 'fileManagerController@awsdemo');


		Route::get('file/filemanager', 'fileManagerController@show')->name('filemanager');
		Route::any('file/mkDir/{path}', 'fileManagerController@makeDirectory');



		Route::any('call/qb/{data?}', 'AdminController@callQb')->name('callQb');
		Route::get('qberrorlog', 'QuickBookController@index')->name('qberrorlog');
		Route::any('closefiles', 'AdminController@closefiles')->name('closefiles');
		Route::any('closefiles-listing', 'AdminController@closefileslisting')->name('closefileslisting');
		Route::any('closefilessubmit', 'AdminController@closefilessubmit')->name('closefilessubmit');
		Route::any('closefilessubmitsingle/{module?}/{id?}', 'AdminController@closefilessubmitsingle')->name('closefilessubmitsingle');
		Route::any('reactivatefile', 'AdminController@reactivatefile')->name('reactivatefile');
		Route::any('getfilesforclose', 'AdminController@getfilesforclose')->name('getfilesforclose');
		Route::any('closefiles/listbydatatableserverside', 'AdminController@listbydatatableserverside')->name('closefileslistbydatatableserverside');
		Route::any('importqb', 'AdminController@importqb')->name('importqb');
		Route::any('importqbdata/{flag?}', 'AdminController@importqbdata')->name('importqbdata');


		Route::get('housefileinvoices/{flagModule?}', 'HouseFileInvoiceController@index')->name('housefileinvoices');
		Route::get('houseinvoices/create/{flagModule?}/{flagInvoice?}/{houseId?}/{flagFromWhere?}', 'HouseFileInvoiceController@create')->name('createhousefileinvoice');
		Route::any('houseinvoices/store', 'HouseFileInvoiceController@store')->name('storehousefileinvoice');
		Route::get('houseinvoices/edit/{id}/{flagModule?}/{flagFromWhere?}', 'HouseFileInvoiceController@edit')->name('edithousefileinvoice');
		Route::any('houseinvoices/update/{id}', 'HouseFileInvoiceController@update')->name('updatehousefileinvoice');
		Route::any('houseinvoices/gethousedetailforinvoice', 'HouseFileInvoiceController@gethousedetailforinvoice')->name('gethousedetailforinvoice');
		Route::any('houseinvoices/delete/{id}', 'HouseFileInvoiceController@destroy')->name('deletehousefileinvoice');
		Route::post('houseinvoices/storehouseinvoiceandprint', 'HouseFileInvoiceController@storehouseinvoiceandprint');
		Route::any('houseinvoices/copy/{id}/{flag?}/{flagModule?}', 'HouseFileInvoiceController@copy')->name('copyhouseinvoice');
		Route::any('houseinvoices/deletehousefileinvoicefromedit/{id}', 'HouseFileInvoiceController@deletehousefileinvoicefromedit')->name('deletehousefileinvoicefromedit');
		Route::any('houseinvoices/viewandprinthousefileinvoice/{id}', 'HouseFileInvoiceController@viewandprinthousefileinvoice')->name('viewandprinthousefileinvoice');
		Route::any('houseinvoices/viewhousefileinvoicedetails/{id}/{flagModule?}', 'HouseFileInvoiceController@show')->name('viewhousefileinvoicedetails');
		Route::any('houseinvoices/listbydatatableserverside', 'HouseFileInvoiceController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('houseinvoices/checkoperationfordatatableserverside', 'HouseFileInvoiceController@checkoperationfordatatableserverside')->name('checkoperationfordatatableserverside');
		Route::any('houseinvoices/checkhousefiledate', 'HouseFileInvoiceController@checkhousefiledate')->name('checkhousefiledate');
		Route::any('houseinvoices/getcargoinhousefileinvoiceforinputpicker', 'HouseFileInvoiceController@getcargoinhousefileinvoiceforinputpicker')->name('getcargoinhousefileinvoiceforinputpicker');
		Route::any('houseinvoices/getcargohousefileinvoiceforinputpicker', 'HouseFileInvoiceController@getcargohousefileinvoiceforinputpicker')->name('getcargohousefileinvoiceforinputpicker');



		Route::get('housefileexpense/create/{houseId?}/{flagFromWhere?}', 'HouseFileExpenseController@create')->name('createhousefileexpense');
		Route::post('housefileexpense/store', 'HouseFileExpenseController@store')->name('storehousefileexpense');
		Route::get('housefileexpense/index', 'HouseFileExpenseController@index')->name('housefileexpenses');
		Route::any('housefileexpense/edit/{id}/{flagFromWhere?}', 'HouseFileExpenseController@edit')->name('edithousefileexpense');
		Route::any('housefileexpense/editrequestedbyagent/{id}/{flagFromWhere?}', 'HouseFileExpenseController@editrequestedbyagent')->name('edithousefileexpenserequestedbyagent');
		Route::post('housefileexpense/update/{id}', 'HouseFileExpenseController@update')->name('updatehousefileexpense');
		Route::post('housefileexpense/updaterequestedbyagent/{id}', 'HouseFileExpenseController@updaterequestedbyagent')->name('updatehousefileexpenserequestedbyagent');
		Route::any('housefileexpense/delete/{id}', 'HouseFileExpenseController@destroy')->name('deletehousefileexpense');
		Route::any('housefileexpense/print/{expenseId}/{houseId}/{flag?}', 'HouseFileExpenseController@print')->name('getprintsinglehousefileexpense');
		Route::any('housefileexpense/printall/{flag?}', 'HouseFileExpenseController@printall')->name('printallhousefileexpense');
		Route::any('housefileexpense/viewdetails/{id}', 'HouseFileExpenseController@viewdetails')->name('viewdetailshousefileexpense');
		Route::any('housefileexpense/listbydatatableserverside', 'HouseFileExpenseController@listbydatatableserverside')->name('listbydatatableserverside');
		Route::any('housefileexpense/checkoperationfordatatableserverside', 'HouseFileExpenseController@checkoperationfordatatableserverside')->name('checkcargohousefileexpenseoperationfordatatableserverside');
		Route::any('hawbfile/gethousedata', 'HawbFilesController@gethousedata')->name('gethousedata');

		Route::get('agenthousefileexpense/index', 'AgentHouseFileExpenseController@index')->name('agenthousefileexpenses');
		Route::get('agenthousefileexpense/create/{houseId?}/{flagFromWhere?}', 'AgentHouseFileExpenseController@create')->name('createagenthousefileexpenses');
		Route::post('agenthousefileexpense/store', 'AgentHouseFileExpenseController@store');
		Route::any('agenthousefileexpense/edit/{id}', 'AgentHouseFileExpenseController@edit')->name('editagenthousefileexpenses');
		Route::post('agenthousefileexpense/update/{id}', 'AgentHouseFileExpenseController@update')->name('updateagenthousefileexpenses');

		Route::get('cashierhousefile/cashierhousefileexpenses', 'CashierHouseFileExpenseController@index')->name('cashierhousefileexpenses');
		Route::any('cashierhousefile/getprintviewsinglehousefileexpensecashier/{expenseId}/{houseId}/{flag?}', 'CashierHouseFileExpenseController@getprintviewsinglehousefileexpensecashier')->name('getprintviewsinglehousefileexpensecashier');
		Route::any('cashierhousefile/getprintsinglehousefileexpensecashier/{expenseId}/{houseId}', 'CashierHouseFileExpenseController@getprintsinglehousefileexpensecashier')->name('getprintsinglehousefileexpensecashier');
		Route::any('cashierhousefile/changehousefilestatusbycashier', 'CashierHouseFileExpenseController@changehousefilestatusbycashier')->name('changehousefilestatusbycashier');

		// Delivery boy Management
		Route::get('deliveryboys', 'DeliveryBoyController@index')->name('deliveryboys');
		Route::get('deliveryboy/create', 'DeliveryBoyController@create')->name('createdeliveryboy');
		Route::post('deliveryboy/store', 'DeliveryBoyController@store');
		Route::any('deliveryboy/edit/{id}', 'DeliveryBoyController@edit')->name('editdeliveryboy');
		Route::any('deliveryboy/update/{id}', 'DeliveryBoyController@update');
		Route::any('deliveryboy/delete/{id}', 'DeliveryBoyController@destroy')->name('deletedeliveryboy');

		Route::any('deliveryboy/view-details/{id}', 'DeliveryBoyController@show')->name('viewdetailsdeliveryboy');
		Route::any('deliveryboy/filter-files', 'DeliveryBoyController@filterfiles')->name('filterfiles');

		Route::any('deliveryboy/cash-collection-details-deliveryboy/{id}', 'DeliveryBoyController@cashcollectiondetailsdeliveryboy')->name('cashcollectiondetailsdeliveryboy');
		Route::any('deliveryboy/filter-files-cash-collection', 'DeliveryBoyController@filterfilescashcollection')->name('filterfilescashcollection');

		Route::any('deliveryboy/manifest-details-deliveryboy/{id}', 'DeliveryBoyController@manifestdetailsdeliveryboy')->name('manifestdetailsdeliveryboy');
		Route::any('deliveryboy/filter-files-manifest-details', 'DeliveryBoyController@filterfilesmanifestdetails')->name('filterfilesmanifestdetails');
		Route::any('delivery-boy-shipment-not-delivered/{module?}/{deliveryBoyId?}', 'DeliveryBoyController@deliveryboyshipmentnotdelivered')->name('deliveryboyshipmentnotdelivered');
		Route::any('delivery-boy-shipment-delivered-or-not', 'DeliveryBoyController@deliveryboyshipmentdeliveredornot')->name('deliveryboyshipmentdeliveredornot');
		Route::any('deliveryboy/ups/listbydatatableserverside', 'DeliveryBoyController@upslistbydatatableserverside')->name('upslistbydatatableserverside');
		//-- END-- Delivery boy Management

		// Manifests Management
		Route::get('manifests/listing', 'ManifestsController@index')->name('manifests');
		Route::get('manifests/listbydatatableserverside', 'ManifestsController@listbydatatableserverside')->name('listbydatatableserversidemanifests');
		Route::get('manifests/import', 'ManifestsController@import')->name('importmanifests');
		Route::post('manifests/importdata', 'ManifestsController@importdata');
		Route::any('manifests/printandexport/{fromDate?}/{toDate?}/{port?}/{career?}/{consignee?}/{shipper?}/{comodity?}/{submitButtonName?}', 'ManifestsController@printandexport')->name('printandexportmanifest');
		Route::any('manifests/progressstatus', 'ManifestsController@progressstatus')->name('progressstatus');
		Route::any('manifests/checkuniquefile', 'ManifestsController@checkuniquefile')->name('checkuniquefile');
		Route::any('manifests/getmanifestports', 'ManifestsController@getmanifestports')->name('getmanifestports');
		Route::any('manifests/getmanifestcarrier', 'ManifestsController@getmanifestcarrier')->name('getmanifestcarrier');
		Route::any('manifests/getmanifestconsignee', 'ManifestsController@getmanifestconsignee')->name('getmanifestconsignee');
		Route::any('manifests/getmanifestshipper', 'ManifestsController@getmanifestshipper')->name('getmanifestshipper');
		Route::any('manifests/getmanifestcomodity', 'ManifestsController@getmanifestcomodity')->name('getmanifestcomodity');
		//-- END-- Manifests Management

		Route::any('changeinfilenumber', 'HomeController@changeinfilenumber')->name('changeinfilenumber');
		Route::any('invoicesequences', 'HomeController@invoicesequences')->name('invoicesequences');
		Route::any('reportsinvoicesfiles', 'ReportsController@reportsinvoicesfiles')->name('reportsinvoicesfiles');
		Route::any('reports/listbydatatableserversideinreports', 'ReportsController@listbydatatableserversideinreports')->name('listbydatatableserversideinreports');
		Route::any('checkbackground', 'HomeController@checkbackground')->name('checkbackground');
		Route::any('checkbackgroundactioncalled', 'HomeController@checkbackgroundactioncalled')->name('checkbackgroundactioncalled');
		Route::any('checkreceipt', 'HomeController@checkreceipt')->name('checkreceipt');
		Route::get('remove-duplicate-files', 'UpsController@removeDuplicateFiles');
		Route::get('script-to-create-ups-master-files', 'HomeController@scriptToCreateUpsMasterFile');
		Route::get('backup-aeroposts', 'HomeController@scriptToRestoreAeropostFile');


		Route::get('check-guarantee/create/{moduleFlag?}/{moduleId?}/{id?}', 'CheckGuaranteeToPayController@create')->name('createCheckGuarantee');
		Route::post('check-guarantee/store', 'CheckGuaranteeToPayController@store');

		Route::get('check-guarantee', 'CheckGuaranteeToPayController@index')->name('check-guarantee');
		Route::any('check-guarantee/listbydatatableserverside', 'CheckGuaranteeToPayController@listbydatatableserverside')->name('checkguaranteelistbydatatableserverside');
		Route::get('check-guarantee/add', 'CheckGuaranteeToPayController@add')->name('addCheckGuarantee');
		Route::post('check-guarantee/storecheck', 'CheckGuaranteeToPayController@storecheck')->name('storeCheckGuarantee');
		Route::get('check-guarantee/edit/{id?}', 'CheckGuaranteeToPayController@edit')->name('editCheckGuarantee');
		Route::post('check-guarantee/update', 'CheckGuaranteeToPayController@update')->name('updateCheckGuarantee');
		Route::post('check-guarantee/getBillingAmount', 'CheckGuaranteeToPayController@getBillingAmount')->name('getBillingAmount');
		Route::any('check-guarantee/delete/{id?}', 'CheckGuaranteeToPayController@destroy')->name('destroyCheckGuarantee');
		Route::any('check-guarantee/approve', 'CheckGuaranteeToPayController@approve')->name('checkguaranteeapprove');


		Route::any('reports/accountpayablereport', 'CostsController@accountpayablereport')->name('accountpayablereport');
		Route::any('reports/listaccountpayablereport', 'CostsController@listaccountpayablereport')->name('listaccountpayablereport');
		Route::any('reports/exportaccountpayablereport/{fromDate?}/{toDate?}/{modules?}/{vendors?}/{duration?}', 'CostsController@exportaccountpayablereport')->name('exportaccountpayablereport');
		Route::any('reports/getaccountpayablereportdata', 'CostsController@getaccountpayablereportdata')->name('getaccountpayablereportdata');
		Route::any('reports/approveexpenseinaccountpayablereport', 'CostsController@approveexpenseinaccountpayablereport')->name('approveexpenseinaccountpayablereport');
		Route::any('reports/apdisbursement', 'CostsController@apdisbursement')->name('ap-disbursement');
		Route::any('reports/apdisbursement-submit', 'CostsController@apdisbursementsubmit')->name('apdisbursementsubmit');
	}
);
Route::any('manifests/background/{datas?}', 'ManifestsController@background');
Route::any('manifests/reloadfilestatus', 'ManifestsController@reloadfilestatus');
Route::any('cronforgeneratelocalinvoicemonthly', 'CargoController@cronforgeneratelocalinvoicemonthly')->name('cronforgeneratelocalinvoicemonthly');
Route::get('qb/callbacksync/{accessToken?}', 'AdminController@callbacksync')->name('callbacksync');
Route::get('qb/callbacksyncdemo/{accessToken?}', 'AdminController@callbacksyncdemo')->name('callbacksyncdemo');
Route::get('syncBillingpartyFromQBToLocal', 'CurrencyController@syncBillingpartyFromQBToLocal')->name('syncBillingpartyFromQBToLocal');
Route::get('syncBillingpartyFromLocalToQB', 'CurrencyController@syncBillingpartyFromLocalToQB')->name('syncBillingpartyFromLocalToQB');
Route::get('syncVendorsFromQBToLocal', 'CurrencyController@syncVendorsFromQBToLocal')->name('syncVendorsFromQBToLocal');
Route::get('syncVendorsFromLocalToQB', 'CurrencyController@syncVendorsFromLocalToQB')->name('syncVendorsFromLocalToQB');
Route::get('syncCostItemsFromQBToLocal', 'CurrencyController@syncCostItemsFromQBToLocal')->name('syncCostItemsFromQBToLocal');
Route::get('syncCostItemsFromLocalToQB', 'CurrencyController@syncCostItemsFromLocalToQB')->name('syncCostItemsFromLocalToQB');
Route::get('syncBillingItemsFromQBToLocal', 'CurrencyController@syncBillingItemsFromQBToLocal')->name('syncBillingItemsFromQBToLocal');
Route::get('syncBillingItemsFromLocalToQB', 'CurrencyController@syncBillingItemsFromLocalToQB')->name('syncBillingItemsFromLocalToQB');
Route::get('syncAccountsFromQBToLocal', 'CurrencyController@syncAccountsFromQBToLocal')->name('syncAccountsFromQBToLocal');
Route::get('syncAccountsFromLocalToQB', 'CurrencyController@syncAccountsFromLocalToQB')->name('syncAccountsFromLocalToQB');
//Reoptimized class loader: 
Route::get('/optimize', function () {
	$exitCode = Artisan::call('optimize');
	return '<h1>Reoptimized class loader</h1>';
});

//Route cache:
Route::get('/route-cache', function () {
	$exitCode = Artisan::call('route:cache');
	return '<h1>Routes cached</h1>';
});

//Clear Route cache:
Route::get('/route-clear', function () {
	$exitCode = Artisan::call('route:clear');
	return '<h1>Route cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function () {
	$exitCode = Artisan::call('view:clear');
	return '<h1>View cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function () {
	$exitCode = Artisan::call('config:cache');
	return '<h1>Clear Config cleared</h1>';
});

Route::get('/clear-cache', function () {
	$exitCode = Artisan::call('cache:clear');
	return '<h1>Cache facade value cleared</h1>';
});

Route::get('/config-clear', function () {
	$exitCode = Artisan::call('config:clear');
	return '<h1>Config Cache cleared</h1>';
});
