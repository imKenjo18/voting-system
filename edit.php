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

$row_id = $_GET["id"];
$table_name = $_GET["of"];

// Variables
$name = $name_error = $max = $max_error = $candidate_valid = $candidate_username_error = $candidate_position_error = $candidate_party_list_error = '';

if ($table_name != "candidates") {
  $name = get_query_result("SELECT name FROM $table_name WHERE id = '$row_id'")["name"];
} else if ($table_name == "candidates") {
  $candidate = get_query_result("SELECT username, position, party_list_id FROM candidates WHERE id = '$row_id'");
  $candidate_username = $candidate["username"];
  $candidate_position = $candidate["position"];
  $candidate_party_list_id = $candidate["party_list_id"];

  $candidate_name = get_query_result("SELECT name FROM users WHERE username = '$candidate_username'")["name"];
  $candidate_party_list = get_query_result("SELECT name FROM party_lists WHERE id = '$candidate_party_list_id'")["name"];

  // $voter_check_query = quick_query("SELECT username, name FROM users WHERE privilege = 'voter' ORDER BY name ASC");
  $position_check_query = quick_query("SELECT name FROM positions ORDER BY name ASC");
  $party_list_check_query = quick_query("SELECT id, name FROM party_lists ORDER BY name ASC");
}

if ($table_name == "positions") {
  $max = get_query_result("SELECT maximum FROM $table_name WHERE id = '$row_id'")["maximum"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST["edit"])) {
    if ($table_name == "users") {
      // What the server does when editing a voter
      if (empty(trim($_POST["name"]))) {
        $name_error = "Please enter a name.";
      } else {
        $name = trim($_POST["name"]);
      }
    
      if (empty($name_error)) {
        $update_sql = "UPDATE $table_name SET name = ? WHERE id = $row_id";
    
        if ($stmt = mysqli_prepare($connection, $update_sql)) {
          mysqli_stmt_bind_param($stmt, "s", $param_name);
    
          $param_name = $name;
    
          if (mysqli_stmt_execute($stmt)) {
            header("location: admin.php");
            exit;
          } else {
            echo 'Oops! Something went wrong. Please try again later.';
          }
    
          mysqli_stmt_close($stmt);
        }
      }
    } else if ($table_name == "candidates") {
      // What the server does when editing a candidate    
      if (empty(trim($_POST["position"]))) {
        $candidate_position_error = "Please select a position.";
      } else {
        $candidate_new_position = trim($_POST["position"]);
      }
  
      if (empty(trim($_POST["party_list"]))) {
        $candidate_party_list_error = "Please select a party-list.";
      } else {
        $candidate_new_party_list = trim($_POST["party_list"]);
      }
  
      if (!empty($candidate_new_position) && !empty($candidate_new_party_list)) {
        // Checks if position is already fully occupied for the party-list in the candidates list
        if ($candidate_new_position == $candidate_position && $candidate_new_party_list == $candidate_party_list_id) {
          header("location: admin.php");
          exit;
        }
        
        $position_max = get_query_result("SELECT maximum FROM positions WHERE name = '$candidate_new_position'")["maximum"];
        
        $same_position_check = "SELECT username FROM candidates WHERE position = ? AND party_list_id = ?";
        
        if ($stmt = mysqli_prepare($connection, $same_position_check)) {
          mysqli_stmt_bind_param($stmt, "si", $param_position, $param_party_list);
  
          $param_position = $candidate_new_position;
          $param_party_list = $candidate_new_party_list;
  
          if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
  
            if (mysqli_stmt_num_rows($stmt) < $position_max) {
              if (empty($candidate_position_error) && empty($candidate_party_list_error)) {
                $update_candidate = "UPDATE candidates SET position = ?, party_list_id = ? WHERE username = '$candidate_username'";
            
                if ($stmt2 = mysqli_prepare($connection, $update_candidate)) {
                  mysqli_stmt_bind_param($stmt2, "si", $param_position, $param_party_list);
            
                  $param_position = $candidate_new_position;
                  $param_party_list = $candidate_new_party_list;
            
                  if (mysqli_stmt_execute($stmt2)) {
                    header("location: admin.php");
                    exit;
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
    } else if ($table_name == "party_lists") {
      // What the server does when editing a party-list
      if (empty(trim($_POST["name"]))) {
        $name_error = "Please enter a name.";
      } else {
        $name = trim($_POST["name"]);
      }
    
      if (empty($name_error)) {
        $update_sql = "UPDATE $table_name SET name = ? WHERE id = $row_id";
    
        if ($stmt = mysqli_prepare($connection, $update_sql)) {
          mysqli_stmt_bind_param($stmt, "s", $param_name);
    
          $param_name = $name;
    
          if (mysqli_stmt_execute($stmt)) {
            header("location: admin.php");
            exit;
          } else {
            echo 'Oops! Something went wrong. Please try again later.';
          }
    
          mysqli_stmt_close($stmt);
        }
      }
    } else if ($table_name == "positions") {
      // What the server does when editing a position
      if (empty(trim($_POST["name"]))) {
        $name_error = "Please enter a name.";
      } else {
        $name = trim($_POST["name"]);
      }

      if (empty(trim($_POST["max"]))) {
        $max_error = "Please add a maximum.";
      } else {
        $max = trim($_POST["max"]);
      }
    
      if (empty($name_error) && empty($max_error)) {
        $update_sql = "UPDATE $table_name SET name = ?, maximum = ? WHERE id = $row_id";
    
        if ($stmt = mysqli_prepare($connection, $update_sql)) {
          mysqli_stmt_bind_param($stmt, "si", $param_name, $param_max);
    
          $param_name = $name;
          $param_max = $max;
    
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
  <link rel="stylesheet" href="assets/styles/edit.css">
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
      <h1>Edit</h1>
      <form autocomplete="off" action="" method="POST">
        <?php
          if ($table_name != "candidates") {
            echo '<div class="form-item">';
            echo '<input autofocus type="text" id="name" name="name" placeholder="Name" value="';
            if (!empty($name)) echo $name;
            echo '">';
            
            if (!empty($name_error)) echo "<p>$name_error</p>";
            echo "</div>";
          } else if ($table_name == "candidates") {
            echo '<div class="dropdown">';
            echo "<label>Candidate</label>\n";
            echo '<select name="candidate" disabled>';
            echo "<option hidden value=\"$candidate_username\">$candidate_name</option>";

            // while ($row = mysqli_fetch_assoc($voter_check_query)) {
            //   $voter_username = $row["username"];
            //   $voter_name = $row["name"];
            //   echo "<option value=\"$voter_username\">$voter_name</option>";
            // }

            echo "</select>";
            if (!empty($candidate_username_error)) echo "<p>$candidate_username_error</p>";
            echo "</div>";

            echo '<div class="dropdown">';
            echo "<label>Position</label>\n";
            echo '<select name="position">';
            echo "<option hidden>$candidate_position</option>";

            while ($row = mysqli_fetch_assoc($position_check_query)) {
              $position_name = $row["name"];
              echo "<option>$position_name</option>";
            }

            echo "</select>";
            if (!empty($candidate_position_error)) echo "<p>$candidate_position_error</p>";
            echo "</div>";

            echo '<div class="dropdown">';
            echo "<label>Party-List</label>\n";
            echo '<select name="party_list">';
            echo "<option hidden value=\"$candidate_party_list_id\">$candidate_party_list</option>";

            while ($row = mysqli_fetch_assoc($party_list_check_query)) {
              $party_list_id = $row["id"];
              $party_list_name = $row["name"];
              echo "<option value=\"$party_list_id\">$party_list_name</option>";
            }

            echo "</select>";
            if (!empty($candidate_position_error)) echo "<p>$candidate_position_error</p>";
            echo "</div>";
          }

          if ($table_name == "positions") {
            echo '<div class="form-item">';
            echo '<input type="text" id="max" name="max" placeholder="Number of Seats" value="';
            if (!empty($max)) echo $max;
            echo '">';
            
            if (!empty($max_error)) echo "<p>$max_error</p>";
            echo "</div>";
          }
        ?>
        <button type="submit" name="edit">Edit</button>
        <?php if (!empty($candidate_same_position_error)) echo "<p style=\"margin-top: 10px;\">$candidate_same_position_error</p>" ?>
      </form>
    </div>
  </main>
</body>
</html>