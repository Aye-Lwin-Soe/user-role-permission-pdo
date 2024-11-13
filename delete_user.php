<?php
include 'auth_check.php';
include 'db_connection.php';

if (isset($_SESSION['user_permissions']['User']) && in_array('Delete', $_SESSION['user_permissions']['User'])) {
} else {
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    try {

        $stmt_check = $conn->prepare("SELECT id FROM admin_users WHERE id = :user_id");
        $stmt_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {

            $stmt_delete = $conn->prepare("DELETE FROM admin_users WHERE id = :user_id");
            $stmt_delete->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_delete->execute();

            header('Location: users.php');
            exit;
        } else {
            echo "User not found!";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No user ID specified!";
}

$conn = null;
