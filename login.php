<?php  
session_start();
require 'db_connect.php'; // Uses $pdo

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $msg = 'All fields are required.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $msg = 'Username or email must be between 3–50 characters.';
    } elseif (strlen($password) < 6 || strlen($password) > 20) {
        $msg = 'Password must be between 6–20 characters.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :u OR email = :e");
        $stmt->execute(['u' => $username, 'e' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $uid = $user['id'] ?? null;
        $db_username = $user['username'] ?? null;
        $hash = $user['password'] ?? null;
        $role = $user['role'] ?? null;

        if ($user && password_verify($password, $hash)) {
            $_SESSION['user_id'] = $uid;
            $_SESSION['username'] = $db_username;
            $_SESSION['role'] = $role;

            // Remember me
            if (isset($_POST['remember'])) {
                setcookie('remember_user', $db_username, time() + 86400*7, "/");
            } else {
                setcookie('remember_user', '', time() - 3600, "/");
            }

            // Redirect by role
            if ($role === 'admin') header("Location: admin_dashboard.php");
            else header("Location: client_dashboard.php");
            exit;
        } else {
            $msg = 'Invalid username/email or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Crowdfunding</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<style>
:root{--blue1:#007bff;--blue2:#00aaff;--card:#fff;}
*{box-sizing:border-box;}
body{margin:0;font-family:Poppins,Arial,sans-serif;background:linear-gradient(135deg,var(--blue2),var(--blue1));height:100vh;display:flex;align-items:center;justify-content:center;}
.card{width:360px;background:var(--card);border-radius:14px;padding:30px;box-shadow:0 8px 30px rgba(0,0,0,0.12);text-align:center;}
h2{margin:0 0 18px;color:#0b3a66;}
input[type=text],input[type=password]{width:100%;padding:11px;margin:8px 0;border:1px solid #ddd;border-radius:8px;font-size:14px;}
button{width:100%;background:var(--blue1);color:#fff;border:0;padding:11px;border-radius:8px;font-weight:600;margin-top:12px;cursor:pointer;transition:0.3s;}
button:hover{opacity:0.9;}
.error{color:#b90000;margin-bottom:10px;}
.controls{display:flex;align-items:center;gap:8px;justify-content:flex-start;margin-top:6px;}
.toggle-btn{position:absolute;right:10px;top:12px;cursor:pointer;color:#007bff;}
.link{color:#004aad;text-decoration:underline;}
.small{font-size:14px;color:#333;margin-top:12px;}
</style>
</head>
<body>
<div class="card">
<h2>Login</h2>
<?php if($msg): ?><div class="error"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<form method="post" action="">
    <!-- Username/Email -->
    <input type="text" name="username" placeholder="Username or Email" 
           required minlength="3" maxlength="50"
           value="<?= htmlspecialchars($_COOKIE['remember_user'] ?? '') ?>">

    <!-- Password with toggle -->
    <div style="position:relative;">
        <input type="password" id="password" name="password" placeholder="Password" 
               required minlength="6" maxlength="20">
        <span id="toggle" class="toggle-btn" onclick="togglePass()">👁️</span>
    </div>

    <!-- Remember me -->
    <div class="controls">
        <label><input type="checkbox" name="remember" <?= isset($_COOKIE['remember_user'])?'checked':'' ?>> Remember me</label>
    </div>

    <button type="submit">Login</button>

    <!-- Removed Forgot Password -->
    <p class="small">Don't have an account? <a class="link" href="register.php">Register</a></p>
</form>
</div>

<script>
function togglePass(){
    const p = document.getElementById('password');
    const t = document.getElementById('toggle');
    if(p.type==='password'){p.type='text';t.textContent='🙈';} 
    else {p.type='password';t.textContent='👁️';}
}
</script>
</body>
</html>
