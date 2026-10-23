# Modelos de consulta de Student

Se conserva la segmentación de `adminModels`: una clase por pantalla, conexión privada creada con `createConnection()`, consultas preparadas, `bind_param`, `get_result()` y resultados como arreglo, entero o `null`. La conexión opcional del constructor sirve para las pruebas; el uso normal no necesita argumentos.

| Archivo | Clase | Información disponible |
| --- | --- | --- |
| `studentModels.php` | `StudentDashboard` | Inscripción del alumno, cantidad y listado de materias de su curso |
| `studentCoursesModel.php` | `StudentCourses` | Inscripción, datos del curso y materias del curso |
| `studentStatisticsModel.php` | `StudentStatistics` | Inscripción, progreso y estado del curso, cantidad de materias y docentes distintos |

Todas las consultas son `SELECT` sobre una sola tabla, sin joins, subconsultas ni modificaciones de datos. Los identificadores se envían como parámetros enteros.

## Identificadores

`getEnrollment($studentId)` recibe el ID del usuario autenticado: `student.id` corresponde a `users.id`, según `registros.md`. Devuelve `id` y `course_id`, o `null` si no hay inscripción.

Los demás métodos reciben el `course_id` obtenido de esa inscripción, no el ID del usuario. Al integrar las vistas, usar el usuario de la sesión y su inscripción; no aceptar directamente un curso enviado por el navegador sin verificar pertenencia. Se mantienen las lecturas separadas para que las relaciones y los joins se implementen después.

```php
require_once __DIR__ . '/../../models/studentModels/studentModels.php';
$model = new StudentDashboard();
$enrollment = $model->getEnrollment((int) $user['id']);
$subjects = [];
$subjectsCount = 0;

if ($enrollment !== null && $enrollment['course_id'] !== null) {
    $courseId = (int) $enrollment['course_id'];
    $subjects = $model->getSubjects($courseId);
    $subjectsCount = $model->getSubjectsCount($courseId);
}
```

Los listados de materias exponen únicamente `id`, `course_id` y `teacher_id`, las columnas verificables en los modelos admin y la documentación. Los nombres de materias y docentes, horarios, calificaciones, asistencia, créditos y entregas no se consultan porque falta confirmar su esquema. El progreso devuelto pertenece a `course`, no representa una calificación individual. Los valores `NULL` del progreso se conservan.

## Pruebas de lectura

Ejecutar con PHP y la extensión `mysqli` contra una instancia MySQL/MariaDB de pruebas. Se puede usar un usuario que solo pueda conectarse: no hacen falta permisos para escribir ni tablas físicas.

```bash
STUDENT_TEST_DB_USER=agent \
STUDENT_TEST_DB_SOCKET=/ruta/mysql.sock \
php tests/studentModelsTest.php
```

También se admiten `STUDENT_TEST_DB_HOST`, `STUDENT_TEST_DB_PORT` y `STUDENT_TEST_DB_PASSWORD` para una instancia de pruebas propia.

Las pruebas ejecutan los modelos con consultas preparadas reales en MariaDB. La conexión de prueba reemplaza cada tabla por una relación derivada definida con `SELECT ... UNION ALL SELECT ...`, sin crear tablas, insertar registros ni alterar la base. Verifican 44 lecturas: separación por alumno/curso, orden de materias, docentes distintos, progreso cero y nulo, registros inexistentes y tablas vacías. Rechazan SQL recibido como identificador y consultas que no sean `SELECT` o que contengan joins.

**Límite de validación:** la base local solo tiene `users` y `role`; no contiene `student`, `course` ni `subject`. Por eso estas pruebas verifican el SQL y los resultados con datos controlados, no la compatibilidad con el esquema académico real. Ese contraste queda pendiente de disponer del esquema o de una conexión de lectura a esas tablas. No se modifican las vistas ni los modelos de admin.
