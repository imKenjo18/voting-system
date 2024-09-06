<?php
require_once "../assets/dbhandler.php";
require_once "../assets/functions.php";

$result = get_query_result("SELECT * FROM positions WHERE name = 'Councilor'");

$sql = "SELECT maximum FROM positions WHERE name = ?";
$current = 10;
$editable_text = "Edit using the edit popover";

if ($stmt = mysqli_prepare($connection, $sql)) {
  mysqli_stmt_bind_param($stmt, "s", $param_name);

  $param_name = "Councilor";

  if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 1) {
      mysqli_stmt_bind_result($stmt, $maximum_db);

      if (mysqli_stmt_fetch($stmt)) {
        if ($current < $maximum_db) {
          $allow = "Yes, please.";
        } else {
          $allow = "No more.";
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

if (isset($_POST["edit"])) {
  $editable_text = trim($_POST["text_to_show"]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body style="height: 200vh">
  <?php
    echo $result["name"] . ' - ' . $result["maximum"];
    echo "<br>";
    echo $allow;
    echo "<br>";
    echo $editable_text;
    echo "<br>";
  ?>
  <button popovertarget="apop">Pop up</button>
  <h1 popover id="apop">Hello</h1>

  <button popovertarget="apop2">Pop up2</button>
  <h1 popover id="apop2">Hello2</h1>

  <div popover id="edit-container">
    <h1>Edit</h1>
    <form autocomplete="off" action="" method="POST">
      <input autofocus type="text" id="name" name="text_to_show" placeholder="Text to show">
      <button type="submit" name="edit">Edit</button>
    </form>
  </div>
  <button popovertarget="edit-container">
    <img class="icons" id="edit-svg" src="../assets/icons/edit.svg" width="25" height="25" alt="Edit">
  </button>
</body>
</html>