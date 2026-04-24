<?php
include('includes/dbconnection.php');
try {
    $dbh->exec("ALTER TABLE tblbooking ADD COLUMN Latitude VARCHAR(100) AFTER Location");
    echo "Latitude added\n";
} catch (Exception $e) {
    echo "Latitude exists or failed\n";
}
try {
    $dbh->exec("ALTER TABLE tblbooking ADD COLUMN Longitude VARCHAR(100) AFTER Latitude");
    echo "Longitude added\n";
} catch (Exception $e) {
    echo "Longitude exists or failed\n";
}
?>
