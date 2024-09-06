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

$user_id = $_GET["id"];

if ($user_id == 1) {
  header("location: admin.php");
  exit;
}

$user_check = quick_query("SELECT username, password FROM users WHERE id = '$user_id'");
$user_check_rows = mysqli_num_rows($user_check);

if ($user_check_rows == 0) {
  header("location: admin.php");
  exit;
}

$user = mysqli_fetch_assoc($user_check);
$current_username = $user["username"];
$current_password = $user["password"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST["change_username"])) {
    if (empty(trim($_POST["username"]))) {
      $username_error = "Please enter a username.";
    } else if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
      $username_error = "Username can only contain letters, numbers, and underscores.";
    } else if (trim($_POST["username"]) == "admin") {
      $username_error = "This username is reserved.";
    } else {
      // Checks if username is taken or not
      $username_check = "SELECT username FROM users WHERE username = ?";
  
      if ($stmt = mysqli_prepare($connection, $username_check)) {
        mysqli_stmt_bind_param($stmt, "s", $param_username);
  
        $param_username = trim($_POST["username"]);
  
        if (mysqli_stmt_execute($stmt)) {
          mysqli_stmt_store_result($stmt);
  
          if (mysqli_stmt_num_rows($stmt) == 1) {
            $username_error = "This username is already taken.";
          } else {
            $username = trim($_POST["username"]);
          }
        } else {
          echo 'Oops! Something went wrong. Please try again later.';
        }
  
        mysqli_stmt_close($stmt);
      }
    }

    if (empty($username_error)) {  
      $update_username = "UPDATE users SET username = ? WHERE id = '$user_id'";
  
      if ($stmt = mysqli_prepare($connection, $update_username)) {
        mysqli_stmt_bind_param($stmt, "s", $param_username);
  
        $param_username = $username;
  
        if (mysqli_stmt_execute($stmt)) {
          header("location: admin.php");
          exit;
        } else {
          echo 'Oops! Something went wrong. Please try again later.';
        }
  
        mysqli_stmt_close($stmt);
      }
    }
  } else if (isset($_POST["change_password"])) {
    if (empty(trim($_POST["new_password"]))) {
      $new_password_error = "Please enter a password.";
    } else if (strlen(trim($_POST["new_password"])) < 3) {
      $new_password_error = "Password must have 3 or more characters.";
    } else {
      $new_password = trim($_POST["new_password"]);
    }
  
    if (empty(trim($_POST["confirm_password"]))) {
      $confirm_password_error = "Please confirm password.";
    } else {
      $confirm_password = trim($_POST["confirm_password"]);
  
      // Checks if password and confirmation match
      if (empty($new_password_error) && ($new_password != $confirm_password)) {
        $confirm_password_error = "Password did not match.";
      }
    }

    if (empty($new_password_error) && empty($confirm_password_error)) {  
      $update_password = "UPDATE users SET password = ? WHERE id = '$user_id'";
  
      if ($stmt = mysqli_prepare($connection, $update_password)) {
        mysqli_stmt_bind_param($stmt, "s", $param_password);
  
        $param_password = $new_password;
  
        if (mysqli_stmt_execute($stmt)) {
          header("location: admin.php");
          exit;
        } else {
          echo 'Oops! Something went wrong. Please try again later.';
        }
  
        mysqli_stmt_close($stmt);
      }
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
  <link rel="stylesheet" href="assets/styles/account-settings.css">
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
      <h1>Change Username</h1>
      <form autocomplete="off" action="" method="POST">
        <div class="form-item">
          <input autofocus type="text" id="username" name="username" placeholder="Username" value="<?= $current_username ?>">
          <?php if (!empty($username_error)) echo "<p>$username_error</p>" ?>
        </div>
        <button type="submit" name="change_username">CHANGE USERNAME</button>
      </form>

      <hr style="border-color: darkgray;">

      <h1>Change Password</h1>
      <form autocomplete="off" action="" method="POST">
        <div class="form-item">
          <input type="text" disabled value="Current Password: <?= $current_password ?>">
        </div>
        <div class="form-item">
          <input type="password" id="new_password" name="new_password" placeholder="New Password" value="<?php if (!empty($new_password)) echo $new_password ?>">
          <?php if (!empty($new_password_error)) echo "<p>$new_password_error</p>" ?>
        </div>
        <div class="form-item">
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
          <?php if (!empty($confirm_password_error)) echo "<p>$confirm_password_error</p>" ?>
        </div>
        <button type="submit" name="change_password">CHANGE PASSWORD</button>
      </form>
    </div>
  </main>
</body>
</html>