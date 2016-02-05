<?php
/*
 * SKID is the IDKey used by the skillslist table
 * PID is the current player IDKey from the Player Table
 * SID is the skills table IDKey, this should not be passed between functions and is for internal use only
 * LID is a location ID key from the locations table
 * NID is a node ID Key from the nodes table
 * AID is the Activity ID Key from the Activities table
 * XPI is an input for XP granted
 * xp is the current experience value for the appropriate skill
 * lvl is the current level value for the appropriate skill
 * $conn is the storage for the connection to the database, it should not be passed, and each function should create its own connection
 * DIFF is the difficulty of the current task, DIFF must be lower than your current LVL in the skill
 * IID is the IDKey of the Item being searched for or affected
 * Items are definitions of objects in the game world, they are not part of the inventory system
 * CATID the IDKey of the category of an Item, Categories allow the seperation of Items into subgroups such as 
 * Helm, Armour, Gloves, Boots, Shields and Weapons, as well as several other categories
 * Active, only 1 item of each Category may be active at a time, activating a second item will either
 * deactivate all other items of that category for the triggering player, or make a crafting attempt if a valid recipe is found
 * for the 2 ItemID's
 */


//Booting functions and UI printing
function Headprint(){
	NoSessionHeadprint();
	session_start();
}
function NoSessionHeadprint(){
	echo "
	<html>
		<head>
			<title>Island</title>
			<link rel='stylesheet' type='text/css' href='main.css'>
		</head>
		<body>";
}
function PrintUI($PID){
	//printLheader();
	printRheader();
	$LID=getlocation($PID);
	
	printnav($PID,$LID);
	echo "<div class='accordionclear'>";
	printskills($PID);
	printinv($PID);
	
	echo "</div>";
	printlog($PID);
	printchat($PID);
	echo "<script>
var log = document.getElementById('log');
log.scrollTop = log.scrollHeight;
</script><script>
var chat = document.getElementById('chat');
chat.scrollTop = chat.scrollHeight;
</script>";
}
function printRheader(){
	echo "<rheader>";
	echo "<p class='InvP'><a href='#invtab' class='tab'>Show Inv</a> <a href='#skillstab' class='tab'>Show Skills</a></p>";
	echo "</rheader>";
}
function printLheader(){
	echo "<lheader>";
	echo "<p class='InvP'><a href='#Trav' class='tab'>Show Travel</a> <a href='#Nav' class='tab'>Show Nav</a></p>";
	echo "</lheader>";
}
function printinv($PID){
	echo "<div id='invtab'>";
		echo "<div class='content'>";
		$bag=getInventory($PID);
		$bagN=count($bag);
		for($x=0;$x<$bagN;$x=$x+6){
			$name=$bag[$x];
			$Quant=$bag[$x+1];
			$IID=$bag[$x+2];
			$HP=$bag[$x+3];
			$Desc=$bag[$x+4];
			$ACT=$bag[$x+5];
			if($ACT==1){
				echo "<b>";
			}//This section currently has the HP attribute of tools disabled
			echo "<p title='$Desc' class='InvP'>$Quant:$name $HP</p>";
			if($ACT==1){
				echo "</b>";
			}
		}
		echo "</div>";
	echo "</div>";
}
function printskills($PID){
	echo "<div id='skillstab'>";
		echo "<div class='content'>";
		$skills=getSkillInfo($PID);
		$skillsN=count($skills);
		for($x=0;$x<$skillsN;$x=$x+6){
			$lvl=$skills[$x];
			$xp=$skills[$x+1];
			$Bstr=$skills[$x+2];
			$Bnum=$skills[$x+3];
			$skillName=$skills[$x+4];
			$desc=$skills[$x+5];
			$xpneeded=getXPNeeded($lvl);
			if($Bnum>0&&$Bstr>0){
				echo "<p title='$desc' class='InvP'>$xp/$xpneeded LVL $lvl+$Bstr/$Bnum $skillName</p>";
			}
			echo "<p title='$desc' class='InvP'>$xp/$xpneeded LVL $lvl $skillName</p>";
		}
		echo "</div>";
	echo "</div>";
}
function printnav($PID,$LID){
	$nodes=getNodes($LID,$PID);
	$numN=count($nodes)/3;
	echo "<div id='Nav'>";
		echo "<div>";
			for($x=0;$x<$numN;$x=$x+3){
			$NID=$nodes[$x+2];
			$activities=getActivities($NID,$PID);
			$numA=count($activities)/3;
			echo "<p title='".$nodes[$x+1]."' class='ActionP'><b>$nodes[$x]</b></p>";
			for($y=0;$y<$numA;$y=$y+3){
				$AID=$activities[$y+2];
				$desc=$activities[$y+1];
				$name=$activities[$y];
				echo "<p title='$desc' class='InvP'><form action='action.php#invtab' method='post'>
					<input type='text' value='ACTION' name='mode' hidden>
					<input type='text' value='$AID' name='activity' hidden>
					<input type='submit' value='$name' title=''> </form></p>";
			}
		}
		echo "</div>";
		echo "<div>";
		echo "<p><b>Travel</b></p>";
		$paths=getRoutes($LID);
		$pathsN=count($paths);
		for($x=0;$x<$pathsN;$x=$x+4){
			$desc=$paths[$x+2];
			$name=$paths[$x+1];
			$idkey=$paths[$x];
			echo "<p title='$desc' class='InvP'><form action='action.php#invtab' method='post'>
			<input type='text' value='MOVE' name='mode' hidden>
			<input type='text' value='$idkey' name='route' hidden>
			<input type='submit' value='$name'>
			</form></p>";
		}
		echo "</div>";
	echo "</div>";
}
function printlog($PID){
	echo "<div class='log' id='log'>";
	$log=getActionLog($PID);
	$logN=count($log);
	for($x=$logN-1;$x>-1;$x--){
		$desc=$log[$x];
		echo "<p class='logP'>$desc</p>";
	}
	echo "</div>";
}
function printchat($PID){
	echo "<chat class='scroll' id='chat'>";
	$chat=getChatLog($PID);
	$chatN=count($chat);
	for($x=$chatN-1;$x>-1;$x--){
		$message=$chat[$x];
		echo "<p class='logP'>$message</p>";
	}
	echo "</chat>";
	echo "<chatsender>";
	echo "<form action='action.php' method='post'><input type='text' name='mode' value='CHAT' hidden><input type='text' name='msg' class='chatbox'><input type='submit' value='Send'></form>";
	echo "</chatsender>";
}
function printtravel($PID,$LID){
	echo "<div id='Trav'>";
	
	echo "</div>";
}




//Player related functions
//use to grant XP points to a player, takes in the playerID, the XP to give, and the skillslist IDKey to be granted to
function grantXP($PID,$XPI,$SKID){
	$conn=conDB();
	$lvl=lookupLVL($PID,$SKID);
	$xp=lookupXP($PID,$SKID);
	$fxp=getXPScale($XPI,$lvl);
	$xp=$xp+$fxp;
	$lvlarray=checkLVLUP($xp,$lvl);
	$name=getSkillName($SKID);
	AddtoLog($PID,"You gained $fxp experience in $name!",0,1);
	if($xp>$lvlarray[0]){
		AddtoLog($PID,"You are now level $lvlarray[1] in $name!",0,1);
	}
	$xp=$lvlarray[0];
	$lvl=$lvlarray[1];
	if($lvl>0){
		$sql="UPDATE skills SET LVL='$lvl',XP='$xp' WHERE SkillID='$SKID' AND PlayerID='$PID'";
	}
	else{
		$sql="INSERT INTO skills (PlayerID,SkillID,LVL,XP) VALUES ('$PID','$SKID','$lvl','$xp')";
	}
	mysqli_query($conn, $sql);
}
//Grants target player a Quantity of items, and if they do not already have a copy of the item, put a new copy in their inventory
function grantItem($PID,$IID,$QUANT){
	$conn=conDB();
	$QUANT2=checkBag($PID,$IID);
	$stack=checkStackable($IID);
	$Bspace=CheckBagSpace($PID);
	if($stack==1){
		if($QUANT2==0){
			if($Bspace>0){
				$sql="INSERT INTO bag (PlayerID,ItemID,Quantity,Active) VALUES ('$PID','$IID','$QUANT','0')";
				mysqli_query($conn, $sql);
			}
		}
		else{
			$fQuant=$QUANT+$QUANT2;
			$sql="UPDATE bag SET Quantity='$fQuant' WHERE PlayerID='$PID' AND ItemID='$IID'";
			mysqli_query($conn, $sql);
		}
	}
	else{
		if($Bspace>0){
			$HP=getItemMaxHP($IID);
			$sql="INSERT INTO bag (PlayerID,ItemID,Quantity,Active,Health) VALUES ('$PID','$IID','$QUANT','0','$HP')";
			mysqli_query($conn, $sql);
		}
	}
	$name=getItemName($IID);
	AddtoLog($PID,"You place the $name in your bag!",0,1);
}
//Removes the specified quantity 
function removeItem($PID,$IID,$QUANT){
	$conn=conDB();
	$QUANT2=checkBag($PID,$IID);
	$BID=findBagSlot($PID,$IID);
	if($QUANT2>$QUANT)
	{
		$QUANT3=$QUANT2-$QUANT;
		$sql="UPDATE bag SET Quantity='$QUANT3' WHERE IDKey='$BID'";
		mysqli_query($conn, $sql);
		return 0;
	}
	else if($QUANT2==$QUANT){
		$sql="DELETE FROM bag WHERE IDKey='$BID'";
		mysqli_query($conn, $sql);
		return 0;
	}
	else if($QUANT2>$QUANT){
		return 1;
	}
}
//Activate the selected item
function activateItem($PID,$BID){
	$conn=conDB();
	$CACT=checkActive($PID);
	if($CACT==0){
		$sql="UPDATE bag SET Active='1' WHERE PlayerID='$PID' AND IDKey='$BID'";
		mysqli_query($conn, $sql);
	}else{
		craftItem($PID,$BID,$CACT);
		deactivateItem();
	}
}
//Attempt to craft an item
function craftItem($PID,$BID,$BID2){
	$conn=conDB();
	$IID=getIID($PID,$BID);
	$IID2=getIID($PID,$BID2);
	$newIID=checkRecipe($IID,$IID2);
	if($newIID!=0){
		$R1=removeItem($PID,$IID,1);
		$sql="SELECT * FROM craftrecipe WHERE (ItemID1='$IID' AND ItemID2='$IID2') OR (ItemID2='$IID' AND ItemID1='$IID2')";
		$result = mysqli_query($conn, $sql);
		$row= $result->fetch_assoc();
		$min=$row["Min"];
		$max=$row["Max"];
		$DIFF=$row["Difficulty"];
		$LVL=$row["LVL"];
		$SKID=$row["SkillID"];
		if($R1==0){
			$R2=removeItem($PID,$IID2,1);
			if($R2==0){
				$skill=performAct($PID,$SKID,$DIFF,$LVL);
				if($skill==true){
					if($min!=$max){
						$QUANT=rand($min,$max);
					}
					else{
						$QUANT=$max;
					}
					AddtoLog($PID,"You successfully Crafted an Item!",0,1);
					grantItem($PID,$newIID,$QUANT);
					
				}
				AddtoLog($PID,"You were unable to Craft the item.",0,0);
			}
		}
	}
}
function AddtoLog($PID,$DESC,$GLO,$RESULT){
	$conn=conDB();
	$date=getTimeStamp();
	$sql="INSERT INTO actionlog (PlayerID,Description,ActionResult,Global,TimeStamp) VALUES ('$PID','$DESC','$RESULT','$GLO','$date')";
	mysqli_query($conn, $sql);
}
function AddtoChat($PID,$MSG,$GLO,$TPID){
	$conn=conDB();
	$date=getTimeStamp();
	$sql="INSERT INTO chat (PlayerID,Message,TimeStamp,Global,TargetPID) VALUES ('$PID','$MSG','$date','$GLO','$TPID')";
	mysqli_query($conn, $sql);
}
function TakeAction($PID,$AID){
	$info=getActivityinfo($AID);
	$success=performAct($PID,$info[3],$info[2],$info[1]);
	if($success==true){
		AddtoLog($PID,$info[0],0,1);
		$RID=selectReward($AID);
		$ridArray=getRewardinfo($RID);
		$IID=$ridArray[0];
		$QUANT=$ridArray[1];
		$XPID=$ridArray[2];
		grantXP($PID,$XPID,$info[3]);
		grantItem($PID,$IID,$QUANT);
	}
	else if($success==false){
		AddtoLog($PID,"You were unsuccesful.",0,0);
	}
}
function MovePlayer($PID,$RID){
	$LID=getlocation($PID);
	$LID2=getDestination($LID,$RID);
	setPlayerLoc($PID,$LID2);
	$name=getLocationName($LID2);
	AddtoLog($PID,"You traveled to $name!",0,1);
}
function setPlayerLoc($PID,$LID){
	$conn=conDB();
	$sql="UPDATE players SET LocationID='$LID' WHERE IDKey='$PID'";
	mysqli_query($conn, $sql);
}
//Game Development Related functions
//Creates a new item definition in the database
//Game Content creation functions
function createItem($Name,$Desc,$SKID,$MHP,$CATID,$STACK){
	$conn=conDB();
	$sql="INSERT INTO items(Name,Description,SkillID,MaxHP,CategoryID,Stackable) VALUES ('$Name','$Desc','$SKID','$MHP','$CATID','$STACK')";
	mysqli_query($conn, $sql);
}




//Subfunctions these are minor functions
//$conn=condDB(); must be called prior to making a request of the Database, its return should be stored in $conn and should not be passed
//Connects to the SQL database
function conDB(){
	$conn = new mysqli($_SESSION["servername"], $_SESSION["Dusername"], $_SESSION["Dpassword"],$_SESSION["dbname"]);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}
//Returns the players current Level in a skill
function lookupLVL($PID,$SKID){
	$conn=conDB();
	$sql="SELECT LVL FROM skills WHERE SkillID='$SKID' AND PlayerID='$PID'";
	$result = mysqli_query($conn, $sql);
	$lvl=0;
	if (mysqli_num_rows($result) ==1) {
		$row= $result->fetch_assoc();
		$lvl=$row["LVL"];
	}
	return $lvl;
}
//Returns the players current XP in a skill
function lookupXP($PID,$SKID){
	$conn=conDB();
	$sql="SELECT XP FROM skills WHERE SkillID='$SKID' AND PlayerID='$PID'";
	$result = mysqli_query($conn, $sql);
	$XP=0;
	if (mysqli_num_rows($result) ==1) {
		$row= $result->fetch_assoc();
		$XP=$row["XP"];
	}
	return $XP;
}
//Checks wether a player has leveled up and returns the current XP and LVL, these are modified to the new values if a level is gained!
function checkLVLUP($XP,$LVL){
	$XPneeded=getXPNeeded($LVL);
	if($XP>=$XPneeded){
		$LVL++;
		$XP=$XP-$XPneeded;
	}
	return array($XP,$LVL);
}
function getXPNeeded($LVL){
	$conn=conDB();
	$sql="SELECT XPneeded FROM tolvl WHERE LVL='$LVL'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	return $row["XPneeded"];
}
//Shows a banner congratulating the player on leveling up!
//Returns the current strength of the layers skill boost for the request skill
function lookupBoost($PID,$SKID){
	$conn=conDB();
	$sql="SELECT BoostSTR,BoostNUM FROM skills WHERE PlayerID='$PID' AND SkillID='$SKID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	if($row["BoostNUM"]>0){//if there are boost charges available
		$bnum=$row["BoostNUM"]-1;//remove 1 boost
		$sql2="UPDATE skills SET BoostNUM='$bnum' WHERE PlayerID='$PID' AND SkillID='$SKID'";//submit to database
		mysqli_query($conn, $sql2);
		return $row["BoostSTR"];//return the STR of the boost
	}
	else{
		return 0;
	}
}
//returns the total quantity of an item IID in a players PID bag
function checkBag($PID,$IID){
	$conn=conDB();
	$sql="SELECT Quantity FROM bag WHERE ItemID='$IID' AND PlayerID='$PID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	if($row["Quantity"]>0){
		return $row["Quantity"];
	}
	return 0;
}
//Returns the number of remaining slots in a players inventory
function getIID($BID){
	$conn=conDB();
	$sql="SELECT ItemID FROM bag WHERE IDKey='$BID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	return $row["ItemID"];
}
//Check How many bag slots remain for the seleted player
function checkBagSpace($PID){
	$conn=conDB();
	$sql="SELECT BagSpace FROM players WHERE IDKey='$PID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	$sql2="SELECT COUNT(*) FROM bag WHERE PlayerID='$PID'";
	$result2 = mysqli_query($conn, $sql2);
	$row2= $result2->fetch_assoc();
	$Maxslots=$row["BagSpace"];
	$currentslots=$row2["COUNT(*)"];
	$slots=$Maxslots-$currentslots;
	return $slots;
}
//Returns whether an item is stackable or not
function checkStackable($IID){
	$conn=conDB();
	$sql="SELECT Stackable FROM items WHERE IDKey='$IID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	return $row["Stackable"];
}
//Check wether a recipe exists for the 2 items selected
function checkRecipe($IID,$IID2){
	$conn=conDB();
	$sql="SELECT IDKey FROM craftrecipe WHERE (ItemID1='$IID' AND ItemID2='$IID2') OR (ItemID2='$IID' AND ItemID1='$IID2') ";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	if($row["IDKey"]>0){
		return $row["IDKey"];
	}
	else{
		return 0;
	}
}
//Determine what bag slot is currently occupied by an item, will use the first item int he bag first
function findBagSlot($PID,$IID){
	$conn=conDB();
	$sql="SELECT IDKey FROM bag WHERE PlayerID='$PID' AND ItemID='$IID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	return $row["IDKey"];
}
//Find the Maximum Health of an item defintion

//Check what item if any is active in the players bag
function checkActive($PID){
	$conn=conDB();
	$sql="SELECT IDKey FROM bag WHERE PlayerID='$PID' AND Active='1'";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) ==1) {
		$row= $result->fetch_assoc();
		return $row["IDKey"];
	}
	return 0;
}
//Remove the active tag from an item so that a new active item may be selected
function deactivateItem(){
	$conn=conDB();
	$sql="UPDATE bag SET Active='0' WHERE PlayerID='$PID'";
	mysqli_query($conn, $sql);
}
//Confirm a players current Location

//Log a user into the system
function connectUser($user,$pass){
	$conn=conDB();
	$sql="SELECT * FROM players WHERE Username='$user' AND Password='$pass'";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) ==1) {
		$row= $result->fetch_assoc();
		return $row["IDKey"];
	}
	return 0;
}
//Find the Nodes available from a Location

//Check wether an attempt to perform an Activity succeeds
function performAct($PID,$SKID,$DIFF,$TLVL){
	$lvl=lookupLVL($PID,$SKID);
	$boost=lookupBoost($PID,$SKID);
	if($lvl>=$TLVL){
		$fdiff=$DIFF-$lvl;
		$RANDOM=rand($boost,100);
		if($RANDOM>$fdiff){
			return true;
		}
	}
	return false;
}
//Find all items in player Bag

function selectReward($AID){
	$conn=conDB();
	$sqlsum="SELECT Sum(Weight) AS Tweight FROM rewards WHERE ActID='$AID'";
	$resultsum = mysqli_query($conn, $sqlsum);
	$rowsum= $resultsum->fetch_assoc();
	$Tweight=$rowsum["Tweight"]-1;
	$sql="SELECT Weight,IDKey FROM rewards WHERE ActID='$AID'";
	$result = mysqli_query($conn, $sql);
	$weights=array();
	$x=0;
	while($row= $result->fetch_assoc()){
		$weight=$row["Weight"];
		$RID=$row["IDKey"];
		for($y=0;$y<$weight+$x;$y++){
			$weights[$y+$x]=$RID;
		}
			$x=$x+$weight;
	}
	$rewardN=rand(0,$Tweight);
	return $weights[$rewardN];
}

//GETS
function getRewardinfo($RID){
	$conn=conDB();
	$sql="SELECT * FROM rewards WHERE IDKey='$RID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	$return=array();
	$IID=$row["ItemID"];
	$Quant=$row["Quant"];
	$xp=$row["XPID"];
	$return[0]=$IID;
	$return[1]=$Quant;
	$return[2]=$xp;
	return $return;
}
function getInventory($PID){
	$conn=conDB();
	$sql="SELECT items.Name,items.IDKey,bag.Quantity,items.Description,bag.Active,bag.Health FROM bag INNER JOIN items ON bag.ItemID=items.IDKey WHERE bag.PlayerID='$PID'";
	$x=0;
	$result = mysqli_query($conn, $sql);
	$return=array();
	while($row= $result->fetch_assoc()){
		$name=$row["Name"];
		$quant=$row["Quantity"];
		$IID=$row["IDKey"];
		$HP=$row["Health"];
		$desc=$row["Description"];
		$active=$row["Active"];
		$return[$x]=$name;
		$x++;
		$return[$x]=$quant;
		$x++;
		$return[$x]=$IID;
		$x++;
		$return[$x]=$HP;
		$x++;
		$return[$x]=$desc;
		$x++;
		$return[$x]=$active;
		$x++;
	}
	return $return;
}
function getActionLog($PID){
	$conn=conDB();
	$sql="SELECT * FROM actionlog WHERE PlayerID='$PID' OR Global='1' ORDER BY IDKey DESC LIMIT 100";
	$result = mysqli_query($conn, $sql);
	$return=array();
	$x=0;
	while($row= $result->fetch_assoc()){
		$desc=$row["Description"];
		$time=$row["TimeStamp"];
		$final="$time:$desc";
		$return[$x]=$final;
		$x++;
	}
	return $return;
}
function getChatLog($PID){
	$conn=conDB();
	$sql="SELECT players.CharName,chat.Message,chat.TimeStamp FROM chat INNER JOIN players ON chat.PlayerID=players.IDKey WHERE chat.Global='1' OR chat.TargetPID='$PID' ORDER BY chat.IDKey DESC LIMIT 100";
	$result = mysqli_query($conn, $sql);
	$return=array();
	$x=0;
	while($row= $result->fetch_assoc()){
		$message=$row["Message"];
		$player=$row["CharName"];
		$time=$row["TimeStamp"];
		$final="$time:$player:$message";
		$return[$x]=$final;
		$x++;
	}
	return $return;
}
function getTimeStamp(){
	$date= date("H:i:s");
	return $date;
}
function getSkillName($SKID){
	$conn=conDB();
	$sql="SELECT Name FROM skillslist WHERE IDKey='$SKID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	return $row["Name"];
}
function getItemName($IID){
	$conn=conDB();
	$sql="SELECT Name FROM items WHERE IDKey='$IID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	return $row["Name"];
}
function getActivityinfo($AID){
	$conn=conDB();
	$sql="SELECT * FROM activities WHERE IDKey='$AID'";
	$result = mysqli_query($conn, $sql);
	$return=array();
	$row= $result->fetch_assoc();
	$return[0]=$row["Description"];
	$return[1]=$row["LVL"];
	$return[2]=$row["DC"];
	$return[3]=$row["SkillID"];
	return $return;
}
function getNodes($LID,$PID){
	$conn=conDB();
	$sql="SELECT * FROM nodes WHERE LocID='$LID'";
	$x=0;
	$result = mysqli_query($conn, $sql);
	while($row= $result->fetch_assoc()){
		$name= $row["Name"];
		$desc=$row["Description"];
		$NID=$row["IDKey"];
		$return[$x]=$name;
		$x++;
		$return[$x]=$desc;
		$x++;
		$return[$x]=$NID;
		$x++;
	}
	return $return;
}
//Find the activities that are available through a Node
function getActivities($NID,$PID){
	$conn=conDB();
	$sql="SELECT * FROM activities WHERE NodeID='$NID'";
	$x=0;
	$result = mysqli_query($conn, $sql);
	$return=array();
	while($row= $result->fetch_assoc()){
		$SKID=$row["SkillID"];
		$lvl=lookupLVL($PID,$SKID);
		if($lvl>=$row["LVL"]){
			$name= $row["Name"];
			$desc=$row["Description"];
			$AID=$row["IDKey"];
			$return[$x]=$name;
			$x++;
			$return[$x]=$desc;
			$x++;
			$return[$x]=$AID;
			$x++;
		}
	}
	return $return;
}
function getlocation($PID){
	$conn=conDB();
	$sql="SELECT LocationID FROM players WHERE IDKey='$PID'";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) ==1) {
		$row= $result->fetch_assoc();
		return $row["LocationID"];
	}
	return 1;
}
function getItemMaxHP($IID){
	$conn=conDB();
	$sql="SELECT MaxHP FROM items WHERE IdKey='$IID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	return $row["MaxHP"];
}
function getXPScale($XPID,$LVL){
	$conn=conDB();
	$sql="SELECT * FROM xpscales WHERE IDKey='$XPID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	$scalelvl=$row["LVL"];
	$BXP=$row["BaseXP"];
	$XPran=$row["XPRan"];
	$lvlDIF=$LVL-$scalelvl;
	$fXP=$BXP+rand(1,$XPran);
	if($lvlDIF>0){
		$x=$lvlDIF*5;
		if($x>50){
			$x=50;
		}
		$x2=100-$x;
		$x3=$x2/100;
		$x4=$fXP*$x3;
		$fXP=$fXP-$x4;
	}
	if($fXP<1){
		$fXP=1;
	}
	return $fXP;
}
function getRoutes($LID){
	$conn=conDB();
	$sql="SELECT * FROM routes WHERE Loc1='$LID' OR Loc2='$LID'";
	$x=0;
	$result = mysqli_query($conn, $sql);
	$return=array();
	while($row= $result->fetch_assoc()){
		$return[$x]=$row["IDKey"];
		$x++;
		$return[$x]=$row["Name"];
		$x++;
		$return[$x]=$row["Description"];
		$x++;
		$l1=$row["Loc1"];
		$l2=$row["Loc2"];
		if($LID==$l1){
			$return[$x]=$l1;
		}
		else{
			$return[$x]=$l2;
		}
		$x++;
	}
	return $return;
}
function getSkillInfo($PID){
	$conn=conDB();
	$sql="SELECT * FROM skills WHERE PlayerID='$PID'";
	$x=0;
	$result = mysqli_query($conn, $sql);
	$return=array();
	while($row= $result->fetch_assoc()){
		$SKID=$row["SkillID"];
		$lvl=$row["LVL"];
		$xp=$row["XP"];
		$Bstr=$row["BoostSTR"];
		$Bnum=$row["BoostNUM"];
		$desc=getSkillDesc($SKID);
		$skillName=getSkillName($SKID);
		$return[$x]=$lvl;
		$x++;
		$return[$x]=$xp;
		$x++;
		$return[$x]=$Bstr;
		$x++;
		$return[$x]=$Bnum;
		$x++;
		$return[$x]=$skillName;
		$x++;
		$return[$x]=$desc;
		$x++;
	}
	return $return;
}
function getSkillDesc($SKID){
	$conn=conDB();
	$sql="SELECT Description FROM skillslist WHERE IDKey='$SKID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	return $row["Description"];
}
function getDestination($LID,$RID){
	$conn=conDB();
	$sql="SELECT * FROM routes WHERE IDKey='$RID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	$loc1=$row["Loc1"];
	$loc2=$row["Loc2"];
	$int=$row["Intersect"];
	if($int>0){
		$sql2="SELECT * FROM routes WHERE IDKey='$int'";
		$result2 = mysqli_query($conn, $sql2);
		$row2= $result2->fetch_assoc();
		$loc3=$row2["Loc1"];
		$loc4=$row2["Loc2"];
	}
	$locations=array();
	$x=0;
	if($loc1!=$LID){
		while($x<30){
			$locations[$x]=$loc1;
			$x=$x+1;
		}
	}
	if($loc2!=$LID){
	while($x<30){
			$locations[$x]=$loc2;
			$x=$x+1;
		}
	}
	if($int>0){
		if($loc3!=$LID){
			$locations[$x]=$loc3;
			$x++;
		}
		if($loc4!=$LID){
			$locations[$x]=$loc4;
			$x++;
		}
	}
	$locN=count($locations);
	$y=rand(0,$locN);
	return $locations[$y];
}
function getLocationName($LID){
	$conn=conDB();
	$sql="SELECT Name FROM locations WHERE IDKey='$LID'";
	$result = mysqli_query($conn, $sql);
	$row= $result->fetch_assoc();
	$name=$row["Name"];
	return $name;
}
?>
