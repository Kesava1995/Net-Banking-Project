<?php
$mess="";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
if(empty($_POST['username']) or empty($_POST['password']) or empty($_POST['password2']) 
	or empty($_POST['email']) or empty($_POST['DOB']) or empty($_POST['Bal']) or empty($_POST['Acno'])){
$mess= "User name, password ,DOB and Email are required";
}
else{
$u=$_POST['username'];
$p1=$_POST['password'];
$p2=$_POST['password2'];
$em=$_POST['email'];
$dob=$_POST['DOB'];
$mob=isset($_POST['mob']) ? trim($_POST['mob']) : "";
$aem=$_POST['aem'];
$lang=$_POST['lang'];
$moto=$_POST['moto'];
$bal=$_POST['Bal'];
$acno=$_POST['Acno'];

if (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
    $mess = "Invalid email format.";
}

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "db1006"; // Replace this
$port = 3306;

try {
    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($dobDate)->y;
} catch (Exception $e) {
    $mess = "Invalid Date of Birth format.";
    $age = 0;
}

if ($mess === "") {
if($p1==$p2 && $age>=18){
$hashedPass = password_hash($p1, PASSWORD_DEFAULT);
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Prepare statement
$sql = "INSERT INTO tb1006 (username, email, pass, Acnum, Balance, DOB, mob, aem, preflan, mot)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt = $conn->prepare($sql);

// Bind variables
if (!$stmt) {
$mess = "SQL prepare failed: " . $conn->error;
} else {
$stmt->bind_param("ssssssssss", $u, $em, $hashedPass, $acno, $bal, $dob, $mob, $aem, $lang, $moto);
}

// Execute
if ($stmt->execute()) {
    $mess= "Net Banking Credentials Created successfully!";
} else {
    $mess= "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();

}
else{
	if($p1!=$p2)$mess="Passwords do not match";
	if($age<18){
		$mess="Ineligible to create an account";
	}
	if(gettype($acno)!="integer"){
		$mess="Account Number has to be only a number";
	}
	if(gettype($bal)!="double"){
		$mess="Balance can only be a Number with decimals";
	}
}

}
}
}
?>
<html>
<head>
<title>Net Bank Register</title>
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
</head>
<body>
<div>
<form method="POST" action="register.php">
<h1>NET BANKING CUSTOMER REGISTRATION <br>[BANK MEMBERS ACCESS ONLY]</h1>
<p style="color:red;">
<?php
if (isset($mess)) {
    echo htmlentities($mess);
}
?>
</p>
<table>
<tr>
<td>
<label for="username">Username</label>
</td>
<td>
<input type="text"  name="username" value="<?= htmlentities($_POST['username'] ?? '') ?>"><br>
</td>
</tr>
<tr>
<td>
<label for="email">Email</label>
</td>
<td>
<input type="email"  name="email"><br>
</td>
</tr>
<tr>
<td>
<label for="password">Password</label>
</td>
<td>
<input type="password" name="password"><br>
</td>
</tr>
<tr>
<td>
<label for="password2">Retype Password</label>
</td>
<td>
<input type="password" name="password2"><br>
</td>
</tr>
<tr>
<td>
<label for="Acno">Account Number</label>
</td>
<td>
<input type="number" name="Acno" value="<?= htmlentities($_POST['Acno'] ?? '') ?>"><br>
</td>
</tr>
<tr>
<td>
<label for="Bal">Balance</label>
</td>
<td>
<input type="number" name="Bal" value="<?= htmlentities($_POST['Bal'] ?? '') ?>"><br>
</td>
</tr>
<tr>
<td>
<label for="DOB">Date Of Birth</label>
</td>
<td>
<input type="date" name="DOB" value="<?= htmlentities($_POST['DOB'] ?? '') ?>"><br>
</td>
</tr>
<tr>
<td>
<label for="mob">Mobile</label>
</td>
<td>
<input type="text" name="mob" value="<?= htmlentities($_POST['mob'] ?? '') ?>"><br>
</td>
</tr>
<tr>
<td>
<label for="aem">Alternate Email id</label>
</td>
<td>
<input type="text" name="aem" value="<?= htmlentities($_POST['aem'] ?? '') ?>"><br>
</td>
</tr>
<tr>
<td>
<label for="lang">Language Prefered</label>
</td>
<td>
<input type="text" name="lang" value="<?= htmlentities($_POST['lang'] ?? '') ?>"><br>
</td>
</tr>
<tr>
<td>
<label for="moto">Mother Tongue</label>
</td>
<td>
<input type="text" name="moto" value="<?= htmlentities($_POST['moto'] ?? '') ?>"><br>
</td>
</tr>
<tr>
<td style="padding-left:50px;">
<input type="submit" value="Register">
</td>
<td style="padding-left:30px;">
<input type="reset" value="Cancel">
</td>
</tr>
</table>
</form>
</div>
</body>
</html>