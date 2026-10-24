<?php

    // Establish and configure MySQL database connection
    function createConnection() {

        $host = "localhost";
        $user = 'root';
        $password = 'Martzcal05@';
        $baseDatos = "edu_system";

        $connection = new mysqli($host, $user, $password, $baseDatos);

        if ($connection->connect_error) {
            die("Database connection failed: " . $connection->connect_error);
        }

        $connection->set_charset("utf8mb4");

        return $connection;
    }

?>