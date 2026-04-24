<?php
include('includes/dbconnection.php');

echo "Starting DB Verification for Payment Module...\n";

// 1. Insert dummy booking
$bno = "TEST" . rand(1000, 9999);
$sql = "INSERT INTO tblbooking(BookingNumber, Name, MobileNumber, VehicleType, VehicleNumber, Problem, Location, AssignTo, Status, ServiceCost) 
        VALUES('$bno', 'Test User', 9876543210, 'Car', 'DL-1C-AA-1111', 'Engine failure', 'Delhi', 1, 'Completed', 1500.50)";
$dbh->exec($sql);
echo "[OK] Dummy Completed Booking Created: $bno with ServiceCost: 1500.50\n";

// 2. Insert dummy payment
$txnid = "TXN" . date('YmdHis');
$sql2 = "INSERT INTO tblpayment(BookingNumber, PaymentMode, TransactionID, PaymentAmount, PaymentStatus) 
         VALUES('$bno', 'UPI', '$txnid', 1500.50, 'Paid')";
$dbh->exec($sql2);
echo "[OK] Dummy Payment Processed: $txnid via UPI\n";

// 3. Verify join query for receipt
$sql3 = "SELECT b.BookingNumber, b.ServiceCost, p.TransactionID, p.PaymentStatus 
         FROM tblbooking b 
         JOIN tblpayment p ON b.BookingNumber = p.BookingNumber 
         WHERE b.BookingNumber = '$bno'";
$stmt = $dbh->query($sql3);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if($res && $res['PaymentStatus'] == 'Paid' && $res['ServiceCost'] == 1500.50) {
    echo "[SUCCESS] Verification passed! Booking & Payment tables are linked correctly.\n";
} else {
    echo "[FAILED] Data mismatch in tables.\n";
}

// Cleanup
$dbh->exec("DELETE FROM tblbooking WHERE BookingNumber='$bno'");
$dbh->exec("DELETE FROM tblpayment WHERE BookingNumber='$bno'");
echo "[OK] Test data cleaned up.\n";
?>
