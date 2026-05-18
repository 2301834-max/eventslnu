<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$phpunitBinary = $projectRoot.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'phpunit';

if (! file_exists($phpunitBinary)) {
    fwrite(STDERR, "Local PHPUnit binary was not found at vendor/bin/phpunit.\n");
    exit(1);
}

$junitPath = tempnam($projectRoot, 'phpunit-junit-');

if ($junitPath === false) {
    fwrite(STDERR, "Unable to create a temporary JUnit report file.\n");
    exit(1);
}

$userArgs = array_slice($argv, 1);
$phpunitArgs = array_merge(
    [
        $phpunitBinary,
        '--configuration',
        'phpunit.xml',
        '--colors=never',
        '--no-output',
        '--log-junit',
        $junitPath,
    ],
    $userArgs
);

$command = escapeshellarg(PHP_BINARY);

foreach ($phpunitArgs as $arg) {
    $command .= ' '.escapeshellarg($arg);
}

$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open($command, $descriptorSpec, $pipes, $projectRoot);

if (! is_resource($process)) {
    fwrite(STDERR, "Unable to start PHPUnit process.\n");
    exit(1);
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

$exitCode = proc_close($process);

if (! file_exists($junitPath)) {
    $message = trim($stderr."\n".$stdout);
    fwrite(STDERR, ($message !== '' ? $message : 'PHPUnit finished without producing a JUnit report.')."\n");
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

$progress = '';
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
        $progress .= 'F';
        $failures[] = $signature.PHP_EOL.trim((string) $testCase->failure);

        continue;
    }

    if (isset($testCase->error)) {
        $progress .= 'E';
        $errors[] = $signature.PHP_EOL.trim((string) $testCase->error);

        continue;
    }

    if (isset($testCase->skipped)) {
        $progress .= 'S';
        $skipped[] = $signature;

        continue;
    }

    $progress .= '.';
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

echo $progress.PHP_EOL.PHP_EOL;

if ($time !== null) {
    echo 'Time: '.number_format($time, 3).'s'.PHP_EOL;
}

$summary = 'Tests: '.$tests;

if ($assertions !== null) {
    $summary .= ', Assertions: '.$assertions;
}

echo $summary.PHP_EOL;

if ($failures !== []) {
    echo PHP_EOL.'Failures:'.PHP_EOL;

    foreach ($failures as $index => $failure) {
        echo ($index + 1).'. '.$failure.PHP_EOL.PHP_EOL;
    }
}

if ($errors !== []) {
    echo PHP_EOL.'Errors:'.PHP_EOL;

    foreach ($errors as $index => $error) {
        echo ($index + 1).'. '.$error.PHP_EOL.PHP_EOL;
    }
}

if ($skipped !== []) {
    echo PHP_EOL.'Skipped:'.PHP_EOL;

    foreach ($skipped as $signature) {
        echo '- '.$signature.PHP_EOL;
    }
}

@unlink($junitPath);

exit($exitCode);
