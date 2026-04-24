<?php
include('includes/dbconnection.php');

echo "Starting Notification Flow Verification...\n";

// 1. Submit Request
$bookingno = 'TEST' . mt_rand(1000, 9999);
$name = 'Test User';
$mobile = '1234567890';
$vtype = 'Car';
$vnumber = 'ABC-123';
$problem = 'Test Problem';
$location = 'Test Location';
$status = 'Pending';
$lat = '0.0';
$lng = '0.0';
$photo = '';

$sql = "INSERT INTO tblbooking(BookingNumber, Name, MobileNumber, VehicleType, VehicleNumber, Problem, Location, Photo, Status) 
        VALUES(:bookingno, :name, :mobile, :vtype, :vnumber, :problem, :location, :photo, :status)";
$query = $dbh->prepare($sql);
$query->bindParam(':bookingno', $bookingno, PDO::PARAM_STR);
$query->bindParam(':name', $name, PDO::PARAM_STR);
$query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
$query->bindParam(':vtype', $vtype, PDO::PARAM_STR);
$query->bindParam(':vnumber', $vnumber, PDO::PARAM_STR);
$query->bindParam(':problem', $problem, PDO::PARAM_STR);
$query->bindParam(':location', $location, PDO::PARAM_STR);
$query->bindParam(':photo', $photo, PDO::PARAM_STR);
$query->bindParam(':status', $status, PDO::PARAM_STR);
$query->execute();
$booking_id = $dbh->lastInsertId();

if($booking_id) {
    // Simulate Notification from index.php
    $msg = "Your request for $vtype breakdown has been submitted successfully.";
    $nsql = "INSERT INTO tblnotifications(BookingNumber, Message) VALUES(:bno, :msg)";
    $nquery = $dbh->prepare($nsql);
    $nquery->bindParam(':bno', $bookingno, PDO::PARAM_STR);
    $nquery->bindParam(':msg', $msg, PDO::PARAM_STR);
    $nquery->execute();
    
    echo "1. Request Submitted successfully. Inserted Notification.\n";
} else {
    echo "1. Failed to submit request.\n";
    exit;
}

// 2. Admin Assigns Driver
// Create dummy driver first if one doesn't exist to make sure we have one
$sql_d = "INSERT INTO tbldriver (DriverName, MobileNumber) VALUES ('Test Driver', '9876543210')";
$dbh->exec($sql_d);
$driver_id = $dbh->lastInsertId();

$assign_status = 'Approved';
$sql_a = "UPDATE tblbooking SET AssignTo=:driverid, Status=:status WHERE ID=:vid";
$query_a = $dbh->prepare($sql_a);
$query_a->bindParam(':driverid', $driver_id, PDO::PARAM_INT);
$query_a->bindParam(':status', $assign_status, PDO::PARAM_STR);
$query_a->bindParam(':vid', $booking_id, PDO::PARAM_INT);
$query_a->execute();

$drv_name = 'Test Driver';
$msg2 = "Your request has been approved. Driver " . $drv_name . " has been assigned to you.";
$nsql2 = "INSERT INTO tblnotifications(BookingNumber, Message) VALUES(:bno, :msg)";
$nquery2 = $dbh->prepare($nsql2);
$nquery2->bindParam(':bno', $bookingno, PDO::PARAM_STR);
$nquery2->bindParam(':msg', $msg2, PDO::PARAM_STR);
$nquery2->execute();

echo "2. Admin Assigned Driver successfully. Inserted Notification.\n";

// 3. Driver Updates Status
$update_status = 'On The Way';
$remark = 'Heading there now';
$sql_u1 = "INSERT INTO tblserviceupdate(BookingNumber, Status, Remark) VALUES(:bno, :status, :remark)";
$query_u1 = $dbh->prepare($sql_u1);
$query_u1->bindParam(':bno', $bookingno, PDO::PARAM_STR);
$query_u1->bindParam(':status', $update_status, PDO::PARAM_STR);
$query_u1->bindParam(':remark', $remark, PDO::PARAM_STR);
$query_u1->execute();

$sql_u2 = "UPDATE tblbooking SET Status=:status WHERE ID=:vid";
$query_u2 = $dbh->prepare($sql_u2);
$query_u2->bindParam(':status', $update_status, PDO::PARAM_STR);
$query_u2->bindParam(':vid', $booking_id, PDO::PARAM_INT);
$query_u2->execute();

$msg3 = "Your request status has been updated to: " . $update_status;
$nsql3 = "INSERT INTO tblnotifications(BookingNumber, Message) VALUES(:bno, :msg)";
$nquery3 = $dbh->prepare($nsql3);
$nquery3->bindParam(':bno', $bookingno, PDO::PARAM_STR);
$nquery3->bindParam(':msg', $msg3, PDO::PARAM_STR);
$nquery3->execute();

echo "3. Driver Updated Status successfully. Inserted Notification.\n";

// 4. Verify Notifications in DB
echo "\nVerifying Notifications in Database for Booking $bookingno:\n";
$sql_v = "SELECT * FROM tblnotifications WHERE BookingNumber = :bno ORDER BY CreatedAt ASC";
$query_v = $dbh->prepare($sql_v);
$query_v->bindParam(':bno', $bookingno, PDO::PARAM_STR);
$query_v->execute();
$notifications = $query_v->fetchAll(PDO::FETCH_OBJ);

foreach ($notifications as $notif) {
    echo " > " . $notif->Message . " (" . $notif->CreatedAt . ")\n";
}

if(count($notifications) == 3) {
    echo "SUCCESS: Found 3 notifications as expected.\n";
} else {
    echo "ERROR: Expected 3 notifications, found " . count($notifications) . ".\n";
}

// 5. Add a dummy rating for testing the dashboard
$rating = 5;
$feedback = "Excellent service!";
$sql_f = "INSERT INTO tblfeedback (BookingNumber, Rating, Feedback) VALUES (:bno, :rating, :feedback)";
$query_f = $dbh->prepare($sql_f);
$query_f->bindParam(':bno', $bookingno, PDO::PARAM_STR);
$query_f->bindParam(':rating', $rating, PDO::PARAM_INT);
$query_f->bindParam(':feedback', $feedback, PDO::PARAM_STR);
$query_f->execute();
echo "Inserted test rating for driver dashboard.\n";

?>
