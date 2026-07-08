<?php

use Illuminate\Support\Facades\Route;
use KeyAgency\TaxonomyTermsOrder\Http\Controllers\IndexController;
use KeyAgency\TaxonomyTermsOrder\Http\Controllers\ReorderController;
use KeyAgency\TaxonomyTermsOrder\Http\Controllers\ResetController;
use KeyAgency\TaxonomyTermsOrder\Http\Controllers\ShowController;

Route::prefix('taxonomy-terms-order')->name('taxonomy-terms-order.')->group(function () {
    Route::get('/', IndexController::class)->name('index');
    Route::get('{taxonomy}', ShowController::class)->name('show');
    Route::post('{taxonomy}/reorder', ReorderController::class)->name('reorder');
    Route::post('{taxonomy}/reset', ResetController::class)->name('reset');
});
