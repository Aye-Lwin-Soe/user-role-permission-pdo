<?php
session_start();

try {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
