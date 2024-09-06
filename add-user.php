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
require_once "assets/functions.php";

// Voter Registration Variables
$username = $password = $confirm_password = $username_error = $password_error = $confirm_password_error = $name = $name_error = '';

// Candidate Registration Variables
$candidate_username = $candidate_username_error = $candidate_position = $candidate_position_error = $candidate_party_list = $candidate_party_list_error = $candidate_valid = '';

// Checks users for choosing of candidate
$voter_check = "SELECT username, name FROM users WHERE privilege = 'voter' ORDER BY name ASC";
$voter_check_query = mysqli_query($connection, $voter_check);

// Checks positions for choosing of position
$position_check = "SELECT name FROM positions ORDER BY name ASC";
$position_check_query = mysqli_query($connection, $position_check);

// Checks party_lists for choosing of party-list
$party_list_check = "SELECT id, name FROM party_lists ORDER BY name ASC";
$party_list_check_query = mysqli_query($connection, $party_list_check);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST["add_voter"])) {
    // What the server does when adding a voter

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
  
    if (empty(trim($_POST["password"]))) {
      $password_error = "Please enter a password.";
    } else if (strlen(trim($_POST["password"])) < 3) {
      $password_error = "Password must have 3 or more characters.";
    } else {
      $password = trim($_POST["password"]);
    }
  
    if (empty(trim($_POST["confirm_password"]))) {
      $confirm_password_error = "Please confirm password.";
    } else {
      $confirm_password = trim($_POST["confirm_password"]);
  
      // Checks if password and confirmation match
      if (empty($password_error) && ($password != $confirm_password)) {
        $confirm_password_error = "Password did not match.";
      }
    }
  
    if (empty(trim($_POST["name"]))) {
      $name_error = "Please enter a name.";
    } else {
      $name = trim($_POST["name"]);
    }
  
    if (empty($username_error) && empty($password_error) && empty($confirm_password_error) && empty($name_error)) {  
      $insert_voter = "INSERT INTO users (username, password, privilege, name, is_done_voting) VALUES (?, ?, ?, ?, ?)";
  
      if ($stmt = mysqli_prepare($connection, $insert_voter)) {
        mysqli_stmt_bind_param($stmt, "sssss", $param_username, $param_password, $param_privilege, $param_name, $param_done_voting);
  
        $param_username = $username;
        $param_password = $password;
        $param_privilege = "voter";
        $param_name = $name;
        $param_done_voting = "false";
  
        if (mysqli_stmt_execute($stmt)) {
          $add_success = "Successfully added.";
        } else {
          echo 'Oops! Something went wrong. Please try again later.';
        }
  
        mysqli_stmt_close($stmt);
      }
    }
  } else if (isset($_POST["add_candidate"])) {
    // What the server does when adding candidate

    if (empty(trim($_POST["candidate"]))) {
      $candidate_username_error = "Please select an account.";
    } else {
      // Checks if username is registered and belongs to a voter
      $username_check = "SELECT username FROM users WHERE username = ? AND privilege = 'voter'";
  
      if ($stmt = mysqli_prepare($connection, $username_check)) {
        mysqli_stmt_bind_param($stmt, "s", $param_username);
  
        $param_username = trim($_POST["candidate"]);
  
        if (mysqli_stmt_execute($stmt)) {
          mysqli_stmt_store_result($stmt);
  
          if (mysqli_stmt_num_rows($stmt) == 1) { 
            $candidate_valid = true;
          } else {
            $candidate_username_error = "Username not found or does not belong to a registered account.";
          }
        } else {
          echo 'Oops! Something went wrong. Please try again later.';
        }
  
        mysqli_stmt_close($stmt);
      }

      if ($candidate_valid == true) {
        // Checks if username is already registered in the candidates list
        $candidate_check = "SELECT username FROM candidates WHERE username = ?";
        
        if ($stmt = mysqli_prepare($connection, $candidate_check)) {
          mysqli_stmt_bind_param($stmt, "s", $param_username);

          $param_username = trim($_POST["candidate"]);

          if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 0) {
              $candidate_username = trim($_POST["candidate"]);
            } else {
              $candidate_username_error = "Already registered in the candidates list.";
            }
          } else {
            echo 'Oops! Something went wrong. Please try again later.';
          }

          mysqli_stmt_close($stmt);
        }
      }
    }
  
    if (empty(trim($_POST["position"]))) {
      $candidate_position_error = "Please select a position.";
    } else {
      $candidate_position = trim($_POST["position"]);
    }

    if (empty(trim($_POST["party_list"]))) {
      $candidate_party_list_error = "Please select a party-list.";
    } else {
      $candidate_party_list = trim($_POST["party_list"]);
    }

    if (!empty($candidate_position) && !empty($candidate_party_list)) {
      // Checks if position is already fully occupied for the party-list in the candidates list
      $position_max = get_query_result("SELECT maximum FROM positions WHERE name = '$candidate_position'")["maximum"];
      
      $same_position_check = "SELECT username FROM candidates WHERE position = ? AND party_list_id = ?";
      
      if ($stmt = mysqli_prepare($connection, $same_position_check)) {
        mysqli_stmt_bind_param($stmt, "si", $param_position, $param_party_list);

        $param_position = $candidate_position;
        $param_party_list = $candidate_party_list;

        if (mysqli_stmt_execute($stmt)) {
          mysqli_stmt_store_result($stmt);

          if (mysqli_stmt_num_rows($stmt) < $position_max) {
            if (empty($candidate_username_error) && empty($candidate_position_error) && empty($candidate_party_list_error)) {
              $insert_candidate = "INSERT INTO candidates (username, position, party_list_id) VALUES (?, ?, ?)";
          
              if ($stmt2 = mysqli_prepare($connection, $insert_candidate)) {
                mysqli_stmt_bind_param($stmt2, "ssi", $param_username, $param_position, $param_party_list);
          
                $param_username = $candidate_username;
                $param_position = $candidate_position;
                $param_party_list = $candidate_party_list;
          
                if (mysqli_stmt_execute($stmt2)) {
                  $candidate_add_success = "Successfully added.";
                } else {
                  echo 'Oops! Something went wrong. Please try again later.';
                }
          
                mysqli_stmt_close($stmt2);
              }
            }
          } else {
              $candidate_same_position_error = "Position is fully occupied for this party-list.";
          }
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
  <link rel="stylesheet" href="assets/styles/add-user.css">
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
    <!-- ADD VOTER FORM -->
    <div id="voter-container">
      <h1>Add Voter</h1>
      <form autocomplete="off" action="" method="POST">
        <div class="form-item">
          <input autofocus type="text" id="voter-username" name="username" placeholder="Username" value="<?php
            if (empty($add_success)) {
              if (!empty($username)) {
                echo $username;
              }
            }
          ?>">
          <?php if (!empty($username_error)) echo "<p>$username_error</p>" ?>
        </div>

        <div class="form-item">
          <input type="password" id="voter-password" name="password" placeholder="Password" value="<?php
            if (empty($add_success)) {
              if (!empty($password)) {
                echo $password;
              }
            }
          ?>">
          <?php if (!empty($password_error)) echo "<p>$password_error</p>" ?>
        </div>

        <div class="form-item">
          <input type="password" id="voter-confirm-password" name="confirm_password" placeholder="Confirm Password" value="<?php
            if (empty($add_success)) {
              if (!empty($confirm_password)) {
                echo $confirm_password;
              }
            }
          ?>">
          <?php if (!empty($confirm_password_error)) echo "<p>$confirm_password_error</p>" ?>
        </div>

        <div class="form-item">
          <input type="text" id="voter-name" name="name" placeholder="Name" value="<?php
            if (empty($add_success)) {
              if (!empty($name)) {
                echo $name;
              }
            }
          ?>">
          <?php if (!empty($name_error)) echo "<p>$name_error</p>" ?>
        </div>
        
        <button type="submit" name="add_voter">Add</button>
        <?php if (!empty($add_success)) echo "<p style=\"margin-top: 10px;\">$add_success</p>" ?>
      </form>
    </div>

    <!-- ADD CANDIDATE FORM -->
    <div id="candidate-container">
      <h1>Add Candidate</h1>
      <form autocomplete="off" action="" method="POST">
        <div class="dropdown">
          <label>Candidate</label>
          <select name="candidate">
            <option hidden></option>
            <?php
              while ($row = mysqli_fetch_assoc($voter_check_query)) {
                $voter_username = $row["username"];
                $voter_name = $row["name"];
                echo "<option value=\"$voter_username\">$voter_name</option>";
              }
            ?>
          </select>
          <?php if (!empty($candidate_username_error)) echo "<p>$candidate_username_error</p>" ?>
        </div>

        <div class="dropdown">
          <label>Position</label>
          <select name="position">
            <option hidden></option>
            <?php
              while ($row = mysqli_fetch_assoc($position_check_query)) {
                $position_name = $row["name"];
                echo "<option>$position_name</option>";
              }
            ?>
          </select>
          <?php if (!empty($candidate_position_error)) echo "<p>$candidate_position_error</p>" ?>
        </div>

        <div class="dropdown">
          <label>Party-List</label>
          <select name="party_list">
            <option hidden></option>
            <?php
              while ($row = mysqli_fetch_assoc($party_list_check_query)) {
                $party_list_id = $row["id"];
                $party_list_name = $row["name"];
                echo "<option value=\"$party_list_id\">$party_list_name</option>";
              }
            ?>
          </select>
          <?php if (!empty($candidate_party_list_error)) echo "<p>$candidate_party_list_error</p>" ?>
        </div>

        <button type="submit" name="add_candidate">Add</button>
        <?php if (!empty($candidate_add_success)) echo "<p style=\"margin-top: 10px;\">$candidate_add_success</p>" ?>
        <?php if (!empty($candidate_same_position_error)) echo "<p style=\"margin-top: 10px;\">$candidate_same_position_error</p>" ?>
      </form>
    </div>
  </main>
</body>
</html>