<?php
    
    function getRoleType($role_id) {
        include_once __DIR__ . "/../../config/connection.php";
    
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $connection = createConnection();

            $sql = "SELECT roleType FROM role WHERE id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $role_id);
            $stmt->execute();
        
            $result = $stmt->get_result();
            $roleType = $result->fetch_assoc();

            return $roleType['roleType'];
        } catch (\Throwable $th) {
            echo "Error: " . $th->getMessage();
        }

        
    }

?>