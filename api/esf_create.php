<?php
require_once __DIR__ . '/db.php';

function generate_account_id(mysqli $conn): string {
    $sql = "
        SELECT esf_id
        FROM tbl_esf
        WHERE esf_id LIKE 'ACC-%'
        ORDER BY CAST(SUBSTRING_INDEX(esf_id, '-', -1) AS UNSIGNED) DESC
        LIMIT 1
    ";

    $result = $conn->query($sql);
    if (!$result) {
        throw new RuntimeException('Failed to generate account ID: ' . $conn->error);
    }

    $lastId = null;
    if ($row = $result->fetch_assoc()) {
        $lastId = (string) ($row['esf_id'] ?? '');
    }
    $result->free();

    if ($lastId === null || $lastId === '') {
        return 'ACC-001';
    }

    $parts = explode('-', $lastId);
    $number = isset($parts[1]) ? (int) $parts[1] : 0;

    return 'ACC-' . str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
}

try {
    $data = request_json();
    $conn = db_connect();
    $accountId = generate_account_id($conn);

    $stmt = $conn->prepare(
        'INSERT INTO tbl_esf (esf_id, candidate_name, recruiter_name, client_name, date_of_joining, mode_of_employment, designation, billing_type, leave_per_annum, bill_rate, total_working_days, work_location, monthly_bill_rate, contract_period, bgv_required, subcontracting_fee_percent, subcontracting_amount, previous_ctc, bonus_given, ctc_offered, bonus_amount, ctc_per_month, bonus_payment_tenure, hike_given_percent, margin_percent, burden_percent, burden_amount, approval, gross_margin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param(
        'ssssssssiddssssdddsdddiddddss',
        $accountId,
        $data['candidateName'],
        $data['recruiterName'],
        $data['clientName'],
        $data['dateOfJoining'],
        $data['employmentMode'],
        $data['designation'],
        $data['billingType'],
        $data['leavesPerAnnum'],
        $data['billRate'],
        $data['workingPerAnnum'],
        $data['workLocation'],
        $data['monthlyBillRate'],
        $data['contractPeriod'],
        $data['bgvRequired'],
        $data['subconPer'],
        $data['subconAmount'],
        $data['prevCTC'],
        $data['bonus'],
        $data['ctcOffered'],
        $data['bonusAmt'],
        $data['ctcPerMonth'],
        $data['bonusPayTenure'],
        $data['hikeGiven'],
        $data['marginPer'],
        $data['burdenPer'],
        $data['burdenAmt'],
        $data['approval'],
        $data['grossMargin']
    );

    if (!$stmt->execute()) {
        throw new RuntimeException('Insert failed: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
    json_response([
        'success' => true,
        'message' => 'Record saved successfully',
        'esf_id' => $accountId
    ]);
} catch (Throwable $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 500);
}
