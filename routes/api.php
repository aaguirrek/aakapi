<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Ixudra\Curl\Facades\Curl;
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

Route::post('/frappe', function (Request $request) {
    $login=[
        "usr"=> $request->user,
        "pwd" => $request->pwd
    ]; 
    if( $request->exists('usr') ){
        $login=[
            "usr"=> $request->usr,
            "pwd" => $request->pwd
        ];  
    }
    $domain="hisalud.com";
    if( $request->exists('domain') ){
        $domain = $request->domain;
    }
    
    $curl = Curl::to( "https://".$domain."/api/method/login")
        ->withHeader("Content-Type: application/json" )
        ->withData( json_encode($login) )
        ->returnResponseObject()
        ->withResponseHeaders()
        ->post();
    
        $cookie="";        
    if( isset($curl->headers["Set-Cookie"])  ){
        $cookie = $curl->headers["Set-Cookie"][0];
    }else{
        $cookie = ["status"=>"no logeed"];
    }
    
    $auth = json_decode($curl->content);

    return ["body" => $auth, "cookie"=>$cookie];
});
Route::post('/demologin', function (Request $request) {
    $login=[
        "usr"=> $request->usr,
        "pwd" => $request->pwd
    ]; 
    $curl = Curl::to( "https://peruintercorp.com/api/method/login")
        ->withHeader("Content-Type: application/json" )
        ->withData( json_encode($login) )
        ->returnResponseObject()
        ->withResponseHeaders()
        ->post();
    
        $cookie="";        
    if( isset($curl->headers["Set-Cookie"])  ){
        $cookie = $curl->headers["Set-Cookie"][0];
    }else{
        $cookie = ["status"=>"no logeed"];
    }
    
    $auth = json_decode($curl->content);

    return ["body" => $auth, "cookie"=>$cookie];
});

Route::post('/get', function (Request $request) {    
    $curl = Curl::to("https://hisalud.com/api/resource/".$request->url )
        ->withOption("COOKIE", $request->cookie )
        ->asJson()
        ->get();
    
    return response()->json($curl, 200); 
});

Route::get('/bing', function () {    
    $curl = Curl::to("https://bing.biturl.top/?resolution=1920&format=json&index=0&mkt=en-US" )
        ->asJson()
        ->get();
    
    return response()->json($curl, 200); 
});

Route::post('/post', function (Request $request) {
    $curl = Curl::to("https://hisalud.com/api/resource/".$request->url )
        ->withOption("COOKIE", $request->cookie )
        ->withData( $request->data )
        ->asJson()
        ->post();
    return response()->json($curl, 200); 
});

Route::post('/put', function (Request $request) {
    $curl = Curl::to("https://hisalud.com/api/resource/".$request->url )
        ->withOption("COOKIE", $request->cookie )
        ->withData( $request->data )
        ->asJson()
        ->put();
    return response()->json($curl, 200); 
});

Route::post('/delete', function (Request $request) {
    $curl = Curl::to("https://hisalud.com/api/resource/".$request->url )
        ->withOption("COOKIE", $request->cookie )
        ->asJson()
        ->delete();
    return response()->json($curl, 200); 
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/Appointment', 'PostApiController@Appointment');
Route::post('/getPatient', 'PostApiController@getPatient');
Route::post('/storePatient', 'PostApiController@storePatient');
Route::post('/storePatient2', 'PostApiController@storePatient2');
Route::post('/medico_filtro', 'PostApiController@filters');
Route::get('/dni/{dni}', 'PostApiController@DNI');
Route::post('/newuser', 'PostApiController@create_web_user');
Route::post('/culqi', 'PagosController@culqi_charge');



Route::any('/resource/{doctype}/{name?}', 'PostApiController@any');
Route::post('/method/{method}', 'PostApiController@metodo');
Route::post('/upload_file', 'PostApiController@file_upload');
Route::any('/searchData', 'PagosController@search');
Route::any('/docType/{name}', 'PagosController@get_all');
Route::any('/linkValue/{name}', 'PagosController@get_all_table');
