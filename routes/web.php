<?php

use App\Http\Controllers\CampaignController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CampaignController::class, 'index'])->name('home');
Route::get('/campaign/data', [CampaignController::class, 'getCampaignsData']);
Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaign');
Route::get('/campaigns/{campaign}/publishers', [CampaignController::class, 'publishers'])->name('publishers');
Route::get('/getcampaignstermwisedata/{campaign}', [CampaignController::class, 'getCampaignsDataByTermWise'])->name('publishers');
Route::get('/getcampaignsdatabydateandhourwise/{campaign}', [CampaignController::class, 'getCampaignsDataByDateAndHourWise'])->name('publishers');

