<?php
$mess="";
$frbank="Bank of PHP";
if($_GET == null)die("Name parameter missing");
else{
session_start();
if (!isset($_SESSION['username']) || $_SESSION['username'] !== $_GET['username']) {
    header("Location: login.php");
    exit();
}
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "db1006";
$port = 3306;

$dbuser="";
$dbpass="";
$inputUsername = $_GET['username']; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the SELECT statement with a placeholder
$sql = "SELECT * FROM tb1006 WHERE username = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind the parameter (s = string)
$stmt->bind_param("s", $inputUsername);

// Execute
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch and output rows
    while ($row = $result->fetch_assoc()) {
        $dbuser=htmlentities($row['username']);
		$dbem=$row['email'];
		$dbdob=$row['DOB'];
		$dbmob=$row['mob'];
		$dbaem=$row['aem'];
		$dbprefl=$row['preflan'];
		$dbmoto=$row['mot'];
		$dbacno=$row['Acnum'];
		$dbbal=$row['Balance'];
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
		$stmt->close();
		$conn->close();
        header("Location: login.php");
        exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['debit'])) {
	 if ($dbbal <=0 or floatval($_POST['deb'])>$dbbal) {
		header("Location: account.php?username=" . urlencode($inputUsername));
		$_SESSION['message'] = "Error: Insufficient funds."; 
		exit();
    }
	$conn->begin_transaction();
	$conn->query("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE");
	try {
	$dbbal-=floatval($_POST['deb']);
	$ttype=$_POST['debit'];
	$tan=$_POST['AN'];
	$tbank=$_POST['BAN'];
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);}
	// Prepare the SELECT statement with a placeholder
	$sql = "UPDATE tb1006 SET Balance=? WHERE username = ?";
	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		die("Prepare failed: " . $conn->error);
	}

	// Bind the parameter (s = string)
	$stmt->bind_param("ds", $dbbal, $inputUsername);

	// Execute
	$stmt->execute();

	//Transaction Recording
	$sql = "INSERT INTO tranrec (username, trantype, amount, FromAcnum, ToAcnum, Frombank, Tobank, Balance)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	$stmt = $conn->prepare($sql);

	// Bind variables
	if (!$stmt) {
		$mess = "SQL prepare failed: " . $conn->error;
	} else {
		$u = $inputUsername;
		$stmt->bind_param("ssssssss", $u, $ttype, $_POST['deb'], $dbacno, $tan, $frbank, $tbank, $dbbal);
	}

	// Execute
	if ($stmt->execute()) {
		echo "<script>document.getElementById('4').value = " . json_encode($dbbal) . ";</script>";
		header("Location: account.php?username=" . urlencode($inputUsername));
		$_SESSION['message']= "Debit successful!";
		exit();
	} else {
		$mess= "Error: " . $stmt->error;
	}
	$conn->commit(); // If all succeed
	} catch (Exception $e) {
    $conn->rollback(); // Revert changes if any fail
}
}
}
?>
<html>
<head>
<title>Account Landing page</title>
<style>
body{
	background-color:pink;
	justify-content: center;
	align-items: center;
	display: flex;
}
div{
	background-color:white;
	border:2px solid red;
	text-align:center;
	font-size:30px;
	padding: 20px;
}
</style>
<script>
var user="<?php echo htmlentities($dbuser)?>", 
email="<?php echo htmlentities($dbem)?>", 
Acno="<?php echo htmlentities($dbacno)?>", 
Bal = <?= json_encode($dbbal) ?>,
DOB="<?php echo htmlentities($dbdob)?>", 
Mob="<?php echo htmlentities($dbmob)?>", 
Altemail="<?php echo htmlentities($dbaem)?>", 
PrefLan="<?php echo htmlentities($dbprefl)?>", 
Mot="<?php echo htmlentities($dbmoto)?>";
window.onload=function(){
document.getElementById("1").value=user;
document.getElementById("2").value=email;
document.getElementById("3").value=Acno;
document.getElementById("4").value=Bal;
document.getElementById("5").value=DOB;
document.getElementById("6").value=Mob;
document.getElementById("7").value=Altemail;
document.getElementById("8").value=PrefLan;
document.getElementById("9").value=Mot;}
</script>
</head>
<body>
<div>
<h1> Hello <?php echo htmlentities($inputUsername);  ?></h1>
<table>
<tr colspan='2'><td style='padding-left:150px;'>    
<form method='POST'>
    <input type='submit'  value='Logout' name='logout'>
</form></td>
    <td style='padding-left:150px;'>
        <form method='GET' action='<?php echo htmlentities($_SERVER['PHP_SELF']); ?>'>
            <input type='hidden' name='username' value='<?php echo htmlentities($inputUsername); ?>'>
            <input type='submit' value='View Transactions' name='Viewt'>
        </form>
</td>
</tr>

<tr colspan='2'><td>
<?php
if (isset($_SESSION['message'])) {
	$mess=$_SESSION['message'];
	unset($_SESSION['message']);
    echo "<pre style='font-size:25; font-family:Calibri; color:Red; text-align:center;'>".
	htmlentities($mess).
	"</pre>";
}
?></td></tr>
<caption>USER Details</caption>
<tr><th>Username</th><td><input type="text" name="username" id="1" readonly></td></tr>
<tr><th>Email</th><td><input type="text" name="email" id="2" readonly></td></tr>
<tr><th>Account Number</th><td><input type="text" name="acno" id="3" readonly></td></tr>
<tr><th>Balance</th><td><input type="text" name="bal" id="4" readonly></td></tr>
<tr><th>DOB</th><td><input type="text" name="dob" id="5" readonly></td></tr>
<tr><th>Mobile</th><td><input type="text" name="mob" id="6" readonly></td></tr>
<tr><th>Alternate Email ID</th><td><input type="text" name="aem" id="7" readonly></td></tr>
<tr><th>Preferred Language</th><td><input type="text" name="prefl" id="8" readonly></td></tr>
<tr><th>Mother Tongue</th><td><input type="text" name="mot" id="9" readonly></td></tr>
<form method='POST'>
<tr><td colspan="2">
<label>Choose Transaction Type:</label>
<select name="credebi">
    <option value="Credit">Credit</option>
    <option value="Debit" <?= (isset($_POST['credebi']) && $_POST['credebi'] == "Debit") ? "selected" : "" ?>>Debit</option>
</select>
<input type='submit' value='Proceed' name='Tran'>
</td></tr>

<?php if (isset($_POST['Tran']) && $_POST['credebi'] == "Debit") { ?>
<tr><td colspan="2">
<table><tr><td>
    <label>To Account Number</label>
    <input type='text' name='AN'><br></td></tr>
    <tr><td><label>To Bank</label>
    <input type='text' name='BAN'><br></td></tr>
    <tr><td><label>Enter Amount to Debit:</label>
    <input type='number' name='deb'><br></td></tr>
    <tr><td colspan="2"><input type='submit' value='Debit' name='debit'></td></tr>
</table>
</td></tr>
<?php } ?>

<?php if (isset($_GET['Viewt'])) { ?>
<tr><td colspan="2">
<?php
if ($_SERVER["REQUEST_METHOD"] == "GET" and isset($_GET['Viewt'])) { 
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SELECT statement with a placeholder
    $sql = "SELECT * FROM tranrec WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameter (s = string)
    $stmt->bind_param("s", $inputUsername);

    // Execute
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch and output rows
        // You should display these rows, not just overwrite variables
        // If you only assign to variables, you'll only get the last row
        echo "<h2>Transaction History</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Username</th><th>Transaction<br>Type</th><th>Amount</th><th>From Account</th><th>To Account</th><th>From Bank</th><th>To Bank</th><th>Balance<br>After<br>Transaction</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlentities($row['username']) . "</td>";
            echo "<td>" . htmlentities($row['trantype']) . "</td>";
            echo "<td>" . htmlentities($row['amount']) . "</td>";
            echo "<td>" . htmlentities($row['FromAcnum']) . "</td>";
            echo "<td>" . htmlentities($row['ToAcnum']) . "</td>";
            echo "<td>" . htmlentities($row['Frombank']) . "</td>";
            echo "<td>" . htmlentities($row['Tobank']) . "</td>";
            echo "<td>" . htmlentities($row['Balance']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No transactions found for " . htmlentities($inputUsername) . ".</p>";
    }
    // It's good practice to close the statement and result set
    $stmt->close();
    $result->close();
}
?>
</td></tr>
<?php } ?>
</form>
<?php

?>
</div>
</table>

</div>
</body>
</html>
