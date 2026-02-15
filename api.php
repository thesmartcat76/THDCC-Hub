<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$dataFile = 'sessions.json';

// Initialize file if it doesn't exist
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get':
        getSessions();
        break;
    case 'add':
        addSession();
        break;
    case 'delete':
        deleteSession();
        break;
    case 'clear':
        clearSessions();
        break;
    case 'upload':
        uploadSessions();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function getSessions() {
    global $dataFile;
    $data = json_decode(file_get_contents($dataFile), true);
    echo json_encode($data);
}

function addSession() {
    global $dataFile;
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Session name required']);
        return;
    }

    $sessions = json_decode(file_get_contents($dataFile), true);
    $sessions[] = [
        'name' => $input['name'],
        'date' => $input['date'] ?? '',
        'desc' => $input['desc'] ?? ''
    ];
    
    file_put_contents($dataFile, json_encode($sessions, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'sessions' => $sessions]);
}

function deleteSession() {
    global $dataFile;
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['index'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Index required']);
        return;
    }

    $sessions = json_decode(file_get_contents($dataFile), true);
    array_splice($sessions, $input['index'], 1);
    file_put_contents($dataFile, json_encode($sessions, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'sessions' => $sessions]);
}

function clearSessions() {
    global $dataFile;
    file_put_contents($dataFile, json_encode([]));
    echo json_encode(['success' => true]);
}

function uploadSessions() {
    global $dataFile;
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['sessions']) || !is_array($input['sessions'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Sessions array required']);
        return;
    }

    // Validate each session has a name
    foreach ($input['sessions'] as $session) {
        if (!isset($session['name']) || empty($session['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All sessions must have a name']);
            return;
        }
    }

    $sessions = $input['sessions'];
    file_put_contents($dataFile, json_encode($sessions, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'sessions' => $sessions]);
}
?>
