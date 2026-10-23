<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado (403) · Edu-System</title>
    <link rel="stylesheet" href="/style.css">
    <style>
        .error-container {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
            background: #f8fafc;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .error-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            max-width: 480px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .error-code {
            font-size: 3.5rem;
            font-weight: 800;
            color: #dc2626;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin: 1rem 0 0.5rem 0;
        }
        .error-desc {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }
        .btn-home {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-home:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <h1 class="error-code">403</h1>
            <h2 class="error-title">Acceso restringido</h2>
            <p class="error-desc">No cuentas con los permisos o rol requerido para acceder a este apartado del sistema educativo.</p>
            <a href="/index.php" class="btn-home">Volver al inicio</a>
        </div>
    </div>
</body>
</html>
