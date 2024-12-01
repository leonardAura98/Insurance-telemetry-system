<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/login.html?error=login_required');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $vehicle_id = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
    $claim_type = filter_input(INPUT_POST, 'claim_type', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $incident_date = filter_input(INPUT_POST, 'incident_date', FILTER_SANITIZE_STRING);

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO claims (user_id, vehicle_id, claim_type, description, amount, incident_date) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissds", $user_id, $vehicle_id, $claim_type, $description, $amount, $incident_date);

        if (!$stmt->execute()) {
            throw new Exception("Error inserting claim");
        }

        $claim_id = $conn->insert_id;

        if (isset($_FILES['documents'])) {
            $upload_dir = '../uploads/claims/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
                $file_name = uniqid() . '_' . $_FILES['documents']['name'][$key];
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($tmp_name, $file_path)) {
                    $doc_stmt = $conn->prepare("INSERT INTO claim_documents (claim_id, file_path) VALUES (?, ?)");
                    $doc_stmt->bind_param("is", $claim_id, $file_path);
                    $doc_stmt->execute();
                }
            }
        }

        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) 
                                   VALUES (?, 'claim_submitted', ?)");
        $log_description = "Submitted claim for " . $claim_type . " - Amount: " . $amount;
        $log_stmt->bind_param("is", $user_id, $log_description);
        $log_stmt->execute();

        $conn->commit();
        header('Location: ../html/dashboard.html?message=claim_submitted');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Claim submission error: " . $e->getMessage());
        header('Location: ../html/file-claim.html?error=submission_failed');
        exit();
    }
}
?>
