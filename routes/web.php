<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/face-verify-login', [\App\Http\Controllers\FaceVerifyController::class, 'verifyLoginFace'])->name('face_verify_login');

Route::middleware(['auth', 'verified'])->group(function () {
    // Master Admin Routes
    Route::group(['prefix' => 'master', 'middleware' => 'can:master-access'], function() {
        Route::get('/dashboard', [App\Http\Controllers\Master\AdminController::class, 'index'])->name('master.dashboard');
        Route::post('/companies/{company}/approve', [App\Http\Controllers\Master\AdminController::class, 'approveCompany'])->name('master.companies.approve');
        Route::post('/companies/{company}/suspend', [App\Http\Controllers\Master\AdminController::class, 'suspendCompany'])->name('master.companies.suspend');
        Route::delete('/companies/{company}', [App\Http\Controllers\Master\AdminController::class, 'destroyCompany'])->name('master.companies.destroy');
    });
    Route::get('/dashboard', function () {
        if (auth()->user()->is_master) {
            return redirect()->route('master.dashboard');
        }
        return redirect()->route(auth()->user()->role . '.dashboard');
    })->name('dashboard');
    Route::post('/locale', function(\Illuminate\Http\Request $request) {
        $request->validate(['locale' => 'required|in:uz,en,ru']);
        $user = auth()->user();
        $user->ui_language = $request->locale;
        $user->save();
        session()->put('locale', $request->locale);
        session()->save();
        return back();
    })->name('locale.change');


    Route::post('/leave-impersonation', [\App\Http\Controllers\StaffController::class, 'leaveImpersonate'])->name('leave.impersonate');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/operator/dashboard', [DashboardController::class, 'operator'])->name('operator.dashboard');
    Route::get('/cashier/dashboard', [DashboardController::class, 'cashier'])->name('cashier.dashboard');
    Route::get('/teacher/dashboard', [DashboardController::class, 'teacher'])->name('teacher.dashboard');
    Route::get('/teacher/students', [\App\Http\Controllers\Academy\TeacherController::class, 'myStudents'])->name('teacher.students');
    Route::post('/teacher/grade', [\App\Http\Controllers\Academy\TeacherController::class, 'storeGrade'])->name('teacher.grade.store');
    Route::post('/teacher/topic', [\App\Http\Controllers\Academy\TeacherController::class, 'storeTopic'])->name('teacher.topic.store');
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
        
        // Academy Management
        Route::get('/academy', [\App\Http\Controllers\Academy\AcademyController::class, 'index'])->name('admin.academy.index');
        Route::resource('academy/courses', \App\Http\Controllers\Academy\CourseController::class)->names('admin.academy.courses');
        Route::resource('academy/students', \App\Http\Controllers\Academy\StudentController::class)->names('admin.academy.students');
        Route::resource('academy/groups', \App\Http\Controllers\Academy\GroupController::class)->names('admin.academy.groups');
        Route::resource('academy/rooms', \App\Http\Controllers\Academy\RoomController::class)->names('admin.academy.rooms');
        Route::resource('academy/teachers', \App\Http\Controllers\Academy\AdminTeacherController::class)->names('admin.academy.teachers');
        Route::resource('academy/schedules', \App\Http\Controllers\Academy\ScheduleController::class)->names('admin.academy.schedules');
        Route::resource('academy/telegram-bots', \App\Http\Controllers\Academy\TelegramBotController::class)->names('admin.academy.telegram_bots');
        
        Route::get('academy/attendance/{group}', [\App\Http\Controllers\Academy\AttendanceController::class, 'getStudents'])->name('admin.academy.attendance.students');
        Route::post('academy/attendance', [\App\Http\Controllers\Academy\AttendanceController::class, 'store'])->name('admin.academy.attendance.store');

        Route::get('academy/students/search', [\App\Http\Controllers\Academy\StudentController::class, 'search'])->name('admin.academy.students.search');
        Route::post('academy/students/add-to-group', [\App\Http\Controllers\Academy\StudentController::class, 'addToGroup'])->name('admin.academy.students.add_to_group');
        
        Route::post('academy/payments', [\App\Http\Controllers\Academy\AcademyPaymentController::class, 'store'])->name('admin.academy.payments.store');
        Route::get('academy/payments/history/{student}', [\App\Http\Controllers\Academy\AcademyPaymentController::class, 'history'])->name('admin.academy.payments.history');
        
        // Audit Logs (Ghost Log)
        Route::get('/audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.audit_logs.index');
        Route::get('/audit-logs/pdf', [App\Http\Controllers\Admin\AuditLogController::class, 'downloadPdf'])->name('admin.audit_logs.pdf');
    });

    Route::get('/clients/search', [\App\Http\Controllers\ClientController::class, 'search'])->name('clients.search');
    
    // Exports
    Route::get('/reports/daily', [\App\Http\Controllers\ExportController::class, 'dailyReport'])->name('reports.daily');
    Route::get('/reports/clients', [\App\Http\Controllers\ExportController::class, 'exportClients'])->name('reports.clients');
    Route::get('/reports/transactions', [\App\Http\Controllers\ExportController::class, 'exportTransactions'])->name('reports.transactions');

    // Chat & Tasks
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/task', [\App\Http\Controllers\ChatController::class, 'assignTask'])->name('chat.task.assign');
    Route::post('/chat/task/{task}/submit', [\App\Http\Controllers\ChatController::class, 'submitTask'])->name('chat.task.submit');
    Route::post('/chat/task/{task}/verify', [\App\Http\Controllers\ChatController::class, 'verifyTask'])->name('chat.task.verify');
    Route::delete('/chat/task/{task}', [\App\Http\Controllers\ChatController::class, 'deleteTask'])->name('chat.task.delete');
    
    Route::post('/chat/clear', [\App\Http\Controllers\ChatController::class, 'clear'])->name('chat.clear');
    Route::delete('/chat/message/{message}', [\App\Http\Controllers\ChatController::class, 'deleteMessage'])->name('chat.message.delete');
    Route::post('/chat/message/{message}', [\App\Http\Controllers\ChatController::class, 'editMessage'])->name('chat.message.edit');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
