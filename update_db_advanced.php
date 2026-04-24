<?php
include('includes/dbconnection.php');

$steps = [];

// 1. Add AvailabilityStatus to tbldriver
try {
    $dbh->exec("ALTER TABLE tbldriver ADD COLUMN AvailabilityStatus ENUM('Available','Busy','Offline') NOT NULL DEFAULT 'Available'");
    $steps[] = "✅ Added AvailabilityStatus to tbldriver";
} catch (PDOException $e) { $steps[] = "⚠️ AvailabilityStatus: " . $e->getMessage(); }

// 2. Add Latitude/Longitude to tblbooking (may already exist)
try {
    $dbh->exec("ALTER TABLE tblbooking ADD COLUMN Latitude VARCHAR(50) NULL DEFAULT NULL");
    $steps[] = "✅ Added Latitude to tblbooking";
} catch (PDOException $e) { $steps[] = "⚠️ Latitude: " . $e->getMessage(); }

try {
    $dbh->exec("ALTER TABLE tblbooking ADD COLUMN Longitude VARCHAR(50) NULL DEFAULT NULL");
    $steps[] = "✅ Added Longitude to tblbooking";
} catch (PDOException $e) { $steps[] = "⚠️ Longitude: " . $e->getMessage(); }

// 3. Add IsRead to tblnotifications
try {
    $dbh->exec("ALTER TABLE tblnotifications ADD COLUMN IsRead TINYINT(1) NOT NULL DEFAULT 0");
    $steps[] = "✅ Added IsRead to tblnotifications";
} catch (PDOException $e) { $steps[] = "⚠️ IsRead: " . $e->getMessage(); }

// 4. Add DriverID to tblfeedback for easier joins
try {
    $dbh->exec("ALTER TABLE tblfeedback ADD COLUMN DriverID INT(11) NULL DEFAULT NULL");
    $steps[] = "✅ Added DriverID to tblfeedback";
} catch (PDOException $e) { $steps[] = "⚠️ DriverID in tblfeedback: " . $e->getMessage(); }

echo "<pre style='font-family:monospace;font-size:15px;padding:20px;'>";
echo "<b>Database Update Results:</b>\n\n";
foreach ($steps as $s) echo $s . "\n";
echo "\n<b>Done. You can delete this file now.</b>";
echo "</pre>";
?>
