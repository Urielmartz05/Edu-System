<?php
require_once __DIR__ . '/../src/config/connection.php';
$c = createConnection();

$sql = "CREATE TABLE IF NOT EXISTS student_block_progress (
    student_id INT NOT NULL,
    block_id INT NOT NULL,
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, block_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (block_id) REFERENCES blocks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

if ($c->query($sql)) {
    echo "student_block_progress table verified/created successfully." . PHP_EOL;
} else {
    echo "Error creating student_block_progress table: " . $c->error . PHP_EOL;
}
