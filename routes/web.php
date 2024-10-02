<?php
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use App\Filament\Resources\OrderResource\Pages\DetailsOrder;
Route::get('/', function (){
    return view('app');
});
Route::get('/', function (){
    return redirect('/admin/login');
});
Route::get('admin/orders/details/{record}', DetailsOrder::class)->name('details');
