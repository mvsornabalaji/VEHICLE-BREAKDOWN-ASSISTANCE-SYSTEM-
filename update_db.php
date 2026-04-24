<?php
include('includes/dbconnection.php');

try {
    // Add ServiceCost column
    $sql1 = "ALTER TABLE tblbooking ADD ServiceCost DECIMAL(10,2) NULL DEFAULT NULL AFTER CompletionDate";
    $dbh->exec($sql1);
    echo "Added ServiceCost column.\n";
} catch (PDOException $e) {
    echo "Error adding column (might exist already): " . $e->getMessage() . "\n";
}

try {
    // Create tblpayment table
    $sql2 = "CREATE TABLE IF NOT EXISTS tblpayment (
      ID int(11) NOT NULL AUTO_INCREMENT,
      BookingNumber varchar(120) DEFAULT NULL,
      PaymentMode varchar(50) DEFAULT NULL,
      TransactionID varchar(100) DEFAULT NULL,
      PaymentAmount decimal(10,2) DEFAULT NULL,
      PaymentStatus varchar(50) DEFAULT NULL,
      PaymentDate timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (ID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $dbh->exec($sql2);
    echo "Created tblpayment table.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
