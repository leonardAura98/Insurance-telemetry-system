<?php
require 'config.php';

// Fetch all premiums
if ($_GET['action'] === 'fetch_premiums') {
    $result = $conn->query("SELECT premiums.id, vehicles.reg_no, premiums.premium_amount FROM premiums JOIN vehicles ON premiums.vehicle_id = vehicles.id");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Delete a premium record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete_premium') {
    $premiumId = $_POST['premium_id'];

    // Delete premium record
    $query = $conn->prepare("DELETE FROM premiums WHERE id = ?");
    $query->bind_param("i", $premiumId);

    if ($query->execute()) {
        echo "Premium record deleted successfully.";
    } else {
        echo "Error deleting premium record.";
    }
}
?>
