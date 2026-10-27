<?php
    include_once __DIR__ . "/../../config/connection.php";
    require_once __DIR__ . '/../../config/verificationEmail.php';
    $conexion = createConnection();

    $mensaje = "";
    $isSuccess = false;
    $registeredStudentId = 0;

    // Process student signup registration request upon POST submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = (int)trim($_POST["id"] ?? 0);
        $nombre = trim($_POST["nombre"] ?? "");
        $lastname = trim($_POST["lastname"] ?? "");
        $email = trim(strtolower($_POST["email"] ?? ""));
        $password = $_POST["password"] ?? "";
        // Public registration is strictly for students, requiring institutional admin approval
        $role = 4;
        $verifiedValue = 0; // Always pending administrator verification
        $tokenConfirmacion = null;

        if ($id <= 0 || empty($nombre) || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $mensaje = "All required fields must be completed properly.";
        } else {
            // Generate secure password hash
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Begin atomic transaction to preserve relational integrity
            $conexion->begin_transaction();

            try {
                $sqlUser = "INSERT INTO users
                    (id, name, lastname, email, password, role, token_verificacion, verified) VALUES
                    (?,?,?,?,?,?,?,?)";
                $stmt = $conexion->prepare($sqlUser);
                $stmt->bind_param("issssisi", $id, $nombre, $lastname, $email, $passwordHash, $role, $tokenConfirmacion, $verifiedValue);

                if ($stmt->execute()) {
                    // Commit changes atomically
                    $conexion->commit();
                    $stmt->close();
                    $isSuccess = true;
                    $registeredStudentId = $id;
                    $mensaje = "Student account created successfully. It is awaiting administrator verification.";
                } else {
                    $conexion->rollback();
                    $stmt->close();
                    $mensaje = "Could not register student account.";
                }
            } catch (mysqli_sql_exception $e) {
                $conexion->rollback();
                if ($e->getCode() === 1062) {
                    $mensaje = "Student ID or institutional email is already registered.";
                } else {
                    error_log('Student registration failed: ' . $e->getMessage());
                    $mensaje = "Could not register user. Please try again.";
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
    <title>Student Registration · Edu-System</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../../ui/header/header.php'; ?>

    <main class="registro-main">
        <div class="registro-main-container">
            <div class="registro-columna-izquierda">
                <h1 class="registro-title">Student Registration</h1>

                <?php if ($isSuccess): ?>
                    <div style="width:100%; padding:24px; background:#fffbeb; border:1px solid #fde68a; border-radius:12px; margin-bottom:20px; text-align:center;">
                        <div style="width:48px; height:48px; margin:0 auto 12px; background:#fef3c7; color:#d97706; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:22px;">⏳</div>
                        <h2 style="font-size:20px; font-weight:700; color:#92400e; margin-bottom:8px;">
                            Registration Submitted!
                        </h2>
                        <span style="display:inline-block; font-size:12px; font-weight:600; color:#b45309; background:#fef3c7; padding:4px 10px; border-radius:20px; margin-bottom:12px;">
                            Pending Administrator Approval
                        </span>
                        <p style="color:#78350f; font-size:13.5px; line-height:1.6; margin-bottom:18px;">
                            Your student account (Control ID #<?php echo (int)$registeredStudentId; ?>) has been created successfully. An institutional administrator must review and accept your verification before you can sign in.
                        </p>
                        <a href="../../../index.php" class="registro-button" style="display:inline-block; text-decoration:none; width:auto; padding:12px 28px;">
                            Return to Sign In
                        </a>
                    </div>
                <?php else: ?>
                    <form class="registro-form" method="POST">
                        <label>Student Control ID Number:</label>
                        <input type="number" name="id" placeholder="Student ID number" required>

                        <label>First Name:</label>
                        <input type="text" name="nombre" placeholder="First name" required>

                        <label>Last Name:</label>
                        <input type="text" name="lastname" placeholder="Last name" required>

                        <label>Institutional Email:</label>
                        <input type="email" name="email" placeholder="Email address" required>

                        <label>Password:</label>
                        <input type="password" name="password" placeholder="Create password" required>

                        <div style="padding:10px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:16px; font-size:12px; color:#64748b; line-height:1.5;">
                            ℹ️ <em>New student accounts require review and activation by an administrator before logging in.</em>
                        </div>

                        <button class="registro-button" type="submit">Register as Student</button>
                    </form>

                    <?php if (!empty($mensaje)): ?>
                        <p class="registro-message" style="color:#b91c1c; background:#fef2f2; border:1px solid #fecaca; padding:10px 14px; border-radius:8px; width:100%; text-align:center; margin-top:14px;">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <a class="registro-link" href="../../../index.php">Already have an account? Sign In</a>
            </div>

            <div class="registro-columna-derecha"></div>
        </div>
    </main>
</body>
</html>
