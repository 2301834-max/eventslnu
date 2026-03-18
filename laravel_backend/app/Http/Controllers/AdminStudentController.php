<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['role'] = 'student';
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
            'email' => 'required|email|unique:users,email,' . $student->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

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
