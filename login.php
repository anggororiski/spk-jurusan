<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Proses login saat form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password tidak boleh kosong!';
    } else {
        // Koneksi pakai env vars Railway, fallback ke hardcode
        $host = getenv('MYSQLHOST')     ?: getenv('DB_HOST')     ?: 'localhost';
        $db_user = getenv('MYSQLUSER')  ?: getenv('DB_USER')     ?: 'if0_41810587';
        $db_pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: 'ruDNL9SgZI';
        $db_name = getenv('MYSQLDATABASE') ?: getenv('DB_NAME')  ?: 'if0_41810587_spk_jurusan';
        $db_port = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

        $conn = @mysqli_connect($host, $db_user, $db_pass, $db_name, $db_port);

        if (!$conn) {
            $error = 'Koneksi database gagal. Cek konfigurasi server.';
        } else {
            mysqli_set_charset($conn, "utf8");
            $username_safe = mysqli_real_escape_string($conn, $username);

            $sql    = "SELECT * FROM `user` WHERE username = '$username_safe' LIMIT 1";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);

                $valid = false;
                if (function_exists('password_verify') && password_verify($password, $row['password'])) {
                    $valid = true;
                } elseif ($row['password'] === md5($password)) {
                    $valid = true;
                } elseif ($row['password'] === $password) {
                    $valid = true;
                }

                if ($valid) {
                    $_SESSION['user']    = $row['username'];
                    $_SESSION['user_id'] = $row['id'];
                    mysqli_close($conn);
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = 'Username atau password salah!';
                }
            } else {
                $error = 'Username atau password salah!';
            }
            mysqli_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPK Jurusan SMK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header i { font-size: 60px; color: #667eea; margin-bottom: 15px; }
        .form-control { border-radius: 10px; padding: 12px 15px; border: 1px solid #ddd; }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none; border-radius: 10px; padding: 12px;
            width: 100%; font-weight: bold; transition: transform 0.3s;
        }
        .btn-login:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-graduation-cap"></i>
            <h3>SPK Pemilihan Jurusan</h3>
            <p class="text-muted">SMK - DKV vs TKR</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="username" placeholder="Username" required autofocus
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-login btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
        </form>
        <div class="text-center mt-3">
            <small class="text-muted">Demo: admin / admin123</small>
        </div>
    </div>
</body>
</html>
