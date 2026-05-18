@extends('super-admin.layout')

@section('title', 'Reports')

@section('content')
<section class="grid gap-4 md:grid-cols-3">
    @foreach([
        ['label' => 'Admins', 'value' => $adminsCount],
        ['label' => 'Events', 'value' => $eventsCount],
        ['label' => 'Activity Logs', 'value' => $logsCount],
    ] as $item)
        <article class="rounded-[1.35rem] border border-blue-100 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ $item['label'] }}</p>
            <p class="mt-3 text-4xl font-bold">{{ number_format($item['value']) }}</p>
        </article>
    @endforeach
</section>
@endsection
