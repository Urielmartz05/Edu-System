# Registros del Sistema (Edu-System)

Documento de referencia con los registros actuales de la base de datos (a excepción de la tabla `files`).

---

## 1. Tabla `role`
| ID | Rol (`roleType`) |
|:--:|:-----------------|
| 1  | Admin            |
| 2  | Director         |
| 3  | Teacher          |
| 4  | Student          |

---

## 2. Tabla `departments`
| ID | Departamento |
|:--:|:-------------------------|
| 1  | Ingeniería de Software    |
| 2  | Ciencias Computacionales  |
| 3  | Inteligencia Artificial   |
| 4  | Ciberseguridad y Redes    |

---

## 3. Tabla `users` (Usuarios y Credenciales de Prueba)
> **Nota de acceso:** Todos los usuarios de prueba tienen la contraseña `password123` (o `student123` / `alumno123` según se configuró en sus pruebas previas) y cuentan con correo verificado (`verified = 1`).

| ID | Nombre Completo | Correo Electrónico | Rol (ID) | Rol Nombre | Contraseña |
|:--:|:----------------|:-------------------|:--------:|:-----------|:-----------|
| 1  | Administrador Principal | `admin@example.com` | 1 | Admin | `password123` |
| 2  | Director General | `director@example.com` | 2 | Director | `password123` |
| 3  | Docente Ponente | `docente@example.com` | 3 | Teacher | `password123` |
| 4  | Teacher Academic | `teacher@example.com` | 3 | Teacher | `password123` |
| 7  | Estudiante Prueba | `student@example.com` | 4 | Student | `student123` / `password123` |
| 8  | Alumno Demo | `alumno@example.com` | 4 | Student | `alumno123` / `password123` |
| 9  | Sofía Mendoza | `directora.sofia@example.com` | 2 | Director | `password123` |
| 10 | Carlos Ramírez | `carlos.ramirez@example.com` | 3 | Teacher | `password123` |
| 11 | Elena Torres | `elena.torres@example.com` | 3 | Teacher | `password123` |
| 12 | Mateo López | `mateo.lopez@example.com` | 4 | Student | `password123` |
| 13 | Valeria Gómez | `valeria.gomez@example.com` | 4 | Student | `password123` |
| 14 | Lucas Hernández | `lucas.hernandez@example.com` | 4 | Student | `password123` |
| 15 | Camila Díaz | `camila.diaz@example.com` | 4 | Student | `password123` |

---

## 4. Tabla `admin`
| User ID (`id`) | Administrador | Correo |
|:--------------:|:--------------|:-------|
| 1              | Administrador Principal | `admin@example.com` |

---

## 5. Tabla `director`
| User ID (`id`) | Director | Correo | Departamento Asignado (`department_id`) |
|:--------------:|:---------|:-------|:----------------------------------------|
| 2              | Director General | `director@example.com` | 1 (Ingeniería de Software) |
| 9              | Sofía Mendoza | `directora.sofia@example.com` | 2 (Ciencias Computacionales) |

---

## 6. Tabla `teacher`
| User ID (`id`) | Docente | Correo |
|:--------------:|:--------|:-------|
| 3              | Docente Ponente | `docente@example.com` |
| 4              | Teacher Academic | `teacher@example.com` |
| 10             | Carlos Ramírez | `carlos.ramirez@example.com` |
| 11             | Elena Torres | `elena.torres@example.com` |

---

## 7. Tabla `course` (Cursos / Grupos)
| ID | Nombre del Curso | Departamento (`department_id`) | Progreso | Estado (`status`) |
|:--:|:-----------------|:-------------------------------|:--------:|:-----------------:|
| 1  | Desarrollo Web Full Stack | 1 (Ingeniería de Software) | 75.0% | 1 (Activo) |
| 2  | Estructuras de Datos y Algoritmos | 1 (Ingeniería de Software) | 82.5% | 1 (Activo) |
| 3  | Bases de Datos Avanzadas | 2 (Ciencias Computacionales) | 54.0% | 1 (Activo) |
| 4  | Machine Learning y Deep Learning | 3 (Inteligencia Artificial) | 40.0% | 1 (Activo) |
| 5  | Ciberseguridad y Pentesting | 4 (Ciberseguridad y Redes) | 65.0% | 1 (Activo) |
| 6  | Arquitectura Cloud & DevOps | 1 (Ingeniería de Software) | 30.0% | 1 (Activo) |

---

## 8. Tabla `subject` (Materias / Asignaturas)
| ID | Asignatura | Curso Asociado (`course_id`) | Docente a Cargo (`teacher_id`) |
|:--:|:-----------|:-----------------------------|:-------------------------------|
| 1  | Programación Backend con PHP y MySQL | 1 (Desarrollo Web Full Stack) | 3 (Docente Ponente) |
| 2  | Desarrollo Frontend Moderno y Responsive | 1 (Desarrollo Web Full Stack) | 4 (Teacher Academic) |
| 3  | Algoritmos y Complejidad Computacional | 2 (Estructuras de Datos y Algoritmos) | 10 (Carlos Ramírez) |
| 4  | Diseño y Optimización de Bases de Datos | 3 (Bases de Datos Avanzadas) | 11 (Elena Torres) |
| 5  | Modelos Predictivos y Redes Neuronales | 4 (Machine Learning y Deep Learning) | 10 (Carlos Ramírez) |
| 6  | Seguridad Informática y Análisis de Vulnerabilidades | 5 (Ciberseguridad y Pentesting) | 11 (Elena Torres) |

---

## 9. Tabla `student` (Alumnos Inscritos)
| User ID (`id`) | Estudiante | Correo | Curso Inscrito (`course_id`) |
|:--------------:|:-----------|:-------|:-----------------------------|
| 7              | Estudiante Prueba | `student@example.com` | 1 (Desarrollo Web Full Stack) |
| 8              | Alumno Demo | `alumno@example.com` | 1 (Desarrollo Web Full Stack) |
| 12             | Mateo López | `mateo.lopez@example.com` | 2 (Estructuras de Datos y Algoritmos) |
| 13             | Valeria Gómez | `valeria.gomez@example.com` | 3 (Bases de Datos Avanzadas) |
| 14             | Lucas Hernández | `lucas.hernandez@example.com` | 4 (Machine Learning y Deep Learning) |
| 15             | Camila Díaz | `camila.diaz@example.com` | 5 (Ciberseguridad y Pentesting) |

---

## 10. Tabla `files`
> **Estado:** 0 registros (omitida conforme a las especificaciones).
