<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = getDbConnection();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? '';

    if ($method === 'GET') {
        $faq = $pdo ? $pdo->query('SELECT * FROM faq ORDER BY created_at DESC')->fetchAll() : [];
        $inquiries = $pdo ? $pdo->query('SELECT * FROM inquiries ORDER BY created_at DESC')->fetchAll() : [];
        $logs = $pdo ? $pdo->query('SELECT * FROM chat_logs ORDER BY created_at DESC LIMIT 20')->fetchAll() : [];

        echo json_encode([
            'faq' => $faq,
            'inquiries' => $inquiries,
            'logs' => $logs,
        ]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        if ($action === 'faq' || (!empty($input['keyword']) && !empty($input['response']))) {
            $keyword = trim((string)($input['keyword'] ?? ''));
            $response = trim((string)($input['response'] ?? ''));

            if ($keyword === '' || $response === '') {
                echo json_encode(['success' => false, 'message' => 'Keyword and response are required.']);
                exit;
            }

            if ($pdo) {
                $stmt = $pdo->prepare('INSERT INTO faq (keyword, response) VALUES (?, ?)');
                $stmt->execute([$keyword, $response]);
            }

            echo json_encode(['success' => true, 'message' => 'FAQ item added successfully.']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Unknown admin action.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unsupported request method.']);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Admin request failed.',
        'error' => $e->getMessage()
    ]);
}
