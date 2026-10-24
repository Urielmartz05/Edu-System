<?php

    class AdminStatistics {

        private $connection;

        // Initialize MySQL database connection.
        public function __construct($connection = null) {

            if ($connection === null) {
                require_once __DIR__ . '/../../config/connection.php';
                $connection = createConnection();
            }

            $this->connection = $connection;

        }

        // Fetch total number of recorded student enrollments.
        public function getTotalEnrollments() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS total_enrollments FROM student";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['total_enrollments'] ?? 0);
        }

        // Fetch global arithmetic mean course progress.
        public function getAverageCourseProgress() {

            $connection = $this->connection;
            $sql = "SELECT AVG(progress) AS avg_progress FROM course";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return round((float) ($row['avg_progress'] ?? 0), 1);
        }

        // Count number of active published courses.
        public function getActiveCoursesCount() {

            $connection = $this->connection;
            $sql = "SELECT COUNT(id) AS active_courses FROM course WHERE status = 1";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return (int) ($row['active_courses'] ?? 0);
        }

        // Fetch list of courses ranked by highest recorded progress.
        public function getCoursesProgress() {

            $connection = $this->connection;
            $sql = "SELECT id, course_name, progress, status FROM course ORDER BY progress DESC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $courses = [];

            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }

            $stmt->close();

            return $courses;
        }

        // Fetch detailed performance list for each course (enrolled students and modules).
        public function getCoursePerformanceList() {

            $connection = $this->connection;
            $sql = "SELECT 
                        c.id,
                        c.course_name,
                        c.progress,
                        c.status,
                        COUNT(DISTINCT s.id) AS enrolled_count,
                        COUNT(DISTINCT b.id) AS block_count
                    FROM course c
                    LEFT JOIN student s ON s.course_id = c.id
                    LEFT JOIN blocks b ON b.course_id = c.id
                    GROUP BY c.id, c.course_name, c.progress, c.status
                    ORDER BY c.progress DESC";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $list = [];

            while ($row = $result->fetch_assoc()) {
                $list[] = $row;
            }

            $stmt->close();

            return $list;
        }

        // Calculate percentage completion rate for fully completed courses (100%).
        public function getCompletionRate() {

            $connection = $this->connection;
            $sql = "SELECT 
                        COUNT(CASE WHEN progress >= 100 THEN 1 END) AS completed,
                        COUNT(id) AS total
                    FROM course";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            $total = (int) ($row['total'] ?? 0);
            if ($total === 0) {
                return 0.0;
            }

            return round(((int)$row['completed'] / $total) * 100, 1);
        }

        // Generate progress distribution across 4 tiers (High, Mid, Low, None).
        public function getProgressDistribution() {

            $connection = $this->connection;
            $sql = "SELECT 
                        COUNT(CASE WHEN progress >= 80 THEN 1 END) AS tier_high,
                        COUNT(CASE WHEN progress >= 50 AND progress < 80 THEN 1 END) AS tier_mid,
                        COUNT(CASE WHEN progress > 0 AND progress < 50 THEN 1 END) AS tier_low,
                        COUNT(CASE WHEN progress = 0 OR progress IS NULL THEN 1 END) AS tier_none,
                        COUNT(id) AS total
                    FROM course";

            $stmt = $connection->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            $total = max(1, (int) ($row['total'] ?? 1));

            return [
                'high' => round(((int)$row['tier_high'] / $total) * 100),
                'mid' => round(((int)$row['tier_mid'] / $total) * 100),
                'low' => round(((int)$row['tier_low'] / $total) * 100),
                'none' => round(((int)$row['tier_none'] / $total) * 100),
                'counts' => [
                    'high' => (int)$row['tier_high'],
                    'mid' => (int)$row['tier_mid'],
                    'low' => (int)$row['tier_low'],
                    'none' => (int)$row['tier_none'],
                ]
            ];
        }

    }

?>
