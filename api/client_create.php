<?php
require_once __DIR__ . '/db.php';

function generate_client_id(mysqli $conn): string {
    $sql = "
        SELECT client_id
        FROM tbl_clients
        WHERE client_id LIKE 'CLI-%'
        ORDER BY CAST(SUBSTRING_INDEX(client_id, '-', -1) AS UNSIGNED) DESC
        LIMIT 1
    ";

    $result = $conn->query($sql);
    if (!$result) {
        throw new RuntimeException('Failed to generate client ID: ' . $conn->error);
    }

    $lastId = null;
    if ($row = $result->fetch_assoc()) {
        $lastId = (string) ($row['client_id'] ?? '');
    }
    $result->free();

    if ($lastId === null || $lastId === '') {
        return 'CLI-001';
    }

    $parts = explode('-', $lastId);
    $number = isset($parts[1]) ? (int) $parts[1] : 0;

    return 'CLI-' . str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
}

try {
    $data = request_json();
    $conn = db_connect();
    $clientId = generate_client_id($conn);

    $stmt = $conn->prepare(
        'INSERT INTO tbl_clients (client_id, client_name, client_mobile, client_email, client_city, client_country, client_industry, client_requirebg, client_billtype, client_burden, client_workingdays) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param(
        'sssssssssss',
        $clientId,
        $data['clientName'],
        $data['mobile'],
        $data['email'],
        $data['city'],
        $data['country'],
        $data['industry'],
        $data['requirebgv'],
        $data['billingType'],
        $data['burden'],
        $data['workingDays']
    );

    if (!$stmt->execute()) {
        throw new RuntimeException('Insert failed: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
    json_response([
        'success' => true,
        'message' => 'Client saved successfully',
        'client_id' => $clientId
    ]);
} catch (Throwable $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 500);
}
