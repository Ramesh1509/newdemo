<?php
session_start();
include 'db_connect.php';

// generate captcha
if (!isset($_SESSION['captcha']) || isset($_GET['regen'])) {
    $_SESSION['captcha'] = strval(rand(100000, 999999));
}

// logout if needed
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $cap = trim($_POST['captcha'] ?? '');

    if ($cap !== ($_SESSION['captcha'] ?? '')) {
        $err = "❌ गलत CAPTCHA!"; 
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $res = $stmt->get_result();
        $admin = $res->fetch_assoc();
        $stmt->close();

        if (!$admin) {
            $err = "❌ Username नहीं मिला!";
        } else {
            $stored = $admin['password'];
            $password_ok = false;

            if (strlen($stored) >= 60 && (substr($stored,0,4) === '$2y$' || substr($stored,0,4) === '$2a$' || substr($stored,0,4) === '$2b$')) {
                if (password_verify($pass, $stored)) $password_ok = true;
            } else {
                if ($pass === $stored) $password_ok = true;
            }

            if ($password_ok) {
                if (!(strlen($stored) >= 60 && (substr($stored,0,4) === '$2y$' || substr($stored,0,4) === '$2a$' || substr($stored,0,4) === '$2b$'))) {
                    $newHash = password_hash($pass, PASSWORD_DEFAULT);
                    $up = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $up->bind_param("si", $newHash, $admin['id']);
                    $up->execute();
                    $up->close();
                }

                $_SESSION['is_admin'] = true;
                $_SESSION['admin_user'] = $admin['username'];
                header("Location: dashboard.php");
                exit;
            } else {
                $err = "❌ Password गलत है!";
            }
        }
    }

    // Auto refresh after wrong input
    echo "<script>setTimeout(()=>{window.location='admin.php';},1500);</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Sarkari Exam</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      margin:0;
      padding:0;
    }
    .container {
      width: 420px;
      margin: 30px auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 { color: #4caf50; text-align: center; }

    input {
      margin: 8px 0;
      padding: 14px;
      width: 100%;
      font-size: 16px;
      box-sizing: border-box;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    button, select {
      margin: 8px 0;
      padding: 10px;
      width: 100%;
      font-size: 16px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
    }

    button { background: #4caf50; color: white; font-weight: bold; }
    button:hover { background: #45a049; }

    .captcha-box {
      display:flex;
      align-items:center;
      justify-content:space-between;
      background:#e9f5ff;
      padding:10px;
      border-radius:6px;
      border:1px solid #b3d9ff;
      margin-bottom:8px;
    }
    .captcha-text {
      font-weight:800;
      font-size:28px;
      color:#880e4f;
      letter-spacing:4px;
      user-select:none;
    }

    /* ✨ improved regenerate button */
    .refresh-btn {
      background: linear-gradient(145deg, #007bff, #00bfff);
      color:white;
      font-weight:bold;
      border:none;
      padding:10px 15px;
      border-radius:10%;
      cursor:pointer;
      font-size:30px;
      transition:0.3s;
      box-shadow:0 4px 6px rgba(0,0,0,0.2);
    }
    .refresh-btn:hover {
      background:linear-gradient(145deg, #00aaff, #007bff);
      transform:rotate(180deg) scale(1.1);
    }

    .extra-buttons {
      display:flex;
      justify-content:space-between;
      margin-top:10px;
      gap:8px;
      flex-wrap:wrap;
    }

    .clear-btn {
      flex:1;
      background:cyan;
      color:black;
      border:none;
      border-radius:6px;
      padding:10px;
      cursor:pointer;
      font-weight:bold;
      transition:0.3s;
    }
    .clear-btn:hover { background:#00d4d4; }

    .home-btn {
      flex:1;
      background:dodgerblue;
      color:white;
      border:none;
      border-radius:6px;
      padding:10px;
      cursor:pointer;
      font-weight:bold;
      transition:0.3s;
    }
    .home-btn:hover { background:#0078ff; }

    .logout-btn {
      background:hotpink;
      color:white;
      border:none;
      border-radius:6px;
      padding:8px;
      margin-top:8px;
      cursor:pointer;
      font-weight:bold;
      width:100%;
    }
    .logout-btn:hover { background:deeppink; }

    .forget-btn {
      background:teal;
      color:white;
      border:none;
      border-radius:6px;
      padding:10px;
      width:100%;
      font-weight:bold;
      margin-top:8px;
      cursor:pointer;
    }
    .forget-btn:hover { background:#007c7c; }

    @media (max-width:520px) {
      .container { width: 92%; margin: 18px auto; padding:16px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Admin Login</h2>

    <?php if($err): ?>
      <p style="color:red; font-weight:bold; text-align:center;"><?=htmlspecialchars($err)?></p>
    <?php endif; ?>

    <form method="POST" action="admin.php" autocomplete="off">
      <input type="text" name="username" placeholder="Username" required><br>
      <input type="password" name="password" placeholder="Password" required><br>

      <div class="captcha-box">
        <span class="captcha-text"><?=htmlspecialchars($_SESSION['captcha'])?></span>
        <a href="admin.php?regen=1" class="refresh-btn" title="Regenerate">↻</a>
      </div>

      <input type="text" name="captcha" placeholder="Enter CAPTCHA" required><br>

      <button type="submit">Login</button>
      <button type="button" class="forget-btn" onclick="window.location.href='forgot.php'">Forgot Username / Password?</button>

      <div class="extra-buttons">
        <button type="button" class="clear-btn" onclick="window.location.reload()">Clear</button>
        <button type="button" class="home-btn" onclick="window.location.href='index.php'">Home</button>
      </div>
    </form>
  </div>
</body>
</html>