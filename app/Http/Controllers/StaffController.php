<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;
use App\Models\Transaction;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function impersonate(User $user)
    {
        if (auth()->user()->role !== 'admin' || $user->role === 'admin') {
            abort(403, 'Siz ushbu foydalanuvchi profiliga kiraolmaysiz.');
        }
        
        // Store the original admin ID in session
        session()->put('impersonate_by', auth()->id());
        
        // Log in as the user
        auth()->login($user);
        
        return redirect()->route($user->role . '.dashboard')->with('success', $user->name . ' profiliga muvaffaqiyatli kirdingiz.');
    }

    public function leaveImpersonate()
    {
        if (session()->has('impersonate_by')) {
            $adminId = session()->pull('impersonate_by');
            $admin = User::find($adminId);
            
            if ($admin && $admin->role === 'admin') {
                auth()->login($admin);
                return redirect()->route('admin.dashboard')->with('success', 'Admin profiliga qaytdingiz.');
            }
        }
        
        return redirect()->route('login');
    }

    public function toggleBlock(User $user)
    {
        if (auth()->user()->role !== 'admin' || $user->id === auth()->id()) {
            abort(403);
        }
        
        $user->status = $user->status === 'blocked' ? 'offline' : 'blocked';
        $user->save();
        
        $statusText = $user->status === 'blocked' ? 'BLOKLANDI' : 'Faoliyatga qaytarildi';
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "Xodim {$user->name} {$statusText}",
            'ip_address' => request()->ip()
        ]);
        
        return back()->with('success', "Xodim holati o'zgartirildi: {$statusText}");
    }

    public function index()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $users = User::with(['shifts.pauses'])->get();
        return view('dashboards.admin_staff', compact('users'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:4',
            'role' => 'required|in:admin,operator,cashier,developer',
            'salary' => 'nullable|numeric',
            'bonus' => 'nullable|numeric',
            'work_start_time' => 'nullable',
            'work_end_time' => 'nullable',
            'allowed_ip' => 'nullable|string',
            'avatar' => 'nullable|image|max:10240',
            'face_id_image' => 'nullable|image|max:10240',
            'face_id_token' => 'nullable|string',
            'pin_code' => 'nullable|string|max:10',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = '/storage/' . $request->file('avatar')->store('avatars', 'public');
        }

        $faceIdPath = $request->face_id_token ?? strtoupper(Str::random(10));
        if ($request->hasFile('face_id_image')) {
            $faceIdPath = '/storage/' . $request->file('face_id_image')->store('face_scans', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'salary' => $request->salary ?? 0,
            'bonus' => $request->bonus ?? 0,
            'internal_id' => 'PND-' . rand(100, 999),
            'face_id_token' => $faceIdPath,
            'pin_code' => $request->pin_code ?? str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
            'avatar' => $avatarPath,
            'allowed_ip' => $request->allowed_ip,
            'work_start_time' => $request->work_start_time ?? '09:00:00',
            'work_end_time' => $request->work_end_time ?? '18:00:00'
        ]);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Registered new staff: ' . $user->name,
            'new_values' => ['role' => $user->role, 'email' => $user->email, 'face_id' => $user->face_id_token],
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with('success', 'Staff Member Added.');
    }

    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email,'.$user->id.',id,deleted_at,NULL',
            'password' => 'nullable|string|min:4',
            'role' => 'required|in:admin,operator,cashier,developer',
            'work_start_time' => 'nullable',
            'work_end_time' => 'nullable',
            'allowed_ip' => 'nullable|string',
            'avatar' => 'nullable|image|max:10240',
            'face_id_image' => 'nullable|image|max:10240',
            'pin_code' => 'nullable|string|max:10',
        ]);

        if ($request->hasFile('avatar')) {
            $user->avatar = '/storage/' . $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('face_id_image')) {
            $user->face_id_token = '/storage/' . $request->file('face_id_image')->store('face_scans', 'public');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role;
        $user->allowed_ip = $request->allowed_ip;
        
        if ($request->filled('pin_code')) {
            $user->pin_code = $request->pin_code;
        }
        
        if ($request->filled('work_start_time')) $user->work_start_time = $request->work_start_time;
        if ($request->filled('work_end_time')) $user->work_end_time = $request->work_end_time;
        
        $user->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated staff details: ' . $user->name,
            'new_values' => ['role' => $user->role, 'email' => $user->email],
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with('success', 'Staff Member '. $user->name .' Updated Successfully.');
    }

    public function updatePayroll(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'salary' => 'nullable|numeric',
            'bonus' => 'nullable|numeric',
            'fine_amount' => 'nullable|numeric',
            'extra_percentage' => 'nullable|numeric',
            'deduct_balance' => 'nullable|numeric',
            'payout_type' => 'nullable|in:balance,loan'
        ]);

        $old_salary = $user->salary;
        $old_balance = $user->balance;

        $user->salary = $request->salary ?? $user->salary;
        $user->bonus = $request->bonus ?? $user->bonus;
        $user->fine_amount = $request->fine_amount ?? $user->fine_amount;
        $user->extra_percentage = $request->extra_percentage ?? $user->extra_percentage;

        if ($request->deduct_balance > 0) {
            $amount = $request->deduct_balance;
            $type = $request->payout_type ?? 'balance';
            $finalDesc = "";

            if ($type === 'balance') {
                $user->balance -= $amount;
                $finalDesc = "Payroll Payout (Balance) for {$user->name}";
            } else {
                $user->debt += $amount;
                $finalDesc = "Payroll Payout (Loan/Qarz) for {$user->name}";
            }

            Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'expense',
                'amount' => $amount,
                'description' => $finalDesc,
            ]);
        }

        $user->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated payroll for ' . $user->name,
            'old_values' => ['salary' => $old_salary, 'balance' => $old_balance],
            'new_values' => ['salary' => $user->salary, 'balance' => $user->balance],
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with('success', 'Payroll Updated.');
    }

    public function adjustBalance(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'balance_action' => 'required|in:add,subtract,set',
            'balance_amount' => 'required|numeric|min:0',
            'debt_action' => 'required|in:add,subtract,set',
            'debt_amount' => 'required|numeric|min:0'
        ]);

        $old_balance = $user->balance;
        $old_debt = $user->debt;

        // Balance adjustment
        if ($request->balance_action === 'add') {
            $user->balance += $request->balance_amount;
        } elseif ($request->balance_action === 'subtract') {
            $user->balance -= $request->balance_amount;
        } elseif ($request->balance_action === 'set') {
            $user->balance = $request->balance_amount;
        }

        // Debt adjustment
        if ($request->debt_action === 'add') {
            $user->debt += $request->debt_amount;
        } elseif ($request->debt_action === 'subtract') {
            $user->debt -= $request->debt_amount;
            if ($user->debt < 0) $user->debt = 0; // Prevent negative debt
        } elseif ($request->debt_action === 'set') {
            $user->debt = $request->debt_amount;
        }

        $user->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "Xodim {$user->name} balansi/qarzi tahrirlandi",
            'old_values' => ['balance' => $old_balance, 'debt' => $old_debt],
            'new_values' => ['balance' => $user->balance, 'debt' => $user->debt],
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with('success', "{$user->name} balansi va qarzi yangilandi.");
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete yourself or active core admin.']);
        }
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'TERMINATED STAFF: ' . $user->name,
            'new_values' => ['role' => $user->role, 'email' => $user->email],
            'ip_address' => request()->ip()
        ]);

        $user->delete();

        return redirect()->back()->with('success', 'Staff Member Terminated Permanently.');
    }
}
