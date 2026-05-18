<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
            'email' => ['required', 'email', 'unique:users,email', 'regex:'.User::INSTITUTIONAL_EMAIL_REGEX],
            'student_id' => 'required|string|max:7|unique:users,student_id|regex:/^\d{1,7}$/',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'email.regex' => 'Email must use the student number with @lnu.edu.ph, for example 2301360@lnu.edu.ph.',
            'student_id.regex' => 'Student ID must contain numbers only, up to 7 digits.',
            'password.regex' => 'Password must include at least one letter and one number.',
        ]);

        $validated['role'] = 'student';
        $validated['email'] = strtolower($validated['email']);
        $validated['student_id'] = trim($validated['student_id']);
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
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($student->id), 'regex:'.User::INSTITUTIONAL_EMAIL_REGEX],
            'student_id' => ['required', 'string', 'max:7', Rule::unique('users', 'student_id')->ignore($student->id), 'regex:/^\d{1,7}$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'email.regex' => 'Email must use the student number with @lnu.edu.ph, for example 2301360@lnu.edu.ph.',
            'student_id.regex' => 'Student ID must contain numbers only, up to 7 digits.',
            'password.regex' => 'Password must include at least one letter and one number.',
        ]);

        $validated['email'] = strtolower($validated['email']);
        $validated['student_id'] = trim($validated['student_id']);

        if (! empty($validated['password'])) {
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

        $studentName = $student->name;
        $studentId = $student->student_id;

        $student->delete();

        ActivityLog::record('student.deleted', 'Deleted student: '.$studentName, $student, auth()->user(), [
            'student_name' => $studentName,
            'student_id' => $studentId,
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully!');
    }
}
