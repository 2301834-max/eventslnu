<?php

declare(strict_types=1);

$timezone = new DateTimeZone('Asia/Manila');
$currentDateInput = $argv[1] ?? 'today';
$currentDate = (new DateTimeImmutable($currentDateInput, $timezone))->setTime(0, 0);

$totalRecords = 3000;
$years = [2023, 2024, 2025, 2026];
$perYear = intdiv($totalRecords, count($years));
$remainder = $totalRecords % count($years);
$ongoingRecords = 10;

$outputDirectory = dirname(__DIR__).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'generated';
$outputFile = $outputDirectory.DIRECTORY_SEPARATOR.'event_records_3000.csv';

if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0777, true) && ! is_dir($outputDirectory)) {
    fwrite(STDERR, "Unable to create output directory: {$outputDirectory}".PHP_EOL);
    exit(1);
}

$records = [];

foreach ($years as $index => $year) {
    $yearCount = $perYear + ($index < $remainder ? 1 : 0);
    $forcedOngoingCount = $year === (int) $currentDate->format('Y') ? min($ongoingRecords, $yearCount) : 0;

    for ($i = 0; $i < $yearCount; $i++) {
        $eventDate = $i < $forcedOngoingCount
            ? $currentDate
            : randomDateInYear($year, $timezone, $year === (int) $currentDate->format('Y') ? $currentDate : null);

        $records[] = [
            'event_name' => generateEventName(),
            'event_date' => $eventDate->format('Y-m-d'),
            'event_status' => determineStatus($eventDate, $currentDate),
        ];
    }
}

shuffle($records);

$handle = fopen($outputFile, 'wb');

if ($handle === false) {
    fwrite(STDERR, "Unable to open output file: {$outputFile}".PHP_EOL);
    exit(1);
}

fputcsv($handle, ['event_name', 'event_date', 'event_status']);

foreach ($records as $record) {
    fputcsv($handle, [
        $record['event_name'],
        $record['event_date'],
        $record['event_status'],
    ]);
}

fclose($handle);

$yearSummary = [];
$statusSummary = [
    'Completed' => 0,
    'Ongoing' => 0,
    'Upcoming' => 0,
];

foreach ($records as $record) {
    $year = substr($record['event_date'], 0, 4);
    $yearSummary[$year] = ($yearSummary[$year] ?? 0) + 1;
    $statusSummary[$record['event_status']]++;
}

ksort($yearSummary);

fwrite(STDOUT, "Generated {$totalRecords} event records.".PHP_EOL);
fwrite(STDOUT, 'Current date used for status calculation: '.$currentDate->format('Y-m-d').PHP_EOL);
fwrite(STDOUT, "Output file: {$outputFile}".PHP_EOL);
fwrite(STDOUT, 'Year distribution: '.json_encode($yearSummary, JSON_UNESCAPED_SLASHES).PHP_EOL);
fwrite(STDOUT, 'Status distribution: '.json_encode($statusSummary, JSON_UNESCAPED_SLASHES).PHP_EOL);

function randomDateInYear(int $year, DateTimeZone $timezone, ?DateTimeImmutable $excludeDate = null): DateTimeImmutable
{
    $start = new DateTimeImmutable("{$year}-01-01", $timezone);
    $end = $start->modify('+1 year')->modify('-1 day');

    do {
        $offset = random_int(0, (int) $start->diff($end)->days);
        $candidate = $start->modify("+{$offset} days");
    } while ($excludeDate !== null && $candidate->format('Y-m-d') === $excludeDate->format('Y-m-d'));

    return $candidate;
}

function determineStatus(DateTimeImmutable $eventDate, DateTimeImmutable $currentDate): string
{
    $eventValue = $eventDate->format('Y-m-d');
    $currentValue = $currentDate->format('Y-m-d');

    if ($eventValue === $currentValue) {
        return 'Ongoing';
    }

    return $eventValue > $currentValue ? 'Upcoming' : 'Completed';
}

function generateEventName(): string
{
    static $templates = [
        '%s %s',
        '%s %s %s',
        '%s and %s %s',
    ];

    static $modifiers = [
        'Annual',
        'Campus',
        'Student',
        'Faculty',
        'Community',
        'Regional',
        'Interdepartmental',
        'Young Leaders',
        'Future-Ready',
        'Applied',
        'Creative',
        'Inclusive',
        'Industry-Academe',
        'University-Wide',
        'College',
    ];

    static $topics = [
        'Leadership',
        'Research',
        'Innovation',
        'Career Development',
        'Wellness',
        'Mental Health',
        'Digital Literacy',
        'Cybersecurity',
        'Data Analytics',
        'Robotics',
        'Entrepreneurship',
        'Financial Literacy',
        'Environmental Awareness',
        'Volunteerism',
        'Community Outreach',
        'Teaching Excellence',
        'STEM',
        'Arts and Culture',
        'Campus Journalism',
        'Public Speaking',
        'Debate',
        'Hospitality',
        'Tourism',
        'Engineering Design',
        'Science Education',
        'Nursing Care',
        'Psychology',
        'Language and Literature',
        'Student Government',
        'Disaster Preparedness',
        'Media Production',
        'Business Management',
        'Inclusive Education',
        'Alumni Engagement',
        'Athletics',
        'Service Learning',
        'Sustainability',
        'Academic Excellence',
        'Health Awareness',
        'Teacher Training',
    ];

    static $eventTypes = [
        'Seminar',
        'Workshop',
        'Conference',
        'Summit',
        'Forum',
        'Expo',
        'Fair',
        'Symposium',
        'Bootcamp',
        'Orientation',
        'Colloquium',
        'Assembly',
        'Showcase',
        'Training',
        'Camp',
        'Congress',
        'Dialogue',
        'Series',
        'Learning Session',
        'Mentoring Day',
    ];

    $template = $templates[array_rand($templates)];
    $modifier = $modifiers[array_rand($modifiers)];
    $primaryTopic = $topics[array_rand($topics)];
    $secondaryTopic = $topics[array_rand($topics)];
    $eventType = $eventTypes[array_rand($eventTypes)];

    while ($secondaryTopic === $primaryTopic) {
        $secondaryTopic = $topics[array_rand($topics)];
    }

    return match ($template) {
        '%s %s' => sprintf($template, $primaryTopic, $eventType),
        '%s %s %s' => sprintf($template, $modifier, $primaryTopic, $eventType),
        default => sprintf($template, $primaryTopic, $secondaryTopic, $eventType),
    };
}
