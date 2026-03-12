<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function start(Request $request)
    {
        $user = auth()->user();

        // Check if already has an open shift
        $activeShift = Shift::where('user_id', $user->id)->whereNull('ended_at')->first();
        if ($activeShift) {
            return redirect()->back()->with('error', 'You already have an active shift!');
        }
        // Optional PIN validation fallback
        if ($request->has('pin_code') && $request->filled('pin_code')) {
            if (!$user->pin_code || $user->pin_code !== $request->pin_code) {
                return redirect()->back()->with('error', 'Noto\'g\'ri PIN-kod! Iltimos, qayta urining.');
            }
            // Log this specific event
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Kamera ishlamadi yoki xira - Xodim ' . $user->name . ' PIN-kod orqali tizimga kirdi (Fallback).',
                'new_values' => ['time' => now()],
                'ip_address' => request()->ip()
            ]);
        }

        Shift::create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        $user->update(['status' => 'online']);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'shift_start',
            'new_values' => ['time' => now()],
        ]);

        return redirect()->back()->with('success', 'Shift Started. Systems Online.');
    }

    public function pause(Request $request)
    {
        $user = auth()->user();
        $activeShift = Shift::where('user_id', $user->id)->whereNull('ended_at')->first();
        
        if (!$activeShift || $activeShift->currentPause()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Smenani tanaffusga olib bo\'lmaydi.']);
            }
            return redirect()->back()->with('error', 'Smenani tanaffusga olib bo\'lmaydi.');
        }

        $activeShift->pauses()->create(['paused_at' => now()]);
        $user->update(['status' => 'away']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Tanaffus boshlandi.']);
        }

        return redirect()->back()->with('success', 'Tanaffus boshlandi.');
    }

    public function resume(Request $request)
    {
        $user = auth()->user();
        $activeShift = Shift::where('user_id', $user->id)->whereNull('ended_at')->first();
        $pause = $activeShift ? $activeShift->currentPause() : null;

        if (!$pause) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Faol tanaffus topilmadi.']);
            }
            return redirect()->back()->with('error', 'Faol tanaffus topilmadi.');
        }

        $pause->update(['resumed_at' => now()]);
        $user->update(['status' => 'online']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Ish davom ettirilmoqda.']);
        }

        return redirect()->back()->with('success', 'Ish davom ettirilmoqda.');
    }

    public function stop(Request $request)
    {
        $user = auth()->user();

        $activeShift = Shift::where('user_id', $user->id)->whereNull('ended_at')->first();
        if (!$activeShift) {
            return redirect()->back()->with('error', 'No active shift found.');
        }

        // If currently on pause, resume it first automatically or prevent stop? Let's just resume and stop.
        $pause = $activeShift->currentPause();
        if ($pause) {
            $pause->update(['resumed_at' => now()]);
        }

        $endTime = now();
        $activeShift->update([
            'ended_at' => $endTime,
        ]);

        // Calculate actual work time (Total shift - total pauses)
        $totalSeconds = abs($endTime->diffInSeconds($activeShift->started_at, true));
        $pauseSeconds = 0;
        foreach($activeShift->pauses as $p) {
            if ($p->resumed_at) {
                $pauseSeconds += abs($p->resumed_at->diffInSeconds($p->paused_at, true));
            }
        }
        
        $workSeconds = max(0, $totalSeconds - $pauseSeconds);
        $hoursWorked = $workSeconds / 3600;
        
        // Base rate: 15,000 UZS per hour
        $earnedSalary = $hoursWorked * 15000;
        
        $user->salary += $earnedSalary;
        $user->status = 'offline';
        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'shift_stop',
            'new_values' => ['time' => now(), 'earned' => $earnedSalary, 'work_hours' => $hoursWorked],
        ]);

        return redirect()->back()->with('success', 'Shift Ended. Work time: ' . round($hoursWorked, 2) . 'h');
    }
}
