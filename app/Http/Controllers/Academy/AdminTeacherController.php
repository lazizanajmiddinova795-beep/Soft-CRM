<?php

namespace App\Http\Controllers\Academy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminTeacherController extends Controller
{
    public function index()
    {
        $teachers = User::where('role', 'teacher')->get();
        return view('dashboards.academy.teachers.index', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'passport_serial' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'passport_serial' => $request->passport_serial,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
            'status' => 'offline',
            'internal_id' => 'TCH-' . mt_rand(1000, 9999),
            'company_id' => auth()->user()->company_id,
        ];

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = 'storage/' . $path;
        }

        User::create($data);

        return redirect()->back()->with('success', 'O\'qituvchi muvaffaqiyatli qo\'shildi!');
    }

    public function update(Request $request, User $teacher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'passport_serial' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . $teacher->id,
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|max:5120',
        ]);

        $teacher->name = $request->name;
        $teacher->phone = $request->phone;
        $teacher->passport_serial = $request->passport_serial;
        $teacher->email = $request->email;
        
        if ($request->password) {
            $teacher->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($teacher->avatar && file_exists(public_path($teacher->avatar))) {
                @unlink(public_path($teacher->avatar));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $teacher->avatar = 'storage/' . $path;
        }

        $teacher->save();

        return redirect()->back()->with('success', 'O\'qituvchi muvaffaqiyatli yangilandi!');
    }

    public function destroy(User $teacher)
    {
        $teacher->delete();
        return redirect()->back()->with('success', 'O\'qituvchi muvaffaqiyatli o\'chirildi!');
    }
}
