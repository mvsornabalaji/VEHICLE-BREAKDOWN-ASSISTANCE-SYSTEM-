<?php
include('includes/header.php');

$search_results = null;

if(isset($_POST['search_history'])) {
    $mobile = $_POST['mobile'];
    
    // Fetch all bookings for this mobile number
    $sql = "SELECT tblbooking.*, tbldriver.DriverName, tbldriver.MobileNumber as DriverMobile 
            FROM tblbooking 
            LEFT JOIN tbldriver ON tblbooking.AssignTo = tbldriver.ID 
            WHERE tblbooking.MobileNumber = :mobile 
            ORDER BY tblbooking.ID DESC";
            
    $query = $dbh->prepare($sql);
    $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
    $query->execute();
    $search_results = $query->fetchAll(PDO::FETCH_OBJ);
}
?>

<section class="py-5 bg-light" style="min-height: 80vh;">
    <div class="container">
        <h2 class="text-center fw-bold mb-5"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Service History</h2>
        
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm p-4 text-center rounded-4">
                    <p class="text-muted mb-4">Enter your registered mobile number to view all your past vehicle breakdown assistance requests.</p>
                    <form method="post" class="d-flex flex-column gap-3 align-items-center justify-content-center">
                        <div class="input-group input-group-lg w-100">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input type="number" name="mobile" class="form-control border-start-0" required placeholder="Enter 10-digit Mobile Number" pattern="[0-9]{10}">
                        </div>
                        <button type="submit" name="search_history" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">View History</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if($search_results !== null): ?>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h5 class="fw-bold mb-4">Results for: <span class="text-primary"><?php echo htmlentities($mobile); ?></span></h5>
                    
                    <?php if(count($search_results) > 0): ?>
                        <div class="table-responsive bg-white rounded-3 shadow-sm p-3">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Booking Ref</th>
                                        <th>Vehicle Info</th>
                                        <th>Problem</th>
                                        <th>Driver Assigned</th>
                                        <th>Status</th>
                                        <th>Cost</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($search_results as $row): ?>
                                        <?php
                                        $statusBadge = 'primary';
                                        if($row->Status == 'Completed') $statusBadge = 'success';
                                        if($row->Status == 'Rejected')  $statusBadge = 'danger';
                                        if($row->Status == 'Pending' || $row->Status == '') {
                                            $statusBadge = 'warning text-dark';
                                            $row->Status = 'Pending';
                                        }
                                        // Check payment
                                        $pchk = $dbh->prepare("SELECT PaymentStatus FROM tblpayment WHERE BookingNumber=:bno AND PaymentStatus='Paid'");
                                        $pchk->bindParam(':bno', $row->BookingNumber, PDO::PARAM_STR);
                                        $pchk->execute();
                                        $isPaid = ($pchk->rowCount() > 0);
                                        // Check rating
                                        $rchk = $dbh->prepare("SELECT Rating FROM tblfeedback WHERE BookingNumber=:bno");
                                        $rchk->bindParam(':bno', $row->BookingNumber, PDO::PARAM_STR);
                                        $rchk->execute();
                                        $rrow = $rchk->fetch(PDO::FETCH_OBJ);
                                        ?>
                                        <tr>
                                            <td class="text-nowrap text-muted small"><?php echo date('d M Y', strtotime($row->BookingDate)); ?></td>
                                            <td class="fw-bold text-dark">#<?php echo htmlentities($row->BookingNumber); ?></td>
                                            <td>
                                                <span class="d-block fw-semibold"><?php echo htmlentities($row->VehicleType); ?></span>
                                                <span class="small text-muted"><?php echo htmlentities($row->VehicleNumber); ?></span>
                                            </td>
                                            <td><span class="d-inline-block text-truncate" style="max-width:130px;" title="<?php echo htmlentities($row->Problem); ?>"><?php echo htmlentities($row->Problem); ?></span></td>
                                            <td>
                                                <?php if($row->DriverName): ?>
                                                    <span class="d-block fw-semibold"><?php echo htmlentities($row->DriverName); ?></span>
                                                    <span class="small text-muted"><i class="fa-solid fa-phone fa-xs"></i> <?php echo htmlentities($row->DriverMobile); ?></span>
                                                    <?php if($rrow): ?>
                                                    <div class="small text-warning mt-1">
                                                        <?php for($s=1;$s<=5;$s++) echo ($s<=$rrow->Rating)?'★':'☆'; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-<?php echo $statusBadge; ?>"><?php echo htmlentities($row->Status); ?></span></td>
                                            <td>
                                                <?php if($row->ServiceCost): ?>
                                                    <span class="fw-semibold text-success">₹<?php echo number_format($row->ServiceCost,2); ?></span><br>
                                                    <?php if($isPaid): ?>
                                                        <span class="badge bg-success small">Paid</span>
                                                    <?php else: ?>
                                                        <a href="payment.php?bno=<?php echo htmlentities($row->BookingNumber); ?>" class="badge bg-warning text-dark small">Pay Now</a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form action="track.php" method="post" class="m-0">
                                                    <input type="hidden" name="searchdata" value="<?php echo htmlentities($row->BookingNumber); ?>">
                                                    <button type="submit" name="track" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye me-1"></i> Details</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center shadow-sm border-0 py-4 rounded-3 text-dark">
                            <i class="fa-solid fa-inbox fa-3x mb-3 d-block text-warning opacity-75"></i>
                            <h5 class="mb-0">No service history found for this mobile number.</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include('includes/footer.php'); ?>
