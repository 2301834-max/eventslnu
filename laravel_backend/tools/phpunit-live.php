<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$phpunitBinary = $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'phpunit';

if (function_exists('ob_implicit_flush')) {
    ob_implicit_flush(true);
}

if (defined('STDOUT') && function_exists('stream_set_write_buffer')) {
    @stream_set_write_buffer(STDOUT, 0);
}

if (defined('STDERR') && function_exists('stream_set_write_buffer')) {
    @stream_set_write_buffer(STDERR, 0);
}

if (!file_exists($phpunitBinary)) {
    fwrite(STDERR, "Local PHPUnit binary was not found at vendor/bin/phpunit.\n");
    exit(1);
}

$junitPath = tempnam($projectRoot, 'phpunit-junit-');
$eventsPath = tempnam($projectRoot, 'phpunit-events-');

if ($junitPath === false || $eventsPath === false) {
    fwrite(STDERR, "Unable to create temporary PHPUnit report files.\n");
    exit(1);
}

$userArgs = array_slice($argv, 1);
$delayMs = 0;

foreach ($userArgs as $index => $arg) {
    if (preg_match('/^--delay-ms=(\d+)$/', $arg, $matches) === 1) {
        $delayMs = (int) $matches[1];
        unset($userArgs[$index]);
    }
}

$userArgs = array_values($userArgs);

$phpunitArgs = array_merge(
    [
        $phpunitBinary,
        '--configuration',
        'phpunit.xml',
        '--colors=never',
        '--no-output',
        '--log-junit',
        $junitPath,
        '--log-events-text',
        $eventsPath,
    ],
    $userArgs
);

$command = escapeshellarg(PHP_BINARY);

foreach ($phpunitArgs as $arg) {
    $command .= ' ' . escapeshellarg($arg);
}

$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open($command, $descriptorSpec, $pipes, $projectRoot);

if (!is_resource($process)) {
    fwrite(STDERR, "Unable to start PHPUnit process.\n");
    exit(1);
}

fclose($pipes[0]);
stream_set_blocking($pipes[1], false);
stream_set_blocking($pipes[2], false);

$stdoutBuffer = '';
$stderrBuffer = '';
$eventsOffset = 0;
$eventsBuffer = '';
$statuses = [];
$printedAnyProgress = false;

$writeProgress = static function (string $symbol) use (&$printedAnyProgress, $delayMs): void {
    fwrite(STDOUT, $symbol);

    if (function_exists('fflush')) {
        @fflush(STDOUT);
    }

    if ($delayMs > 0) {
        usleep($delayMs * 1000);
    }

    $printedAnyProgress = true;
};

$consumeEvents = static function (string &$buffer) use (&$statuses, $writeProgress): void {
    while (($newlinePos = strpos($buffer, "\n")) !== false) {
        $line = trim(substr($buffer, 0, $newlinePos));
        $buffer = (string) substr($buffer, $newlinePos + 1);

        if ($line === '') {
            continue;
        }

        if (preg_match('/^Test (Failed|Errored|Ignored|Skipped|Passed) \((.+)\)$/', $line, $matches) === 1) {
            $eventType = $matches[1];
            $signature = $matches[2];

            $statuses[$signature] = match ($eventType) {
                'Failed' => 'F',
                'Errored' => 'E',
                'Ignored', 'Skipped' => 'S',
                default => '.',
            };

            continue;
        }

        if (preg_match('/^Test Finished \((.+)\)$/', $line, $matches) === 1) {
            $signature = $matches[1];
            $writeProgress($statuses[$signature] ?? '.');
            unset($statuses[$signature]);
        }
    }
};

$readAppendedEvents = static function () use ($eventsPath, &$eventsOffset, &$eventsBuffer, $consumeEvents): void {
    clearstatcache(true, $eventsPath);

    if (!file_exists($eventsPath)) {
        return;
    }

    $size = filesize($eventsPath);

    if ($size === false || $size <= $eventsOffset) {
        return;
    }

    $handle = fopen($eventsPath, 'rb');

    if ($handle === false) {
        return;
    }

    if (fseek($handle, $eventsOffset) !== 0) {
        fclose($handle);
        return;
    }

    $chunk = stream_get_contents($handle);
    fclose($handle);

    if ($chunk === false || $chunk === '') {
        return;
    }

    $eventsOffset += strlen($chunk);
    $eventsBuffer .= $chunk;
    $consumeEvents($eventsBuffer);
};

do {
    $read = [$pipes[1], $pipes[2]];
    $write = null;
    $except = null;
    $hasData = stream_select($read, $write, $except, 0, 150000);

    if ($hasData === false) {
        break;
    }

    foreach ($read as $stream) {
        $chunk = stream_get_contents($stream);

        if ($chunk === false || $chunk === '') {
            continue;
        }

        if ($stream === $pipes[1]) {
            $stdoutBuffer .= $chunk;
        } else {
            $stderrBuffer .= $chunk;
        }
    }

    $readAppendedEvents();
    $status = proc_get_status($process);
} while ($status['running']);

$stdoutRemainder = stream_get_contents($pipes[1]);
$stderrRemainder = stream_get_contents($pipes[2]);

if ($stdoutRemainder !== false && $stdoutRemainder !== '') {
    $stdoutBuffer .= $stdoutRemainder;
}

if ($stderrRemainder !== false && $stderrRemainder !== '') {
    $stderrBuffer .= $stderrRemainder;
}

fclose($pipes[1]);
fclose($pipes[2]);

$readAppendedEvents();

$finalEvents = @file_get_contents($eventsPath);
if ($finalEvents !== false && strlen($finalEvents) > $eventsOffset) {
    $eventsBuffer .= substr($finalEvents, $eventsOffset);
    $consumeEvents($eventsBuffer);
}

$exitCode = proc_close($process);

if ($printedAnyProgress) {
    echo PHP_EOL . PHP_EOL;
}

if (!file_exists($junitPath)) {
    $message = trim($stderrBuffer . "\n" . $stdoutBuffer);
    fwrite(STDERR, ($message !== '' ? $message : 'PHPUnit finished without producing a JUnit report.') . "\n");
    exit($exitCode);
}

$xml = simplexml_load_file($junitPath);

if ($xml === false) {
    fwrite(STDERR, "Unable to parse generated JUnit report.\n");
    exit($exitCode === 0 ? 1 : $exitCode);
}

$testCases = [];

$collectTestCases = static function (\SimpleXMLElement $node) use (&$collectTestCases, &$testCases): void {
    foreach ($node->testcase as $testCase) {
        $testCases[] = $testCase;
    }

    foreach ($node->testsuite as $childSuite) {
        $collectTestCases($childSuite);
    }
};

if ($xml->getName() === 'testsuites') {
    foreach ($xml->testsuite as $testSuite) {
        $collectTestCases($testSuite);
    }
} elseif ($xml->getName() === 'testsuite') {
    $collectTestCases($xml);
}

$topSuite = null;

if ($xml->getName() === 'testsuites' && isset($xml->testsuite[0])) {
    $topSuite = $xml->testsuite[0];
} elseif ($xml->getName() === 'testsuite') {
    $topSuite = $xml;
}

$tests = $topSuite ? (int) $topSuite['tests'] : count($testCases);
$assertions = $topSuite && isset($topSuite['assertions']) ? (int) $topSuite['assertions'] : null;
$time = $topSuite && isset($topSuite['time']) ? (float) $topSuite['time'] : null;

$failures = [];
$errors = [];
$skipped = [];

foreach ($testCases as $testCase) {
    $signature = sprintf(
        '%s::%s',
        (string) $testCase['class'],
        (string) $testCase['name']
    );

    if (isset($testCase->failure)) {
        $failures[] = $signature . PHP_EOL . trim((string) $testCase->failure);
        continue;
    }

    if (isset($testCase->error)) {
        $errors[] = $signature . PHP_EOL . trim((string) $testCase->error);
        continue;
    }

    if (isset($testCase->skipped)) {
        $skipped[] = $signature;
    }
}

if ($time !== null) {
    echo 'Time: ' . number_format($time, 3) . 's' . PHP_EOL;
}

$summary = 'Tests: ' . $tests;

if ($assertions !== null) {
    $summary .= ', Assertions: ' . $assertions;
}

echo $summary . PHP_EOL;

if ($failures !== []) {
    echo PHP_EOL . 'Failures:' . PHP_EOL;

    foreach ($failures as $index => $failure) {
        echo ($index + 1) . '. ' . $failure . PHP_EOL . PHP_EOL;
    }
}

if ($errors !== []) {
    echo PHP_EOL . 'Errors:' . PHP_EOL;

    foreach ($errors as $index => $error) {
        echo ($index + 1) . '. ' . $error . PHP_EOL . PHP_EOL;
    }
}

if ($skipped !== []) {
    echo PHP_EOL . 'Skipped:' . PHP_EOL;

    foreach ($skipped as $signature) {
        echo '- ' . $signature . PHP_EOL;
    }
}

if (trim($stderrBuffer) !== '') {
    fwrite(STDERR, trim($stderrBuffer) . PHP_EOL);
}

@unlink($junitPath);
@unlink($eventsPath);

exit($exitCode);
