<?php
include 'db_connect.php';

function fetch_links($conn, $section) {
  $stmt = $conn->prepare("SELECT title, url, is_new FROM links WHERE section = ? ORDER BY id DESC");
  $stmt->bind_param("s", $section);
  $stmt->execute();
  $res = $stmt->get_result();
  $rows = $res->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

$latestJobs = fetch_links($conn, 'latestJobs');
$admitCards = fetch_links($conn, 'admitCards');
$results = fetch_links($conn, 'results');
$answerKey = fetch_links($conn, 'answerKey');
$syllabus = fetch_links($conn, 'syllabus');
$admission = fetch_links($conn, 'admission');
$otherLinks = fetch_links($conn, 'otherLinks');
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khorandi E-mitra Portal</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Poppins',sans-serif; background:#f4f6fa; }

    /* Navbar */
    header.navbar {
      background:#003366;
      color:white;
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:20px 25px;
      position:sticky;
      top:0;
      z-index:1000;
    }

    .logo {
      font-size:24px;
      font-weight:bold;
      color:#00e5ff;
      text-shadow:0 0 5px rgba(0,229,255,0.6);
    }

    .nav-links {
      display:flex;
      align-items:center;
      gap:18px;
    }

    .nav-links a, .dropbtn {
      color:white;
      text-decoration:none;
      font-weight:bold;
      padding:8px 12px;
      transition:0.3s;
    }

    .nav-links a:hover, .dropdown:hover .dropbtn {
      background:#ff6600d9;
      border-radius:2px;
    }

    .dropdown {
      position:relative;
    }

    /* 🔹 All Categories button updated */
    .dropbtn {
      background:#90a4aed4;
      border:2px solid yellow;  /* grape border */
      border-radius:4px;
      cursor:pointer;
      padding:8px 14px;
      font-weight:bold;
    }
    .dropbtn:hover {
      background:#5a00b3;
    }

    .dropdown-content {
      display:none;
      position:absolute;
      background:#004080;
      min-width:160px;
      border-radius:4px;
      top:42px;
      z-index:999;
    }

    .dropdown-content a {
      color:white;
      padding:10px 12px;
      display:block;
      text-decoration:none;
    }

    .dropdown-content a:hover { background:#ff6600; }
    .dropdown.show .dropdown-content { display:block; }

    .menu-toggle {
      display:none;
      background:none;
      border:none;
      color:white;
      font-size:26px;
      cursor:pointer;
    }

    @media(max-width:768px){
      .nav-links {
        display:none;
        flex-direction:column;
        background:#00264d;
        width:100%;
        position:absolute;
        top:80px;
        left:0;
        padding:10px 0;
      }

      .nav-links.show { display:flex; }

      .nav-links a {
        padding:14px 0;
        text-align:center;
        border-bottom:1px solid rgba(255,255,255,0.2);
      }

      .dropdown-content {
        position:static;
        background:#00264d;
      }

      .menu-toggle { display:block; }
    }

    /* 🔹 Welcome Line Animation (slow & continuous) */
    .welcome-line {
      background:silver;
      color:red;
      font-weight:bold;
      font-style:italic;
      font-size:18px;
      padding:8px 0;
      overflow:hidden;
      white-space:nowrap;
      text-align:center;
    }
    .welcome-line span {
      display:inline-block;
      white-space:nowrap;
      animation:scrollText 18s linear infinite;
    }
    @keyframes scrollText {
      from { transform:translateX(100%); }
      to { transform:translateX(-100%); }
    }

    /* Category Section */
    .container {
      width:90%;
      max-width:1100px;
      margin:30px auto;
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
      gap:20px;
    }

    .category {
      background:white;
      border-radius:4px;
      box-shadow:0 2px 10px rgba(0,0,0,0.1);
      padding:10px;
      transition:transform 0.3s,box-shadow 0.3s;
    }

  /*  .category:hover {
      transform:translateY(-5px);
      box-shadow:0 5px 15px rgba(0,0,0,0.2);
    }
*/
    .category h2 {
      background:#003366;
      color:#ffcc00;
      padding:10px;
      text-align:center;
      border-radius:3px;
      margin-bottom:15px;
    }

    .category ul { list-style:disc;
    padding-left : 25px;
    }
    .category ul li { margin:10px 0; }

    .category ul li a {
      color:#5e35b1;
      font-weight:bold;
      text-decoration:none;
    }

    .category ul li a:hover { color:#ff6600; }

    /* New tag animation */
    .new-tag {
      font-weight:bold;
      font-style:italic;
      margin-left:6px;
      animation:colorBlink 1.2s infinite;
    }

    @keyframes colorBlink {
      0% { color:red; }
      25% { color:#ff6600; }
      50% { color:#008000; }
      75% { color:#0066ff; }
      100% { color:#cc00cc; }
    }

    /* Footer */
    footer {
      background:#003366;
      color:white;
      text-align:center;
      padding:15px;
      margin-top:40px;
    }

    /* Popup Age Calculator */
    .popup {
      display:none;
      position:fixed;
      top:0; left:0;
      width:100%; height:100%;
      background:rgba(0,0,0,0.6);
      justify-content:center;
      align-items:center;
      z-index:10000;
    }

    .popup-content {
      background:white;
      padding:25px;
      border-radius:10px;
      text-align:center;
      width:320px;
      box-shadow:0 4px 12px rgba(0,0,0,0.3);
    }

    .popup-content h3 { color:#003366; margin-bottom:15px; }
    .popup-content input {
      width:80%;
      margin:10px 0;
      padding:10px;
      border:2px solid #1e88e5;
      border-radius:6px;
      text-align:center;
    }

    .calc-btn {
      background:#1e88e5;
      color:white;
      border:none;
      padding:10px 16px;
      border-radius:6px;
      cursor:pointer;
      font-weight:bold;
      margin-top:10px;
    }

    .calc-btn:hover { background:#0d47a1; }

    .clear-btn {
      background:purple;
      color:white;
      border:none;
      padding:10px 16px;
      border-radius:6px;
      cursor:pointer;
      font-weight:bold;
      margin-top:8px;
    }

    .clear-btn:hover { background:#800080; }

    .close-btn {
      background:#e91e63;
      color:white;
      border:none;
      padding:8px 14px;
      border-radius:6px;
      margin-top:12px;
      cursor:pointer;
    }

    .close-btn:hover { background:#c2185b; }

    .result { margin-top:10px; font-size:18px; color:#0d47a1; font-weight:bold; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <header class="navbar">
    <div class="logo">Khorandi E-mitra Portal</div>
    <button class="menu-toggle" id="menuToggle">☰</button>
    <nav class="nav-links" id="navLinks">
      <a href="#" onclick="showAll()">Home</a>
      <div class="dropdown" id="dropdownMenu">
        <button class="dropbtn" id="dropBtn">All Categories ▾</button>
        <div class="dropdown-content" id="categoryList">
          <a href="#latestJobs" data-section="latestJobs">Latest Jobs</a>
          <a href="#admitCards" data-section="admitCards">Admit Card</a>
          <a href="#results" data-section="results">Results</a>
          <a href="#answerKey" data-section="answerKey">Answer Key</a>
          <a href="#syllabus" data-section="syllabus">Syllabus</a>
          <a href="#admission" data-section="admission">Admission</a>
          <a href="#otherLinks" data-section="otherLinks">Important Links</a>
        </div>
      </div>
      <a href="#" onclick="toggleCalculator()">Age Calculator</a>
      <a href="admin.php">Admin Login</a>
      <a href="forgot.php">Forgot Password</a>
    </nav>
  </header>

  <!-- Welcome line -->
  <div class="welcome-line"><span> Khorandi E-mitra (ई-मित्र) पोर्टल में आपका स्वागत है! </span></div>

  <!-- Categories -->
  <div class="container" id="categoryContainer">
    <?php
    $sections = [
      "latestJobs" => "Latest Jobs",
      "admitCards" => "Admit Cards",
      "results" => "Results",
      "answerKey" => "Answer Key",
      "syllabus" => "Syllabus",
      "admission" => "Admission",
      "otherLinks" => "Important Links"
    ];
    foreach ($sections as $id => $title): ?>
      <div class="category" id="<?= $id ?>">
        <h2><?= $title ?></h2>
        <ul>
          <?php foreach (fetch_links($conn, $id) as $p): ?>
            <li><a href="<?= htmlspecialchars($p['url']) ?>" target="_blank"><?= htmlspecialchars($p['title']) ?></a>
              <?= $p['is_new'] ? '<span class="new-tag">New</span>' : '' ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Popup Age Calculator -->
  <div class="popup" id="agePopup">
    <div class="popup-content">
      <h3>🎂 Age Calculator</h3>
      <label>जन्म तिथि:</label><br>
      <input type="date" id="dob"><br>
      <label>वर्तमान तिथि:</label><br>
      <input type="date" id="current"><br>
      <button class="calc-btn" onclick="calculateAge()">Calculate</button>
      <button class="clear-btn" onclick="clearFields()">Clear</button>
      <div class="result" id="result"></div>
      <button class="close-btn" onclick="closePopup()">Close</button>
    </div>
  </div>

  <footer>
    © 2025 Khorandi E-mitra Portal | Designed by Ramesh Kumar Kumawat
  </footer>

  <script>
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const categoryList = document.getElementById('categoryList');
    const categories = document.querySelectorAll('.category');

    // Toggle menu on small screen
    menuToggle.addEventListener('click', () => navLinks.classList.toggle('show'));

    // Toggle dropdown open/close
    dropdownMenu.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle('show');
    });

    // Close dropdown if click outside
    document.addEventListener('click', (e) => {
      if (!dropdownMenu.contains(e.target)) dropdownMenu.classList.remove('show');
    });

    // 🔹 Updated - close dropdown after clicking category
    categoryList.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const section = link.dataset.section;
        categories.forEach(cat => {
          cat.style.display = (cat.id === section) ? 'block' : 'none';
        });
        dropdownMenu.classList.remove('show');
        navLinks.classList.remove('show');
        window.scrollTo({ top: document.getElementById(section).offsetTop - 60, behavior: 'smooth' });
      });
    });

    // Show all sections (home)
    function showAll() {
      categories.forEach(cat => cat.style.display = 'block');
    }

    function toggleCalculator() {
      document.getElementById('agePopup').style.display = 'flex';
    }

    function closePopup() {
      document.getElementById('agePopup').style.display = 'none';
      document.getElementById('result').innerHTML = '';
    }

    function clearFields() {
      document.getElementById('dob').value = '';
      document.getElementById('current').value = '';
      document.getElementById('result').innerHTML = '';
    }

    function calculateAge() {
      const dobVal = document.getElementById('dob').value;
      const curVal = document.getElementById('current').value;
      if (!dobVal || !curVal) { document.getElementById('result').innerText = '⚠ कृपया तारीखें भरे!'; return; }
      const dob = new Date(dobVal);
      const cur = new Date(curVal);
      if (dob > cur) { document.getElementById('result').innerText = '⚠ जन्म तिथि वर्तमान तिथि से बाद नहीं हो सकती!'; return; }
      let y = cur.getFullYear() - dob.getFullYear();
      let m = cur.getMonth() - dob.getMonth();
      let d = cur.getDate() - dob.getDate();
      if (d < 0) { m--; d += new Date(cur.getFullYear(), cur.getMonth(), 0).getDate(); }
      if (m < 0) { y--; m += 12; }
      document.getElementById('result').innerHTML = `आपकी उम्र है:<br><b>${y} वर्ष, ${m} माह, ${d} दिन</b>`;
    }
  </script>
</body>
</html>