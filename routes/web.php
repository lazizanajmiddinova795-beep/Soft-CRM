<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->role . '.dashboard');
    })->name('dashboard');

    Route::post('/leave-impersonation', [\App\Http\Controllers\StaffController::class, 'leaveImpersonate'])->name('leave.impersonate');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/operator/dashboard', [DashboardController::class, 'operator'])->name('operator.dashboard');
    Route::get('/cashier/dashboard', [DashboardController::class, 'cashier'])->name('cashier.dashboard');

    // Operator specific
    Route::post('/contracts', [\App\Http\Controllers\ContractController::class, 'store'])->name('contracts.store');

    // Cashier specific
    Route::post('/contracts/{contract}/approve', [\App\Http\Controllers\ContractController::class, 'approve'])->name('contracts.approve');
    Route::post('/contracts/{contract}/reject', [\App\Http\Controllers\ContractController::class, 'reject'])->name('contracts.reject');
    Route::get('/contracts/{contract}/print', [\App\Http\Controllers\ContractController::class, 'printLog'])->name('contracts.print');
    Route::get('/contracts/{contract}/download', [\App\Http\Controllers\ContractController::class, 'download'])->name('contracts.download');

    // System & Shifts
    Route::post('/heartbeat', [\App\Http\Controllers\DashboardController::class, 'heartbeat'])->name('system.heartbeat');
    Route::post('/shift/start', [\App\Http\Controllers\ShiftController::class, 'start'])->name('shift.start');
    Route::post('/shift/stop', [\App\Http\Controllers\ShiftController::class, 'stop'])->name('shift.stop');
    Route::post('/shift/pause', [\App\Http\Controllers\ShiftController::class, 'pause'])->name('shift.pause');
    Route::post('/shift/resume', [\App\Http\Controllers\ShiftController::class, 'resume'])->name('shift.resume');
    
    // Treasury Manual Entry
    Route::post('/treasury/manual', [\App\Http\Controllers\TransactionController::class, 'storeManual'])->name('treasury.manual');

    // Operator specific routes
    Route::group(['prefix' => 'operator'], function() {
        Route::get('/stats/realtime', [\App\Http\Controllers\OperatorApiController::class, 'stats'])->name('operator.stats');
        Route::get('/clients/search', [\App\Http\Controllers\ClientController::class, 'search'])->name('operator.clients.search');
        Route::post('/operator/face-verify', [\App\Http\Controllers\FaceVerifyController::class, 'verify'])->name('operator.face_verify');
        Route::get('/syndicate', [\App\Http\Controllers\ChatController::class, 'index'])->name('operator.chat.index');
    });
    // Admin specific routes
    Route::group(['prefix' => 'admin'], function() {
        Route::get('/staff', [\App\Http\Controllers\StaffController::class, 'index'])->name('admin.staff.index');
        Route::post('/staff', [\App\Http\Controllers\StaffController::class, 'store'])->name('admin.staff.store');
        Route::post('/staff/{user}/update', [\App\Http\Controllers\StaffController::class, 'update'])->name('admin.staff.update');
        Route::delete('/staff/{user}', [\App\Http\Controllers\StaffController::class, 'destroy'])->name('admin.staff.destroy');
        Route::post('/staff/{user}/payroll', [\App\Http\Controllers\StaffController::class, 'updatePayroll'])->name('admin.staff.payroll');
        Route::post('/staff/{user}/adjust-balance', [\App\Http\Controllers\StaffController::class, 'adjustBalance'])->name('admin.staff.adjustBalance');
        Route::post('/staff/{user}/impersonate', [\App\Http\Controllers\StaffController::class, 'impersonate'])->name('admin.staff.impersonate');
        Route::post('/staff/{user}/toggle-block', [\App\Http\Controllers\StaffController::class, 'toggleBlock'])->name('admin.staff.toggleBlock');
        
        Route::get('/fcc', [\App\Http\Controllers\FccController::class, 'index'])->name('admin.fcc.index');
        Route::post('/fcc/{contract}', [\App\Http\Controllers\FccController::class, 'update'])->name('admin.fcc.update');
        Route::delete('/fcc/{contract}', [\App\Http\Controllers\FccController::class, 'destroy'])->name('admin.fcc.destroy');
        
        Route::get('/finance', [\App\Http\Controllers\FinanceController::class, 'index'])->name('admin.finance.index');
        Route::post('/finance/{transaction}', [\App\Http\Controllers\FinanceController::class, 'update'])->name('admin.finance.update');
        Route::delete('/finance/{transaction}', [\App\Http\Controllers\FinanceController::class, 'destroy'])->name('admin.finance.destroy');
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('admin.settings.update');
        Route::delete('/settings/clear-data', [\App\Http\Controllers\SettingController::class, 'clearData'])->name('admin.settings.clearData');
        
        // Realtime stats
        Route::get('/stats/realtime', [\App\Http\Controllers\DashboardController::class, 'adminStats'])->name('admin.stats');
        Route::post('/ai/chat', [\App\Http\Controllers\AiAssistantController::class, 'chat'])->name('admin.ai.chat');

        // Client Management
        Route::get('/clients', [\App\Http\Controllers\ClientController::class, 'index'])->name('admin.clients.index');
        Route::post('/clients', [\App\Http\Controllers\ClientController::class, 'store'])->name('admin.clients.store');
        Route::post('/clients/{client}', [\App\Http\Controllers\ClientController::class, 'update'])->name('admin.clients.update');
        Route::delete('/clients/{client}', [\App\Http\Controllers\ClientController::class, 'destroy'])->name('admin.clients.destroy');
        
        // Debt Management
        Route::post('/debts', [\App\Http\Controllers\DebtController::class, 'store'])->name('admin.debts.store');
        Route::post('/debts/installment/{installment}/pay', [\App\Http\Controllers\DebtController::class, 'payInstallment'])->name('admin.debts.payInstallment');
        Route::post('/debts/{debt}/pay', [\App\Http\Controllers\DebtController::class, 'payOneTime'])->name('admin.debts.payOneTime');
        Route::get('/debts/{debt}/schedule', [\App\Http\Controllers\DebtController::class, 'showSchedule'])->name('admin.debts.showSchedule');
        Route::get('/debts/{debt}/print-schedule', [\App\Http\Controllers\DebtController::class, 'printSchedule'])->name('admin.debts.printSchedule');
    });

    Route::get('/clients/search', [\App\Http\Controllers\ClientController::class, 'search'])->name('clients.search');

    // Chat & Tasks
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/task', [\App\Http\Controllers\ChatController::class, 'assignTask'])->name('chat.task.assign');
    Route::post('/chat/task/{task}/complete', [\App\Http\Controllers\ChatController::class, 'completeTask'])->name('chat.task.complete');
    
    Route::post('/chat/task/{task}', [\App\Http\Controllers\ChatController::class, 'editTask'])->name('chat.task.edit');
    Route::delete('/chat/task/{task}', [\App\Http\Controllers\ChatController::class, 'deleteTask'])->name('chat.task.delete');
    
    Route::post('/chat/clear', [\App\Http\Controllers\ChatController::class, 'clear'])->name('chat.clear');
    Route::delete('/chat/message/{message}', [\App\Http\Controllers\ChatController::class, 'deleteMessage'])->name('chat.message.delete');
    Route::post('/chat/message/{message}', [\App\Http\Controllers\ChatController::class, 'editMessage'])->name('chat.message.edit');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
