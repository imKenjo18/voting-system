<?php
// error_reporting(0);
require_once "functions.php";

define('WEBSITE_TITLE', 'Voting System');

// Login to phpmyadmin
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');

date_default_timezone_set('Asia/Manila');

$loginConn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Creates database if it doesn't exist
$createDB = "CREATE DATABASE IF NOT EXISTS `voting_system`";
mysqli_query($loginConn, $createDB);

// Connects to database
define('DB_NAME', 'voting_system');
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($connection === false) {
  die('ERROR: Could not connect. ' . mysqli_connect_error());
}

// Creates the "users" table in the database
$createUsersTable = "CREATE TABLE IF NOT EXISTS `voting_system`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(255) NOT NULL ,
  `password` VARCHAR(255) NOT NULL ,
  `privilege` VARCHAR(255) NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `is_done_voting` VARCHAR(255) NOT NULL ,
  `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `date_edited` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE (`username`)
  ) ENGINE = InnoDB;";
mysqli_query($connection, $createUsersTable);

// Creates the "party_lists" table in the database
$createPartyListsTable = "CREATE TABLE IF NOT EXISTS `voting_system`.`party_lists` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  -- `abbreviation` VARCHAR(255) NOT NULL ,
  `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `date_edited` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE (`name`)
  ) ENGINE = InnoDB;";
mysqli_query($connection, $createPartyListsTable);

// Creates the "positions" table in the database
$createPositionsTable = "CREATE TABLE IF NOT EXISTS `voting_system`.`positions` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  `maximum` INT NOT NULL ,
  -- `ranking` INT NOT NULL ,
  `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `date_edited` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE (`name`)
  -- UNIQUE (`ranking`)
  ) ENGINE = InnoDB;";
mysqli_query($connection, $createPositionsTable);

// Creates the "candidates" table in the database
$createCandidatesTable = "CREATE TABLE IF NOT EXISTS `voting_system`.`candidates` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(255) NOT NULL ,
  `position` VARCHAR(255) NOT NULL ,
  `party_list_id` INT NOT NULL ,
  `votes` INT NOT NULL DEFAULT '0' ,
  `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `date_edited` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE (`username`) ,
  CONSTRAINT `fk_candidates_users` FOREIGN KEY (`username`) REFERENCES `users`(`username`) ON DELETE CASCADE ON UPDATE CASCADE ,
  CONSTRAINT `fk_candidates_party_lists` FOREIGN KEY (`party_list_id`) REFERENCES `party_lists`(`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
  CONSTRAINT `fk_candidates_positions` FOREIGN KEY (`position`) REFERENCES `positions`(`name`) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE = InnoDB;";
mysqli_query($connection, $createCandidatesTable);

// Creates the "main_settings" table in the database
$createMainSettingsTable = "CREATE TABLE IF NOT EXISTS `voting_system`.`main_settings` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `preference` VARCHAR(255) NOT NULL ,
  `value` VARCHAR(255) NOT NULL ,
  `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `date_edited` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE (`preference`)
  ) ENGINE = InnoDB;";
mysqli_query($connection, $createMainSettingsTable);

$usersSql = "SELECT * FROM users";
$usersResult = mysqli_query($connection, $usersSql);

// Creates Admin account
if (mysqli_num_rows($usersResult) == 0) {
  $addAdminSql = "INSERT INTO `users` (`username`, `password`, `privilege`) VALUES ('admin', 'admin', 'root')";
  mysqli_query($connection, $addAdminSql);
}

$settingsSql = "SELECT * FROM main_settings";
$settingsResult = mysqli_query($connection, $settingsSql);

if (mysqli_num_rows($settingsResult) == 0) {
  quick_query("INSERT INTO `main_settings` (`preference`, `value`) VALUES ('voting_status', 'open')");
}
?>