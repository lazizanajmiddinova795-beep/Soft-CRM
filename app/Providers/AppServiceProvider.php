<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Logout;
use App\Models\Shift;
use App\Models\AuditLog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                $activeShift = Shift::where('user_id', $event->user->id)->whereNull('ended_at')->first();
                if ($activeShift) {
                    $activeShift->update(['ended_at' => now()]);
                    $event->user->update(['status' => 'offline']);
                    AuditLog::create([
                        'user_id' => $event->user->id,
                        'action' => 'shift_force_stop_on_logout',
                        'new_values' => ['time' => now()],
                    ]);
                }
            }
        });
    }
}
