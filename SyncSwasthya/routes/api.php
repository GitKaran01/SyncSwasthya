<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FamilyController;

Route::prefix('v1')->group(function(){

    Route::get('/families',[FamilyController::class,'index']);
    Route::post('/families',[FamilyController::class,'store']);
    Route::get('/families/{id}',[FamilyController::class,'show']);
    Route::put('/families/{id}',[FamilyController::class,'update']);
    Route::delete('/families/{id}',[FamilyController::class,'destroy']);

    // Members
    Route::post('/families/{id}/add-member',[FamilyController::class,'addMember']);
    Route::put('/members/{id}',[FamilyController::class,'updateMember']);
    Route::delete('/members/{id}',[FamilyController::class,'destroyMember']);
});
