<?php
include('includes/header.php');

if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $vtype = $_POST['vtype'];
    $vnumber = $_POST['vnumber'];
    $problem = $_POST['problem'];
    $location = $_POST['location'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];
    $bookingno = mt_rand(100000000, 999999999);
    $status = 'Pending';
    
    // Check if photo is uploaded
    $photo = "";
    if(isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["photo"]["name"];
        $filetype = $_FILES["photo"]["type"];
        $filesize = $_FILES["photo"]["size"];
    
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
    
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) die("Error: File size is larger than the allowed limit.");
    
        if(in_array($filetype, $allowed)) {
            $newFilename = time() . "_" . $filename;
            move_uploaded_file($_FILES["photo"]["tmp_name"], "uploads/" . $newFilename);
            $photo = $newFilename;
        }
    }

    $sql = "INSERT INTO tblbooking(BookingNumber, Name, MobileNumber, VehicleType, VehicleNumber, Problem, Location, Latitude, Longitude, Photo, Status) 
            VALUES(:bookingno, :name, :mobile, :vtype, :vnumber, :problem, :location, :lat, :lng, :photo, :status)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookingno',$bookingno,PDO::PARAM_STR);
    $query->bindParam(':name',$name,PDO::PARAM_STR);
    $query->bindParam(':mobile',$mobile,PDO::PARAM_STR);
    $query->bindParam(':vtype',$vtype,PDO::PARAM_STR);
    $query->bindParam(':vnumber',$vnumber,PDO::PARAM_STR);
    $query->bindParam(':problem',$problem,PDO::PARAM_STR);
    $query->bindParam(':location',$location,PDO::PARAM_STR);
    $query->bindParam(':lat',$lat,PDO::PARAM_STR);
    $query->bindParam(':lng',$lng,PDO::PARAM_STR);
    $query->bindParam(':photo',$photo,PDO::PARAM_STR);
    $query->bindParam(':status',$status,PDO::PARAM_STR);
    $query->execute();

    $lastInsertId = $dbh->lastInsertId();
    if($lastInsertId) {
        // Notification logic
        $msg = "Your request for $vtype breakdown has been submitted successfully.";
        $nsql = "INSERT INTO tblnotifications(BookingNumber, Message) VALUES(:bno, :msg)";
        $nquery = $dbh->prepare($nsql);
        $nquery->bindParam(':bno', $bookingno, PDO::PARAM_STR);
        $nquery->bindParam(':msg', $msg, PDO::PARAM_STR);
        $nquery->execute();

        echo "<script>alert('Your Assistance Request is submitted successfully. Your Booking No is: $bookingno');</script>";
        echo "<script>window.location.href='track.php'</script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again.');</script>";
    }
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>Fast & Reliable Breakdown Assistance</h1>
        <p class="lead mb-4">Stuck on the road? We'll get you moving in no time.</p>
        
        <!-- Emergency SOS -->
        <a href="#requestForm" class="text-decoration-none">
            <button class="sos-btn mb-4 d-flex flex-column align-items-center justify-content-center">
                <i class="fa-solid fa-triangle-exclamation mb-1" style="font-size: 2.5rem;"></i>
                <span style="font-size: 1.2rem;">SOS</span>
            </button>
        </a>
        <p class="text-light">Tap for Emergency Service</p>
    </div>
</section>

<!-- Vehicle Types Section -->
<section class="py-5 bg-white vehicle-types">
    <div class="container text-center">
        <h3 class="mb-4 fw-bold">Select Your Vehicle Type</h3>
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-3">
                <div class="bg-light shadow-sm bg-body-tertiary">
                    <i class="fa-solid fa-motorcycle fa-3x text-primary mb-2"></i>
                    <h5 class="fw-bold">Bike</h5>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light shadow-sm bg-body-tertiary">
                    <i class="fa-solid fa-car fa-3x text-success mb-2"></i>
                    <h5 class="fw-bold">Car</h5>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light shadow-sm bg-body-tertiary">
                    <i class="fa-solid fa-truck fa-3x text-warning mb-2"></i>
                    <h5 class="fw-bold">Truck</h5>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-light shadow-sm bg-body-tertiary">
                    <i class="fa-solid fa-bus fa-3x text-danger mb-2"></i>
                    <h5 class="fw-bold">Bus</h5>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Request Form Section -->
<section id="requestForm" class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <h2 class="text-center mb-4 fw-bold"><i class="fa-solid fa-clipboard-list text-primary me-2"></i>Request Assistance</h2>
                    <form method="post" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" class="form-control" name="name" required placeholder="John Doe">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mobile Number</label>
                                <input type="number" class="form-control" name="mobile" required placeholder="Enter 10-digit number" pattern="[0-9]{10}">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vehicle Type</label>
                                <select class="form-select" name="vtype" required>
                                    <option value="">Select Type</option>
                                    <option value="Bike">Bike</option>
                                    <option value="Car">Car</option>
                                    <option value="Truck">Truck</option>
                                    <option value="Bus">Bus</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vehicle Number</label>
                                <input type="text" class="form-control" name="vnumber" required placeholder="e.g. MH-12-AB-1234">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Problem Description</label>
                            <textarea class="form-control" name="problem" rows="3" required placeholder="E.g., Flat tire, Engine won't start..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Location (Address/Landmark)</label>
                            <div class="input-group">
                                <textarea class="form-control" id="location" name="location" rows="2" required placeholder="Enter your detailed location..."></textarea>
                                <button type="button" class="btn btn-outline-secondary" id="getLocationBtn" title="Auto detect location">
                                    <i class="fa-solid fa-location-crosshairs"></i> Detect
                                </button>
                            </div>
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <!-- Live map preview -->
                            <div id="mapPreview" class="mt-2 rounded overflow-hidden border" style="display:none; height:220px;">
                                <iframe id="mapFrame" width="100%" height="220" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Upload Vehicle Photo <span class="text-muted fw-normal">(Optional)</span></label>
                            <input class="form-control" type="file" name="photo" id="photoInput" accept="image/*">
                            <div class="form-text">Max 5MB — JPG, PNG, GIF</div>
                            <div id="photoPreviewWrap" class="mt-2" style="display:none;">
                                <img id="photoPreview" src="" alt="Preview" class="img-thumbnail" style="max-height:180px;">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="submit" class="btn btn-primary btn-lg btn-animated">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const getLocationBtn = document.getElementById('getLocationBtn');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const locInput = document.getElementById('location');
    const mapPreview = document.getElementById('mapPreview');
    const mapFrame = document.getElementById('mapFrame');

    getLocationBtn.addEventListener('click', function() {
        if (navigator.geolocation) {
            getLocationBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Detecting...';
            getLocationBtn.disabled = true;
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    latInput.value = lat;
                    lngInput.value = lng;

                    // Show live map preview
                    mapFrame.src = `https://maps.google.com/maps?q=${lat},${lng}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
                    mapPreview.style.display = 'block';
                    
                    // Reverse geocoding via Nominatim
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(response => response.json())
                        .then(data => {
                            locInput.value = data && data.display_name ? data.display_name : `Lat: ${lat}, Lng: ${lng}`;
                            getLocationBtn.innerHTML = '<i class="fa-solid fa-check text-success"></i> Detected';
                            getLocationBtn.disabled = false;
                        })
                        .catch(() => {
                            locInput.value = `Lat: ${lat}, Lng: ${lng}`;
                            getLocationBtn.innerHTML = '<i class="fa-solid fa-check text-success"></i> Detected';
                            getLocationBtn.disabled = false;
                        });
                },
                function(error) {
                    alert("Error getting location: " + error.message);
                    getLocationBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Detect';
                    getLocationBtn.disabled = false;
                },
                { enableHighAccuracy: true }
            );
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    });

    // Photo preview
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    const photoPreviewWrap = document.getElementById('photoPreviewWrap');
    if(photoInput) {
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                    photoPreviewWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                photoPreviewWrap.style.display = 'none';
            }
        });
    }
});
</script>
