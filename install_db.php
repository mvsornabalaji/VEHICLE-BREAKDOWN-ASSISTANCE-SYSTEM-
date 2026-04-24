<?php
$host = 'localhost';
$user = 'root';
$pass = '12022003';

try {
    $dbh = new PDO("mysql:host=$host", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Read SQL file
    $sql = file_get_contents('vehicle_assist_db.sql');
    $dbh->exec($sql);
    echo "Database created successfully\n";
} catch(PDOException $e) {
    echo "Creation failed: " . $e->getMessage();
}
?>
