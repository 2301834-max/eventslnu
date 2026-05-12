<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStudentController extends Controller
{
    /**
     * Show all students
     */
    public function index(): View
    {
        $students = User::where('role', 'student')
            ->withCount('registrations')
            ->latest()
            ->paginate(15);

        return view('admin.students.index', ['students' => $students]);
    }

    /**
     * Show create student form
     */
    public function create(): View
    {
        return view('admin.students.create');
    }

    /**
     * Store new student
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email', 'regex:' . User::INSTITUTIONAL_EMAIL_REGEX],
            'student_id' => 'required|string|max:50|unique:users,student_id|regex:/^[A-Za-z0-9-]+$/',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.regex' => 'Please use your institutional email ending in @lnu.edu.ph.',
            'student_id.regex' => 'Student ID may only contain letters, numbers, and hyphens.',
        ]);

        $validated['role'] = 'student';
        $validated['email'] = strtolower($validated['email']);
        $validated['student_id'] = strtoupper($validated['student_id']);
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully!');
    }

    /**
     * Show student details
     */
    public function show(User $student): View
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $registrations = $student->registrations()->with('event')->paginate(10);
        $attendanceRecords = $student->attendanceRecords()->with('event')->paginate(10);

        return view('admin.students.show', [
            'student' => $student,
            'registrations' => $registrations,
            'attendanceRecords' => $attendanceRecords,
        ]);
    }

    /**
     * Show edit student form
     */
    public function edit(User $student): View
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        return view('admin.students.edit', ['student' => $student]);
    }

    /**
     * Update student
     */
    public function update(Request $request, User $student): RedirectResponse
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($student->id), 'regex:' . User::INSTITUTIONAL_EMAIL_REGEX],
            'student_id' => ['required', 'string', 'max:50', Rule::unique('users', 'student_id')->ignore($student->id), 'regex:/^[A-Za-z0-9-]+$/'],
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'email.regex' => 'Please use your institutional email ending in @lnu.edu.ph.',
            'student_id.regex' => 'Student ID may only contain letters, numbers, and hyphens.',
        ]);

        $validated['email'] = strtolower($validated['email']);
        $validated['student_id'] = strtoupper($validated['student_id']);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully!');
    }

    /**
     * Delete student
     */
    public function destroy(User $student): RedirectResponse
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully!');
    }
}
