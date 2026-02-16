<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$type = $_GET['type'] ?? 'sessions';
$dataFile = $type . '.json';

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
        echo json_encode(['error' => ucfirst(rtrim($GLOBALS['dataFile'], '.json')) . ' name required']);
        return;
    }

    $items = json_decode(file_get_contents($GLOBALS['dataFile']), true);
    $items[] = [
        'name' => $input['name'],
        'date' => $input['date'] ?? '',
        'desc' => $input['desc'] ?? ''
    ];
    
    file_put_contents($GLOBALS['dataFile'], json_encode($items, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'items' => $items]);
}

function deleteSession() {
    global $dataFile;
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['index'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Index required']);
        return;
    }

    $items = json_decode(file_get_contents($GLOBALS['dataFile']), true);
    array_splice($items, $input['index'], 1);
    file_put_contents($GLOBALS['dataFile'], json_encode($items, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'items' => $items]);
}

function clearSessions() {
    global $dataFile;
    file_put_contents($dataFile, json_encode([]));
    echo json_encode(['success' => true]);
}

// Alias for consistency with new type parameter system
function clearItems() {
    clearSessions();
}

function uploadSessions() {
    global $dataFile;
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['sessions']) || !is_array($input['sessions'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Items array required']);
        return;
    }

    // Validate each item has a name
    foreach ($input['sessions'] as $session) {
        if (!isset($session['name']) || empty($session['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All items must have a name']);
            return;
        }
    }

    $items = $input['sessions'];
    file_put_contents($GLOBALS['dataFile'], json_encode($items, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'items' => $items]);
}
?>
