<?php
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app, PDO $pdo) {
    //Student login
    $app->post('/api/login', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();
        $matricNo = $data['matric_no'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE matric_no = ?");
        $stmt->execute([$matricNo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && hash('sha256', $password) === $user['password']) {
            unset($user['password']);
            $response->getBody()->write(json_encode([
                'success' => true,
                'user' => $user
            ]));
        } else {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Invalid Matric Number or Password'
            ]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });
    // Staff login
    $app->post('/api/login/staff', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && hash('sha256', $password) === $user['password']) {
            unset($user['password']);
            $response->getBody()->write(json_encode([
                'success' => true,
                'user' => $user
            ]));
        } else {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Invalid Email or Password'
            ]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });
    // Register
    $app->post('/api/register', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $matricNo = $data['matric_no'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';

        if (!$name || !$matricNo || !$password || !$role) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'All fields are required'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Check if matric number already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE matric_no = ?");
        $stmt->execute([$matricNo]);
        if ($stmt->fetch()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Matric number already exists'
            ]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }

        $hashedPassword = hash('sha256', $password);

        $stmt = $pdo->prepare("INSERT INTO users (name, matric_no, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $matricNo, $hashedPassword, $role]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User registered successfully'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });
    // Admin create courses
    $app->post('/api/courses', function ($request, $response) use ($pdo) {
        $data = $request->getParsedBody();
        $code = $data['code'] ?? '';
        $name = $data['name'] ?? '';

        if (!$code || !$name) {
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                ->write(json_encode(['success' => false, 'message' => 'Missing course code or name']));
        }

        $stmt = $pdo->prepare("INSERT INTO courses (code, name) VALUES (?, ?)");
        $stmt->execute([$code, $name]);

        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Course added']));
        return $response->withHeader('Content-Type', 'application/json');
    });
    // Admin get all courses
    $app->get('/api/courses', function ($request, $response) use ($pdo) {
        $stmt = $pdo->query("SELECT courses.*, users.name AS lecturer_name 
                            FROM courses 
                            LEFT JOIN users ON courses.lecturer_id = users.id");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($courses));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/lecturers', function ($request, $response) use ($pdo) {
        $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'lecturer'");
        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($lecturers));
        return $response->withHeader('Content-Type', 'application/json');
    });
    // Admin assign a course to a lecturer
    $app->post('/api/assign-course', function ($request, $response) use ($pdo) {
        $data = $request->getParsedBody();
        $courseId = $data['course_id'] ?? null;
        $lecturerId = $data['lecturer_id'] ?? null;

        if (!$courseId || !$lecturerId) {
           $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Missing data'
            ]));

            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $pdo->prepare("UPDATE courses SET lecturer_id = ? WHERE id = ?");
        $stmt->execute([$lecturerId, $courseId]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Course assigned'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });
    // Lecturer get courses
    $app->get('/api/lecturer/courses/{lecturer_id}', function ($request, $response, $args) use ($pdo) {
        $lecturerId = $args['lecturer_id'];
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE lecturer_id = ?");
        $stmt->execute([$lecturerId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'courses' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    // Lecturer get students in a course
    $app->get('/api/lecturer/course/{course_id}/students', function ($request, $response, $args) use ($pdo) {
        $courseId = $args['course_id'];

        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.matric_no
            FROM users u
            JOIN course_enrollments ce ON u.id = ce.student_id
            WHERE ce.course_id = ? AND u.role = 'student'
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'students' => $students
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    // Lecturer grade students
    $app->post('/api/lecturer/grade', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();
        $courseId = $data['course_id'];
        $studentId = $data['student_id'];
        $componentMark = $data['component_mark'];
        $finalExamMark = $data['final_exam_mark'];

        $finalMark = round($componentMark * 0.7 + $finalExamMark * 0.3, 2);

        $stmt = $pdo->prepare("
            INSERT INTO grades (course_id, student_id, component_mark, final_exam_mark, final_mark)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                component_mark = VALUES(component_mark),
                final_exam_mark = VALUES(final_exam_mark),
                final_mark = VALUES(final_mark)
        ");
        $stmt->execute([$courseId, $studentId, $componentMark, $finalExamMark, $finalMark]);

        return $response->withHeader('Content-Type', 'application/json')
                        ->write(json_encode(['success' => true, 'message' => 'Grade updated']));
    });

    $app->get('/api/student/{id}/courses', function ($request, $response, $args) use ($pdo) {
        $studentId = $args['id'];

        $stmt = $pdo->prepare("
            SELECT c.id, c.code, c.name 
            FROM courses c
            JOIN course_enrollments ce ON ce.course_id = c.id
            WHERE ce.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'courses' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/courses/simple', function ($request, $response) use ($pdo) {
        $stmt = $pdo->query("SELECT id, code, name FROM courses");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'courses' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/api/student/enroll', function ($request, $response) use ($pdo) {
        $data = $request->getParsedBody();
        $studentId = $data['student_id'] ?? null;
        $courseId = $data['course_id'] ?? null;

        if (!$studentId || !$courseId) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Missing student_id or course_id'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Prevent duplicate enrollment
        $check = $pdo->prepare("SELECT * FROM course_enrollments WHERE student_id = ? AND course_id = ?");
        $check->execute([$studentId, $courseId]);
        if ($check->fetch()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Already enrolled'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Enroll student
        $stmt = $pdo->prepare("INSERT INTO course_enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->execute([$studentId, $courseId]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Enrolled successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
