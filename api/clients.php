<?php
require_once __DIR__ . '/db.php';

try {
    $conn = db_connect();
    $sql = "
        SELECT
            client_id AS cid,
            client_name AS name,
            client_mobile AS mobile,
            client_email AS email,
            client_city AS city,
            client_country AS country,
            client_industry AS industry,
            client_requirebg AS requirebgv,
            client_billtype AS billingtype,
            client_burden AS burden,
            client_workingdays AS workingdays
        FROM tbl_clients
        ORDER BY client_id ASC
    ";

    $result = $conn->query($sql);
    if (!$result) {
        throw new RuntimeException('Query failed: ' . $conn->error);
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $result->free();
    $conn->close();
    json_response(['success' => true, 'data' => $rows]);
} catch (Throwable $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 500);
}
