<?php
session_start();
$notice = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nickname"])) {
    $nickname = trim($_POST["nickname"]);
    if ($nickname !== "") {
        $_SESSION["nickname"] = htmlspecialchars($nickname, ENT_QUOTES, "UTF-8");
        $notice = "Welcome, " . $_SESSION["nickname"] . "!";
    }
}
$nickname = $_SESSION["nickname"] ?? "";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SR Ludo</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="topbar">
    <div class="brand"><span class="brand-mark">SR</span><span>SR LUDO</span></div>
    <span class="tag">PLAY • FRIENDS • FUN</span>
  </header>
  <main>
    <section class="hero">
      <div class="hero-copy">
        <p class="eyebrow">WELCOME TO</p>
        <h1>SR <span>LUDO</span></h1>
        <p class="intro">Bring your friends together for a classic Ludo experience.</p>
        <?php if ($nickname): ?>
          <p class="welcome"><?= $notice ?: "Welcome back, " . $nickname . "!" ?></p>
        <?php else: ?>
          <form method="post" class="join-form">
            <label for="nickname">Choose a player name</label>
            <div class="form-row">
              <input id="nickname" name="nickname" maxlength="20" placeholder="Your name" required>
              <button type="submit">Join</button>
            </div>
          </form>
        <?php endif; ?>
        <a class="primary-link" href="#how-to-play">How to play</a>
      </div>
      <div class="board-card" aria-label="Decorative Ludo board preview">
        <div class="mini-board">
          <div class="home red"><i></i><i></i><i></i><i></i></div>
          <div class="path"></div>
          <div class="home green"><i></i><i></i><i></i><i></i></div>
          <div class="home yellow"><i></i><i></i><i></i><i></i></div>
          <div class="home blue"><i></i><i></i><i></i><i></i></div>
          <div class="center-star">★</div>
        </div>
      </div>
    </section>
    <section class="features" id="how-to-play">
      <article><b>01</b><h2>Invite friends</h2><p>Share your room details with friends and get ready to play.</p></article>
      <article><b>02</b><h2>Roll the dice</h2><p>Take turns, move your tokens, and enjoy the classic game.</p></article>
      <article><b>03</b><h2>Play fair</h2><p>Keep the game friendly and follow the rules agreed by everyone.</p></article>
    </section>
    <footer>© <?= date("Y") ?> SR Ludo. A free-play demo starter.</footer>
  </main>
</body>
</html>
