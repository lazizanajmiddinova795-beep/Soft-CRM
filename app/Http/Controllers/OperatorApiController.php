<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Shift;
use App\Models\Task;

class OperatorApiController extends Controller
{
    public function stats()
    {
        if (auth()->user()->role !== 'operator') return response()->json(['error' => 'Unauthorized'], 403);
        
        $myContracts = Contract::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->get();
            
        $activeShift = Shift::where('user_id', auth()->id())->whereNull('ended_at')->first();
        $myTasks = Task::where('assigned_to', auth()->id())->where('status', '!=', 'completed')->get();

        $shiftSeconds = 0;
        if ($activeShift) {
            $totalSeconds = now()->diffInSeconds($activeShift->started_at, true);
            $pauseSeconds = 0;
            foreach($activeShift->pauses as $p) {
                if ($p->resumed_at) {
                    $pauseSeconds += $p->resumed_at->diffInSeconds($p->paused_at, true);
                } else {
                    $pauseSeconds += now()->diffInSeconds($p->paused_at, true);
                }
            }
            $shiftSeconds = max(0, $totalSeconds - $pauseSeconds);
        }
        
        $shiftDuration = sprintf('%02d:%02d:%02d', 
            floor($shiftSeconds / 3600), 
            floor(($shiftSeconds / 60) % 60), 
            $shiftSeconds % 60
        );

        // Monthly stats calculation (assuming shift data is available)
        // Just as an example, sum duration
        $monthlyShifts = Shift::where('user_id', auth()->id())
            ->whereMonth('started_at', now()->month)
            ->whereNotNull('ended_at')
            ->get();
        $monthlyHours = $monthlyShifts->sum(function($s) {
            return $s->ended_at->diffInMinutes($s->started_at);
        }) / 60;
        
        $myReceipts = Contract::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'balance' => auth()->user()->balance,
            'xp' => auth()->user()->xp,
            'salary' => auth()->user()->salary,
            'monthlyHours' => round($monthlyHours, 1),
            'contractCount' => count($myContracts),
            'approvedCount' => $myContracts->where('status', 'approved')->count(),
            'rejectedCount' => $myContracts->where('status', 'rejected')->count(),
            'pendingCount' => $myContracts->where('status', 'pending')->count(),
            'contracts' => $myContracts,
            'receipts' => $myReceipts,
            'tasks' => $myTasks,
            'shiftDuration' => $shiftDuration,
            'hasActiveShift' => $activeShift ? true : false,
            'isPaused' => $activeShift ? ($activeShift->currentPause() ? true : false) : false,
            'shiftStart' => $activeShift ? $activeShift->started_at->format('Y-m-d H:i:s') : null,
            'shiftSeconds' => $shiftSeconds,
        ]);
    }
}
