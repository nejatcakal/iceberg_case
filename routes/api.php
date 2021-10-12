<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pnlinh\GoogleDistance\Facades\GoogleDistance;
use GuzzleHttp\Client;
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
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
Route::group(
    [
        'middleware'=>'api',
        'namespace'=>'App\Http\Controllers',
        'prefix'=>'auth'
    ],
    function($router){
        Route::post('login','AuthController@login');
        Route::post('register','AuthController@register');
        Route::post('logout','AuthController@logout');
        Route::post('profile','AuthController@profile');
        Route::get('/error', function(Request $request) {
            return response()->json(['error' => 'Unauthorized'], 401);
        })->name("error");
    }

);

Route::group(
    [
        'middleware' => 'api',
        'namespace'  => 'App\Http\Controllers',
        'prefix'=>'contacts'
    ],
    function ($router) {
        Route::get('','ContactsController@index' );
        Route::post('add','ContactsController@store' );
        Route::put('update/{contact}','ContactsController@update' );
        Route::delete('delete/{contact}','ContactsController@destroy');
        Route::get('get/{contact}','ContactsController@show' );
    }
);

Route::group(
    [
        'middleware' => 'api',
        'namespace'  => 'App\Http\Controllers',
        'prefix'=>'appointments'
    ],
    function ($router) {
        Route::get('','AppointmentsController@index' );
        Route::post('add','AppointmentsController@store' );
        Route::put('update/{appointment}','AppointmentsController@update' );
        Route::delete('delete/{appointment}','AppointmentsController@destroy');
        Route::get('get/{appointment}','AppointmentsController@show' );
        Route::get('available_user','AppointmentsController@availableUser' );
    }
);

Route::get('/calculate_start_and_end_time', function(Request $request) {

    $origin_postcode = $request->origin_postcode;
    $destination_postcode = $request->destination_postcode;
    $mode = $request->mode;
    $appointment_time  = $request->appointment_time;

    $origin_data = json_decode(file_get_contents('http://api.postcodes.io/postcodes/'.$origin_postcode),true);//get origin adress data
    $destination_data = json_decode(file_get_contents('http://api.postcodes.io/postcodes/'.$destination_postcode),true);//get adress destination data
  
    $origin = $origin_data['status']=='200'?$origin_data['result']['latitude'].",".$origin_data['result']['longitude']:0; // check origin data
    $destination = $destination_data['status']=='200'?$destination_data['result']['latitude'].",".$destination_data['result']['longitude']:0;// check destination data
    
    if($origin!=0 && $destination!=0){

        //$result =  google_distance($mode,$origin, $destination);// get distance and duration betwen two address
        $apiUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';
        $getApiKey = env('GOOGLE_MAPS_DISTANCE_API_KEY');
        
        $client = new Client();

        try {
            $response = $client->get($apiUrl, [
                'query' => [
                    'units'        => 'imperial',
                    'origins'      => $origin,
                    'destinations' => $destination,
                    'key'          => $getApiKey,
                    'random'       => random_int(1, 100),
                ],
            ]);

            $statusCode = $response->getStatusCode();
          
            if (200 == $statusCode) {
                $responseData = json_decode($response->getBody()->getContents());
                //print_r($responseData);
                if (isset($responseData->rows[0]->elements[0]->duration)) {
                    $result =  $responseData->rows[0]->elements[0]->duration->value;
                }
            }else{
                $result = -1;
            }
           
            
        } catch (Exception $e) {
            
            $result = -1;
        }


        $startTime = new DateTime($appointment_time);// set start time
        $endTime = new DateTime($appointment_time);//set end time
        
        $startTime->modify('-'.$result.' seconds'); // calculate  start time
        $endTime->modify('+'.($result + 3600).' seconds');// calculate  end time
        return json_encode(["result"=>$result,"status"=>true,"start"=>$startTime->format('H:i'),"end"=>$endTime->format('H:i')]); // return start and end time

    }else{
        return json_encode(["status"=>false]);
    }
});

