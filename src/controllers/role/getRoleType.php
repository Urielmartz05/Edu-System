<?php
    
    function getRoleType($role_id) {
        include_once __DIR__ . "/../../config/connection.php";
    
        try {
            $connection = createConnection();

            $sql = "SELECT roleType FROM role WHERE id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $role_id);
            $stmt->execute();
        
            $result = $stmt->get_result();
            $roleType = $result->fetch_assoc();

            return $roleType['roleType'] ?? null;
        } catch (\Throwable $th) {
            error_log("Error in getRoleType: " . $th->getMessage());
            return null;
        }

        
    }

?>