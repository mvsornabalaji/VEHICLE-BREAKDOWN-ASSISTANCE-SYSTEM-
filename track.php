<?php
include('includes/header.php');

$search_results = null;

// Handle Feedback Submission
if(isset($_POST['submit_feedback'])) {
    $bno      = $_POST['bno'];
    $rating   = intval($_POST['rating']);
    $feedback = $_POST['feedback_text'];

    // Prevent duplicate feedback
    $chk = $dbh->prepare("SELECT ID FROM tblfeedback WHERE BookingNumber=:bno");
    $chk->bindParam(':bno', $bno, PDO::PARAM_STR);
    $chk->execute();
    if($chk->rowCount() == 0) {
        // Get DriverID for this booking
        $dq = $dbh->prepare("SELECT AssignTo FROM tblbooking WHERE BookingNumber=:bno");
        $dq->bindParam(':bno', $bno, PDO::PARAM_STR);
        $dq->execute();
        $drow = $dq->fetch(PDO::FETCH_OBJ);
        $driverID = $drow ? $drow->AssignTo : null;

        $fsql = "INSERT INTO tblfeedback(BookingNumber, DriverID, Rating, Feedback) VALUES(:bno, :did, :rating, :feedback)";
        $fquery = $dbh->prepare($fsql);
        $fquery->bindParam(':bno',      $bno,      PDO::PARAM_STR);
        $fquery->bindParam(':did',      $driverID, PDO::PARAM_INT);
        $fquery->bindParam(':rating',   $rating,   PDO::PARAM_INT);
        $fquery->bindParam(':feedback', $feedback, PDO::PARAM_STR);
        $fquery->execute();
    }
    echo "<script>alert('Thank you for your feedback!');</script>";
    echo "<script>window.location.href='track.php'</script>";
}

if(isset($_POST['track'])) {
    $searchdata = $_POST['searchdata'];
    $sql = "SELECT tblbooking.*, tbldriver.DriverName, tbldriver.MobileNumber as DriverMobile
            FROM tblbooking
            LEFT JOIN tbldriver ON tblbooking.AssignTo = tbldriver.ID
            WHERE tblbooking.BookingNumber = :search OR tblbooking.MobileNumber = :search
            ORDER BY tblbooking.ID DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':search', $searchdata, PDO::PARAM_STR);
    $query->execute();
    $search_results = $query->fetchAll(PDO::FETCH_OBJ);
}
?>

<section class="py-5 bg-light" style="min-height: 80vh;">
<div class="container">
    <h2 class="text-center fw-bold mb-5"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Track Your Request</h2>

    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4">
                <form method="post" class="d-flex gx-3 align-items-center justify-content-center">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="searchdata" class="form-control border-start-0" required placeholder="Enter Booking Number or Mobile Number">
                        <button type="submit" name="track" class="btn btn-primary px-4 fw-bold">Track Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if($search_results !== null): ?>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if(count($search_results) > 0): ?>
                <?php foreach($search_results as $row): ?>

                <?php
                // Status steps for timeline
                $allSteps = ['Pending', 'Approved', 'On The Way', 'In Process', 'Completed'];
                $currentStatus = $row->Status;
                $currentIdx = array_search($currentStatus, $allSteps);
                if($currentStatus == 'Rejected') $currentIdx = -1;

                // Check if feedback already given
                $fbChk = $dbh->prepare("SELECT ID FROM tblfeedback WHERE BookingNumber=:bno");
                $fbChk->bindParam(':bno', $row->BookingNumber, PDO::PARAM_STR);
                $fbChk->execute();
                $feedbackGiven = ($fbChk->rowCount() > 0);

                // Fetch notifications
                $nSql = "SELECT * FROM tblnotifications WHERE BookingNumber=:bno ORDER BY CreatedAt ASC";
                $nQ = $dbh->prepare($nSql);
                $nQ->bindParam(':bno', $row->BookingNumber, PDO::PARAM_STR);
                $nQ->execute();
                $notifications = $nQ->fetchAll(PDO::FETCH_OBJ);

                // Fetch service updates
                $suSql = "SELECT * FROM tblserviceupdate WHERE BookingNumber=:bno ORDER BY UpdateDate ASC";
                $suQ = $dbh->prepare($suSql);
                $suQ->bindParam(':bno', $row->BookingNumber, PDO::PARAM_STR);
                $suQ->execute();
                $serviceUpdates = $suQ->fetchAll(PDO::FETCH_OBJ);

                // Payment check
                $pSql = "SELECT * FROM tblpayment WHERE BookingNumber=:bno AND PaymentStatus='Paid'";
                $pQ = $dbh->prepare($pSql);
                $pQ->bindParam(':bno', $row->BookingNumber, PDO::PARAM_STR);
                $pQ->execute();
                $paymentDone = ($pQ->rowCount() > 0);
                $payRow = $pQ->fetch(PDO::FETCH_OBJ);
                ?>

                <div class="card border-0 shadow-sm mb-5" data-booking-no="<?php echo htmlentities($row->BookingNumber); ?>">
                    <!-- Card Header -->
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold">Booking #<?php echo htmlentities($row->BookingNumber); ?></h5>
                        <?php
                        $badgeMap = [
                            'Completed' => 'success', 'Rejected' => 'danger',
                            'Pending'   => 'warning text-dark', 'Approved' => 'info text-dark',
                            'On The Way'=> 'primary', 'In Process'=> 'warning text-dark'
                        ];
                        $bc = $badgeMap[$currentStatus] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?php echo $bc; ?> fs-6 px-3 py-2 live-status-badge">
                            <span class="status-live-dot"></span><?php echo htmlentities($currentStatus); ?>
                        </span>
                    </div>

                    <div class="card-body p-4">

                        <!-- ===== STATUS TIMELINE ===== -->
                        <?php if($currentStatus != 'Rejected'): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-route text-primary me-2"></i>Real-Time Status</h6>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 position-relative">
                                <?php foreach($allSteps as $idx => $step):
                                    $done    = ($currentIdx !== false && $idx <= $currentIdx);
                                    $active  = ($idx == $currentIdx);
                                    $stepIcons = ['fa-paper-plane','fa-user-check','fa-truck-fast','fa-screwdriver-wrench','fa-flag-checkered'];
                                    $stepIcon = $stepIcons[$idx] ?? 'fa-circle';
                                ?>
                                <div class="text-center flex-fill" style="min-width:80px;">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1
                                        <?php echo $done ? 'bg-success text-white' : 'bg-light text-muted border'; ?>"
                                        style="width:46px;height:46px;font-size:1.1rem;
                                        <?php echo $active ? 'box-shadow:0 0 0 4px rgba(25,135,84,0.25);' : ''; ?>">
                                        <i class="fa-solid <?php echo $stepIcon; ?>"></i>
                                    </div>
                                    <div class="small fw-semibold <?php echo $done ? 'text-success' : 'text-muted'; ?>" style="font-size:0.72rem;">
                                        <?php echo htmlentities($step); ?>
                                    </div>
                                </div>
                                <?php if($idx < count($allSteps)-1): ?>
                                <div class="flex-fill" style="height:3px;background:<?php echo ($currentIdx !== false && $idx < $currentIdx) ? '#198754' : '#dee2e6'; ?>;min-width:20px;max-width:60px;"></div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-danger mb-4"><i class="fa-solid fa-ban me-2"></i>This request has been <strong>Rejected</strong>.</div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Left: Details -->
                            <div class="col-lg-6 mb-4">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i>Request Details</h6>
                                <table class="table table-borderless table-sm">
                                    <tr><th width="40%" class="text-muted">Name:</th><td><?php echo htmlentities($row->Name); ?></td></tr>
                                    <tr><th class="text-muted">Mobile:</th><td><?php echo htmlentities($row->MobileNumber); ?></td></tr>
                                    <tr><th class="text-muted">Vehicle:</th><td><?php echo htmlentities($row->VehicleType); ?> — <?php echo htmlentities($row->VehicleNumber); ?></td></tr>
                                    <tr><th class="text-muted">Problem:</th><td><?php echo htmlentities($row->Problem); ?></td></tr>
                                    <tr><th class="text-muted">Location:</th><td><i class="fa-solid fa-location-dot text-danger me-1"></i><?php echo htmlentities($row->Location); ?></td></tr>
                                    <tr><th class="text-muted">Submitted:</th><td><?php echo date('d M Y, h:i A', strtotime($row->BookingDate)); ?></td></tr>
                                    <?php if($row->ServiceCost): ?>
                                    <tr><th class="text-muted">Service Cost:</th><td class="fw-bold text-success">₹<?php echo number_format($row->ServiceCost, 2); ?></td></tr>
                                    <?php endif; ?>
                                </table>

                                <?php if($row->Photo): ?>
                                <div class="mt-2">
                                    <span class="text-muted small fw-semibold">Vehicle Photo:</span><br>
                                    <a href="uploads/<?php echo htmlentities($row->Photo); ?>" target="_blank">
                                        <img src="uploads/<?php echo htmlentities($row->Photo); ?>" alt="Vehicle" class="img-thumbnail mt-1" style="max-height:130px;">
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Right: Driver + Map -->
                            <div class="col-lg-6 mb-4">
                                <?php if($row->DriverName): ?>
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-id-badge text-success me-2"></i>Assigned Driver</h6>
                                <div class="card bg-light border-0 p-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:50px;height:50px;font-size:1.4rem;">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlentities($row->DriverName); ?></div>
                                            <div class="text-muted small"><i class="fa-solid fa-phone fa-xs me-1"></i><?php echo htmlentities($row->DriverMobile); ?></div>
                                            <a href="tel:<?php echo htmlentities($row->DriverMobile); ?>" class="btn btn-sm btn-success mt-1 rounded-pill px-3">
                                                <i class="fa-solid fa-phone me-1"></i> Call Driver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if($row->Latitude && $row->Longitude): ?>
                                <h6 class="fw-bold mb-2"><i class="fa-solid fa-map-pin text-danger me-2"></i>Breakdown Location</h6>
                                <div class="rounded overflow-hidden border shadow-sm">
                                    <iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                                        src="https://maps.google.com/maps?q=<?php echo htmlentities($row->Latitude); ?>,<?php echo htmlentities($row->Longitude); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed">
                                    </iframe>
                                </div>
                                <a href="https://www.google.com/maps?q=<?php echo htmlentities($row->Latitude); ?>,<?php echo htmlentities($row->Longitude); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2 w-100">
                                    <i class="fa-solid fa-up-right-from-square me-1"></i> Open in Google Maps
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ===== NOTIFICATIONS TIMELINE ===== -->
                        <?php if(count($notifications) > 0): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-bell text-warning me-2"></i>Notifications & Updates</h6>
                            <div class="small text-muted mb-2 live-latest-msg"></div>
                            <div class="timeline ps-3" style="border-left:3px solid #dee2e6;">
                                <?php foreach($notifications as $nrow): ?>
                                <div class="mb-3 position-relative ps-3">
                                    <span class="position-absolute bg-primary rounded-circle" style="width:12px;height:12px;left:-7px;top:4px;"></span>
                                    <div class="small text-muted"><?php echo date('d M Y, h:i A', strtotime($nrow->CreatedAt)); ?></div>
                                    <div><?php echo htmlentities($nrow->Message); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ===== SERVICE UPDATE HISTORY ===== -->
                        <?php if(count($serviceUpdates) > 0): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>Driver Activity Log</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr><th>Status</th><th>Remarks</th><th>Photo</th><th>Time</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($serviceUpdates as $su): ?>
                                        <tr>
                                            <td><span class="badge bg-info text-dark"><?php echo htmlentities($su->Status); ?></span></td>
                                            <td><?php echo htmlentities($su->Remark); ?></td>
                                            <td>
                                                <?php if($su->ConditionPhoto): ?>
                                                <a href="uploads/<?php echo htmlentities($su->ConditionPhoto); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                            </td>
                                            <td class="text-nowrap small"><?php echo date('d M, h:i A', strtotime($su->UpdateDate)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ===== PAYMENT SECTION ===== -->
                        <?php if($currentStatus == 'Completed' && $row->ServiceCost): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-credit-card text-primary me-2"></i>Payment</h6>
                            <?php if($paymentDone): ?>
                            <div class="alert alert-success d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check fa-lg"></i>
                                <div>Payment of <strong>₹<?php echo number_format($payRow->PaymentAmount, 2); ?></strong> received via <?php echo htmlentities($payRow->PaymentMode); ?>.
                                    <a href="receipt.php?bno=<?php echo htmlentities($row->BookingNumber); ?>" class="btn btn-sm btn-outline-success ms-2">View Receipt</a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span><i class="fa-solid fa-triangle-exclamation me-2"></i>Payment pending — ₹<?php echo number_format($row->ServiceCost, 2); ?></span>
                                <a href="payment.php?bno=<?php echo htmlentities($row->BookingNumber); ?>" class="btn btn-warning fw-bold">Pay Now</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- ===== RATING & REVIEW ===== -->
                        <?php if($currentStatus == 'Completed' && !$feedbackGiven): ?>
                        <div class="card border-0 bg-light p-4">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-star text-warning me-2"></i>Rate Your Experience</h6>
                            <form method="post">
                                <input type="hidden" name="bno" value="<?php echo htmlentities($row->BookingNumber); ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rating</label>
                                    <div class="star-rating d-flex gap-2 fs-2" id="starRating">
                                        <?php for($s=1;$s<=5;$s++): ?>
                                        <i class="fa-regular fa-star text-warning star-btn" data-val="<?php echo $s; ?>" style="cursor:pointer;"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating" id="ratingInput" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Your Feedback</label>
                                    <textarea class="form-control" name="feedback_text" rows="3" placeholder="Share your experience with the driver..."></textarea>
                                </div>
                                <button type="submit" name="submit_feedback" class="btn btn-warning fw-bold px-4">Submit Review</button>
                            </form>
                        </div>
                        <?php elseif($currentStatus == 'Completed' && $feedbackGiven): ?>
                        <div class="alert alert-success border-0">
                            <i class="fa-solid fa-circle-check me-2"></i>You have already submitted your review. Thank you!
                        </div>
                        <?php endif; ?>

                    </div><!-- card-body -->
                </div><!-- card -->
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning text-center py-4 border-0 shadow-sm">
                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block text-warning opacity-75"></i>
                    <h5>No booking found for the provided details.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
</section>

<?php include('includes/footer.php'); ?>

<script>
// Star rating interaction
document.querySelectorAll('.star-btn').forEach(function(star) {
    star.addEventListener('click', function() {
        const val = parseInt(this.getAttribute('data-val'));
        document.getElementById('ratingInput').value = val;
        document.querySelectorAll('.star-btn').forEach(function(s, i) {
            s.classList.toggle('fa-solid', i < val);
            s.classList.toggle('fa-regular', i >= val);
        });
    });
    star.addEventListener('mouseover', function() {
        const val = parseInt(this.getAttribute('data-val'));
        document.querySelectorAll('.star-btn').forEach(function(s, i) {
            s.classList.toggle('fa-solid', i < val);
            s.classList.toggle('fa-regular', i >= val);
        });
    });
});
const starRating = document.getElementById('starRating');
if(starRating) {
    starRating.addEventListener('mouseleave', function() {
        const selected = parseInt(document.getElementById('ratingInput').value) || 0;
        document.querySelectorAll('.star-btn').forEach(function(s, i) {
            s.classList.toggle('fa-solid', i < selected);
            s.classList.toggle('fa-regular', i >= selected);
        });
    });
}
</script>
