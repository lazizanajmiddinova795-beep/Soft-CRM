<?php

namespace App\Http\Controllers\Academy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::forCompany()->withCount('groups')->latest()->get();
        return view('academy.courses.index', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|string',
        ]);

        Course::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Kurs muvaffaqiyatli qo\'shildi!');
    }

    public function update(Request $request, Course $course)
    {
        if ($course->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|string',
        ]);

        $course->update([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Kurs muvaffaqiyatli tahrirlandi!');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->back()->with('success', 'Kurs o\'chirildi.');
    }
}
