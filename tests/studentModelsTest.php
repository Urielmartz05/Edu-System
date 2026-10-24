<?php
// Ejecutar contra una instancia MySQL/MariaDB de pruebas. Solo se ejecutan SELECT.
require_once __DIR__ . '/../src/models/studentModels/studentDashboardModel.php';
require_once __DIR__ . '/../src/models/studentModels/studentCoursesModel.php';
require_once __DIR__ . '/../src/models/studentModels/studentStatisticsModel.php';

final class StudentReadFixtureConnection {
    private $connection;
    private $tables;
    public $queries = [];

    public function __construct(mysqli $connection, bool $empty = false) {
        $this->connection = $connection;
        // Relaciones derivadas: no se crean tablas ni se insertan registros.
        $this->tables = [
            'student' => 'SELECT 7 AS id, 1 AS course_id UNION ALL SELECT 8, 1 UNION ALL SELECT 12, 2',
            'course' => "SELECT 1 AS id, 'Desarrollo Web' AS course_name, 1 AS department_id, 75.0 AS progress, 1 AS status UNION ALL SELECT 2, 'Algoritmos', 2, 0.0, 0 UNION ALL SELECT 3, 'Sin materias', 1, NULL, 1",
            'subject' => "SELECT 9 AS id, 1 AS course_id, NULL AS subject_name, 3 AS teacher_id UNION ALL SELECT 2, 1, 'Programación Backend', 4 UNION ALL SELECT 5, 1, 'Diseño Web', 3 UNION ALL SELECT 6, 2, 'Algoritmos', 10",
        ];
        if ($empty) {
            foreach ($this->tables as $table => $sql) {
                $this->tables[$table] = 'SELECT * FROM (' . $sql . ') AS empty_fixture WHERE 1 = 0';
            }
        }
    }

    public function prepare($sql) {
        if (!preg_match('/^SELECT\s/i', $sql) || preg_match('/\b(JOIN|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|ALTER)\b/i', $sql)) {
            throw new RuntimeException('Los modelos deben ejecutar únicamente SELECT sin JOIN.');
        }
        if (substr_count($sql, '?') !== 1) {
            throw new RuntimeException('Cada lectura debe filtrar por un parámetro.');
        }
        $this->queries[] = $sql;
        $sql = preg_replace_callback('/\bFROM\s+(student|course|subject)\b/i', function ($match) {
            $table = strtolower($match[1]);
            return 'FROM (' . $this->tables[$table] . ') AS ' . $table;
        }, $sql, -1, $replaced);
        if ($replaced !== 1) {
            throw new RuntimeException('Tabla inesperada en la consulta.');
        }
        return $this->connection->prepare($sql);
    }
}

function expectSame($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message . ': esperado ' . var_export($expected, true) . ', obtenido ' . var_export($actual, true));
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection = new mysqli(
    getenv('STUDENT_TEST_DB_HOST') ?: 'localhost',
    getenv('STUDENT_TEST_DB_USER') ?: 'agent',
    getenv('STUDENT_TEST_DB_PASSWORD') ?: '',
    '',
    (int) (getenv('STUDENT_TEST_DB_PORT') ?: 3306),
    getenv('STUDENT_TEST_DB_SOCKET') ?: null
);
$connection->set_charset('utf8mb4');
$fixture = new StudentReadFixtureConnection($connection);
$models = [new StudentDashboard($fixture), new StudentCourses($fixture), new StudentStatistics($fixture)];
foreach ($models as $model) {
    expectSame(1, (int) $model->getEnrollment(7)['course_id'], 'Curso del primer alumno');
    expectSame(2, (int) $model->getEnrollment(12)['course_id'], 'Aislamiento del segundo alumno');
    expectSame(null, $model->getEnrollment(999), 'Alumno inexistente');
    expectSame(null, $model->getEnrollment(0), 'Identificador cero');
    expectSame(null, $model->getEnrollment(-1), 'Identificador negativo');
    try {
        $model->getEnrollment('7 OR 1=1');
        throw new RuntimeException('Se aceptó un identificador SQL inválido.');
    } catch (TypeError $expected) {
        // El parámetro entero rechaza SQL como identificador.
    }
}
[$dashboard, $courses, $statistics] = $models;
foreach ([$dashboard, $courses] as $model) {
    $subjects = $model->getSubjects(1);
    expectSame([2, 5, 9], array_map('intval', array_column($subjects, 'id')), 'Materias ordenadas y del curso solicitado');
    expectSame(['Programación Backend', 'Diseño Web', null], array_column($subjects, 'subject_name'), 'Nombres de materias, acentos y nombre nulo permitidos por el esquema');
    expectSame([4, 3, 3], array_map('intval', array_column($subjects, 'teacher_id')), 'Identificadores de docentes conservados sin JOIN');
    expectSame([6], array_map('intval', array_column($model->getSubjects(2), 'id')), 'No mezcla materias de otro curso');
    expectSame([], $model->getSubjects(999), 'Lista sin resultados');
}
foreach ([$dashboard, $statistics] as $model) {
    expectSame(3, $model->getSubjectsCount(1), 'Conteo del curso 1');
    expectSame(1, $model->getSubjectsCount(2), 'Conteo del curso 2');
    expectSame(0, $model->getSubjectsCount(999), 'Conteo sin resultados');
}
$course = $courses->getCourse(1);
expectSame('Desarrollo Web', $course['course_name'], 'Nombre del curso');
expectSame(1, (int) $course['department_id'], 'Departamento del curso');
expectSame(75.0, (float) $course['progress'], 'Progreso decimal');
expectSame(0, (int) $courses->getCourse(2)['status'], 'Estado inactivo conservado');
expectSame(null, $courses->getCourse(999), 'Curso inexistente');
expectSame(75.0, (float) $statistics->getCourseProgress(1)['progress'], 'Estadísticas del curso 1');
expectSame(0.0, (float) $statistics->getCourseProgress(2)['progress'], 'Progreso cero válido');
expectSame(null, $statistics->getCourseProgress(3)['progress'], 'Progreso desconocido no se inventa');
expectSame(null, $statistics->getCourseProgress(999), 'Estadísticas inexistentes');
expectSame(2, $statistics->getTeachersCount(1), 'Docentes distintos, sin duplicarlos por materia');
expectSame(1, $statistics->getTeachersCount(2), 'Docente del segundo curso');
expectSame(0, $statistics->getTeachersCount(3), 'Curso sin materias no tiene docentes');
expectSame(0, $statistics->getTeachersCount(999), 'Sin docentes');

$empty = new StudentReadFixtureConnection($connection, true);
expectSame(null, (new StudentDashboard($empty))->getEnrollment(7), 'Tabla student vacía');
expectSame(0, (new StudentDashboard($empty))->getSubjectsCount(1), 'Tabla subject vacía');
expectSame([], (new StudentCourses($empty))->getSubjects(1), 'Lista vacía');
expectSame(null, (new StudentCourses($empty))->getCourse(1), 'Tabla course vacía');
expectSame(null, (new StudentStatistics($empty))->getCourseProgress(1), 'Estadísticas vacías');
expectSame(0, (new StudentStatistics($empty))->getTeachersCount(1), 'Docentes vacíos');
$connection->close();
echo 'PASS: ' . (count($fixture->queries) + count($empty->queries)) . " lecturas SELECT verificadas en MariaDB, sin JOIN ni escrituras.\n";
