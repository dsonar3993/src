<?php
require_once __DIR__ . '/db.php';

try {
    $conn = db_connect();
    $sql = "
        SELECT
            esf_id,
            candidate_name,
            recruiter_name,
            client_name,
            DATE_FORMAT(date_of_joining, '%Y-%m-%d') AS date_of_joining,
            mode_of_employment,
            designation,
            billing_type,
            leave_per_annum,
            bill_rate,
            total_working_days,
            work_location,
            monthly_bill_rate,
            contract_period,
            bgv_required,
            subcontracting_fee_percent,
            subcontracting_amount,
            previous_ctc,
            bonus_given,
            ctc_offered,
            bonus_amount,
            ctc_per_month,
            bonus_payment_tenure,
            hike_given_percent,
            margin_percent,
            burden_percent,
            burden_amount,
            approval,
            gross_margin
        FROM tbl_esf
        ORDER BY esf_id ASC
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
