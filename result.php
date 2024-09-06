<?php
session_start();

if (!isset($_SESSION["isLoggedIn"]) || $_SESSION["isLoggedIn"] !== true) {
  header("location: ./");
  exit;
}

if ($_SESSION["privilege"] == "voter") {
  if ($_SESSION["voting_status"] == "open") {
    if ($_SESSION["is_done_voting"] == "false") {
      header("location: home.php");
      exit;
    }
  }
}

require_once "assets/dbhandler.php";
require_once "assets/functions.php";

$positions_array = array();

$positions = quick_query("SELECT name FROM positions ORDER BY name ASC");

while ($position = mysqli_fetch_assoc($positions)) {
  $name = $position["name"];
  array_push($positions_array, $name);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= WEBSITE_TITLE ?></title>
  <link rel="stylesheet" href="assets/styles/main.css">
  <link rel="stylesheet" href="assets/styles/result.css">
</head>
<body>
  <!-- HEADER / NAVIGATION -->
  <header>
    <nav>
      <?php
        if ($_SESSION["privilege"] != "voter") {
          echo '<a href="admin.php"><button type="button">Home</button></a>' . "\n";
          echo '<a href="result.php"><button type="button">View Result</button></a>' . "\n";
          echo '<a href="add-user.php"><button type="button">Add Voter/Candidate</button></a>' . "\n";
          echo '<a href="add-party-position.php"><button type="button">Add Party-List/Position</button></a>' . "\n";
          echo '<a href="settings.php"><button type="button">Settings</button></a>';
        }
      ?>
      <a href="logout.php"><button type="button" class="logout-button">Log Out</button></a>
    </nav>
  </header>

  <!-- MAIN CONTENT -->
  <main>
    <?php
      if (count($positions_array) > 0) {
        echo ($_SESSION["voting_status"] == "open") ? "<h1>Current Result</h1>" : "<h1>Final Result</h1>";
        echo '<div class="results">';
        for ($i = 0; $i < count($positions_array); $i++) {
          $position_name = $positions_array[$i];
          echo '<div class="list-box-container">';
          echo '<div class="list-box">';
          echo "<h2>$position_name</h2>";

          if ($_SESSION["voting_status"] == "open") {
            $position_query = quick_query("SELECT username, position, party_list_id, votes FROM candidates WHERE position = '$position_name' ORDER BY votes DESC");
  
            while ($position_row = mysqli_fetch_assoc($position_query)) {
              $username = $position_row["username"];
              $party_list_id = $position_row["party_list_id"];
              $votes = $position_row["votes"];
  
              $name = get_query_result("SELECT name FROM users WHERE username = '$username'")["name"];
              $party_list = get_query_result("SELECT name FROM party_lists WHERE id = '$party_list_id'")["name"];
  
              echo
              "<p>$name ($party_list) - $votes</p>";
            }
          } else {
            $position_query = quick_query("SELECT username, position, party_list_id, votes FROM candidates WHERE position = '$position_name' AND votes = (SELECT MAX(votes) from candidates WHERE position = '$position_name')");
  
            while ($position_row = mysqli_fetch_assoc($position_query)) {
              $username = $position_row["username"];
              $party_list_id = $position_row["party_list_id"];
              $votes = $position_row["votes"];
  
              $name = get_query_result("SELECT name FROM users WHERE username = '$username'")["name"];
              $party_list = get_query_result("SELECT name FROM party_lists WHERE id = '$party_list_id'")["name"];
  
              echo
              "<p>$name ($party_list) - $votes</p>";
            }
          }
  
            echo "</div>";
            echo "</div>"; 
        }
        echo "</div>";
      }
    ?>
  </main>
</body>
</html>
<?php
mysqli_close($connection);
?>