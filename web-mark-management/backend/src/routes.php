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

        // 获取该课程所有学生
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.matric_no
            FROM users u
            JOIN course_enrollments ce ON u.id = ce.student_id
            WHERE ce.course_id = ? AND u.role = 'student'
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 为每位学生获取成绩（含 component 名字）
        foreach ($students as &$student) {
            // 获取该学生在该课程的 component 分数 + 名称
            $compStmt = $pdo->prepare("
                SELECT cg.component_id, cg.component_mark, ac.name AS component_name
                FROM component_grades cg
                JOIN assessment_components ac ON cg.component_id = ac.id
                WHERE cg.student_id = ? AND cg.course_id = ?
            ");
            $compStmt->execute([$student['id'], $courseId]);
            $components = $compStmt->fetchAll(PDO::FETCH_ASSOC);

            // 组装 scores 为一个数组（带 name）
            $scores = array_map(function ($comp) {
                return [
                    'component_id' => $comp['component_id'],
                    'name' => $comp['component_name'],
                    'mark' => $comp['component_mark']
                ];
            }, $components);

            // 获取 final_exam_mark
            $finalStmt = $pdo->prepare("
                SELECT final_exam_mark, final_mark
                FROM final_results
                WHERE student_id = ? AND course_id = ?
                LIMIT 1
            ");
            $finalStmt->execute([$student['id'], $courseId]);
            $final = $finalStmt->fetch(PDO::FETCH_ASSOC);

            // 加入成绩数据
            // $student['scores'] = $scores;
            // $student['final_exam_mark'] = $final['final_exam_mark'] ?? 0;

            $student['scores'] = $scores;
            $student['final_exam_mark'] = $final['final_exam_mark'] ?? 0;
            $student['final_mark'] = $final['final_mark'] ?? 0;
        }

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
        $componentScores = $data['component_scores']; // e.g. { 1: 12.5, 2: 8.0 }
        $finalExamMark = $data['final_exam_mark'];

        // Step 1: 保存 component_grades
        foreach ($componentScores as $componentId => $mark) {
            $stmt = $pdo->prepare("
                INSERT INTO component_grades (course_id, student_id, component_id, component_mark)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE component_mark = VALUES(component_mark)
            ");
            $stmt->execute([$courseId, $studentId, $componentId, $mark]);
        }

        // Step 2: 计算 component 成绩
        $componentMark = 0;
        foreach ($componentScores as $componentId => $score) {
            $stmt = $pdo->prepare("SELECT weight FROM assessment_components WHERE id = ?");
            $stmt->execute([$componentId]);
            $component = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($component) {
                $componentMark += $score * ($component['weight'] / 100);
            }
        }

        // Step 3: 计算 final_mark
        $finalMark = round(($componentMark * 0.7) + ($finalExamMark * 0.3), 2);

        // Step 4: 保存到 final_results 表
        $stmt = $pdo->prepare("
            INSERT INTO final_results (course_id, student_id, final_exam_mark, final_mark)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                final_exam_mark = VALUES(final_exam_mark),
                final_mark = VALUES(final_mark),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$courseId, $studentId, $finalExamMark, $finalMark]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Grades and final mark saved'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
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

    $app->get('/api/course/{id}/components', function ($request, $response, $args) use ($pdo) {
        $courseId = $args['id'];
        $stmt = $pdo->prepare("SELECT * FROM assessment_components WHERE course_id = ?");
        $stmt->execute([$courseId]);
        $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode([
            'success' => true,
            'components' => $components
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/api/course/{id}/components', function ($request, $response, $args) use ($pdo) {
        $courseId = $args['id'];
        $data = $request->getParsedBody();
        $name = $data['name'];
        $weight = $data['weight'];

        $stmt = $pdo->prepare("INSERT INTO assessment_components (course_id, name, weight) VALUES (?, ?, ?)");
        $stmt->execute([$courseId, $name, $weight]);

        $id = $pdo->lastInsertId();
        $response->getBody()->write(json_encode([
            'success' => true,
            'component' => [
                'id' => $id,
                'name' => $name,
                'weight' => $weight
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/course/{id}', function ($request, $response, $args) use ($pdo) {
        $courseId = $args['id'];

        $stmt = $pdo->prepare("SELECT courses.*, users.name AS lecturer_name 
                            FROM courses 
                            LEFT JOIN users ON courses.lecturer_id = users.id 
                            WHERE courses.id = ?");
        $stmt->execute([$courseId]);

        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            $response->getBody()->write(json_encode([
                'success' => true,
                'course' => $course
            ]));
        } else {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Course not found'
            ]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/student/{student_id}/course/{course_id}/result', function ($request, $response, $args) use ($pdo) {
        $studentId = $args['student_id'];
        $courseId = $args['course_id'];

        // 获取总成绩
        $stmt = $pdo->prepare("SELECT final_exam_mark, final_mark FROM final_results WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);
        $finalResult = $stmt->fetch(PDO::FETCH_ASSOC);

        // 获取每个 component 的成绩
        $stmt = $pdo->prepare("
            SELECT ac.name AS component_name, cg.component_mark AS mark
            FROM component_grades cg
            JOIN assessment_components ac ON cg.component_id = ac.id
            WHERE cg.student_id = ? AND cg.course_id = ?
        ");
        $stmt->execute([$studentId, $courseId]);
        $componentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'final_result' => $finalResult,
            'component_results' => $componentResults
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });

};
