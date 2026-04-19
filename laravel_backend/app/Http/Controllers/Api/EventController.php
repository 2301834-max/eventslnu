<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Get all events with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::with('creator');

        if (!$request->user()?->isAdmin()) {
            $query->whereNotIn('status', ['draft', 'cancelled']);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by upcoming/ongoing/completed
        if ($request->has('filter')) {
            match ($request->filter) {
                'upcoming' => $query->upcoming(),
                'ongoing' => $query->ongoing(),
                'completed' => $query->completed(),
                default => $query
            };
        }

        // Search by title or location
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $events = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $events->items(),
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
            ]
        ]);
    }

    /**
     * Create a new event
     */
    public function store(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'title' => 'required|string|unique:events|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'max_participants' => 'nullable|integer|min:0',
            'event_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        // Handle image upload
        if ($request->hasFile('event_image')) {
            $path = $request->file('event_image')->store('events', 'public');
            $validated['event_image'] = $path;
        }

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event->load('creator')
        ], 201);
    }

    /**
     * Get a single event with related data
     */
    public function show(Event $event): JsonResponse
    {
        $user = request()->user();

        if (!$user?->isAdmin() && in_array($event->status, ['draft', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
            ], 404);
        }

        $relations = ['creator'];
        if ($user?->isAdmin()) {
            $relations = [
                'creator',
                'registrations' => function ($query) {
                    $query->with('user');
                },
                'attendanceRecords' => function ($query) {
                    $query->with('user')->latest()->limit(50);
                },
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $event->load($relations),
            'statistics' => [
                'total_registrations' => $event->registrations()->count(),
                'approved_registrations' => $event->getApprovedRegistrationsCount(),
                'pending_registrations' => $event->registrations()->pending()->count(),
                'total_attended' => $event->getAttendanceCount(),
                'attendance_rate' => $event->getAttendanceRate() . '%',
                'is_registration_full' => $event->isRegistrationFull(),
            ]
        ]);
    }

    /**
     * Update an event
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'title' => 'nullable|string|unique:events,title,' . $event->id . '|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published,ongoing,completed,cancelled',
            'event_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // Handle image upload
        if ($request->hasFile('event_image')) {
            // Delete old image if exists
            if ($event->event_image) {
                Storage::disk('public')->delete($event->event_image);
            }
            $path = $request->file('event_image')->store('events', 'public');
            $validated['event_image'] = $path;
        }

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event->load('creator')
        ]);
    }

    /**
     * Delete an event
     */
    public function destroy(Event $event): JsonResponse
    {
        if ($response = $this->ensureAdmin(request())) {
            return $response;
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }

    /**
     * Publish an event (change status to published)
     */
    public function publish(Event $event): JsonResponse
    {
        if ($response = $this->ensureAdmin(request())) {
            return $response;
        }

        if ($event->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft events can be published'
            ], 400);
        }

        $event->update(['status' => 'published']);

        return response()->json([
            'success' => true,
            'message' => 'Event published successfully',
            'data' => $event
        ]);
    }

    /**
     * Cancel an event
     */
    public function cancel(Event $event, Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate([
            'reason' => 'nullable|string'
        ]);

        $event->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Event cancelled successfully',
            'data' => $event
        ]);
    }

    /**
     * Start an event (change status to ongoing)
     */
    public function start(Event $event): JsonResponse
    {
        if ($response = $this->ensureAdmin(request())) {
            return $response;
        }

        if (!in_array($event->status, ['published', 'draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Event cannot be started'
            ], 400);
        }

        $event->update(['status' => 'ongoing']);

        return response()->json([
            'success' => true,
            'message' => 'Event started successfully',
            'data' => $event
        ]);
    }

    /**
     * End an event (change status to completed)
     */
    public function end(Event $event): JsonResponse
    {
        if ($response = $this->ensureAdmin(request())) {
            return $response;
        }

        $event->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Event completed successfully',
            'data' => $event
        ]);
    }

    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if ($request->user()?->isAdmin()) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Only administrators can manage events.',
        ], 403);
    }
}
