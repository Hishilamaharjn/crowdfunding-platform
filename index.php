<?php 
session_start();
require 'db_connect.php'; 

$projects = [];

try {
    $stmt = $pdo->query("SELECT * FROM projects WHERE status = 'approved' ORDER BY id DESC");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error in index.php: " . $e->getMessage());
    $projects = []; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Crowdfunding - Empowering Dreams</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  background: linear-gradient(135deg, #007bff, #00aaff);
  color: #333;
  min-height: 100vh;
  padding-top: 90px;
}

/* Header */
header {
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  position: fixed;
  top: 0;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 40px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2);
  z-index: 1000;
}
header .logo { color: #fff; font-weight: 700; font-size: 1.5rem; cursor: pointer; }
header .logo:hover { color: #00ffd1; }
nav a { color: white; text-decoration: none; margin-left: 25px; font-weight: 500; padding: 5px 10px; border-radius: 5px; }
nav a:hover { color: #00ffd1; background: rgba(255,255,255,0.1); }

/* Hero */
.hero { text-align: center; padding: 60px 20px 40px; color: white; }
.hero h1 { font-size: 2.5rem; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
.hero p { font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 0 auto; }

/* Projects */
.projects {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
  max-width: 1100px;
  margin: 20px auto 40px;
  padding: 0 20px;
}
.card {
  background: rgba(255,255,255,0.95);
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  overflow: hidden;
  transition: all 0.3s ease;
  position: relative;
}
.card:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
.card img { width: 100%; height: 180px; object-fit: cover; }
.card-body { padding: 15px; }
.card-body h3 { color: #004aad; margin: 0 0 6px; font-size: 1.1rem; }
.card-body p { font-size: 0.9rem; color: #555; line-height: 1.4; }
.card-body .goal { font-weight: 600; color: #007bff; margin-top: 8px; }

/* Guest overlay */
.card .guest-overlay {
  position: absolute;
  top:0; left:0; width:100%; height:100%;
  display:flex; justify-content:center; align-items:center;
  color:white; font-weight:bold; font-size:1rem;
  border-radius: 12px; text-align:center;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(5px);
  transition: all 0.3s ease;
}
.card:hover .guest-overlay { background: rgba(0,0,0,0.7); }
.card .guest-overlay i { margin-right: 8px; color: #ffd700; }

/* Footer */
footer {
  background: rgba(0,0,0,0.3);
  color: white;
  text-align: center;
  padding: 20px;
  margin-top: 40px;
  backdrop-filter: blur(10px);
}
footer p { margin: 0; opacity: 0.8; }

/* Modal */
#guestModal {
  display: none;
  position: fixed;
  z-index: 2000;
  left: 0; top: 0; width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.6);
  backdrop-filter: blur(5px);
  justify-content: center; align-items: center;
}
#guestModal .modal-content {
  background: white;
  padding: 25px 30px;
  border-radius: 20px;
  max-width: 400px;
  text-align: center;
}
#guestModal .modal-content .btn {
  margin: 10px 5px 0 5px;
  border-radius: 50px; padding: 8px 20px;
}
</style>
</head>
<body>

<header>
  <div class="logo" onclick="location.href='index.php'">🌐 Crowdfunding</div>
  <nav>
    <a href="about.php">About Us</a>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
  </nav>
</header>

<section class="hero">
  <h1>Empowering Dreams, Connecting Futures</h1>
  <p>Discover and support amazing projects that shape the future.</p>
</section>

<main>
  <div class="projects">
    <?php if (empty($projects)): ?>
      <p style="grid-column:1 / -1; text-align:center; color:white; font-size:1.2rem; padding: 40px;">
        No approved projects yet. Check back soon!
      </p>
    <?php else: ?>
      <?php foreach ($projects as $p): 
        $is_logged_in = isset($_SESSION['user_id']);
        $image = htmlspecialchars($p['image'] ?? 'https://via.placeholder.com/300x180?text=Project+Image');
        $title = htmlspecialchars($p['title']);
        $desc = htmlspecialchars(substr($p['description'], 0, 80));
        $goal = number_format($p['goal_amount'] ?? 0, 2);
      ?>
      <div class="card" 
           onclick="<?= $is_logged_in ? "location.href='project_view.php?id={$p['id']}'" : "showGuestModal()" ?>">
        <img src="<?= $image ?>" alt="<?= $title ?>">
        <?php if (!$is_logged_in): ?>
          <div class="guest-overlay"><i class="fa-solid fa-lock"></i> Login/Register to view</div>
        <?php endif; ?>
        <div class="card-body">
          <h3><?= $title ?></h3>
          <p><?= $desc ?>...</p>
          <p class="goal">Goal: $<?= $goal ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<footer>
  <p>© 2025 Crowdfunding | Empowering Innovation & Community Growth</p>
</footer>

<!-- Guest Modal -->
<div id="guestModal">
  <div class="modal-content">
    <h3>Restricted</h3>
    <p>Login or Register to view this project.</p>
    <a href="login.php" class="btn btn-primary">Login</a>
    <a href="register.php" class="btn btn-success">Register</a>
    <br>
    <button onclick="closeGuestModal()" class="btn btn-secondary mt-3">Close</button>
  </div>
</div>

<script>
function showGuestModal() {
    document.getElementById('guestModal').style.display = 'flex';
}
function closeGuestModal() {
    document.getElementById('guestModal').style.display = 'none';
}
</script>

</body>
</html>
