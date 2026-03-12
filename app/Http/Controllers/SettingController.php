<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('dashboards.admin_settings', compact('settings'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'company_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            'bg_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:5120',
            'user_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
        ]);

        if ($request->hasFile('company_logo_file')) {
            $path = $request->file('company_logo_file')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'company_logo'], ['value' => '/storage/' . $path]);
        }

        if ($request->hasFile('bg_image_file')) {
            $path = $request->file('bg_image_file')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'bg_image_url'], ['value' => '/storage/' . $path]);
        }

        // Handle avatar upload for current user
        if ($request->hasFile('user_avatar')) {
            $path = $request->file('user_avatar')->store('avatars', 'public');
            $user = auth()->user();
            $user->avatar = '/storage/' . $path;
            $user->save();
        }

        $data = $request->except(['_token', 'company_logo_file', 'bg_image_file', 'user_avatar']);
        
        foreach($data as $key => $value) {
            if ($value !== null) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        \Illuminate\Support\Facades\Cache::forget('sys_language');

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Updated System Settings & Interface',
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with('success', 'Settings applied globally.');
    }

    public function clearData(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        // Ensure user typed the confirmation word (optional but recommended, here we just check request)
        
        \Illuminate\Support\Facades\DB::transaction(function () {
            // Delete contracts and operations (transactions)
            \App\Models\Transaction::query()->delete();
            \App\Models\Contract::query()->delete();
            
            // Delete debts
            \App\Models\DebtInstallment::query()->delete();
            \App\Models\Debt::query()->delete();

            // Delete clients
            \App\Models\Client::query()->delete();

            // Delete shifts
            \App\Models\ShiftPause::query()->delete();
            \App\Models\Shift::query()->delete();

            // Delete chat and tasks
            \Illuminate\Support\Facades\DB::table('messages')->delete();
            \Illuminate\Support\Facades\DB::table('tasks')->delete();

            // Delete logs
            \App\Models\AuditLog::query()->delete();

            // Delete all staff EXCEPT the currently logged-in admin
            \App\Models\User::where('id', '!=', auth()->id())->forceDelete();
        });

        return redirect()->back()->with('success', 'Tizim ma\'lumotlari muvaffaqiyatli tozalandi (0 holatiga tushirildi).');
    }
}
