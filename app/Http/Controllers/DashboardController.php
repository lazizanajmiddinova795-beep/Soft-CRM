<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function admin()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        $totalTreasury = Transaction::forCompany()->where('type', 'income')->sum('amount') - Transaction::forCompany()->where('type', 'expense')->sum('amount');
        $dailyIncome = Transaction::forCompany()->where('type', 'income')->whereDate('created_at', today())->sum('amount');
        $dailyExpense = Transaction::forCompany()->where('type', 'expense')->whereDate('created_at', today())->sum('amount');
        
        $activeOperators = User::forCompany()->where('role', 'operator')->where('status', 'online')->count();
        $totalOperators = User::forCompany()->where('role', 'operator')->count();
        
        $logsQuery = \App\Models\AuditLog::forCompany()->with('user')->orderBy('created_at', 'desc')->take(10)->get();
        
        $logs = $logsQuery->map(function($log) {
            return [
                'time' => $log->created_at->format('H:i:s'),
                'user' => $log->user->name ?? 'System',
                'action' => str_replace('_', ' ', strtoupper($log->action)),
                'details' => json_encode($log->new_values)
            ];
        });
        
        $pendingContracts = Contract::forCompany()->with('user', 'service')->where('status', 'pending')->orderBy('created_at', 'asc')->get();
        $pendingVerifications = $pendingContracts->count();

        return view('dashboards.admin', compact('totalTreasury', 'dailyIncome', 'dailyExpense', 'activeOperators', 'totalOperators', 'logs', 'pendingVerifications', 'pendingContracts'));
    }

    public function adminStats()
    {
        if (auth()->user()->role !== 'admin') return response()->json(['error' => 'Unauthorized'], 403);
        
        $totalTreasury = Transaction::forCompany()->where('type', 'income')->sum('amount') - Transaction::forCompany()->where('type', 'expense')->sum('amount');
        $dailyIncome = Transaction::forCompany()->where('type', 'income')->whereDate('created_at', today())->sum('amount');
        $dailyExpense = Transaction::forCompany()->where('type', 'expense')->whereDate('created_at', today())->sum('amount');
        
        $activeOperators = User::forCompany()->where('role', 'operator')->where('status', 'online')->count();
        $totalOperators = User::forCompany()->where('role', 'operator')->count();
        $pendingVerifications = Contract::forCompany()->where('status', 'pending')->count();
        
        $logs = \App\Models\AuditLog::forCompany()->with('user')->orderBy('created_at', 'desc')->take(10)->get()->map(function($log) {
            return [
                'time' => $log->created_at->format('H:i:s'),
                'user' => $log->user->name ?? 'System',
                'action' => str_replace('_', ' ', strtoupper($log->action)),
                'details' => json_encode($log->new_values)
            ];
        });
        
        $pendingContracts = Contract::forCompany()->with('user', 'service')->where('status', 'pending')->orderBy('created_at', 'asc')->get()->map(function($ct) {
            return [
                'id' => $ct->id,
                'contract_id' => $ct->contract_id,
                'client_name' => $ct->client_name,
                'amount' => $ct->amount,
                'cost_price' => $ct->cost_price,
                'service' => $ct->service->name ?? 'Custom',
                'user' => $ct->user->name ?? 'Operator',
            ];
        });
        
        return response()->json([
            'totalTreasury' => number_format($totalTreasury, 0, '.', ' '),
            'dailyIncome' => number_format($dailyIncome, 0, '.', ' '),
            'dailyExpense' => number_format($dailyExpense, 0, '.', ' '),
            'activeOperators' => $activeOperators,
            'totalOperators' => $totalOperators,
            'pendingVerifications' => $pendingVerifications,
            'pendingList' => $pendingContracts,
            'logs' => $logs
        ]);
    }

    public function operator()
    {
        if (auth()->user()->role !== 'operator') abort(403);

        $services = Service::forCompany()->get();
        $myContracts = Contract::forCompany()->where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->get();
            
        $activeShift = \App\Models\Shift::where('user_id', auth()->id())->whereNull('ended_at')->first();
        $myTasks = \App\Models\Task::forCompany()->where('assigned_to', auth()->id())->where('status', '!=', 'completed')->get();

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

        return view('dashboards.operator', compact('services', 'myContracts', 'activeShift', 'myTasks', 'shiftSeconds'));
    }

    public function cashier(Request $request)
    {
        if (auth()->user()->role !== 'cashier' && auth()->user()->role !== 'admin') abort(403);

        $pendingContracts = Contract::forCompany()->with(['user', 'service', 'client'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
            
        $dailyReceipts = Transaction::forCompany()->where('type', 'income')->whereDate('created_at', today())->sum('amount');
        $dailyProfit = Contract::forCompany()->where('status', 'approved')->whereDate('created_at', today())->get()->sum(function($c) {
            return $c->amount - $c->cost_price;
        });
        
        $manualExpenses = Transaction::forCompany()->where('type', 'expense')->whereDate('created_at', today())->sum('amount');
        
        $cashIncome = Transaction::forCompany()->where('type', 'income')->where('payment_method', 'cash')->sum('amount');
        $cashExpense = Transaction::forCompany()->where('type', 'expense')->where('payment_method', 'cash')->sum('amount');
        $vault = $cashIncome - $cashExpense;

        $cardIncome = Transaction::forCompany()->where('type', 'income')->where('payment_method', 'card')->sum('amount');
        $cardExpense = Transaction::forCompany()->where('type', 'expense')->where('payment_method', 'card')->sum('amount');
        $cardVault = $cardIncome - $cardExpense;
        
        $activeShift = \App\Models\Shift::where('user_id', auth()->id())->whereNull('ended_at')->first();
        
        $recentTransactions = Transaction::forCompany()->with(['user', 'contract.service', 'contract.client'])->orderBy('created_at', 'desc')->take(100)->get();
        
        $shiftDurationSeconds = 0;
        if ($activeShift) {
            $shiftDurationSeconds = abs(now()->diffInSeconds($activeShift->started_at));
        }
        
        $monthlyShifts = \App\Models\Shift::where('user_id', auth()->id())->whereMonth('started_at', now()->month)->whereNotNull('ended_at')->get();
        $monthlyHours = $monthlyShifts->sum(function($s) { 
            return abs($s->ended_at->diffInMinutes($s->started_at)); 
        }) / 60;
        
        $monthlyRevenue = Transaction::forCompany()->where('type', 'income')->whereMonth('created_at', now()->month)->sum('amount');


        // Reports: staff performance today
        $operatorsToday = User::forCompany()->where('role', 'operator')->get()->map(function($op) {
            $approvedContracts = Contract::forCompany()->where('user_id', $op->id)->whereDate('created_at', today())->where('status', 'approved')->get();
            $op->today_deals = $approvedContracts->count();
            $op->today_revenue = $approvedContracts->sum('amount');
            $op->today_profit = $approvedContracts->sum(function($c) { return $c->amount - $c->cost_price; });
            return $op;
        })->sortByDesc('today_revenue');

        $recentMessages = \App\Models\Message::forCompany()->with('sender')->orderBy('created_at', 'desc')->take(10)->get()->reverse();

        $currentTab = $request->query('tab', 'dashboard');
        $allStaff = User::forCompany()->orderBy('name')->get();

        return view('dashboards.cashier', compact(
            'pendingContracts', 'dailyReceipts', 'dailyProfit', 'manualExpenses', 'vault', 'cardVault',
            'activeShift', 'recentTransactions', 'shiftDurationSeconds', 'monthlyHours', 
            'monthlyRevenue', 'operatorsToday', 'recentMessages', 'currentTab', 'allStaff'
        ));
    }


    public function teacher()
    {
        if (auth()->user()->role !== 'teacher') abort(403);
        
        $activeShift = \App\Models\Shift::where('user_id', auth()->id())->whereNull('ended_at')->first();
        $groups = \App\Models\Group::where('teacher_id', auth()->id())->with('course', 'students', 'room')->get();
        $schedules = \App\Models\Schedule::whereIn('group_id', $groups->pluck('id'))->with('group.room')->get();
        
        return view('dashboards.teacher', compact('activeShift', 'groups', 'schedules'));
    }

    public function heartbeat()
    {
        $user = auth()->user();
        if ($user) {
            $user->update(['last_heartbeat' => now(), 'status' => 'online']);
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['status' => 'error'], 401);
    }
}
