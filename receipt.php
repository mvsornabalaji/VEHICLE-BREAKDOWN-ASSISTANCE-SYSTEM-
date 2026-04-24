<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if(isset($_GET['bno'])) {
    $bno = $_GET['bno'];
    
    // Fetch booking, driver, and payment details
    $sql = "SELECT tblbooking.*, tbldriver.DriverName, tbldriver.MobileNumber as DriverMob, tblpayment.TransactionID, tblpayment.PaymentMode, tblpayment.PaymentDate, tblpayment.PaymentAmount 
            FROM tblbooking 
            JOIN tblpayment ON tblbooking.BookingNumber = tblpayment.BookingNumber
            LEFT JOIN tbldriver ON tblbooking.AssignTo = tbldriver.ID
            WHERE tblbooking.BookingNumber=:bno AND tblpayment.PaymentStatus='Paid'";
            
    $query = $dbh->prepare($sql);
    $query->bindParam(':bno', $bno, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() == 0) {
        echo "<script>alert('Receipt not found or payment not completed.');</script>";
        echo "<script>window.location.href='track.php'</script>";
        exit();
    }
    
    $row = $query->fetch(PDO::FETCH_OBJ);
} else {
    header('location:track.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - <?php echo htmlentities($row->BookingNumber); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .receipt-container { max-width: 800px; margin: 40px auto; }
        .receipt-card { border-top: 5px solid #28a745; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .brand-logo { width: 60px; height: 60px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 15px; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 120px; color: rgba(40, 167, 69, 0.05); font-weight: bold; pointer-events: none; z-index: 0; white-space: nowrap; }
        .content-z { position: relative; z-index: 1; }
        @media print {
            body { background-color: white; }
            .receipt-container { margin: 0; max-width: 100%; box-shadow: none; }
            .receipt-card { box-shadow: none; border-top: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container receipt-container">
    <div class="text-end mb-3 no-print">
        <a href="track.php" class="btn btn-outline-secondary me-2"><i class="fa-solid fa-arrow-left"></i> Home</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print Receipt</button>
    </div>

    <div class="card border-0 receipt-card bg-white position-relative overflow-hidden p-4 p-md-5">
        <span class="watermark">PAID</span>
        
        <div class="content-z">
            <!-- Header -->
            <div class="row mb-5 align-items-center">
                <div class="col-sm-6 text-center text-sm-start mb-4 mb-sm-0">
                    <h2 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-truck-fast text-success me-2"></i>VBMS</h2>
                    <p class="text-muted mb-0">Vehicle Breakdown Management System</p>
                </div>
                <div class="col-sm-6 text-center text-sm-end">
                    <h1 class="text-success fw-bold text-uppercase mb-0">Receipt</h1>
                    <p class="mb-0 fw-semibold text-muted">Date: <?php echo date('d M Y, h:i A', strtotime($row->PaymentDate)); ?></p>
                </div>
            </div>

            <hr class="border-secondary opacity-25 mb-4">

            <!-- Information Section -->
            <div class="row mb-5">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h6 class="text-uppercase text-muted fw-bold mb-3 small">Billed To</h6>
                    <h5 class="fw-bold mb-1"><?php echo htmlentities($row->Name); ?></h5>
                    <p class="mb-1"><i class="fa-solid fa-phone small text-muted me-2"></i> <?php echo htmlentities($row->MobileNumber); ?></p>
                    <p class="mb-1"><i class="fa-solid fa-location-dot small text-muted me-2"></i> <?php echo htmlentities($row->Location); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-uppercase text-muted fw-bold mb-3 small">Payment Details</h6>
                    <table class="table table-sm table-borderless bg-transparent w-auto ms-md-auto mb-0">
                        <tr><td class="text-muted text-start pe-4">Receipt No:</td><td class="fw-bold text-end">REC-<?php echo substr($row->TransactionID, -6); ?></td></tr>
                        <tr><td class="text-muted text-start pe-4">Transaction ID:</td><td class="fw-bold text-end"><?php echo htmlentities($row->TransactionID); ?></td></tr>
                        <tr><td class="text-muted text-start pe-4">Booking Ref:</td><td class="fw-bold text-end">#<?php echo htmlentities($row->BookingNumber); ?></td></tr>
                        <tr><td class="text-muted text-start pe-4">Payment Method:</td><td class="fw-bold text-end"><?php echo htmlentities($row->PaymentMode); ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- Service Table -->
            <h6 class="text-uppercase text-muted fw-bold mb-3 small">Service Summary</h6>
            <div class="table-responsive mb-5">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-uppercase small text-muted">Description</th>
                            <th class="py-3 text-uppercase small text-muted text-center" width="20%">Vehicle Number</th>
                            <th class="py-3 text-uppercase small text-muted text-center" width="25%">Service Date</th>
                            <th class="py-3 text-uppercase small text-muted text-end" width="20%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-4">
                                <span class="fw-bold d-block text-dark"><?php echo htmlentities($row->VehicleType); ?> Breakdown Assistance</span>
                                <span class="text-muted small">Problem: <?php echo htmlentities($row->Problem); ?></span>
                            </td>
                            <td class="py-4 text-center align-middle"><?php echo htmlentities($row->VehicleNumber); ?></td>
                            <td class="py-4 text-center align-middle"><?php echo date('d M Y', strtotime($row->CompletionDate)); ?></td>
                            <td class="py-4 text-end align-middle fw-semibold">₹<?php echo number_format($row->PaymentAmount, 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end py-3 fw-bold text-muted text-uppercase">Total Paid</td>
                            <td class="text-end py-3 fw-bold fs-5 text-success">₹<?php echo number_format($row->PaymentAmount, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Footer Notes -->
            <div class="row align-items-center bg-light rounded p-4 border border-1 border-secondary border-opacity-10">
                <div class="col-sm-8 text-center text-sm-start mb-3 mb-sm-0">
                    <h6 class="fw-bold mb-1">Driver Information</h6>
                    <p class="mb-0 text-muted small"><i class="fa-solid fa-user me-1"></i> <?php echo htmlentities($row->DriverName); ?> &nbsp;|&nbsp; <i class="fa-solid fa-phone me-1"></i> <?php echo htmlentities($row->DriverMob); ?></p>
                </div>
                <div class="col-sm-4 text-center text-sm-end">
                    <i class="fa-solid fa-circle-check text-success fs-1 mb-2"></i>
                    <h5 class="fw-bold text-success mb-0 d-block">Thank You!</h5>
                </div>
            </div>
            
            <div class="text-center mt-5 mb-2">
                <p class="text-muted small mb-0">This is a computer generated receipt and does not require a physical signature.</p>
                <p class="text-muted small">For any queries, please contact VBMS Support.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
