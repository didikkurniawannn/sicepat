<?php

use App\Http\Controllers\Sektoral\GeoApiController;
use App\Http\Controllers\Sektoral\GisController;
use Illuminate\Support\Facades\Route;

// Dashboard GIS (data publik agregat; validasi tenant di controller)
Route::get('/gis/choropleth', [GisController::class, 'choropleth']);
Route::get('/gis/desa', [GisController::class, 'desa']);
Route::get('/gis/facilities', [GisController::class, 'facilities']);

// API geolocation (auth web/session; validasi tenant di controller)
Route::get('/kecamatans/{kecamatan}/boundary', [GeoApiController::class, 'boundary']);
Route::post('/validate-point', [GeoApiController::class, 'validatePoint']);
Route::get('/geocode/search', [GeoApiController::class, 'search']);
Route::get('/facilities/nearby', [GeoApiController::class, 'nearby']);
