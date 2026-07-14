<?php
/*
 * api.php — Route optimizer backend
 * Currently a passthrough placeholder.
 * To activate Go backend: compile optimizer.go → optimizer.exe,
 * then uncomment the proc_open block below.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw    = file_get_contents('php://input');
$points = json_decode($raw, true);

if (!$points || count($points) < 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Minimum 3 points required']);
    exit;
}

// ── Go binary integration (activate when optimizer.exe is compiled) ──
$binary = __DIR__ . DIRECTORY_SEPARATOR . 'optimizer.exe';

if (file_exists($binary)) {
    $desc = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $proc = proc_open('"' . $binary . '"', $desc, $pipes);

    if (!is_resource($proc)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to start optimizer']);
        exit;
    }

    fwrite($pipes[0], json_encode($points));
    fclose($pipes[0]);

    $out    = stream_get_contents($pipes[1]);
    $err    = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code   = proc_close($proc);

    if ($code !== 0) {
        http_response_code(500);
        echo json_encode(['error' => 'Optimizer error: ' . trim($err)]);
        exit;
    }

    echo $out;
    exit;
}

// ── Fallback: tell client to use JS algorithm ──
echo json_encode(['mode' => 'client-side', 'message' => 'Using JavaScript algorithm']);
