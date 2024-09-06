<?php
function get_query_result(string $sql_query) {
  global $connection;

  $sql = $sql_query;
  $query = mysqli_query($connection, $sql);
  
  return mysqli_fetch_assoc($query);
}

function quick_query(string $sql_query) {
  global $connection;

  $sql = $sql_query;
  return mysqli_query($connection, $sql);
}
?>