<?php
    // ==========================================
    // VIEW & HANDLER: PUBLIC REGISTRATION
    // ==========================================

    include_once __DIR__ . "/../../config/connection.php";
    $conexion = createConnection();

    $mensaje = "";
    $tokenConfirmacion = "";
    $isSuccess = false;

    // Retrieve active departments for Director assignment
    $departmentsList = [];
    $deptRes = $conexion->query("SELECT id, department FROM departments ORDER BY department ASC");
    if ($deptRes) {
        while ($d = $deptRes->fetch_assoc()) {
            $departmentsList[] = $d;
        }
    }

    // Process signup registration request upon POST submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = (int)trim($_POST["id"] ?? 0);
        $nombre = trim($_POST["nombre"] ?? "");
        $lastname = trim($_POST["lastname"] ?? "");
        $email = trim(strtolower($_POST["email"] ?? ""));
        $password = trim($_POST["password"] ?? "");
        $role = (int) trim($_POST["role"] ?? 0);
        $departmentId = !empty($_POST["department_id"]) ? (int)$_POST["department_id"] : 1;

        if ($id <= 0 || empty($nombre) || empty($email) || empty($password) || $role <= 0) {
            $mensaje = "All required fields must be completed properly.";
        } else {
            // Generate password hash and confirmation token
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $tokenConfirmacion = bin2hex(random_bytes(16));

            // Begin atomic transaction to preserve relational integrity
            $conexion->begin_transaction();

            try {
                $sqlUser = "INSERT INTO users 
                    (id, name, lastname, email, password, role, token_verificacion, verified) VALUES 
                    (?,?,?,?,?,?,?,0)";
                $stmt = $conexion->prepare($sqlUser);
                $stmt->bind_param("issssis", $id, $nombre, $lastname, $email, $passwordHash, $role, $tokenConfirmacion);
                
                if ($stmt->execute()) {
                    // Synchronize child table based on assigned academic role
                    if ($role === 3) {
                        // Teacher / Instructor
                        $stmtTeacher = $conexion->prepare("INSERT INTO teacher (id) VALUES (?) ON DUPLICATE KEY UPDATE id = id");
                        $stmtTeacher->bind_param("i", $id);
                        $stmtTeacher->execute();
                        $stmtTeacher->close();
                    } elseif ($role === 2) {
                        // Academic Department Director
                        $stmtDirector = $conexion->prepare("INSERT INTO director (id, department_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE department_id = VALUES(department_id)");
                        $stmtDirector->bind_param("ii", $id, $departmentId);
                        $stmtDirector->execute();
                        $stmtDirector->close();
                    } elseif ($role === 1) {
                        // Administrator
                        $stmtAdmin = $conexion->prepare("INSERT INTO admin (id) VALUES (?) ON DUPLICATE KEY UPDATE id = id");
                        $stmtAdmin->bind_param("i", $id);
                        $stmtAdmin->execute();
                        $stmtAdmin->close();
                    }

                    // Commit changes atomically
                    $conexion->commit();
                    $stmt->close();
                    $isSuccess = true;
                    $mensaje = "Account created successfully. Please verify your email to activate it.";
                } else {
                    $conexion->rollback();
                    $stmt->close();
                    $mensaje = "Could not register user account.";
                    $tokenConfirmacion = "";
                }
            } catch (mysqli_sql_exception $e) {
                $conexion->rollback();
                $tokenConfirmacion = "";
                if ($e->getCode() === 1062) {
                    $mensaje = "User ID or institutional email is already registered.";
                } else {
                    $mensaje = "Could not register user: " . $e->getMessage();
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account · Edu-System</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../../ui/header/header.php'; ?>
    
    <main class="registro-main">
        <div class="registro-main-container">
            <div class="registro-columna-izquierda">
                <h1 class="registro-title">Create an Account</h1>
                <form class="registro-form" method="POST">
                    <label>Student / Control ID:</label>
                    <input type="number" name="id" placeholder="Control ID number" required>

                    <label>First Name:</label>
                    <input type="text" name="nombre" placeholder="First name" required>

                    <label>Last Name:</label>
                    <input type="text" name="lastname" placeholder="Last name" required>

                    <label>Institutional Email:</label>
                    <input type="email" name="email" placeholder="Email address" required>

                    <label>Academic Role:</label>
                    <select name="role" id="roleSelector" onchange="toggleDepartmentSelect()" required>
                        <option value="">Select a role</option>
                        <option value="4">Student</option>
                        <option value="3">Teacher / Faculty</option>
                        <option value="2">Director</option>
                    </select>

                    <div id="departmentGroup" style="display:none; flex-direction:column; gap:4px; margin-top:4px;">
                        <label>Appointed Academic Department:</label>
                        <select name="department_id" id="departmentId">
                            <?php foreach ($departmentsList as $d): ?>
                                <option value="<?php echo (int)$d['id']; ?>">
                                    <?php echo htmlspecialchars($d['department']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label>Password:</label>
                    <input type="password" name="password" placeholder="Create password" required>

                    <button class="registro-button" type="submit">Register</button>
                </form>

                <?php if (!empty($mensaje)): ?>
                    <p class="registro-message" style="<?php echo $isSuccess ? 'color:#15803d; background:#f0fdf4; border:1px solid #86efac;' : ''; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($tokenConfirmacion)): ?>
                    <p class="registro-message">
                        To activate your account:
                        <a class="registro-link" href="../../controllers/verificar/verificar.php?token=<?php echo urlencode($tokenConfirmacion); ?>">
                            Confirm Email Address
                        </a>
                    </p>
                <?php endif; ?>

                <a class="registro-link" href="../../../index.php">Already have an account? Sign In</a>
            </div>

            <div class="registro-columna-derecha"></div>
        </div>
    </main>

    <script>
        function toggleDepartmentSelect() {
            const roleSelect = document.getElementById('roleSelector');
            const deptGroup = document.getElementById('departmentGroup');
            if (roleSelect && deptGroup) {
                deptGroup.style.display = (roleSelect.value === '2') ? 'flex' : 'none';
            }
        }
    </script>
</body>
</html>
