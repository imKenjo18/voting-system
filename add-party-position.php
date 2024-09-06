<?php
session_start();

if (!isset($_SESSION["isLoggedIn"]) || $_SESSION["isLoggedIn"] !== true) {
  header("location: ./");
  exit;
}

if ($_SESSION["privilege"] != "root") {
  header("location: home.php");
  exit;
} else if ($_SESSION["privilege"] == "admin") {
  header("location: home.php");
  exit;
}

require_once "assets/dbhandler.php";

// Party-List Variables
$name = $name_error = '';

// Position Variables
$position_name = $position_name_error = $position_maximum = $position_maximum_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST["add_party_list"])) {
    if (empty(trim($_POST["name"]))) {
      $name_error = "Please enter a name.";
    } else {
      // Checks if party list name is taken or not
      $party_list_check = "SELECT id FROM party_lists WHERE name = ?";

      if ($stmt = mysqli_prepare($connection, $party_list_check)) {
        mysqli_stmt_bind_param($stmt, "s", $param_name);

        $param_name = trim($_POST["name"]);

        if (mysqli_stmt_execute($stmt)) {
          mysqli_stmt_store_result($stmt);

          if (mysqli_stmt_num_rows($stmt) == 1) {
            $name_error = "This name is already taken.";
          } else {
            $name = trim($_POST["name"]);
          }
        } else {
          echo 'Oops! Something went wrong. Please try again later.';
        }

        mysqli_stmt_close($stmt);
      }
    }
    
    if (empty($name_error)) {
      $insert_party_list = "INSERT INTO party_lists (name) VALUES (?)";

      if ($stmt = mysqli_prepare($connection, $insert_party_list)) {
        mysqli_stmt_bind_param($stmt, "s", $param_name);

        $param_name = $name;

        if (mysqli_stmt_execute($stmt)) {
          $add_success = "Successfully added.";
        } else {
          echo 'Oops! Something went wrong. Please try again later.';
        }

        mysqli_stmt_close($stmt);
      }
    }
  } else if (isset($_POST["add_position"])) {
    if (empty(trim($_POST["position_name"]))) {
      $position_name_error = "Please enter a name.";
    } else {
      // Checks if position name is registered or not
      $position_check = "SELECT id FROM positions WHERE name = ?";

      if ($stmt = mysqli_prepare($connection, $position_check)) {
        mysqli_stmt_bind_param($stmt, "s", $param_name);

        $param_name = trim($_POST["position_name"]);

        if (mysqli_stmt_execute($stmt)) {
          mysqli_stmt_store_result($stmt);

          if (mysqli_stmt_num_rows($stmt) == 1) {
            $position_name_error = "This position is already registered.";
          } else {
            $position_name = trim($_POST["position_name"]);
          }
        } else {
          echo 'Oops! Something went wrong. Please try again later.';
        }

        mysqli_stmt_close($stmt);
      }
    }

    if (empty(trim($_POST["position_maximum"]))) {
      $position_maximum_error = "Please add a maximum.";
    } else {
      $position_maximum = trim($_POST["position_maximum"]);
    }
    
    if (empty($position_name_error) && empty($position_maximum_error)) {
      $insert_position = "INSERT INTO positions (name, maximum) VALUES (?, ?)";

      if ($stmt = mysqli_prepare($connection, $insert_position)) {
        mysqli_stmt_bind_param($stmt, "si", $param_name, $param_maximum);

        $param_name = $position_name;
        $param_maximum = $position_maximum;

        if (mysqli_stmt_execute($stmt)) {
          $position_add_success = "Successfully added.";
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
  <link rel="stylesheet" href="assets/styles/add-party-position.css">
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
    <!-- ADD PARTY-LIST FORM -->
    <div id="party-list-container">
      <h1>Add Party-List</h1>
      <form autocomplete="off" action="" method="POST">
        <div class="form-item">
          <input autofocus type="text" id="party-list-name" name="name" placeholder="Name" value="<?php
            if (empty($add_success)) {
              if (!empty($name)) {
                echo $name;
              }
            }
          ?>">
          <?php if (!empty($name_error)) echo "<p>$name_error</p>" ?>
        </div>

        <button type="submit" name="add_party_list">Add</button>
        <?php if (!empty($add_success)) echo "<p style=\"margin-top: 10px;\">$add_success</p>" ?>
      </form>
    </div>

    <!-- ADD POSITION FORM -->
    <div id="position-container">
      <h1>Add Position</h1>
      <form autocomplete="off" action="" method="POST">
        <div class="form-item">
          <input type="text" id="position-name" name="position_name" placeholder="Name" value="<?php
            if (empty($position_add_success)) {
              if (!empty($position_name)) {
                echo $position_name;
              }
            }
          ?>">
          <?php if (!empty($position_name_error)) echo "<p>$position_name_error</p>" ?>
        </div>

        <div class="form-item">
          <input type="number" id="position-maximum" name="position_maximum" placeholder="Number of Seats" value="<?php
            if (empty($position_add_success)) {
              if (!empty($position_maximum)) {
                echo $position_maximum;
              }
            }
          ?>">
          <?php if (!empty($position_maximum_error)) echo "<p>$position_maximum_error</p>" ?>
        </div>

        <button type="submit" name="add_position">Add</button>
        <?php if (!empty($position_add_success)) echo "<p style=\"margin-top: 10px;\">$position_add_success</p>" ?>
      </form>
    </div>
  </main>
</body>
</html>