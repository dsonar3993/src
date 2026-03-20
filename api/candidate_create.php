<?php
require_once __DIR__ . '/db.php';

function generate_candidate_id(mysqli $conn): string {
    $sql = "
        SELECT can_id
        FROM tbl_candidates
        WHERE can_id LIKE 'CAND-%'
        ORDER BY CAST(SUBSTRING_INDEX(can_id, '-', -1) AS UNSIGNED) DESC
        LIMIT 1
    ";

    $result = $conn->query($sql);
    if (!$result) {
        throw new RuntimeException('Failed to generate candidate ID: ' . $conn->error);
    }

    $lastId = null;
    if ($row = $result->fetch_assoc()) {
        $lastId = (string) ($row['can_id'] ?? '');
    }
    $result->free();

    if ($lastId === null || $lastId === '') {
        return 'CAND-001';
    }

    $parts = explode('-', $lastId);
    $number = isset($parts[1]) ? (int) $parts[1] : 0;

    return 'CAND-' . str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
}

try {
    $data = request_json();
    $conn = db_connect();
    $candidateId = generate_candidate_id($conn);

    $stmt = $conn->prepare(
        'INSERT INTO tbl_candidates (can_id, can_name, can_gender, can_email, can_mobile, can_dob, can_altm, can_emgNumber, can_emgName, can_skillsp, can_skillss, can_marital, can_pan, can_exp, can_expr, can_caddr, can_paddr, can_insures, can_insured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param(
        'sssssssssssssssssss',
        $candidateId,
        $data['candidateName'],
        $data['gender'],
        $data['email'],
        $data['mobile'],
        $data['dob'],
        $data['altMobile'],
        $data['emergencyNumber'],
        $data['emergencyPerson'],
        $data['primarySkills'],
        $data['secondarySkills'],
        $data['maritalStatus'],
        $data['pan'],
        $data['totalExp'],
        $data['relevantExp'],
        $data['presentAddress'],
        $data['permanentAddress'],
        $data['selfInsurance'],
        $data['dependantsInsurance']
    );

    if (!$stmt->execute()) {
        throw new RuntimeException('Insert failed: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
    json_response([
        'success' => true,
        'message' => 'Candidate saved successfully',
        'candidate_id' => $candidateId
    ]);
} catch (Throwable $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 500);
}
