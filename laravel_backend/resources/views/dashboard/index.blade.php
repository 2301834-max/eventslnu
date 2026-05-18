@extends('dashboard.layout')

@section('title', 'Dashboard')

@section('content')
@php
    $availableEvents = $availableEvents ?? ($totalEvents - $completedEvents);
    $studentRegistrations = auth()->user()
        ? \App\Models\Registration::where('user_id', auth()->id())->count()
        : 0;
    $studentApproved = auth()->user()
        ? \App\Models\Registration::where('user_id', auth()->id())->where('status', 'approved')->count()
        : 0;
@endphp

<div class="student-dashboard">
    <section class="student-hero panel">
        <div>
            <p class="student-eyebrow">Student portal</p>
            <h1>Welcome, {{ auth()->user()->name ?? 'Student' }}</h1>
            <p>
                Track campus events, registrations, and attendance from one quiet workspace.
            </p>
        </div>
        <div class="student-id-card">
            <span>Student ID</span>
            <strong>{{ auth()->user()->student_id ?? 'Not assigned' }}</strong>
        </div>
    </section>

    <section class="stats-grid">
        <article class="stat-card blue">
            <div class="stat-label">Available Events</div>
            <div class="stat-value">{{ number_format($availableEvents) }}</div>
            <p class="student-card-meta">{{ number_format($upcomingEvents) }} upcoming activities</p>
        </article>
        <article class="stat-card orange">
            <div class="stat-label">My Registrations</div>
            <div class="stat-value">{{ number_format($studentRegistrations) }}</div>
            <p class="student-card-meta">{{ number_format($studentApproved) }} approved</p>
        </article>
        <article class="stat-card green">
            <div class="stat-label">Ongoing Events</div>
            <div class="stat-value">{{ number_format($ongoingEvents) }}</div>
            <p class="student-card-meta">Open for live attendance</p>
        </article>
        <article class="stat-card red">
            <div class="stat-label">Completed Events</div>
            <div class="stat-value">{{ number_format($completedEvents) }}</div>
            <p class="student-card-meta">Closed activities</p>
        </article>
    </section>

    <section class="student-grid">
        <div class="panel student-actions">
            <div class="student-section-head">
                <div>
                    <p class="student-eyebrow">Shortcuts</p>
                    <h2>Quick Actions</h2>
                </div>
            </div>

            <div class="student-action-list">
                <a href="{{ route('dashboard.events') }}" class="student-action">
                    <span class="student-action-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                    </span>
                    <span>
                        <strong>Browse campus events</strong>
                        <small>Review schedules, venues, and registration status.</small>
                    </span>
                </a>
                <a href="{{ route('dashboard.registrations') }}" class="student-action">
                    <span class="student-action-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5h6"/><path d="M9 3h6v4H9z"/><rect x="5" y="5" width="14" height="17" rx="2"/><path d="m9 15 2 2 4-5"/></svg>
                    </span>
                    <span>
                        <strong>Check registrations</strong>
                        <small>See pending and approved event requests.</small>
                    </span>
                </a>
                <a href="{{ route('dashboard.attendance') }}" class="student-action">
                    <span class="student-action-icon amber">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9"/></svg>
                    </span>
                    <span>
                        <strong>View attendance</strong>
                        <small>Confirm your check-in history.</small>
                    </span>
                </a>
            </div>
        </div>

        <div class="panel student-note">
            <p class="student-eyebrow">Reminder</p>
            <h2>Before attending</h2>
            <p>Bring your school ID and keep your phone ready for event check-in. Confirm your registration status before going to the venue.</p>
        </div>
    </section>

    <section class="panel student-events">
        <div class="student-section-head">
            <div>
                <p class="student-eyebrow">Latest</p>
                <h2>Recent Events</h2>
            </div>
            <a href="{{ route('dashboard.events') }}" class="student-link">View all events</a>
        </div>

        @if($recentEvents->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Schedule</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentEvents as $event)
                            @php
                                $badgeClass = match ($event->status) {
                                    'published' => 'info',
                                    'ongoing' => 'success',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'warning',
                                };
                            @endphp
                            <tr>
                                <td><strong>{{ $event->title }}</strong><br><span class="student-muted">{{ $event->organization ?? 'Not specified' }}</span></td>
                                <td>{{ $event->location }}</td>
                                <td><span class="badge badge-{{ $badgeClass }}">{{ ucfirst($event->status) }}</span></td>
                                <td>{{ $event->start_date->format('M d, Y h:i A') }}</td>
                                <td>
                                    <a href="{{ route('dashboard.event-detail', $event) }}" class="btn btn-primary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="student-empty">No events found.</p>
        @endif
    </section>
</div>

<style>
    .student-dashboard {
        display: grid;
        gap: 1.5rem;
    }

    .student-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        padding: 1.75rem;
        background: linear-gradient(135deg, #061636, #0b4aa2);
        color: #fff;
        overflow: hidden;
    }

    .student-hero h1 {
        margin: 0.35rem 0 0;
        font-size: clamp(1.8rem, 3vw, 2.8rem);
        line-height: 1;
        font-weight: 900;
    }

    .student-hero p {
        margin: 0.8rem 0 0;
        max-width: 42rem;
        color: rgba(239, 246, 255, 0.82);
        line-height: 1.6;
    }

    .student-eyebrow {
        margin: 0;
        color: inherit;
        opacity: 0.72;
        font-size: 0.75rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.16em;
    }

    .student-id-card {
        min-width: 14rem;
        border-radius: 1rem;
        border: 1px solid rgba(255,255,255,0.20);
        background: rgba(255,255,255,0.10);
        padding: 1rem;
    }

    .student-id-card span {
        display: block;
        color: rgba(239, 246, 255, 0.72);
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.14em;
    }

    .student-id-card strong {
        display: block;
        margin-top: 0.5rem;
        font-size: 1.2rem;
    }

    .student-card-meta,
    .student-muted {
        margin: 0.45rem 0 0;
        color: #66758c;
        font-size: 0.85rem;
    }

    .student-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(18rem, 0.5fr);
        gap: 1rem;
    }

    .student-actions,
    .student-events,
    .student-note {
        padding: 1.25rem;
    }

    .student-section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .student-section-head h2,
    .student-note h2 {
        margin: 0.25rem 0 0;
        font-size: 1.25rem;
        font-weight: 900;
    }

    .student-action-list {
        display: grid;
        gap: 0.8rem;
    }

    .student-action {
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid #dce6f2;
        border-radius: 1rem;
        background: #fff;
        padding: 1rem;
        text-decoration: none;
        transition: transform 160ms ease, box-shadow 160ms ease;
    }

    .student-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 45px rgba(8, 29, 80, 0.10);
    }

    .student-action-icon {
        display: grid;
        place-items: center;
        width: 3rem;
        height: 3rem;
        border-radius: 0.9rem;
    }

    .student-action-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .student-action-icon.blue { background: #dbeafe; color: #1d4ed8; }
    .student-action-icon.green { background: #dcfce7; color: #047857; }
    .student-action-icon.amber { background: #fef3c7; color: #b45309; }

    .student-action strong {
        display: block;
        font-weight: 850;
    }

    .student-action small {
        display: block;
        margin-top: 0.25rem;
        color: #66758c;
        font-size: 0.86rem;
    }

    .student-note {
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .student-note p:last-child {
        color: #526179;
        line-height: 1.65;
    }

    .student-link {
        color: #0b4aa2;
        font-size: 0.9rem;
        font-weight: 850;
        text-decoration: none;
    }

    .student-empty {
        color: #66758c;
        margin: 0;
    }

    @media (max-width: 900px) {
        .student-hero,
        .student-section-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .student-grid {
            grid-template-columns: 1fr;
        }

        .student-id-card {
            width: 100%;
        }
    }
</style>
@endsection
