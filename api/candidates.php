<?php
require_once __DIR__ . '/db.php';

try {
    $conn = db_connect();
    $sql = "
        SELECT
            can_id AS cid,
            can_name AS name,
            can_gender AS gender,
            can_email AS email,
            can_mobile AS mobile,
            DATE_FORMAT(can_dob, '%Y-%m-%d') AS dob,
            can_altm AS altm,
            can_emgNumber AS emgNum,
            can_emgName AS emgName,
            can_skillsp AS skillsp,
            can_skillss AS skillss,
            can_marital AS marital,
            can_pan AS panno,
            can_exp AS expt,
            can_expr AS expr,
            can_caddr AS caddr,
            can_paddr AS paddr,
            can_insures AS insures,
            can_insured AS insured
        FROM tbl_candidates
        ORDER BY can_id ASC
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
