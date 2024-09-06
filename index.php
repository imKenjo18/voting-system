<?php
session_start();

if (isset($_SESSION["isLoggedIn"]) && $_SESSION["isLoggedIn"] === true) {
  if ($_SESSION["privilege"] == "root") {
    header("location: admin.php");
    exit;
  } else if ($_SESSION["privilege"] == "admin") {
    header("location: admin.php");
    exit;
  } else if ($_SESSION["privilege"] == "student") {
    header("location: home.php");
    exit;
  }
}

session_destroy();

require_once "assets/dbhandler.php";

$username = $username_error = $password = $password_error = $login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty(trim($_POST["username"]))) {
    $username_error = "Please enter your username.";
  } else {
    $username = trim($_POST["username"]);
  }

  if (empty(trim($_POST["password"]))) {
    $password_error = "Please enter your password.";
  } else {
    $password = trim($_POST["password"]);
  }

  if (empty($username_error) && empty($password_error)) {
    $sql = "SELECT users.id, username, password, privilege, name, is_done_voting, preference, value FROM users, main_settings WHERE username = ? AND main_settings.id = 1";

    if ($stmt = mysqli_prepare($connection, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $param_username);

      $param_username = $username;

      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
          mysqli_stmt_bind_result($stmt, $id, $username, $password_db, $privilege, $name, $is_done_voting, $preference, $value);

          if (mysqli_stmt_fetch($stmt)) {
            if ($password == $password_db) {
              session_start();

              $_SESSION["isLoggedIn"] = true;
              $_SESSION["id"] = $id;
              $_SESSION["username"] = $username;
              $_SESSION["privilege"] = $privilege;
              $_SESSION["voting_status"] = $value;

              if ($_SESSION["privilege"] == "root") {
                header("location: admin.php");
              } else if ($_SESSION["privilege"] == "admin") {
                header("location: admin.php");
              } else if ($_SESSION["privilege"] == "voter") {
                $_SESSION["name"] = $name;
                $_SESSION["is_done_voting"] = $is_done_voting;

                header("location: home.php");
              }
            } else {
              $login_error = "Invalid username or password.";
            }
          }
        } else {
          $login_error = "Invalid username or password.";
        }
      } else {
        $login_error = "Oops! Something went wrong. Please try again later.";
      }

      mysqli_stmt_close($stmt);
    }
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
  <link rel="stylesheet" href="assets/styles/login.css">
</head>
<body>
  <main>
    <!-- LOGIN CONTAINER -->
    <div id="login-container">
      <h1>Voting System</h1>
      <form autocomplete="off" action="" method="POST">
        <div class="form-item">
          <input type="text" id="login-username" name="username" placeholder="Username" value="<?= $username ?>">
          <?php if (!empty($username_error)) echo "<p>$username_error</p>" ?>
        </div>

        <div class="form-item">
          <input type="password" id="login-password" name="password" placeholder="Password" oninput="checkPasswordValue('#login-password', '.toggle-password-view');">
          <span class="toggle-password-view" onclick="togglePasswordView('#login-password', '#view-password-toggle');"><img src="assets/icons/eye.svg" id="view-password-toggle" width="20" title="Show password"></span>
          <?php if (!empty($password_error)) echo "<p>$password_error</p>" ?>
        </div>

        <button type="submit">Log In</button>
        <?php
          if (!empty($login_error)) {
            echo '<div class="login-error">' . $login_error . '</div>';
          }
        ?>
      </form>
    </div>
    <div id="background"></div>
  </main>

  <script src="assets/scripts/password-toggle.js"></script>
</body>
</html>