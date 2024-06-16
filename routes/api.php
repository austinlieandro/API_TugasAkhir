<?php

use App\Http\Controllers\BengkelController;
use App\Http\Controllers\JamOperasionalController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\ReservasiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/kendaraan', [KendaraanController::class, 'kendaraan']);
Route::post('/daftarBengkel', [BengkelController::class, 'daftarBengkel']);
Route::post('/daftarKaryawan', [KaryawanController::class, 'karyawan']);
Route::post('/kendaraan/{userId}/{id}', [KendaraanController::class, 'updateKendaraan']);
Route::post('/jamOperasional', [JamOperasionalController::class, 'inputJam']);
Route::post('/updateOperasional/{bengkelId}/{id}', [JamOperasionalController::class, 'updateJamOperasional']);
Route::post('/updateKaryawan/{bengkelId}/{id}', [KaryawanController::class, 'updateKaryawan']);
Route::post('/deleteKaryawan/{bengkelId}/{id}', [KaryawanController::class, 'deleteKaryawan']);
Route::post('/updateProfile/{id}', [UserController::class, 'updateProfile']);
Route::post('/deleteKendaraan/{userId}/{id}', [KendaraanController::class, 'deleteKendaraan']);
Route::post('/updateBengkel/{bengkelId}/{id}', [BengkelController::class, 'editBengkel']);
Route::post('/userReservasi', [ReservasiController::class, 'userReservasi']);
Route::post('/assignKaryawan', [ReservasiController::class, 'assignKaryawan']);

Route::get('/bengkel', [BengkelController::class, 'showAllbengkels']);
Route::get('/bengkel/{id}', [BengkelController::class, 'detailBengkels']);
Route::get('/kendaraan/{id}', [KendaraanController::class, 'kendaraanUser']);
Route::get('/karyawan/{id}', [KaryawanController::class, 'daftarKaryawan']);
Route::get('/showReservasiUser/{id}', [ReservasiController::class, 'displayReservasiUser']);
Route::get('/showReservasiBengkel/{id}', [ReservasiController::class, 'displayReservasiBengkel']);
