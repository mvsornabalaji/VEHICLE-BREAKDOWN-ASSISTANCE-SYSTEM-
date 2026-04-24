<?php
// Run this once if admin login fails — resets/inserts the default admin account
include('includes/dbconnection.php');

try {
    // Check if admin exists
    $chk = $dbh->prepare("SELECT ID FROM tbladmin WHERE UserName='admin'");
    $chk->execute();

    if($chk->rowCount() > 0) {
        // Update password to plain text
        $upd = $dbh->prepare("UPDATE tbladmin SET Password='12022003' WHERE UserName='admin'");
        $upd->execute();
        echo "<b style='color:green'>✅ Admin password reset to: 12022003</b><br>";
    } else {
        // Insert default admin
        $ins = $dbh->prepare("INSERT INTO tbladmin(AdminName, UserName, MobileNumber, Email, Password) VALUES('Admin','admin','1234567890','admin@gmail.com','12022003')");
        $ins->execute();
        echo "<b style='color:green'>✅ Admin account created. Username: admin | Password: 12022003</b><br>";
    }

    echo "<br>You can now <a href='admin/index.php'>login here</a>.<br>";
    echo "<br><small style='color:red'>Delete this file after use.</small>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
