<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if(isset($_POST['pay'])) {
    $bno = $_POST['bno'];
    $amount = $_POST['amount'];
    $paymode = $_POST['paymode'];
    
    // Generate a random Transaction ID
    $txnid = "TXN" . date('YmdHis') . rand(100,999);
    $status = "Paid";
    
    $sql = "INSERT INTO tblpayment(BookingNumber, PaymentMode, TransactionID, PaymentAmount, PaymentStatus) 
            VALUES(:bno, :paymode, :txnid, :amount, :status)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bno', $bno, PDO::PARAM_STR);
    $query->bindParam(':paymode', $paymode, PDO::PARAM_STR);
    $query->bindParam(':txnid', $txnid, PDO::PARAM_STR);
    $query->bindParam(':amount', $amount, PDO::PARAM_STR);
    $query->execute();
    
    echo "<script>alert('Payment Successful! Transaction ID: $txnid');</script>";
    echo "<script>window.location.href='receipt.php?bno=$bno'</script>";
}

if(isset($_GET['bno'])) {
    $bno = $_GET['bno'];
    
    // Check if valid and get cost
    $sql = "SELECT * FROM tblbooking WHERE BookingNumber=:bno AND Status='Completed'";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bno', $bno, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() == 0) {
        header('location:track.php');
        exit();
    }
    
    $result = $query->fetch(PDO::FETCH_OBJ);
    $amount = $result->ServiceCost;
    
    // Check if already paid
    $psql = "SELECT * FROM tblpayment WHERE BookingNumber=:bno AND PaymentStatus='Paid'";
    $pq = $dbh->prepare($psql);
    $pq->bindParam(':bno', $bno, PDO::PARAM_STR);
    $pq->execute();
    
    if($pq->rowCount() > 0) {
        echo "<script>alert('Bill already paid!');</script>";
        echo "<script>window.location.href='receipt.php?bno=$bno'</script>";
        exit();
    }
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
    <title>Process Payment - Vehicle Assistance</title>
    <!-- Include styles from header for consistency -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .payment-card:focus-within {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .pay-option.active {
            background-color: #f8f9fa;
            border: 2px solid #0d6efd;
        }
    </style>
</head>
<body class="bg-light">

<!-- Simple nav -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-truck-fast text-warning me-2"></i>VBMS</a>
        <a class="btn btn-outline-light btn-sm" href="track.php"><i class="fa-solid fa-arrow-left"></i> Back to Tracking</a>
    </div>
</nav>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0 fw-bold">Complete Your Payment</h4>
                    <p class="mb-0 text-white-50 small mt-1">Booking #<?php echo htmlentities($bno); ?></p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="text-muted text-uppercase fw-bold small">Total Amount Due</span>
                        <h1 class="display-4 fw-bold text-dark mt-2 mb-0">₹<?php echo number_format($amount, 2); ?></h1>
                        <hr class="w-25 mx-auto border-secondary mt-4">
                    </div>
                    
                    <form method="post" id="paymentForm">
                        <input type="hidden" name="bno" value="<?php echo htmlentities($bno); ?>">
                        <input type="hidden" name="amount" value="<?php echo htmlentities($amount); ?>">
                        
                        <h6 class="fw-bold mb-3">Select Payment Method</h6>
                        
                        <div class="accordion" id="paymentMethods">
                            <!-- UPI -->
                            <div class="accordion-item border rounded mb-3 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUPI">
                                        <i class="fa-brands fa-google-pay text-primary fs-4 me-3"></i> UPI (GPay, PhonePe, Paytm)
                                    </button>
                                </h2>
                                <div id="collapseUPI" class="accordion-collapse collapse" data-bs-parent="#paymentMethods">
                                    <div class="accordion-body bg-light">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted">UPI ID</label>
                                            <input type="text" class="form-control form-control-lg payment-input" placeholder="example@upi">
                                        </div>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="radio" name="paymode" id="payUPI" value="UPI">
                                            <label class="form-check-label fw-bold" for="payUPI">
                                                Select UPI
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Debit/Credit Card -->
                            <div class="accordion-item border rounded mb-3 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCard">
                                        <i class="fa-regular fa-credit-card text-success fs-4 me-3"></i> Credit / Debit Card
                                    </button>
                                </h2>
                                <div id="collapseCard" class="accordion-collapse collapse" data-bs-parent="#paymentMethods">
                                    <div class="accordion-body bg-light">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted">Card Number</label>
                                            <input type="text" class="form-control payment-input mb-2" placeholder="0000 0000 0000 0000" maxlength="19">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="text" class="form-control payment-input" placeholder="MM/YY" maxlength="5">
                                                </div>
                                                <div class="col-6">
                                                    <input type="password" class="form-control payment-input" placeholder="CVV" maxlength="3">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="radio" name="paymode" id="payCard" value="Credit/Debit Card">
                                            <label class="form-check-label fw-bold" for="payCard">
                                                Select Card
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Net Banking -->
                            <div class="accordion-item border rounded overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNet">
                                        <i class="fa-solid fa-building-columns text-secondary fs-4 me-3"></i> Net Banking
                                    </button>
                                </h2>
                                <div id="collapseNet" class="accordion-collapse collapse" data-bs-parent="#paymentMethods">
                                    <div class="accordion-body bg-light">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted">Select Bank</label>
                                            <select class="form-select payment-input">
                                                <option value="">Choose Bank...</option>
                                                <option>State Bank of India</option>
                                                <option>HDFC Bank</option>
                                                <option>ICICI Bank</option>
                                                <option>Axis Bank</option>
                                            </select>
                                        </div>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="radio" name="paymode" id="payNet" value="Net Banking">
                                            <label class="form-check-label fw-bold" for="payNet">
                                                Select Net Banking
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" name="pay" id="payBtn" class="btn btn-primary btn-lg shadow-sm fw-bold disabled">
                                <i class="fa-solid fa-lock me-2"></i> Pay ₹<?php echo number_format($amount, 2); ?> Securely
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light text-center py-3 text-muted small">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i> 100% Secure & Encrypted Payments
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="paymode"]');
        const payBtn = document.getElementById('payBtn');
        
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if(this.checked) {
                    payBtn.classList.remove('disabled');
                }
            });
        });
        
        // When accordion opens, auto-select radio inside
        let accordions = document.querySelectorAll('.accordion-collapse');
        accordions.forEach(acc => {
            acc.addEventListener('shown.bs.collapse', function() {
                let radio = this.querySelector('input[type="radio"]');
                if(radio) {
                    radio.checked = true;
                    // Trigger change event to enable button
                    radio.dispatchEvent(new Event('change'));
                }
            });
        });
    });
</script>
</body>
</html>
