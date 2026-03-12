<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'user_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:10240',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:10240',
            'ui_language' => 'nullable|string|in:uz,ru,en',
            'ui_font_size' => 'nullable|string|in:text-sm,text-base,text-lg',
            'ui_font_color' => 'nullable|string|max:20',
        ]);

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->has('ui_language')) {
            $user->ui_language = $request->input('ui_language');
        }
        
        if ($request->has('ui_font_size')) {
            $user->ui_font_size = $request->input('ui_font_size');
        }

        if ($request->has('ui_font_color')) {
            $user->ui_font_color = $request->input('ui_font_color');
        }

        if ($request->hasFile('user_avatar')) {
            $path = $request->file('user_avatar')->store('avatars', 'public');
            $user->avatar = '/storage/' . $path;
        } elseif ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = '/storage/' . $path;
        }

        $user->save();

        if ($request->has('ui_language')) {
            \Illuminate\Support\Facades\App::setLocale($user->ui_language);
        }

        return redirect()->route('profile.edit')->with('success', __('messages.settings_updated') ?? 'Settings successfully updated.');
    }

    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
