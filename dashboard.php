<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin.php');
    exit;
}

// ✅ Handle Add Link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_link') {
    $section = $_POST['section'] ?? '';
    $title = $_POST['title'] ?? '';
    $url = $_POST['url'] ?? '';
    $is_new = isset($_POST['is_new']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO links (section, title, url, is_new) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $section, $title, $url, $is_new);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php');
    exit;
}

// ✅ Handle Edit (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_link') {
    $id = intval($_POST['edit_id']);
    $title = $_POST['title'] ?? '';
    $url = $_POST['url'] ?? '';
    $is_new = isset($_POST['is_new']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE links SET title=?, url=?, is_new=? WHERE id=?");
    $stmt->bind_param("ssii", $title, $url, $is_new, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php');
    exit;
}

// ✅ Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM links WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php');
    exit;
}

// ✅ Handle Password Change
$pass_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_pass') {
    $cur = $_POST['current_pass'] ?? '';
    $new = $_POST['new_pass'] ?? '';

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $_SESSION['admin_user']);
    $stmt->execute();
    $res = $stmt->get_result();
    $admin = $res->fetch_assoc();
    $stmt->close();

    if ($admin) {
        $stored = $admin['password'];
        $ok = false;
        if (strlen($stored) >= 60 && (substr($stored,0,4)==='$2y$' || substr($stored,0,4)==='$2a$' || substr($stored,0,4)==='$2b$')) {
            if (password_verify($cur, $stored)) $ok = true;
        } else {
            if ($cur === $stored) $ok = true;
        }
        if ($ok) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $up = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $up->bind_param("si", $hash, $admin['id']);
            $up->execute();
            $up->close();
            $pass_msg = "Password changed successfully.";
        } else {
            $pass_msg = "Current password wrong.";
        }
    } else {
        $pass_msg = "Admin record not found.";
    }
}

// ✅ Fetch Links Grouped
$sections = [
    'latestJobs' => 'Latest Jobs',
    'admitCards' => 'Admit Cards',
    'results' => 'Results',
    'answerKey' => 'Answer Key',
    'syllabus' => 'Syllabus',
    'admission' => 'Admission',
    'otherLinks' => 'Other Links'
];

$all_links = [];
foreach ($sections as $k => $v) {
    $stmt = $conn->prepare("SELECT id, title, url, is_new, created_at FROM links WHERE section = ? ORDER BY id DESC");
    $stmt->bind_param("s", $k);
    $stmt->execute();
    $res = $stmt->get_result();
    $all_links[$k] = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Admin Dashboard - Khorandi E-mitra Portal</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f2f2f2; }
    .container { width: 720px; margin: 30px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { color: #4caf50; text-align: center; }
    input, button, select { margin: 8px 0; padding: 12px; width: 100%; box-sizing: border-box; border-radius: 5px; border: 1px solid #ccc; }
    button { background: #4caf50; color: white; font-weight: bold; cursor: pointer; border: none; }
    button:hover { background: #45a049; }

    .list-table { width: 100%; margin: 16px 0; border-collapse: collapse; }
    .list-table td, .list-table th { padding: 6px; border-bottom: 1px solid #ccc; text-align: left; }

    .delete-btn { background: red; color: white; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; font-weight: bold; }
    .delete-btn:hover { background: #b30000; }

    .edit-btn { background: indigo; color: white; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; font-weight: bold; }
    .edit-btn:hover { background: #4b0082; }

    .logout { background: hotpink; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; display: inline-block; margin-top: 10px; }
    .home-btn { background: blue; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; display: inline-block; margin-top: 10px; }

    .note { background:#fff3cd; padding:10px; border-radius:6px; border:1px solid #ffeeba; margin-top:10px; color:#856404; }

    /* 🔸 Coral color for password button */
    .pass-btn { background: coral !important; }
    .pass-btn:hover { background: #ff7f50 !important; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Manage Posts & Links</h2>

    <!-- ✅ Add Link Form -->
    <form method="POST" action="dashboard.php">
      <select name="section" required>
        <?php foreach($sections as $k => $v): ?>
          <option value="<?=htmlspecialchars($k)?>"><?=htmlspecialchars($v)?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="title" placeholder="Title" required>
      <input type="url" name="url" placeholder="https://example.com" required>
      <label><input type="checkbox" name="is_new"> Add “New” tag</label>
      <input type="hidden" name="action" value="add_link">
      <button type="submit">Add</button>
    </form>

    <hr>

    <!-- ✅ Display All Links -->
    <?php foreach($all_links as $cat => $links): ?>
      <h3><?=htmlspecialchars($sections[$cat])?></h3>
      <table class="list-table">
        <tr><th>Title</th><th>Link</th><th>New</th><th>Action</th></tr>
        <?php foreach($links as $l): ?>
          <tr>
            <td><?=htmlspecialchars($l['title'])?></td>
            <td><a href="<?=htmlspecialchars($l['url'])?>" target="_blank"><?=htmlspecialchars($l['url'])?></a></td>
            <td><?= $l['is_new'] ? 'Yes' : 'No' ?></td>
            <td>
              <button class="edit-btn" onclick="openEditForm(<?= $l['id'] ?>, '<?= htmlspecialchars(addslashes($l['title'])) ?>', '<?= htmlspecialchars(addslashes($l['url'])) ?>', <?= $l['is_new'] ?>)">Edit</button>
              <a href="dashboard.php?delete_id=<?= $l['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endforeach; ?>

    <hr>

    <h3>Change Admin Password</h3>
    <?php if($pass_msg): ?><div class="note"><?=htmlspecialchars($pass_msg)?></div><?php endif; ?>
    <form method="POST" action="dashboard.php">
      <input type="password" name="current_pass" placeholder="Current Password" required>
      <input type="password" name="new_pass" placeholder="New Password" required>
      <input type="hidden" name="action" value="change_pass">
      <button type="submit" class="pass-btn">Change Password</button>
    </form>

    <div style="margin-top:12px;">
      <a class="logout" href="admin.php?logout=1">Logout</a>
      &nbsp;&nbsp;
      <a class="home-btn" href="index.php">Go to Home</a>
    </div>
  </div>

  <!-- ✅ Hidden Edit Form -->
  <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; width:380px; margin:100px auto; padding:20px; border-radius:10px;">
      <h3>Edit Link</h3>
      <form method="POST" action="dashboard.php">
        <input type="hidden" name="edit_id" id="edit_id">
        <input type="text" name="title" id="edit_title" required>
        <input type="url" name="url" id="edit_url" required>
        <label><input type="checkbox" name="is_new" id="edit_is_new"> Add “New” tag</label>
        <input type="hidden" name="action" value="edit_link">
        <button type="submit">Save Changes</button>
        <button type="button" onclick="closeEditForm()" style="background:gray; margin-top:5px;">Cancel</button>
      </form>
    </div>
  </div>

  <script>
    function openEditForm(id, title, url, is_new){
      document.getElementById('edit_id').value = id;
      document.getElementById('edit_title').value = title;
      document.getElementById('edit_url').value = url;
      document.getElementById('edit_is_new').checked = is_new ? true : false;
      document.getElementById('editModal').style.display = 'block';
    }
    function closeEditForm(){
      document.getElementById('editModal').style.display = 'none';
    }
  </script>
</body>
</html>