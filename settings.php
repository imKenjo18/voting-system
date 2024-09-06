<?php
session_start();

if (!isset($_SESSION["isLoggedIn"]) || $_SESSION["isLoggedIn"] !== true) {
  header("location: ./");
  exit;
}

if ($_SESSION["privilege"] == "voter") {
  header("location: home.php");
  exit;
}

require_once "assets/dbhandler.php";
require_once "assets/functions.php";

$save_success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST["save"])) {
    if (isset($_POST["voting_status"])) {
      quick_query("UPDATE main_settings SET value = 'closed' WHERE preference = 'voting_status'");
      $_SESSION["voting_status"] = "closed";
    } else {
      quick_query("UPDATE main_settings SET value = 'open' WHERE preference = 'voting_status'");
      $_SESSION["voting_status"] = "open";
    }
    $save_success = "Settings saved.";
  }

  mysqli_close($connection);
} else {
  mysqli_close($connection);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= WEBSITE_TITLE ?></title>
  <link rel="stylesheet" href="assets/styles/main.css">
  <link rel="stylesheet" href="assets/styles/settings.css">
</head>
<body>
  <!-- HEADER / NAVIGATION -->
  <header>
    <nav>
      <a href="admin.php"><button type="button">Home</button></a>
      <a href="result.php"><button type="button">View Result</button></a>
      <a href="add-user.php"><button type="button">Add Voter/Candidate</button></a>
      <a href="add-party-position.php"><button type="button">Add Party-List/Position</button></a>
      <a href="settings.php"><button type="button">Settings</button></a>
      <a href="logout.php"><button type="button" class="logout-button">Log Out</button></a>
    </nav>
  </header>

  <!-- MAIN CONTENT -->
  <main>
    <!-- EDIT FORM -->
    <div id="edit-container">
      <h1>Settings</h1>
      <form autocomplete="off" action="" method="POST">
        <div class="form-item">
          <label class="switch--label">Close Voting</label>
          <label class="switch">
            <?php
              echo ($_SESSION["voting_status"] == "open") ? '<input type="checkbox" name="voting_status">' : '<input type="checkbox" name="voting_status" checked>';
            ?>
            <span class="slider round"></span>
          </label>
        </div>
        <button type="submit" name="save">Save</button>
        <?php if (!empty($save_success)) echo "<p style=\"margin-top: 10px;\">$save_success</p>" ?>
      </form>
    </div>
  </main>
</body>
</html>