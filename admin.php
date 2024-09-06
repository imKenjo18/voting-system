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

$voters = quick_query("SELECT id, name, is_done_voting FROM users WHERE privilege = 'voter' ORDER BY name ASC");
$candidates = quick_query("SELECT id, username, position, party_list_id, votes FROM candidates ORDER BY party_list_id ASC, position ASC");
$party_lists = quick_query("SELECT id, name FROM party_lists ORDER BY name ASC");
$positions = quick_query("SELECT id, name, maximum FROM positions ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= WEBSITE_TITLE ?></title>
  <link rel="stylesheet" href="assets/styles/main.css">
  <link rel="stylesheet" href="assets/styles/admin.css">
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
    <div class="list-box-container">
      <div class="list-box list-box--voters">
        <h2>List of Voters</h2>
        <table>
          <tr>
            <th>Name</th>
            <th>Voted</th>
            <th class="list-box__actions">Actions</th>
          </tr>
          <?php
            while ($voter = mysqli_fetch_assoc($voters)) {
              $name = $voter["name"];
              $status_db = $voter["is_done_voting"];

              $id = $voter["id"];

              if ($status_db == "true") {
                $status = "Yes";
              } else {
                $status = "No";
              }

              echo
              "<tr>
              <td>$name</td>
              <td>$status</td>
              <td class=\"actions\">
                <div class=\"wrapper\">
                  <a href=\"edit?id=$id&of=users\" title=\"Edit\"><img class=\"icons\" id=\"edit-svg\" src=\"assets/icons/edit.svg\" width=\"25\" height=\"25\" alt=\"Edit\"></a>
                  <a href=\"account-settings?id=$id\" title=\"Account Settings\"><img class=\"icons\" id=\"settings-svg\" src=\"assets/icons/settings.svg\" width=\"28\" height=\"28\" alt=\"Account Settings\"></a>
                  <a onClick=\"return confirm('Proceed to Delete?');\" href=\"delete?id=$id&of=users\" title=\"Delete\"><img class=\"icons\" id=\"delete-svg\" src=\"assets/icons/delete.svg\" width=\"28\" height=\"28\" alt=\"Delete\"></a>
                </div>
              </td>
              </tr>";
            }
          ?>
        </table>
      </div>
    </div>

    <div class="list-box-container">
      <div class="list-box list-box--candidates">
        <h2>List of Candidates</h2>
        <table>
          <tr>
            <th>Name</th>
            <th>Position</th>
            <th>Party-List</th>
            <th>Votes</th>
            <th class="list-box__actions">Actions</th>
          </tr>
          <?php
            while ($candidate = mysqli_fetch_assoc($candidates)) {
              $username = $candidate["username"];
              $position = $candidate["position"];
              $party_list_id = $candidate["party_list_id"];
              $votes = $candidate["votes"];

              $name = get_query_result("SELECT name FROM users WHERE username = '$username'")["name"];
              $party_list = get_query_result("SELECT name FROM party_lists WHERE id = '$party_list_id'")["name"];

              $id = $candidate["id"];

              echo
              "<tr>
              <td>$name</td>
              <td>$position</td>
              <td>$party_list</td>
              <td>$votes</td>
              <td class=\"actions\">
                <div class=\"wrapper\">
                  <a href=\"edit?id=$id&of=candidates\" title=\"Edit\"><img class=\"icons\" id=\"edit-svg\" src=\"assets/icons/edit.svg\" width=\"25\" height=\"25\" alt=\"Edit\"></a>
                  <a onClick=\"return confirm('Proceed to Delete?');\" href=\"delete?id=$id&of=candidates\" title=\"Delete\"><img class=\"icons\" id=\"delete-svg\" src=\"assets/icons/delete.svg\" width=\"28\" height=\"28\" alt=\"Delete\"></a>
                </div>
              </td>
              </tr>";
            }
          ?>
        </table>
      </div>
    </div>

    <div class="list-box-container">
      <div class="list-box list-box--party-lists">
        <h2>List of Party-Lists</h2>
        <table>
          <tr>
            <th>Name</th>
            <th class="list-box__actions">Actions</th>
          </tr>
          <?php
            while ($party_list = mysqli_fetch_assoc($party_lists)) {
              $name = $party_list["name"];

              $id = $party_list["id"];

              echo
              "<tr>
              <td>$name</td>
              <td class=\"actions\">
                <div class=\"wrapper\">
                  <a href=\"edit?id=$id&of=party_lists\" title=\"Edit\"><img class=\"icons\" id=\"edit-svg\" src=\"assets/icons/edit.svg\" width=\"25\" height=\"25\" alt=\"Edit\"></a>
                  <a onClick=\"return confirm('Proceed to Delete?');\" href=\"delete?id=$id&of=party_lists\" title=\"Delete\"><img class=\"icons\" id=\"delete-svg\" src=\"assets/icons/delete.svg\" width=\"28\" height=\"28\" alt=\"Delete\"></a>
                </div>
              </td>
              </tr>";
            }
          ?>
        </table>
      </div>
    </div>

    <div class="list-box-container">
      <div class="list-box list-box--positions">
        <h2>List of Positions</h2>
        <table>
          <tr>
            <th>Name</th>
            <th>Seats</th>
            <th class="list-box__actions">Actions</th>
          </tr>
          <?php
            while ($position = mysqli_fetch_assoc($positions)) {
              $name = $position["name"];
              $max = $position["maximum"];

              $id = $position["id"];

              echo
              "<tr>
              <td>$name</td>
              <td>$max</td>
              <td class=\"actions\">
                <div class=\"wrapper\">
                  <a href=\"edit?id=$id&of=positions\" title=\"Edit\"><img class=\"icons\" id=\"edit-svg\" src=\"assets/icons/edit.svg\" width=\"25\" height=\"25\" alt=\"Edit\"></a>
                  <a onClick=\"return confirm('Proceed to Delete?');\" href=\"delete?id=$id&of=positions\" title=\"Delete\"><img class=\"icons\" id=\"delete-svg\" src=\"assets/icons/delete.svg\" width=\"28\" height=\"28\" alt=\"Delete\"></a>
                </div>
              </td>
              </tr>";
            }
          ?>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
<?php
mysqli_close($connection);
?>