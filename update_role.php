<?php
include 'auth_check.php';
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_id = $_POST['role_id'] ?? null;
    $role_name = $_POST['role_name'] ?? null;
    $permissions = $_POST['permissions'] ?? [];

    if (!$role_id || !$role_name) {
        die("Role ID and Role Name are required.");
    }

    try {
        $stmt = $conn->prepare("UPDATE roles SET name = :role_name WHERE id = :role_id");
        $stmt->bindParam(':role_name', $role_name);
        $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
        $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->execute();

        if (!empty($permissions)) {
            $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
            foreach ($permissions as $permission_id) {
                $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
                $stmt->bindParam(':permission_id', $permission_id, PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        if (isset($_SESSION['user_id'])) {
            $session_role_id = $_SESSION['role_id'];

            $permissions_stmt = $conn->prepare("SELECT p.name AS permission_name, f.name AS feature_name
                                                FROM role_permissions rp
                                                INNER JOIN permissions p ON rp.permission_id = p.id
                                                INNER JOIN features f ON p.feature_id = f.id
                                                WHERE rp.role_id = :role_id");
            $permissions_stmt->bindParam(':role_id', $session_role_id, PDO::PARAM_INT);
            $permissions_stmt->execute();
            $permissions_result = $permissions_stmt->fetchAll(PDO::FETCH_ASSOC);

            $permissions = [];
            foreach ($permissions_result as $permission) {
                $permissions[$permission['feature_name']][] = $permission['permission_name'];
            }

            $_SESSION['user_permissions'] = $permissions;

            header("Location: roles.php");
            exit();
        } else {
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
}

$conn = null;
