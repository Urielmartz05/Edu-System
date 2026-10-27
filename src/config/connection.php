<?php

    // Helper function to load environment variables from .env file
    function loadEnv($envPath = null) {
        if ($envPath === null) {
            $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        }

        if (!file_exists($envPath) || !is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            // Ignore comments and empty lines
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Strip surrounding single or double quotes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv("{$name}={$value}");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }

    // Establish and configure MySQL / TiDB database connection
    function createConnection() {
        static $envLoaded = false;
        if (!$envLoaded) {
            loadEnv();
            $envLoaded = true;
        }

        $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? 'localhost'));
        $port = (int)(getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($_SERVER['DB_PORT'] ?? 3306)));
        $user = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: ($_ENV['DB_USERNAME'] ?? ($_ENV['DB_USER'] ?? ($_SERVER['DB_USERNAME'] ?? 'root'))));
        $password = getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: ($_ENV['DB_PASSWORD'] ?? ($_ENV['DB_PASS'] ?? ($_SERVER['DB_PASSWORD'] ?? ''))));
        $baseDatos = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: ($_ENV['DB_DATABASE'] ?? ($_ENV['DB_NAME'] ?? ($_SERVER['DB_DATABASE'] ?? 'edu_system'))));

        $sslMode = getenv('DB_SSL') ?: ($_ENV['DB_SSL'] ?? ($_SERVER['DB_SSL'] ?? null));
        $isRemoteTiDB = ($port === 4000) || (stripos($host, 'tidbcloud.com') !== false);
        $useSsl = ($sslMode === 'true' || $sslMode === '1') || ($sslMode !== 'false' && $sslMode !== '0' && $isRemoteTiDB);

        $connection = mysqli_init();
        if (!$connection) {
            die("Database initialization failed.");
        }

        // Set connect timeout for cloud databases
        $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

        if ($useSsl) {
            // TiDB Cloud requires SSL encryption
            $connection->ssl_set(NULL, NULL, NULL, NULL, NULL);
            $connected = @$connection->real_connect($host, $user, $password, $baseDatos, $port, NULL, MYSQLI_CLIENT_SSL);
        } else {
            $connected = @$connection->real_connect($host, $user, $password, $baseDatos, $port);
        }

        if (!$connected || $connection->connect_error) {
            die("Database connection failed: " . ($connection->connect_error ?: mysqli_connect_error()));
        }

        $connection->set_charset("utf8mb4");

        return $connection;
    }

?>