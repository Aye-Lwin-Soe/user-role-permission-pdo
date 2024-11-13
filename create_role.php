<?php
include 'auth_check.php';
include 'db_connection.php';

try {
    if (isset($_SESSION['user_permissions']['Role']) && in_array('Create', $_SESSION['user_permissions']['Role'])) {
    } else {
        header("Location: dashboard.php");
        exit();
    }

    $sql_features = "SELECT f.id as feature_id, f.name as feature_name, p.id as permission_id, p.name as permission_name
                     FROM features f
                     LEFT JOIN permissions p ON f.id = p.feature_id
                     ORDER BY p.id ASC";
    $stmt = $conn->query($sql_features);

    $features = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $features[$row['feature_id']]['name'] = $row['feature_name'];
        $features[$row['feature_id']]['permissions'][] = [
            'id' => $row['permission_id'],
            'name' => $row['permission_name']
        ];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $role_name = $_POST['role_name'];
        $selected_permissions = $_POST['permissions'] ?? [];

        $stmt = $conn->prepare("INSERT INTO roles (name) VALUES (?)");
        $stmt->execute([$role_name]);
        $role_id = $conn->lastInsertId();

        if (!empty($selected_permissions)) {
            $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($selected_permissions as $permission_id) {
                $stmt->execute([$role_id, $permission_id]);
            }
        }

        header("Location: roles.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <div class="col-md-10 mt-5">
                <h2 class="mb-4">Create Role</h2>
                <form action="create_role.php" method="POST">
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="role_name" name="role_name" required>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Feature Name</th>
                                <th>Permissions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($features as $feature_id => $feature): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($feature['name']); ?></td>
                                    <td>
                                        <?php if (!empty($feature['permissions'])): ?>
                                            <?php foreach ($feature['permissions'] as $permission): ?>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo htmlspecialchars($permission['id']); ?>" id="permission_<?php echo htmlspecialchars($permission['id']); ?>">
                                                    <label class="form-check-label" for="permission_<?php echo htmlspecialchars($permission['id']); ?>">
                                                        <?php echo htmlspecialchars($permission['name']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">No permissions available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="submit" class="btn btn-primary">Create Role</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>