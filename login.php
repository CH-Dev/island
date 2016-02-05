<?php 
session_start();
$_SESSION["servername"]="192.168.2.30:3307";
$_SESSION["Dusername"] = "web";
$_SESSION["Dpassword"] = "L69992772";
$_SESSION["dbname"] = "adventure";
$_SESSION["username"]=$_POST["username"];
$_SESSION["password"]=$_POST["password"];
include_once 'Functions.php';
NoSessionHeadprint();
$PID=connectUser($_POST["username"],$_POST["password"]);
$_SESSION["PID"]=$PID;
PrintUI($PID);
?>
</body>
</html>