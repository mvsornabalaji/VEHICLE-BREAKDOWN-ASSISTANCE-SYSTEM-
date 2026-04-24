<?php
include('includes/dbconnection.php');

// Reset a specific driver password OR list all drivers
if(isset($_POST['reset'])) {
    $did  = intval($_POST['did']);
    $pass = md5(trim($_POST['newpass']));
    $upd  = $dbh->prepare("UPDATE tbldriver SET Password=:p, Status='Active' WHERE ID=:id");
    $upd->bindParam(':p',  $pass, PDO::PARAM_STR);
    $upd->bindParam(':id', $did,  PDO::PARAM_INT);
    $upd->execute();
    echo "<p style='color:green;font-family:sans-serif;'><b>Password reset successfully for driver ID $did.</b></p>";
}

$q = $dbh->prepare("SELECT ID, DriverName, Email, Status FROM tbldriver ORDER BY ID DESC");
$q->execute();
$drivers = $q->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html>
<head><title>Driver Reset</title>
<style>
  body{font-family:sans-serif;padding:20px;}
  table{border-collapse:collapse;width:100%;}
  th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}
  th{background:#f0f0f0;}
  input{padding:6px;margin-right:6px;}
  button{padding:6px 14px;background:#198754;color:white;border:none;border-radius:4px;cursor:pointer;}
</style>
</head>
<body>
<h2>Driver Accounts</h2>
<?php if(count($drivers) == 0): ?>
  <p style="color:red;">No drivers found in database. Add a driver from the Admin panel first.</p>
<?php else: ?>
<table>
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Reset Password</th></tr>
  <?php foreach($drivers as $d): ?>
  <tr>
    <td><?php echo $d->ID; ?></td>
    <td><?php echo htmlentities($d->DriverName); ?></td>
    <td><?php echo htmlentities($d->Email); ?></td>
    <td><?php echo htmlentities($d->Status); ?></td>
    <td>
      <form method="post" style="display:flex;align-items:center;gap:6px;">
        <input type="hidden" name="did" value="<?php echo $d->ID; ?>">
        <input type="text" name="newpass" placeholder="New password" required>
        <button type="submit" name="reset">Reset</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<br>
<p style="color:red;"><small>Delete this file after use.</small></p>
<p><a href="driver/index.php">Go to Driver Login</a></p>
</body>
</html>
