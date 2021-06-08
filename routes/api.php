<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('login', 'UsuarioController@login');
Route::post('registro', 'UsuarioController@registro');
Route::post('/logout', 'UsuarioController@logout');
Route::post('/recurso_usuario', 'UsuarioController@recursoUsuario');

Route::group(['prefix' => 'parametros'], function() {
    Route::get('categoria', 'ParametroController@categoria');
    Route::get('tipo_requerimiento/{id}', 'ParametroController@tipoRequerimiento');
    Route::get('municipio', 'ParametroController@municipio');
    Route::get('sucursal/{id}', 'ParametroController@sucursal');
    Route::get('departamento', 'ParametroController@departamento');
    Route::get('division', 'ParametroController@division');
    Route::get('cargo', 'ParametroController@cargo');
    Route::get('rol', 'ParametroController@rol');
});

Route::group(['prefix' => 'funcionario'], function() {
    Route::post('solicitar_req', 'SolicitudRequerimientoController@solicitudReq');
    Route::post('ver_solicitudes', 'TicketController@verSolicitudes');
    Route::post('calificacion', 'TicketController@calificacion');
});
Route::group(['prefix' => 'agente'], function() {
    Route::post('tickets', 'TicketController@listadoReq');
    Route::post('elegir_ticket', 'TicketController@elegirTicket');
    Route::post('tomar_ticket', 'TicketController@tomarTicket');
    Route::post('terminar_ticket', 'TicketController@terminarTicket');
});
Route::group(['prefix' => 'admin'], function() {
    Route::put('store/{id}', 'UsuarioController@store');
    Route::post('index', 'UsuarioController@index');
    Route::post('eliminar', 'UsuarioController@eliminar');
});