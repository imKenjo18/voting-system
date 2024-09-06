<?php
session_start();

if (!isset($_SESSION["isLoggedIn"]) || $_SESSION["isLoggedIn"] !== true) {
  header("location: ./");
  exit;
}

if ($_SESSION["privilege"] != "voter") {
  header("location: admin.php");
  exit;
}

if ($_SESSION["voting_status"] == "closed") {
  header("location: result.php");
  exit;
}

if ($_SESSION["is_done_voting"] == "true") {
  header("location: result.php");
  exit;
}

require_once "assets/dbhandler.php";
require_once "assets/functions.php";

$vote_error = false;
$vote_success = false;

$voter_username = $_SESSION["username"];

$positions_array = array();
$positions_max_array = array();

$positions = quick_query("SELECT name, maximum FROM positions ORDER BY name ASC");

while ($position = mysqli_fetch_assoc($positions)) {
  $name = $position["name"];
  array_push($positions_array, $name);
  
  $max = $position["maximum"];
  array_push($positions_max_array, $max);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= WEBSITE_TITLE ?></title>
  <link rel="stylesheet" href="assets/styles/main.css">
  <link rel="stylesheet" href="assets/styles/home.css">
</head>
<body>
  <!-- HEADER / NAVIGATION -->
  <header>
    <nav>
      <a href="logout.php"><button type="button" class="logout-button">Log Out</button></a>
    </nav>
  </header>

  <!-- MAIN CONTENT -->
  <main>
    <?php
      if (count($positions_array) > 0) {
        echo "<h1>Ballot</h1>";
        echo '<form autocomplete="off" action="" method="POST">';
        echo '<div class="ballot">';
        for ($i = 0; $i < count($positions_array); $i++) {
          $position_name = $positions_array[$i];
          $position_max = $positions_max_array[$i];

          $position_query = quick_query("SELECT username, position, party_list_id, votes FROM candidates WHERE position = '$position_name' ORDER BY id ASC");
          
          // if (mysqli_num_rows($position_query) == 0) {
          //   continue;
          // }

          echo '<div class="list-box-container">';
          echo '<div class="list-box">';
          echo
          "<fieldset>
            <legend>$position_name / Vote for $position_max</legend>";


          while ($position_row = mysqli_fetch_assoc($position_query)) {
            $username = $position_row["username"];
            $party_list_id = $position_row["party_list_id"];
            $votes = $position_row["votes"];

            $name = get_query_result("SELECT name FROM users WHERE username = '$username'")["name"];
            $party_list = get_query_result("SELECT name FROM party_lists WHERE id = '$party_list_id'")["name"];

            // echo
            // "<div>
            //   <input type=\"checkbox\" id=\"$username\" name=\"$position_name\" value=\"$username\">
            //   <label for=\"$username\">$name ($party_list)</label>
            // </div>";
            echo
            "<div>
              <input type=\"checkbox\" id=\"$username\" name=\"{$position_name}[]\" value=\"$username\">
              <label for=\"$username\">$name ($party_list)</label>
            </div>";
          }
          
          echo "</fieldset>";
          
          if (isset($_POST["submit_ballot"])) {
            if (!empty($_POST[$position_name])) {
              $$position_name = $_POST[$position_name];
    
              if (count($$position_name) > $position_max) {
                echo "<p style=\"margin-top: 10px;\">Please select only $position_max or less.</p>";
                $vote_error = true;
              } else if (count($$position_name) <= $position_max) {
                foreach($$position_name as $selected) {
                  $votes = get_query_result("SELECT votes FROM candidates WHERE username = '$selected'")["votes"];
                  $votes++;

                  quick_query("UPDATE candidates SET votes = '$votes' WHERE username = '$selected'");
                }
                quick_query("UPDATE users SET is_done_voting = 'true' WHERE username = '$voter_username'");

                $vote_success = true;
              }
            }
          }

          echo "</div>";
          echo "</div>";

          if ($i == (count($positions_array) - 1)) {
            echo "</div>";
          }
        }
        
        echo '<button type="submit" id="submit-ballot" name="submit_ballot" onCLick="return confirm(\'THIS ACTION CANNOT BE UNDONE. Are you sure to submit this ballot?\');">Submit Ballot</button>';
        echo "</form>";
      }
    ?>
  </main>
</body>
</html>
<?php
if ($vote_error == false && $vote_success == true) {
  $_SESSION["is_done_voting"] = "true";
  header("location: result.php");
}
?>