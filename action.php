
<?php

include_once 'Functions.php';
Headprint();
$mode=$_POST["mode"];
$PID=$_SESSION["PID"];

if($mode=="CHAT"){
	$msg=$_POST["msg"];
	AddtoChat($PID,$msg,1,0);
}
else if($mode=="ACTION"){
	$AID=$_POST["activity"];
	TakeAction($PID,$AID);
}
else if($mode=="MOVE"){
	$route=$_POST["route"];
	MovePlayer($PID,$route);
}
PrintUI($PID);
?>