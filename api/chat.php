<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';

function normalizeText($text)
{
    $text = strtolower(trim((string)$text));
    $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function containsAny($text, array $keywords)
{
    foreach ($keywords as $keyword) {
        if (strpos($text, strtolower(trim($keyword))) !== false) {
            return true;
        }
    }

    return false;
}

function getCarModelCatalog()
{
    return [
        'toyota corolla' => 18500,
        'honda civic' => 19200,
        'ford ranger' => 24800,
        'bmw x5' => 41000,
        'mercedes c class' => 38500,
    ];
}

function extractModelFromText($text)
{
    $text = normalizeText($text);
    $catalog = getCarModelCatalog();
    $bestModel = null;
    $bestLength = 0;

    foreach ($catalog as $model => $price) {
        if (strpos($text, strtolower($model)) !== false) {
            $length = strlen($model);
            if ($length > $bestLength) {
                $bestModel = $model;
                $bestLength = $length;
            }
        }
    }

    return $bestModel;
}

function getCarPricingReply($message)
{
    $text = normalizeText($message);
    $catalog = getCarModelCatalog();

    $model = extractModelFromText($text);
    if ($model !== null) {
        $price = $catalog[$model];
        return ucfirst($model) . ' has a sample import price of $' . number_format($price, 0) . ' USD. Final pricing can change with shipping, taxes, and destination country.';
    }

    if (containsAny($text, ['model', 'models', 'car model', 'car models', 'cars', 'available cars', 'what cars', 'list of cars'])) {
        $items = [];
        foreach ($catalog as $model => $price) {
            $items[] = ucfirst($model) . ' - $' . number_format($price, 0) . ' USD';
        }

        return 'Our current sample car models and pricing are: ' . implode('; ', $items) . '.';
    }

    if (containsAny($text, ['how much', 'price', 'cost', 'quote', 'estimate', 'valuation', 'amount'])) {
        return 'The cost of importing a car depends on the vehicle type, shipping route, customs duties, and registration fees. Please share the car model and destination country for a more accurate estimate.';
    }

    return null;
}

function getReplyFromKeywords($message)
{
    $text = normalizeText($message);

    $modelReply = getCarPricingReply($text);
    if ($modelReply !== null) {
        return $modelReply;
    }

    if (containsAny($text, ['price', 'cost', 'quote', 'estimate', 'how much', 'amount'])) {
        return 'The cost of importing a car depends on the vehicle type, shipping route, customs duties, and registration fees. Please share the car model and destination country for a more accurate estimate.';
    }

    if (strpos($message, 'shipping') !== false || strpos($message, 'delivery') !== false || strpos($message, 'shipment') !== false) {
        return 'Shipping usually takes 3 to 8 weeks depending on the origin port, shipping method, and customs clearance time.';
    }

    if (strpos($message, 'document') !== false || strpos($message, 'papers') !== false || strpos($message, 'documents') !== false) {
        return 'Common documents include the vehicle title, bill of sale, export certificate, passport copy, and customs paperwork.';
    }

    if (strpos($message, 'import') !== false || strpos($message, 'process') !== false || strpos($message, 'procedure') !== false) {
        return 'Our import process includes car selection, quote approval, shipping arrangement, customs documentation, and final delivery to your location.';
    }

    if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false || strpos($message, 'hey') !== false) {
        return 'Hello! I can help with car import pricing, shipping, paperwork, customs, and delivery status.';
    }

    if (strpos($message, 'status') !== false || strpos($message, 'track') !== false) {
        return 'You can track the shipment after booking by checking the shipping reference number or contacting our support team for updates.';
    }

    if (strpos($message, 'payment') !== false || strpos($message, 'deposit') !== false || strpos($message, 'pay') !== false) {
        return 'Most imports require an initial deposit before shipping, with the remaining balance due before the final delivery stage.';
    }

    if (strpos($message, 'custom') !== false || strpos($message, 'duty') !== false || strpos($message, 'tax') !== false) {
        return 'Customs charges and taxes usually depend on the vehicle value, destination country, regulations, and import duty rates.';
    }

    if (strpos($message, 'inspection') !== false || strpos($message, 'regist') !== false || strpos($message, 'registration') !== false) {
        return 'Vehicle inspection and registration are usually handled after arrival, based on the destination country requirements and local compliance rules.';
    }

    return 'I can help with car import costs, shipping time, documents, customs, payment, and delivery. Please ask a question about one of those topics.';
}

function handleLeadSubmission($lead, $pdo)
{
    $name = trim((string)($lead['name'] ?? ''));
    $phone = trim((string)($lead['phone'] ?? ''));
    $email = trim((string)($lead['email'] ?? ''));
    $carModel = trim((string)($lead['car_model'] ?? ''));
    $country = trim((string)($lead['country'] ?? ''));
    $message = trim((string)($lead['message'] ?? ''));

    if ($name === '' || $phone === '' || $email === '' || $carModel === '' || $country === '') {
        return ['success' => false, 'reply' => 'Please complete your name, phone number, email, car model, and destination country.'];
    }

    if ($pdo) {
        $sql = "INSERT INTO inquiries (name, phone, email, car_model, country, message, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $phone, $email, $carModel, $country, $message]);
    }

    return [
        'success' => true,
        'reply' => "Thank you, {$name}. Your inquiry for a {$carModel} import to {$country} has been received. Our team will contact you shortly."
    ];
}

try {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!is_array($input)) {
        $input = [];
    }

    $pdo = getDbConnection();

    if (isset($input['lead'])) {
        $result = handleLeadSubmission($input['lead'], $pdo);
        echo json_encode($result);
        exit;
    }

    $hasLeadFields = isset($input['name']) || isset($input['email']) || isset($input['phone']) || isset($input['car_model']) || isset($input['country']);
    if ($hasLeadFields) {
        $result = handleLeadSubmission($input, $pdo);
        echo json_encode($result);
        exit;
    }

    $message = isset($input['message']) ? trim((string)$input['message']) : '';

    if ($message === '') {
        echo json_encode(['reply' => 'Please enter a valid message.']);
        exit;
    }

    $reply = getReplyFromKeywords($message);

    if ($pdo) {
        $sql = "SELECT response FROM faq WHERE keyword LIKE ? LIMIT 1";
        $keywords = '%' . strtolower($message) . '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$keywords]);
        $row = $stmt->fetch();

        if ($row && !empty($row['response'])) {
            $reply = $row['response'];
        }

        $insert = "INSERT INTO chat_logs (customer_name, message, reply, created_at) VALUES (?, ?, ?, NOW())";
        $stmt2 = $pdo->prepare($insert);
        $stmt2->execute(['Website Visitor', $message, $reply]);
    }

    echo json_encode(['reply' => $reply]);
} catch (Exception $e) {
    echo json_encode([
        'reply' => 'I am sorry, I could not process your request right now.'
    ]);
}
