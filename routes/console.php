<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Task;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Message;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:check-expired', function () {
    $expiredTasks = Task::where('status', 'pending')->where('deadline', '<', now())->get();
    foreach ($expiredTasks as $task) {
        $task->update(['status' => 'failed']);
        if ($task->fine_amount > 0) {
            // Deduct fine
            $user = User::find($task->assigned_to);
            if ($user) {
                $user->balance -= $task->fine_amount;
                $user->save();
                
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'expense',
                    'amount' => $task->fine_amount,
                    'description' => 'System Fine: Task Overdue (' . $task->title . ')',
                ]);
                
                Message::create([
                    'sender_id' => $task->assigned_by,
                    'message' => "SYSTEM ALERT: Agent {$user->name} failed task '{$task->title}'. Fine applied: {$task->fine_amount} UZS."
                ]);
            }
        }
    }
})->purpose('Check and fail expired tasks, applying fines')->everyMinute();
