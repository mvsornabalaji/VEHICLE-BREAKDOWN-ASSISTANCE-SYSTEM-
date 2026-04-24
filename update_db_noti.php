<?php
include('includes/dbconnection.php');

try {
    $sql = "CREATE TABLE IF NOT EXISTS tblnotifications (
      ID int(11) NOT NULL AUTO_INCREMENT,
      BookingNumber varchar(120) DEFAULT NULL,
      Message mediumtext DEFAULT NULL,
      CreatedAt timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (ID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $dbh->exec($sql);
    echo "Created tblnotifications table successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
