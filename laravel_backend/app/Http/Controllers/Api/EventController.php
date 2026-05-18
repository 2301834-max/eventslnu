<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class EventController extends Controller
{
    /**
     * Get all events with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::with('creator');
        $visibleEventsQuery = Event::query();

        if (! $request->user()?->isAdmin()) {
            $query->whereNotIn('status', ['draft', 'cancelled']);
            $visibleEventsQuery->whereNotIn('status', ['draft', 'cancelled']);
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

        // Search by title, location, or hosting organization
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->user()?->isAdmin()
            ? min(max((int) $request->get('per_page', 200), 1), 200)
            : 200;
        $events = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $events->items(),
            'summary' => [
                'available' => (clone $visibleEventsQuery)
                    ->whereIn('status', ['published', 'ongoing'])
                    ->where('end_date', '>=', now())
                    ->count(),
                'upcoming' => (clone $visibleEventsQuery)
                    ->where('start_date', '>', now())
                    ->whereIn('status', ['published', 'ongoing'])
                    ->count(),
                'today' => (clone $visibleEventsQuery)
                    ->whereDate('start_date', now()->toDateString())
                    ->whereIn('status', ['published', 'ongoing'])
                    ->count(),
                'total_visible' => (clone $visibleEventsQuery)->count(),
            ],
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
            ],
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

        $this->normalizeEventPayload($request);
        $this->normalizePosterUpload($request);

        $validated = $request->validate([
            'title' => 'required|string|unique:events|max:255',
            'organization' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'max_participants' => 'nullable|integer|min:0',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
            'event_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);
        unset($validated['poster']);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        // Handle image upload
        if ($this->hasPosterUpload($request)) {
            $posterPath = $this->storePoster($request);
            $validated['event_image'] = $posterPath;
        }

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event->load('creator'),
        ], 201);
    }

    /**
     * Get a single event with related data
     */
    public function show(Event $event): JsonResponse
    {
        $user = request()->user();

        if (! $user?->isAdmin() && in_array($event->status, ['draft', 'cancelled'], true)) {
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
                'attendance_rate' => $event->getAttendanceRate().'%',
                'is_registration_full' => $event->isRegistrationFull(),
            ],
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

        $this->normalizeEventPayload($request);
        $this->normalizePosterUpload($request);

        $validated = $request->validate([
            'title' => 'nullable|string|unique:events,title,'.$event->id.'|max:255',
            'organization' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published,ongoing,completed,cancelled',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
            'event_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);
        unset($validated['poster']);

        // Handle image upload
        if ($this->hasPosterUpload($request)) {
            $newPosterPath = $this->storePoster($request);

            $this->deletePoster($event);

            $validated['event_image'] = $newPosterPath;
        }

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event->load('creator'),
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
            'message' => 'Event deleted successfully',
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
                'message' => 'Only draft events can be published',
            ], 400);
        }

        $event->update(['status' => 'published']);

        return response()->json([
            'success' => true,
            'message' => 'Event published successfully',
            'data' => $event,
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
            'reason' => 'nullable|string',
        ]);

        $event->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Event cancelled successfully',
            'data' => $event,
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

        if (! in_array($event->status, ['published', 'draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Event cannot be started',
            ], 400);
        }

        $event->update(['status' => 'ongoing']);

        return response()->json([
            'success' => true,
            'message' => 'Event started successfully',
            'data' => $event,
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
            'data' => $event,
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

    private function normalizeEventPayload(Request $request): void
    {
        if ($request->filled('capacity') && ! $request->filled('max_participants')) {
            $request->merge(['max_participants' => $request->input('capacity')]);
        }
    }

    private function storePoster(Request $request): string
    {
        try {
            $path = $request->file('poster')
                ? $request->file('poster')->store('event-posters', 'public')
                : $request->file('event_image')->store('event-posters', 'public');
        } catch (Throwable) {
            $path = false;
        }

        if (! $path || ! Storage::disk('public')->exists($path)) {
            throw ValidationException::withMessages([
                'poster' => ['The event poster could not be uploaded. Please try again.'],
            ]);
        }

        return $path;
    }

    private function normalizePosterUpload(Request $request): void
    {
        if (! $request->hasFile('poster') && $request->hasFile('event_image')) {
            $request->files->set('poster', $request->file('event_image'));
        }
    }

    private function hasPosterUpload(Request $request): bool
    {
        return $request->file('poster')?->isValid()
            || $request->file('event_image')?->isValid();
    }

    private function deletePoster(Event $event): void
    {
        $poster = $event->getRawOriginal('event_image');

        if ($poster && ! filter_var($poster, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($poster)) {
            Storage::disk('public')->delete($poster);
        }
    }
}
