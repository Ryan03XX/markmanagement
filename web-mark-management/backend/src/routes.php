<?php
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Middleware\AuthMiddleware;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once __DIR__ . '/utils.php';

return function (App $app, PDO $pdo) {
    //Student login
    // $app->post('/api/login', function (Request $request, Response $response) use ($pdo) {
    //     $data = $request->getParsedBody();
    //     $matricNo = $data['matric_no'] ?? '';
    //     $password = $data['password'] ?? '';

    //     $stmt = $pdo->prepare("SELECT * FROM users WHERE matric_no = ?");
    //     $stmt->execute([$matricNo]);
    //     $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //     if ($user && hash('sha256', $password) === $user['password']) {
    //         unset($user['password']);
    //         $response->getBody()->write(json_encode([
    //             'success' => true,
    //             'user' => $user
    //         ]));
    //     } else {
    //         $response->getBody()->write(json_encode([
    //             'success' => false,
    //             'message' => 'Invalid Matric Number or Password'
    //         ]));
    //     }

    //     return $response->withHeader('Content-Type', 'application/json');
    // });
    $app->post('/api/login', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();
        $matricNo = $data['matric_no'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE matric_no = ?");
        $stmt->execute([$matricNo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && hash('sha256', $password) === $user['password']) {
            $token = generateJWT($user, $_ENV['JWT_SECRET']);
            logAction($pdo, $user['id'], 'login', 'Student logged in');
            $response->getBody()->write(json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ]
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
    // $app->post('/api/login/staff', function (Request $request, Response $response) use ($pdo) {
    //     $data = $request->getParsedBody();
    //     $email = $data['email'] ?? '';
    //     $password = $data['password'] ?? '';

    //     $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    //     $stmt->execute([$email]);
    //     $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //     if ($user && hash('sha256', $password) === $user['password']) {
    //         unset($user['password']);
    //         $response->getBody()->write(json_encode([
    //             'success' => true,
    //             'user' => $user
    //         ]));
    //     } else {
    //         $response->getBody()->write(json_encode([
    //             'success' => false,
    //             'message' => 'Invalid Email or Password'
    //         ]));
    //     }

    //     return $response->withHeader('Content-Type', 'application/json');
    // });
    $app->post('/api/login/staff', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && hash('sha256', $password) === $user['password']) {
            // 生成 JWT
            $token = generateJWT($user, $_ENV['JWT_SECRET']);
            logAction($pdo, $user['id'], 'login', 'Staff logged in: ' . $user['email']);
            // 返回用户信息（去掉密码）
            unset($user['password']);

            $response->getBody()->write(json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ]
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
    // $app->post('/api/register', function (Request $request, Response $response) use ($pdo) {
    //     $data = $request->getParsedBody();
    //     $name = $data['name'] ?? '';
    //     $matricNo = $data['matric_no'] ?? '';
    //     $password = $data['password'] ?? '';
    //     $role = $data['role'] ?? '';

    //     if (!$name || !$matricNo || !$password || !$role) {
    //         $response->getBody()->write(json_encode([
    //             'success' => false,
    //             'message' => 'All fields are required'
    //         ]));
    //         return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    //     }

    //     // Check if matric number already exists
    //     $stmt = $pdo->prepare("SELECT * FROM users WHERE matric_no = ?");
    //     $stmt->execute([$matricNo]);
    //     if ($stmt->fetch()) {
    //         $response->getBody()->write(json_encode([
    //             'success' => false,
    //             'message' => 'Matric number already exists'
    //         ]));
    //         return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
    //     }

    //     $hashedPassword = hash('sha256', $password);

    //     $stmt = $pdo->prepare("INSERT INTO users (name, matric_no, password, role) VALUES (?, ?, ?, ?)");
    //     $stmt->execute([$name, $matricNo, $hashedPassword, $role]);

    //     $response->getBody()->write(json_encode([
    //         'success' => true,
    //         'message' => 'User registered successfully'
    //     ]));

    //     return $response->withHeader('Content-Type', 'application/json');
    // });
    $app->post('/api/register', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();

        $name = $data['name'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';
        $matricNo = $data['matric_no'] ?? null;
        $email = $data['email'] ?? null;

        $payload = [];

        // Basic validation
        if (!$name || !$password || !$role) {
            $payload = [
                'success' => false,
                'message' => 'Name, password, and role are required'
            ];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Role-based field check
        if (in_array($role, ['admin', 'lecturer']) && !$email) {
            $payload = [
                'success' => false,
                'message' => 'Email is required for admin/lecturer'
            ];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if ($role === 'student' && !$matricNo) {
            $payload = [
                'success' => false,
                'message' => 'Matric number is required for students'
            ];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Check for duplicates
        if ($role === 'student') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE matric_no = ?");
            $stmt->execute([$matricNo]);
            if ($stmt->fetch()) {
                $payload = [
                    'success' => false,
                    'message' => 'Matric number already exists'
                ];
                $response->getBody()->write(json_encode($payload));
                return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $payload = [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
                $response->getBody()->write(json_encode($payload));
                return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
            }
        }

        // Insert user
        $hashedPassword = hash('sha256', $password);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, matric_no, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $name,
            $email,
            $matricNo,
            $hashedPassword,
            $role
        ]);

        $payload = [
            'success' => true,
            'message' => 'User registered successfully'
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(new AuthMiddleware($_ENV['JWT_SECRET'], 'admin'));
    // admin delete user
    $app->delete('/api/users/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
        $id = $args['id'];

        // 检查用户是否存在
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            $payload = [
                'success' => false,
                'message' => 'User not found'
            ];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // 删除用户
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $payload = [
            'success' => true,
            'message' => 'User deleted successfully'
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(new AuthMiddleware($_ENV['JWT_SECRET'], 'admin'));

    $app->get('/api/users', function (Request $request, Response $response) use ($pdo) {
        $stmt = $pdo->query("SELECT id, name, email, role, matric_no FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payload = ['users' => $users];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(new AuthMiddleware($_ENV['JWT_SECRET'], 'admin'));

    $app->put('/api/users/{id}/password', function (Request $request, Response $response, $args) use ($pdo) {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $newPassword = $data['password'] ?? '';

        if (!$newPassword) {
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    'success' => false,
                    'message' => 'Password is required'
                ]));
        }

        // 加密密码
        $hashedPassword = hash('sha256', $newPassword);

        // 更新数据库
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $id]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Password updated successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(new AuthMiddleware($_ENV['JWT_SECRET'], 'admin'));

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
                SELECT final_exam_mark, final_mark, feedback
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
            $student['feedback'] = $final['feedback'] ?? '';
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'students' => $students
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Lecturer grade students
    // $app->post('/api/lecturer/grade', function (Request $request, Response $response) use ($pdo) {
    //     $data = $request->getParsedBody();

    //     $courseId = $data['course_id'];
    //     $studentId = $data['student_id'];
    //     $componentScores = $data['component_scores']; // e.g. { 1: 12.5, 2: 8.0 }
    //     $finalExamMark = $data['final_exam_mark'];

    //     // Step 1: 保存 component_grades
    //     foreach ($componentScores as $componentId => $mark) {
    //         $stmt = $pdo->prepare("
    //             INSERT INTO component_grades (course_id, student_id, component_id, component_mark)
    //             VALUES (?, ?, ?, ?)
    //             ON DUPLICATE KEY UPDATE component_mark = VALUES(component_mark)
    //         ");
    //         $stmt->execute([$courseId, $studentId, $componentId, $mark]);
    //     }

    //     // Step 2: 计算 component 成绩
    //     $componentMark = 0;
    //     foreach ($componentScores as $componentId => $score) {
    //         $stmt = $pdo->prepare("SELECT weight FROM assessment_components WHERE id = ?");
    //         $stmt->execute([$componentId]);
    //         $component = $stmt->fetch(PDO::FETCH_ASSOC);
    //         if ($component) {
    //             $componentMark += $score * ($component['weight'] / 100);
    //         }
    //     }

    //     // Step 3: 计算 final_mark
    //     $finalMark = round(($componentMark * 0.7) + ($finalExamMark * 0.3), 2);

    //     // Step 4: 保存到 final_results 表
    //     $stmt = $pdo->prepare("
    //         INSERT INTO final_results (course_id, student_id, final_exam_mark, final_mark)
    //         VALUES (?, ?, ?, ?)
    //         ON DUPLICATE KEY UPDATE
    //             final_exam_mark = VALUES(final_exam_mark),
    //             final_mark = VALUES(final_mark),
    //             updated_at = CURRENT_TIMESTAMP
    //     ");
    //     $stmt->execute([$courseId, $studentId, $finalExamMark, $finalMark]);

    //     $response->getBody()->write(json_encode([
    //         'success' => true,
    //         'message' => 'Grades and final mark saved'
    //     ]));

    //     return $response->withHeader('Content-Type', 'application/json');
    // });
    $app->post('/api/lecturer/grade', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();

        $courseId = $data['course_id'];
        $studentId = $data['student_id'];
        $componentScores = $data['component_scores'];
        $finalExamMark = $data['final_exam_mark'];
        $feedback = $data['feedback'] ?? null;

        foreach ($componentScores as $componentId => $mark) {
            $stmt = $pdo->prepare("
                INSERT INTO component_grades (course_id, student_id, component_id, component_mark)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE component_mark = VALUES(component_mark)
            ");
            $stmt->execute([$courseId, $studentId, $componentId, $mark]);
        }

        $componentMark = 0;
        foreach ($componentScores as $componentId => $score) {
            $stmt = $pdo->prepare("SELECT weight FROM assessment_components WHERE id = ?");
            $stmt->execute([$componentId]);
            $component = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($component) {
                $componentMark += $score * ($component['weight'] / 100);
            }
        }

        $finalMark = round(($componentMark * 0.7) + ($finalExamMark * 0.3), 2);

        $stmt = $pdo->prepare("
            INSERT INTO final_results (course_id, student_id, final_exam_mark, final_mark, feedback)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                final_exam_mark = VALUES(final_exam_mark),
                final_mark = VALUES(final_mark),
                feedback = VALUES(feedback),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$courseId, $studentId, $finalExamMark, $finalMark, $feedback]);

        $stmt = $pdo->prepare("SELECT code, name FROM courses WHERE id = ?");
        $stmt->execute([$courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            $courseCode = $course['code'];
            $courseName = $course['name'];
            $message = "Your grade for [{$courseCode} - {$courseName}] has been updated.";
            //  Final Mark: {$finalMark}
        } else {
            $message = "Your grade for Course ID {$courseId} has been updated.";
            //  Final Mark: {$finalMark}
        }

        $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$studentId, $message]);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Grades and final mark saved'
            ]));

        return $response->withHeader('Content-Type', 'application/json');
    })->add(new AuthMiddleware($_ENV['JWT_SECRET'], 'lecturer'));
    // $app->post('/api/lecturer/grade', function (Request $request, Response $response) use ($pdo) {
    //     $data = $request->getParsedBody();

    //     $courseId = $data['course_id'] ?? null;
    //     $grades = $data['grades'] ?? [];

    //     if (!$courseId || !is_array($grades)) {
    //         $response->getBody()->write(json_encode(['error' => 'Missing course_id or grades']));
    //         return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    //     }

    //     // 查询课程名称
    //     $courseNameStmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
    //     $courseNameStmt->execute([$courseId]);
    //     $course = $courseNameStmt->fetch();
    //     $courseName = $course ? $course['name'] : "Unknown Course";

    //     foreach ($grades as $entry) {
    //         $studentId = $entry['student_id'];
    //         $componentGrade = $entry['component_grade'];
    //         $finalExamGrade = $entry['final_exam_grade'];

    //         // 插入/更新 component_grades
    //         $stmt = $pdo->prepare("
    //             INSERT INTO component_grades (student_id, course_id, grade)
    //             VALUES (?, ?, ?)
    //             ON DUPLICATE KEY UPDATE grade = VALUES(grade)
    //         ");
    //         $stmt->execute([$studentId, $courseId, $componentGrade]);

    //         // 插入/更新 final_exam_grades
    //         $stmt = $pdo->prepare("
    //             INSERT INTO final_exam_grades (student_id, course_id, grade)
    //             VALUES (?, ?, ?)
    //             ON DUPLICATE KEY UPDATE grade = VALUES(grade)
    //         ");
    //         $stmt->execute([$studentId, $courseId, $finalExamGrade]);

    //         // 计算最终分数
    //         $finalMark = round(($componentGrade * 0.7) + ($finalExamGrade * 0.3), 2);

    //         // 插入/更新 final_results
    //         $stmt = $pdo->prepare("
    //             INSERT INTO final_results (student_id, course_id, final_mark)
    //             VALUES (?, ?, ?)
    //             ON DUPLICATE KEY UPDATE final_mark = VALUES(final_mark)
    //         ");
    //         $stmt->execute([$studentId, $courseId, $finalMark]);

    //         // 插入通知
    //         $message = "Your grades for Course ID {$courseId} - {$courseName} have been updated.";
    //         $stmt = $pdo->prepare("
    //             INSERT INTO notifications (student_id, message)
    //             VALUES (?, ?)
    //         ");
    //         $stmt->execute([$studentId, $message]);
    //     }

    //     $response->getBody()->write(json_encode(['message' => 'Grades saved and students notified.']));
    //     return $response->withHeader('Content-Type', 'application/json');
    // })->add(new AuthMiddleware($_ENV['JWT_SECRET'], 'lecturer'));


    // $app->get('/api/student/{id}/courses', function ($request, $response, $args) use ($pdo) {
    //     $studentId = $args['id'];

    //     $stmt = $pdo->prepare("
    //         SELECT c.id, c.code, c.name 
    //         FROM courses c
    //         JOIN course_enrollments ce ON ce.course_id = c.id
    //         WHERE ce.student_id = ?
    //     ");
    //     $stmt->execute([$studentId]);
    //     $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //     $response->getBody()->write(json_encode([
    //         'success' => true,
    //         'courses' => $courses
    //     ]));
    //     return $response->withHeader('Content-Type', 'application/json');
    // });
    $app->get('/api/student/{id}/courses', function ($request, $response, $args) use ($pdo) {
        $studentId = $args['id'];

        $stmt = $pdo->prepare("
            SELECT 
                c.id, c.code, c.name,
                rr.status AS remark_status
            FROM courses c
            JOIN course_enrollments ce ON ce.course_id = c.id
            LEFT JOIN remark_requests rr 
                ON rr.course_id = c.id AND rr.student_id = ce.student_id
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

    // $app->get('/api/courses/simple', function ($request, $response) use ($pdo) {
    //     $stmt = $pdo->query("SELECT id, code, name FROM courses");
    //     $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //     $response->getBody()->write(json_encode([
    //         'success' => true,
    //         'courses' => $courses
    //     ]));
    //     return $response->withHeader('Content-Type', 'application/json');
    // });
    $app->get('/api/courses/simple', function ($request, $response) use ($pdo) {
        $stmt = $pdo->query("
            SELECT 
                courses.id, 
                courses.code, 
                courses.name, 
                users.name AS instructor 
            FROM courses
            LEFT JOIN users ON courses.lecturer_id = users.id
        ");
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

    $app->get('/api/student/{id}/enrolled-courses', function ($request, $response, $args) use ($pdo) {
        $studentId = $args['id'];

        // 查找学生已报名的课程（带上课程代码、名称和讲师）
        $stmt = $pdo->prepare("
            SELECT 
                courses.id,
                courses.code,
                courses.name,
                users.name AS instructor
            FROM course_enrollments
            JOIN courses ON course_enrollments.course_id = courses.id
            LEFT JOIN users ON courses.lecturer_id = users.id
            WHERE course_enrollments.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'courses' => $courses
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
    // Lecturer course ranking
    $app->get('/api/lecturer/course-ranking/{course_id}', function ($request, $response, $args) use ($pdo) {
        $courseId = $args['course_id'];

        // 获取课程名称
        $courseStmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
        $courseStmt->execute([$courseId]);
        $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
        $courseName = $course ? $course['name'] : 'Unknown Course';

        // 获取课程中所有学生及其总分
        $stmt = $pdo->prepare("
            SELECT u.id AS student_id, u.name, u.matric_no, fr.final_mark
            FROM users u
            JOIN final_results fr ON u.id = fr.student_id
            WHERE fr.course_id = ? AND u.role = 'student'
            ORDER BY fr.final_mark DESC
        ");
        $stmt->execute([$courseId]);
        $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 为每个学生附加 component 分数
        foreach ($ranking as &$student) {
            $compStmt = $pdo->prepare("
                SELECT ac.name, cg.component_mark AS mark
                FROM component_grades cg
                JOIN assessment_components ac ON cg.component_id = ac.id
                WHERE cg.student_id = ? AND cg.course_id = ?
            ");
            $compStmt->execute([$student['student_id'], $courseId]);
            $student['components'] = $compStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'course_name' => $courseName,
            'ranking' => $ranking
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    // Lecturer course ranking export to CSV
    $app->get('/api/lecturer/course-ranking/{course_id}/export', function ($request, $response, $args) use ($pdo) {
        $courseId = $args['course_id'];

        // 获取课程名称
        $courseStmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
        $courseStmt->execute([$courseId]);
        $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
        $courseName = $course ? $course['name'] : 'Unknown Course';

        // 获取学生排名
        $stmt = $pdo->prepare("
            SELECT u.id AS student_id, u.name, u.matric_no, fr.final_mark
            FROM users u
            JOIN final_results fr ON u.id = fr.student_id
            WHERE fr.course_id = ? AND u.role = 'student'
            ORDER BY fr.final_mark DESC
        ");
        $stmt->execute([$courseId]);
        $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 生成 CSV 内容
        $csvData = [];
        $csvData[] = ['Rank', 'Student Name', 'Matric No', 'Final Mark'];
        foreach ($ranking as $index => $student) {
            $csvData[] = [
                $index + 1,
                $student['name'],
                $student['matric_no'],
                $student['final_mark']
            ];
        }

        // 打开内存写入
        $fh = fopen('php://temp', 'rw');
        foreach ($csvData as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csvOutput = stream_get_contents($fh);
        fclose($fh);

        // 返回 CSV 响应
        $filename = 'course_ranking_' . preg_replace('/\s+/', '_', strtolower($courseName)) . '.csv';
        $response->getBody()->write($csvOutput);
        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
    });
    // student course ranking API
    // $app->get('/api/student/course-ranking/{course_id}', function ($request, $response, $args) use ($pdo) {
    //     $courseId = $args['course_id'];

    //     // 获取课程名称
    //     $courseStmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
    //     $courseStmt->execute([$courseId]);
    //     $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
    //     $courseName = $course ? $course['name'] : 'Unknown Course';

    //     // 获取课程中所有学生及其总分
    //     $stmt = $pdo->prepare("
    //         SELECT u.id AS student_id, u.name, u.matric_no, fr.final_mark
    //         FROM users u
    //         JOIN final_results fr ON u.id = fr.student_id
    //         WHERE fr.course_id = ? AND u.role = 'student'
    //         ORDER BY fr.final_mark DESC
    //     ");
    //     $stmt->execute([$courseId]);
    //     $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //     // 为每个学生附加 component 分数
    //     foreach ($ranking as &$student) {
    //         $compStmt = $pdo->prepare("
    //             SELECT ac.name, cg.component_mark AS mark
    //             FROM component_grades cg
    //             JOIN assessment_components ac ON cg.component_id = ac.id
    //             WHERE cg.student_id = ? AND cg.course_id = ?
    //         ");
    //         $compStmt->execute([$student['student_id'], $courseId]);
    //         $student['components'] = $compStmt->fetchAll(PDO::FETCH_ASSOC);
    //     }

    //     $response->getBody()->write(json_encode([
    //         'success' => true,
    //         'course_name' => $courseName,
    //         'ranking' => $ranking
    //     ]));
    //     return $response->withHeader('Content-Type', 'application/json');
    // });
    $app->get('/api/student/course-ranking/{course_id}', function ($request, $response, $args) use ($pdo) {
        $courseId = $args['course_id'];

        // 获取课程名称
        $courseStmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
        $courseStmt->execute([$courseId]);
        $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
        $courseName = $course ? $course['name'] : 'Unknown Course';

        // 获取课程中所有学生及其总分 + feedback
        $stmt = $pdo->prepare("
            SELECT u.id AS student_id, u.name, u.matric_no, fr.final_mark, fr.feedback
            FROM users u
            JOIN final_results fr ON u.id = fr.student_id
            WHERE fr.course_id = ? AND u.role = 'student'
            ORDER BY fr.final_mark DESC
        ");
        $stmt->execute([$courseId]);
        $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 为每个学生附加 component 分数
        foreach ($ranking as &$student) {
            $compStmt = $pdo->prepare("
                SELECT ac.name, cg.component_mark AS mark
                FROM component_grades cg
                JOIN assessment_components ac ON cg.component_id = ac.id
                WHERE cg.student_id = ? AND cg.course_id = ?
            ");
            $compStmt->execute([$student['student_id'], $courseId]);
            $student['components'] = $compStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'course_name' => $courseName,
            'ranking' => $ranking
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/api/remark-request', function ($req, $res, $args) use ($pdo) {
        $data = $req->getParsedBody();
        $stmt = $pdo->prepare("INSERT INTO remark_requests (student_id, course_id) VALUES (?, ?)");

        try {
            $stmt->execute([$data['student_id'], $data['course_id']]);

            $res->getBody()->write(json_encode(['success' => true]));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $e) {
            $res->getBody()->write(json_encode([
                'success' => false,
                'message' => 'You have already submitted a request for this course.'
            ]));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    });

    $app->get('/api/lecturer/remark-requests', function ($req, $res) use ($pdo) {
        $stmt = $pdo->query("
            SELECT rr.id, u.name AS student_name, c.name AS course_name, rr.status
            FROM remark_requests rr
            JOIN users u ON rr.student_id = u.id
            JOIN courses c ON rr.course_id = c.id
        ");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $res->getBody()->write(json_encode([
            'success' => true,
            'requests' => $requests
        ]));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    $app->put('/api/remark-request/{id}', function ($req, $res, $args) use ($pdo) {
        $id = $args['id'];
        $data = $req->getParsedBody();

        $stmt = $pdo->prepare("UPDATE remark_requests SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $id]);

        $res->getBody()->write(json_encode(['success' => true]));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    // $app->post('/api/request-password-reset', function (Request $request, Response $response) use ($app) {
    //     $data = $request->getParsedBody();
    //     $email = $data['email'];

    //     $db = $app->getContainer()->get('db'); // ✅ 修复这里
    //     $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    //     $stmt->execute([$email]);
    //     $user = $stmt->fetch();

    //     if ($user) {
    //         $token = bin2hex(random_bytes(32));
    //         $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    //         $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
    //         $stmt->execute([$token, $expires, $email]);

    //         $resetLink = "http://localhost:8081/reset-password?token=$token";
    //         mail($email, "Reset Your Password", "Click this link to reset your password: $resetLink");

    //         $response->getBody()->write(json_encode(['success' => true, 'message' => 'Reset link sent to email.']));
    //         return $response->withHeader('Content-Type', 'application/json');
    //     }

    //     $response->getBody()->write(json_encode(['success' => false, 'message' => 'Email not found.']));
    //     return $response->withHeader('Content-Type', 'application/json');
    // });

    // $app->post('/api/reset-password', function (Request $request, Response $response) use ($app) {
    //     $data = $request->getParsedBody();
    //     $token = $data['token'];
    //     $newPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    //     $db = $app->getContainer()->get('db'); // ✅ 修复这里
    //     $stmt = $db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    //     $stmt->execute([$token]);
    //     $user = $stmt->fetch();

    //     if ($user) {
    //         $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    //         $stmt->execute([$newPassword, $user['id']]);

    //         $response->getBody()->write(json_encode(['success' => true]));
    //         return $response->withHeader('Content-Type', 'application/json');
    //     }

    //     $response->getBody()->write(json_encode(['success' => false, 'message' => 'Token expired or invalid.']));
    //     return $response->withHeader('Content-Type', 'application/json');
    // });

    $app->get('/api/student/{student_id}/final-marks', function ($request, $response, $args) use ($pdo) {
        $studentId = $args['student_id'];

        $stmt = $pdo->prepare("
            SELECT c.name AS course_name, fr.final_mark
            FROM final_results fr
            JOIN courses c ON fr.course_id = c.id
            WHERE fr.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $results
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/student/{id}/notifications', function ($request, $response, $args) use ($pdo) {
        $studentId = $args['id'];

        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$studentId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'notifications' => $notifications
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/api/student/notification/{id}/read', function ($request, $response, $args) use ($pdo) {
        $notificationId = $args['id'];

        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$notificationId]);

        $response->getBody()->write(json_encode([
            'success' => true
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/student/matric/{matric_no}/enrolled-courses', function ($request, $response, $args) use ($pdo) {
        $matricNo = $args['matric_no'];

        // 查找 student_id
        $stmt = $pdo->prepare("SELECT id FROM users WHERE matric_no = ? AND role = 'student'");
        $stmt->execute([$matricNo]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Student not found.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $studentId = $student['id'];

        // 查找学生已报名的课程
        $stmt = $pdo->prepare("
            SELECT 
                courses.id,
                courses.code,
                courses.name,
                users.name AS instructor
            FROM course_enrollments
            JOIN courses ON course_enrollments.course_id = courses.id
            LEFT JOIN users ON courses.lecturer_id = users.id
            WHERE course_enrollments.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'courses' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/student/by-matric-no/{matric_no}/course/{course_id}/result', function ($request, $response, $args) use ($pdo) {
        $matricNo = $args['matric_no'];
        $courseId = $args['course_id'];

        // Step 1: 通过 matric_no 查找 student_id
        $stmt = $pdo->prepare("SELECT id FROM users WHERE matric_no = ? AND role = 'student'");
        $stmt->execute([$matricNo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                ->write(json_encode(['success' => false, 'message' => 'Student not found']));
        }

        $studentId = $user['id'];

        // Step 2: 获取 final_results 表中的 final_exam_mark 和 final_mark
        $stmt = $pdo->prepare("SELECT final_exam_mark, final_mark FROM final_results WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);
        $finalResult = $stmt->fetch(PDO::FETCH_ASSOC);

        // Step 3: 获取各个 component 的成绩
        $stmt = $pdo->prepare("
            SELECT ac.name AS component_name, cg.component_mark AS mark
            FROM component_grades cg
            JOIN assessment_components ac ON cg.component_id = ac.id
            WHERE cg.student_id = ? AND cg.course_id = ?
        ");
        $stmt->execute([$studentId, $courseId]);
        $componentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 4: 返回 JSON 响应
        $response->getBody()->write(json_encode([
            'success' => true,
            'student_id' => $studentId,
            'final_result' => $finalResult,
            'component_results' => $componentResults
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });

    // $app->get('/api/lecturer/grade/template', function (Request $request, Response $response) {
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // 设置表头
    //     $sheet->setCellValue('A1', 'student_id');
    //     $sheet->setCellValue('B1', 'component_id');
    //     $sheet->setCellValue('C1', 'component_mark');
    //     $sheet->setCellValue('D1', 'final_exam_mark');
    //     $sheet->setCellValue('E1', 'feedback');

    //     // 示例数据
    //     $sheet->setCellValue('A2', '1');
    //     $sheet->setCellValue('B2', '3');
    //     $sheet->setCellValue('C2', '85');
    //     $sheet->setCellValue('D2', '75');
    //     $sheet->setCellValue('E2', 'Good work');

    //     $writer = new Xlsx($spreadsheet);

    //     $filename = 'grade_template.xlsx';
    //     $tempFile = tempnam(sys_get_temp_dir(), $filename);
    //     $writer->save($tempFile);

    //     $stream = new \Slim\Psr7\Stream(fopen($tempFile, 'rb'));

    //     return $response
    //         ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
    //         ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
    //         ->withBody($stream);
    // });

    // $app->post('/api/lecturer/grade/import', function (Request $request, Response $response) use ($pdo) {
    //     $uploadedFiles = $request->getUploadedFiles();

    //     if (empty($uploadedFiles['file'])) {
    //         return $response->withStatus(400)->withJson(['success' => false, 'message' => 'No file uploaded']);
    //     }

    //     $file = $uploadedFiles['file'];
    //     if ($file->getError() !== UPLOAD_ERR_OK) {
    //         return $response->withStatus(400)->withJson(['success' => false, 'message' => 'Upload failed']);
    //     }

    //     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getStream()->getMetadata('uri'));
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $rows = $sheet->toArray();

    //     $courseId = $_POST['course_id'] ?? null;
    //     if (!$courseId) {
    //         return $response->withStatus(400)->withJson(['success' => false, 'message' => 'Course ID is required']);
    //     }

    //     $header = array_map('strtolower', $rows[0]);
    //     for ($i = 1; $i < count($rows); $i++) {
    //         $row = array_combine($header, $rows[$i]);

    //         $studentId = $row['student_id'];
    //         $componentId = $row['component_id'];
    //         $componentMark = $row['component_mark'];
    //         $finalExamMark = $row['final_exam_mark'];
    //         $feedback = $row['feedback'];

    //         // 插入或更新 component_grades
    //         $stmt = $pdo->prepare("
    //             INSERT INTO component_grades (course_id, student_id, component_id, component_mark)
    //             VALUES (?, ?, ?, ?)
    //             ON DUPLICATE KEY UPDATE component_mark = VALUES(component_mark)
    //         ");
    //         $stmt->execute([$courseId, $studentId, $componentId, $componentMark]);

    //         // 计算component总成绩
    //         $stmt = $pdo->prepare("SELECT weight FROM assessment_components WHERE id = ?");
    //         $stmt->execute([$componentId]);
    //         $component = $stmt->fetch(PDO::FETCH_ASSOC);
    //         $componentMarkWeighted = $component ? $componentMark * ($component['weight'] / 100) : 0;

    //         // 获取所有该学生该课程的component总分
    //         $stmt = $pdo->prepare("
    //             SELECT cg.component_mark, ac.weight
    //             FROM component_grades cg
    //             JOIN assessment_components ac ON cg.component_id = ac.id
    //             WHERE cg.course_id = ? AND cg.student_id = ?
    //         ");
    //         $stmt->execute([$courseId, $studentId]);
    //         $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //         $totalComponentMark = 0;
    //         foreach ($components as $comp) {
    //             $totalComponentMark += $comp['component_mark'] * ($comp['weight'] / 100);
    //         }

    //         $finalMark = round(($totalComponentMark * 0.7) + ($finalExamMark * 0.3), 2);

    //         $stmt = $pdo->prepare("
    //             INSERT INTO final_results (course_id, student_id, final_exam_mark, final_mark, feedback)
    //             VALUES (?, ?, ?, ?, ?)
    //             ON DUPLICATE KEY UPDATE
    //                 final_exam_mark = VALUES(final_exam_mark),
    //                 final_mark = VALUES(final_mark),
    //                 feedback = VALUES(feedback),
    //                 updated_at = CURRENT_TIMESTAMP
    //         ");
    //         $stmt->execute([$courseId, $studentId, $finalExamMark, $finalMark, $feedback]);

    //         $stmt = $pdo->prepare("SELECT code, name FROM courses WHERE id = ?");
    //         $stmt->execute([$courseId]);
    //         $course = $stmt->fetch(PDO::FETCH_ASSOC);
    //         $courseName = $course ? "[{$course['code']} - {$course['name']}]" : "Course ID $courseId";

    //         $message = "Your grade for $courseName has been updated.";
    //         $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message, created_at) VALUES (?, ?, NOW())");
    //         $stmt->execute([$studentId, $message]);
    //     }

    //     return $response->withJson(['success' => true, 'message' => 'Grades imported successfully']);
    // })->add(new AuthMiddleware($_ENV['JWT_SECRET'], 'lecturer'));

    $app->get('/api/admin/logs', function (Request $request, Response $response) use ($pdo) {
        $stmt = $pdo->query("
            SELECT logs.*, users.name 
            FROM logs 
            JOIN users ON logs.user_id = users.id 
            ORDER BY logs.timestamp DESC
        ");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ✅ 正确地写入响应体
        $response->getBody()->write(json_encode($logs));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/api/student/{student_id}/course/{course_id}/what-if', function (Request $request, Response $response, array $args) use ($pdo) {
        $studentId = $args['student_id'];
        $courseId = $args['course_id'];

        // 获取 component 分数 + weight
        $stmt = $pdo->prepare("
            SELECT ac.id AS component_id, ac.name AS component_name, ac.weight, cg.component_mark AS mark
            FROM assessment_components ac
            LEFT JOIN component_grades cg 
                ON ac.id = cg.component_id AND cg.student_id = ? AND cg.course_id = ?
            WHERE ac.course_id = ?
        ");
        $stmt->execute([$studentId, $courseId, $courseId]);
        $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 获取 final_exam_mark
        $stmt = $pdo->prepare("SELECT final_exam_mark FROM final_results WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);
        $final = $stmt->fetch(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'success' => true,
            'component_results' => $components,
            'final_exam_mark' => $final['final_exam_mark'] ?? 0
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/api/student/{id}/notify', function ($request, $response, $args) use ($pdo) {
        $studentId = $args['id'];
        $body = json_decode($request->getBody(), true);
        $message = $body['message'] ?? 'You have a new notification.';

        $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$studentId, $message]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Notification sent'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });
};

function logAction($pdo, $user_id, $action, $details = null) {
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $details]);
}
