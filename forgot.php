<?php
session_start();
include 'db_connect.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['user_or_email'] ?? '');
    if ($input === '') {
        $message = "कृपया Username या Email दर्ज करें।";
    } else {
        $stmt = $conn->prepare("SELECT id, username, email FROM admins WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $res = $stmt->get_result();
        $admin = $res->fetch_assoc();
        $stmt->close();

        if (!$admin) {
            $message = "User नहीं मिला।";
        } else {
            // generate temporary password and store hashed
            $temp = 'tp' . substr(md5(time() . rand()), 0, 6);
            $hash = password_hash($temp, PASSWORD_DEFAULT);
            $up = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $up->bind_param("si", $hash, $admin['id']);
            $up->execute();
            $up->close();

            $message = "Temporary password for user <strong>" . htmlspecialchars($admin['username']) . "</strong> is: <strong>" . htmlspecialchars($temp) . "</strong><br>Use this to login then change password in dashboard.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Forgot Password - Khorandi E-mitra Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; }
    .container { width:420px; margin:40px auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
    h2 { color:#f44336e8; text-align:center; }
    input, button { margin:10px 0; padding:12px; width:100%; box-sizing:border-box; border-radius:5px; border:1px solid #ccc; }
    button { background:#5c6bc0fa; color:#fff; font-weight:bold; border:none; cursor:pointer; }
    .note { background:#fff3cd; padding:10px; border-radius:6px; border:1px solid #ffeeba; margin-top:10px; color:#856404; }
    .home-btn { background:#7cb342db; color:white; margin-top:8px; }
    @media (max-width:520px){ .container{width:92%;} }
  </style>
</head>
<body>
  <div class="container">
    <h2>Forgot Username / Password</h2>
    <form method="POST" action="forgot.php">
      <input type="text" name="user_or_email" placeholder="Enter Username or Email" required>
      <button type="submit">Get Temporary Password & Username</button>
    </form>

    <?php if($message): ?>
      <div class="note" style="margin-top:12px;"><?= $message ?></div>
    <?php endif; ?>

    <button class="home-btn" onclick="window.location.href='index.php'">Back to Home</button>
  </div>
</body>
</html>