<?php
// Real-time status polling endpoint
header('Content-Type: application/json');
include('includes/dbconnection.php');

$bno = isset($_GET['bno']) ? trim($_GET['bno']) : '';
if(empty($bno)) { echo json_encode(['error' => 'No booking number']); exit(); }

$sql = "SELECT tblbooking.Status, tblbooking.AssignTo,
               tbldriver.DriverName, tbldriver.MobileNumber as DriverMobile,
               tbldriver.AvailabilityStatus
        FROM tblbooking
        LEFT JOIN tbldriver ON tbldriver.ID = tblbooking.AssignTo
        WHERE tblbooking.BookingNumber = :bno";
$q = $dbh->prepare($sql);
$q->bindParam(':bno', $bno, PDO::PARAM_STR);
$q->execute();
$row = $q->fetch(PDO::FETCH_OBJ);

if(!$row) { echo json_encode(['error' => 'Not found']); exit(); }

// Latest notification
$nq = $dbh->prepare("SELECT Message, CreatedAt FROM tblnotifications WHERE BookingNumber=:bno ORDER BY CreatedAt DESC LIMIT 1");
$nq->bindParam(':bno', $bno, PDO::PARAM_STR);
$nq->execute();
$latestNotif = $nq->fetch(PDO::FETCH_OBJ);

echo json_encode([
    'status'         => $row->Status,
    'driverName'     => $row->DriverName,
    'driverMobile'   => $row->DriverMobile,
    'latestMessage'  => $latestNotif ? $latestNotif->Message : null,
    'latestMsgTime'  => $latestNotif ? $latestNotif->CreatedAt : null,
]);
?>
