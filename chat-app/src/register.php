<?php
require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/User.php';

use ChatApp\Models\User;

$db = Database::getInstance()->getConnection();
$userModel = new User($db);

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $avatar_url = 'default_avatar.png'; // Set default avatar URL
    $status = 'offline'; // Set default status to offline
    $bio = 'Hello, I am a new user!'; // Set default bio

    // Validation
    if (empty($username) || empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        if ($userModel->findByEmail($email)) {
            $error = "Email already exists.";
        } else {
            $userData = [
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'avatar_url' => $avatar_url,
                'bio' => $bio,
                'status' => $status,
            ];

            if ($userModel->create($userData)) {
                // Autologin after registration
                $user = $userModel->findByEmail($email);
                if ($user) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];

                    // Create user session
                    $ipAddress = $_SERVER['REMOTE_ADDR'];
                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                    $sessionToken = $userModel->createSession($user['id'], $ipAddress, $userAgent);

                    if ($sessionToken) {
                        // Set session token in session
                        $_SESSION['session_token'] = $sessionToken;
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = "Failed to create session. Please try again.";
                    }
                } else {
                    $error = "Login failed after registration. Please try again.";
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .card-header {
            background-color: white;
            border-bottom: none;
            padding: 20px;
            text-align: center;
        }
        .card-title {
            font-size: 24px;
            font-weight: bold;
            color: #1877f2;
            margin-bottom: 0;
        }
        .card-body {
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .btn-primary {
            background-color: #1877f2;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: bold;
            color: white;
        }
        .btn-primary:hover {
            background-color: #166fe5;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">Chat</h1>
                    </div>
                    <div class="card-body">
                        <h2>Create an account</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Register</button>
                        </form>
                    </div>
                    <div class="login-link">
                        <p>Already have an account? <a href="login.php">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
