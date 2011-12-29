<?php
#####################################################
#	Ajax will load shit from here...
#
#		Author: NINJ4
#		Created 12/20
#		Last Edited: 
#####################################################
# INIT
require( "wipeX_config.php" );
//echo $_SERVER['REQUEST_URI'];
#if ( !isset( $_GET["data"] ) ) 
#	die();
#####################################################
# Functions

// logs $logString into the log table
function wipeX_Log( $logString, $mcConn ) {
	$sql =	"INSERT INTO `permissions_wipex` 
					( `user`, `action` )
			VALUES	( '". $GLOBALS['wipeX_User'] ."' , '". $logString ."' )";
	mysql_query( $sql, $mcConn );
}

//changes the undo status of $undoID's log entry to Username
function wipeX_LogUndo( $undoID, $mcConn ) {
	$sql =	"UPDATE `permissions_wipex` 
			 SET `undone`='". $GLOBALS['wipeX_User'] ."' 
			 WHERE `id` = '". $undoID ."'";
	mysql_query( $sql, $mcConn );
}
function recurse_Perms ( $group, $key, $mcConn ) {
	echo "oops";
}
#####################################################
# The true script part

if ( $_GET["data"] == "groups" ) {
	$groups_ary = loadGroups( $mcConn );
	foreach ( $groups_ary as $group_ary ) {
		echo "<option class='group' value='". $group_ary['name'] ."'"
				. ( ( $group_ary['default'] ) ? " style='background-color:yellow;'>Default Group: " : ">" )
				. $group_ary['name'] ."</option>\n";
	}
}
else if ( $_GET["data"] == "getlogs" ) {	
# Get logs:
$logStart = ( $_GET["start"] ) ? mysql_real_escape_string( $_GET["start"] ) : 0 ;
$sql = "SELECT *
		FROM `permissions_wipex`
		ORDER BY `time` DESC
		LIMIT ". $logStart ." , 50";
	$result = mysql_query( $sql, $mcConn );
	while( $row = mysql_fetch_array( $result ) ) {
			# For simplicity, if you need to add new items to this list, add at the beginning.
		$human	= array( "Added Parent(s) to ",	"Removed Group",	"Created new Group(s)",	"Added Permission(s) to ",	"Removed Permission(s) from ",	"Removed Member(s) from ",	"Added Member(s) to ",	"Set Rank for ",	"Group <u>",	"</u>: ",	"Set Rank-Ladder for " );
		$logged = array( "?do=addParent",		"?do=remGrp",		"?do=newGrp",				"?do=addPerm",				"?do=remPerm",					"?do=remMem",				"?do=addMem", 			"?do=setRank",	"&group=",			"&data=",	"?do=setLadder" );
		$Haction = str_replace( $logged, $human, $row['action'] );
		
		echo "<tr id='logRow". $row['id'] ."'>";
			echo "<td>". $row['time'] ."</td>";
			echo "<td><b>". $row['user'] ."</b></td>";
			echo "<td id='Haction". $row['id'] ."'>". $Haction ."</td>";
			echo ( ( $row['id'] != 1 ) && ( strpos( $row['action'], "UNDID" ) !== 0 ) && ( !$row['undone'] ) ) ? "<td><input type='checkbox' class='undoThis' value='". $row['id'] ."' /></td>" : "";
		echo "</tr>";
	}
}
else if ( ( $_GET["data"] == "permissions" ) && ( isset( $_GET["group"] ) ) ) {
	if ( strpos( $_GET["group"], "," ) !== false ) {
		$style = "style='background-color:#000000; color:#ffffff'";
		die( "	<option ". $style ." value='-1'>Only select one Group.</option>\n" );
	}
	$i = 0;
	$groups[ $i ] = mysql_real_escape_string( $_GET["group"] );
	if ( $_GET['inherited'] ) {
		$sql 	= "SELECT 	*
					FROM 	`permissions_inheritance`
					WHERE	type = 0
					AND		child = '". current($groups) ."'";
		$result = mysql_query( $sql, $mcConn );
		if ( $result ) {
			while ( $row = mysql_fetch_array( $result ) ) {
				$i++;
				$groups[ $i ] = $row['parent'];
			}
		}
		while ( next( $groups ) ) {
			$sql 	= "SELECT 	*
						FROM 	`permissions_inheritance`
						WHERE	type = 0
						AND		child = '". current($groups) ."'";
			$result = mysql_query( $sql, $mcConn );
			if ( $result ) {
				while ( $row = mysql_fetch_array( $result ) ) {
					$i++;
					$groups[ $i ] = (string) $row['parent'];
				}
			}
		}
		reset( $groups );
		//////////////////////////////////////////////////
	}
	$n = 1;
	$bgcolor = array(
						1	=>	"#AAAACC;",
						2	=>	"#AACCFF;",
						3	=>	"#FFAAFF;",
						4	=>	"#FFAA44;",
						5	=>	"#AAFFAA;",
						6	=>	"#929292;",
					//	7	=>	"#0000CC;",
					//	8	=>	"#0000CC;",
					);
	$i = 3;
	$flagRank = false;
	$flagLadder = false;
	foreach ( $groups as $key => $group ) {
			
		if ( $group == mysql_real_escape_string( $_GET["group"] ) ) {
			$sql 	= "SELECT 	*
						FROM 	`permissions_inheritance`
						WHERE	type = 0
						AND		child = '". $group ."'";
			$result = mysql_query( $sql, $mcConn );
			if ( mysql_num_rows( $result ) == 0 ) {
				$style = "style='background-color:#000000; color:#ffffff'";
				echo "	<option ". $style ." value='-1'>Inherits from: N/A</option>\n";
			}
			else {
				while ( $row = mysql_fetch_array( $result ) ) {
					$style = "style='background-color:rgb(0, 0, 204); color:#ffffff'";
					echo "	<option class='parent' ". $style ." value='". $row['parent'] ."'>Inherits from: ". $row['parent'] ."</option>\n";
				}
			}
		}
	
		$sql 	= "SELECT 	*
					FROM 	`permissions`
					WHERE	type = 0
					AND		name = '". $group ."'";
		$result = mysql_query( $sql, $mcConn );
	
		while ( $row = mysql_fetch_array( $result ) ) {
			$style = "";
			
			if ( $row['permission'] == "rank" ) {
				if ( $group == mysql_real_escape_string( $_GET["group"] ) ) {
					$flagRank = true;
					$style = "style='color: rgb(0, 0, 204);'";
					$slines[0] = "	<option ". $style ." value='-1'>Promotion Rank: ". $row['value'] ."</option>\n";
				}
			}
			else if ( $row['permission'] == "rank-ladder" ) {
				if ( $group == mysql_real_escape_string( $_GET["group"] ) ) {
					$flagLadder = true;
					$style = "style='color: rgb(0, 0, 204);'";
					$slines[1] = "	<option ". $style ."value='-1'>Rank-Ladder: ". $row['value'] ."</option>\n";
				}
			}
			else {
				$style = "style='";
				if ( strpos( $row['permission'] , "-" ) === 0 )
					$style .= "color:rgb(170, 0, 0); ";
				if ( $group != mysql_real_escape_string( $_GET["group"] ) ) {
						
					$style .= "background-color:". $bgcolor[ $n ];
					$bull = "";
					for ( $x = 0 ; $x < $key ; $x++ )
						$bull .= "&bull;&nbsp;";
					$row['permission'] = "$bull ". $row['permission'] ." (from $group)";
				}
				$style .= "'";
				
				$lines[ $i ] = "	<option class='permission' value='". $row['permission'] ."' ". $style .">". $row['permission'] ."</option>\n";
				$i++;
			}
		}
		if ( $group == mysql_real_escape_string( $_GET["group"] ) ) {
			if ( !$flagRank ) {
				$style = "style='background-color:#000000; color:#ffffff'";
				$slines[0] = "	<option ". $style ." value='-1'>Promotion Rank: N/A</option>\n";
			}
			if ( !$flagLadder ) {
				$style = "style='background-color:#000000; color:#ffffff'";
				$slines[1] = "	<option ". $style ." value='-1'>No Rank-Ladder Set</option>\n";
			}
			if ( mysql_num_rows( $result ) == 0 ) {
				$style = "style='background-color:#000000; color:#ffffff'";
				echo "	<option ". $style ." value='-1'>No Unique Permissions</option>\n";
			}
		}
		else {
			$n++;
			if ( $bgcolor[ $n ] == "" )
				$n = 1;
				//echo "**N=".$n;
		}
	}
	if ( !$_GET['inherited'] ) 
		sort( $lines );
	ksort( $slines );
	foreach ( $slines as $line )
//echo htmlspecialchars ($line)."<br />";
		echo $line;
	foreach ( $lines as $line )
//echo htmlspecialchars ($line)."<br />";
		echo $line;
		
}
else if ( ( $_GET["data"] == "members" ) && ( isset( $_GET["group"] ) ) ) {
	if ( strpos( $_GET["group"], "," ) !== false ) {
		$style = "style='background-color:#000000; color:#ffffff'";
		die( "	<option ". $style ." value='-1'>Only select one Group.</option>\n" );
	}
	$sql 	= "SELECT 	*
				FROM 	`permissions_inheritance`
				WHERE	type = 1
				AND		parent = '". $_GET["group"] ."'";
	$result = mysql_query( $sql, $mcConn );
	if ( mysql_num_rows( $result ) == 0 ) {
		$style = "style='background-color:#000000; color:#ffffff'";
		echo "	<option ". $style ." value='-1'>Group Contains No Members!</option>\n";
	}
	else {
		$style = "";
		while ( $row = mysql_fetch_array( $result ) ) {
			$lines[ $row['child'] ] = "	<option ". $style ." value='". $row['child'] ."'>". $row['child'] ."</option>\n";
		}
		ksort( $lines );
		foreach ( $lines as $line )
			echo $line;
	}
}
else if ( isset($_GET['queryNum'] ) ) {
	if ( isset( $_GET['do'] ) ) {
		if ( !$_GET['data'] )
			die( "false,". $_GET['queryNum'] .",Missing data in GET request! (No data field)" );
		else if ( ( ( $_GET['do'] == "setRank" ) || ( $_GET['do'] == "setLadder" ) ) && ( $_GET['group'] ) ) {
		
			if ( $_GET['do'] == "setRank" )
				$permission = "rank";
			if ( $_GET['do'] == "setLadder" )
				$permission = "rank-ladder";
				
			$groups = explode(',', $_GET['group'] );
			foreach ( $groups as $group ) {
				$sql = "SELECT id
						FROM `permissions`
						WHERE `name`='". mysql_real_escape_string( $group ) ."'
						AND `permission`='". $permission ."'";
				$result	=	mysql_query( $sql, $mcConn );
			
				if ( mysql_num_rows( $result ) == 0 ) {
					if ( !$sqlINSERT )
						$sqlINSERT = "INSERT INTO `permissions` 
										( `name`, `permission`, `value` ) VALUES 
										( '". mysql_real_escape_string( $group ) ."', '"
										. $permission ."', '". mysql_real_escape_string( $_GET['data'] ) ."' ) ";
					else				
						$sqlINSERT .= ", ( '". mysql_real_escape_string( $group ) ."', '"
										. $permission ."', '". mysql_real_escape_string( $_GET['data'] ) ."' ) ";
				}
				else {
					$row = mysql_fetch_array( $result );
					if ( !$sqlUPDATE )
						$sqlUPDATE =	"UPDATE `permissions` SET `value`='". mysql_real_escape_string( $_GET['data'] ) ."' 
								 		 WHERE ( `permission` = '". $permission ."'
								 		 AND	`id`=". $row['id'] ." ) ";
					else
						$sqlUPDATE .=	"OR ( `permission` = '". $permission ."'
								 		 AND	`id`=". $row['id'] ." ) ";
				}
			}
			if ( $sqlUPDATE ) {
				$result	=	mysql_query( $sqlUPDATE, $mcConn );
				if ( !$result )
					echo "false,". $_GET['queryNum'] .",". mysql_error() ." - ". $sqlUPDATE ." - ". $_GET['data'] ." - ". $group;
			}
			if ( $sqlINSERT ) {
				$result	=	mysql_query( $sqlINSERT, $mcConn );
				if ( !$result )
					echo "false,". $_GET['queryNum'] .",". mysql_error() ." - ". $sqlINSERT ." - ". $_GET['data'] ." - ". $group;
			}
			
			echo "true,". $_GET['queryNum'];
				
			if ( !$_GET['isUndo'] )
				wipeX_Log(	"?do=". mysql_real_escape_string( $_GET['do'] ) 
							. ( ( $_GET['group'] ) ? "&group=". mysql_real_escape_string( $_GET['group'] ) : "" )
							. "&data=". mysql_real_escape_string( $_GET['data'] ), $mcConn );
			else
				wipeX_LogUndo( mysql_real_escape_string( $_GET['isUndo'] ), $mcConn );
			
		}
		else if ( ( $_GET['do'] == "remMem" ) || ( $_GET['do'] == "remPerm" ) || ( $_GET['do'] == "remGrp" ) ) {
			if 		( ( ( $_GET['do'] == "remMem" ) 
					|| ( $_GET['do'] == "remPerm" ) ) 
					&& ( !$_GET['group'] ) )
				die( "false,". $_GET['queryNum'] .",Missing data in GET request! (No group provided)" );
			$members = explode(',', $_GET['data'] );
			$groups = explode(',', $_GET['group'] );
			if ( $_GET['do'] == "remMem" )
				$sql = "DELETE FROM `permissions_inheritance` WHERE ";
			else if ( $_GET['do'] == "remPerm" )
				$sql = "DELETE FROM `permissions` WHERE ";
			else if ( $_GET['do'] == "remGrp" ) { //delete EVERYTHING about this group.  No loose ends.
				$sql = "DELETE FROM `permissions_entity` WHERE ";
				$sql1 = "DELETE FROM `permissions_inheritance` WHERE ";
				$sql2 = "DELETE FROM `permissions` WHERE ";
			}
			
			foreach ( $members as $member ) {
				foreach ( $groups as $group ) {
					if ( $_GET['do'] == "remMem" )
						$sql	.= "( `parent`='". mysql_real_escape_string( $group ) 
								."' AND `child`='". mysql_real_escape_string( $member ) ."' ) ";
					else if ( $_GET['do'] == "remPerm" )
						$sql	.= "( `name`='". mysql_real_escape_string( $group ) 
								."' AND `permission`='". mysql_real_escape_string( $member ) ."' ) ";
					else if ( $_GET['do'] == "remGrp" ) {
						$member = mysql_real_escape_string( $member );
						$sql	.= "( `name`='". $member ."' AND `type`=0 ) ";
						$sql1	.= "( `child`='$member' ) OR ( `parent`='$member' ) ";
						$sql2	.= "( `name`='$member' ) ";
					}
				}
			}
			$sql = str_replace( ") (", ") OR (", $sql );
			if ( $sql1 != null ) {
//echo"hello";
				$sql1 = str_replace( ") (", ") OR (", $sql1 );
				$sql2 = str_replace( ") (", ") OR (", $sql2 );
				
				$result1	=	mysql_query( $sql1, $mcConn );
				if ( !$result1 )
					die( "false,". $_GET['queryNum'] .",". mysql_error() ." - ". $sql ." - ". $_GET['data'] ." - ". $_GET['group'] );
					
				$result2	=	mysql_query( $sql2, $mcConn );
				if ( !$result2 )
					die( "false,". $_GET['queryNum'] .",". mysql_error() ." - ". $sql ." - ". $_GET['data'] ." - ". $_GET['group'] );
			}
			$result	=	mysql_query( $sql, $mcConn );
			if ( !$result )
				echo "false,". $_GET['queryNum'] .",". mysql_error() ." - ". $sql ." - ". $_GET['data'] ." - ". $_GET['group'];
				
			else {
				echo "true,". $_GET['queryNum'];
				
				if ( !$_GET['isUndo'] )
					wipeX_Log(	"?do=". mysql_real_escape_string( $_GET['do'] ) 
								. ( ( $_GET['group'] ) ? "&group=". mysql_real_escape_string( $_GET['group'] ) : "" )
								. "&data=". mysql_real_escape_string( $_GET['data'] ), $mcConn );
								
				else
					wipeX_LogUndo( mysql_real_escape_string( $_GET['isUndo'] ), $mcConn );
			}
		}			
		else if ( ( $_GET['do'] == "addMem" ) || ( $_GET['do'] == "addPerm" ) || ( $_GET['do'] == "addParent" ) || ( $_GET['do'] == "newGrp" ) ) {
			if		( ( ( $_GET['do'] == "addMem" ) 
					|| ( $_GET['do'] == "addPerm" ) 
					|| ( $_GET['do'] == "addParent" ) ) 
					&& ( !$_GET['group'] ) )
				die( "false,". $_GET['queryNum'] .",Missing data in GET request! (No group provided)" );
			$members = explode(',', $_GET['data'] );
			$groups = explode(',', $_GET['group'] );
			if ( $_GET['do'] == "addMem" ) {
				$sql = "INSERT INTO `permissions_inheritance` ( `child`, `parent`, `type` ) VALUES ";
				$type = 1;
			}
			else if ( $_GET['do'] == "addParent" ) {
				$sql = "INSERT INTO `permissions_inheritance` ( `parent`, `child`, `type` ) VALUES ";
				$type = 0;
			}
			else if ( $_GET['do'] == "newGrp" ) 
				$sql = "INSERT INTO `permissions_entity` ( `name`, `type` ) VALUES ";
			else if ( $_GET['do'] == "addPerm" )
				$sql = "INSERT INTO `permissions` ( `name`, `permission` ) VALUES ";
			
			foreach ( $members as $member ) {
				foreach ( $groups as $group ) {
					if ( ( $_GET['do'] == "addMem" ) || ( $_GET['do'] == "addParent" ) )
						$sql	.= "( '".	mysql_real_escape_string( $member ) ."', '"
								. mysql_real_escape_string( $group ) ."', ". $type ." ) ";
					else if ( $_GET['do'] == "addPerm" )
						$sql .= "( '".	mysql_real_escape_string( $group ) ."', '". mysql_real_escape_string( $member ) ."' ) ";
				}
				if ( $_GET['do'] == "newGrp" ) 
					$sql .= "( '". mysql_real_escape_string( trim( $member ) ) ."', '0' ) ";
			}
			$sql = str_replace( ") (", "), (", $sql );
			$result	=	mysql_query( $sql, $mcConn );
			if ( !$result )
				echo "false,". $_GET['queryNum'] .",". $sql ." - ". $_GET['data'] ." - ". $_GET['group']  ." - ". mysql_error();
			else {
				echo "true,". $_GET['queryNum'];
				
				if ( !$_GET['isUndo'] )
					wipeX_Log(	"?do=". mysql_real_escape_string( $_GET['do'] ) 
								. ( ( $_GET['group'] ) ? "&group=". mysql_real_escape_string( $_GET['group'] ) : "" )
								. "&data=". mysql_real_escape_string( $_GET['data'] ), $mcConn );
				else
					wipeX_LogUndo( mysql_real_escape_string( $_GET['isUndo'] ), $mcConn );
			}
		}
		else if ( ( $_GET['do'] == "undo" ) && ( $_GET['data'] ) ){
			$undoID = mysql_real_escape_string( $_GET['data'] );
			
			$sql = "SELECT action
					FROM `permissions_wipex`
					WHERE id=". $undoID;
			$result = mysql_query( $sql, $mcConn );
			$row = mysql_fetch_array( $result );
			$undoAction = $row['action'];
			if ( strpos( $row['action'], "UNDID" ) === 0 )
				die( "false,". $_GET['queryNum'] .",Cannot undo an undo event!" );
			//except for setladder, setrank
			//if ( strpos( $row['action'], "setRank" ) !== false ) {
			if ( ( strpos( $row['action'], "setRank" ) !== false ) || ( strpos( $row['action'], "setLadder" ) !== false ) ) {
				
				$command = ( ( strpos( $row['action'], "setRank" ) !== false ) ? "setRank" : "setLadder" );
				//echo substr('abcdef', 1, 3);  // bcd
				$group = substr( $row['action'], (	strpos( $row['action'], "group=" ) + 6  ) );
				$group = substr( $group, 0, strpos( $group, "&data" ) );
				
				//first, make sure this would have any effect by searching for newer events
				$sql = "SELECT *
						FROM `permissions_wipex`
						WHERE `action` LIKE '%$command%". $group ."%'
						ORDER BY `id` DESC
						LIMIT 0 , 1";
				$result = mysql_query( $sql, $mcConn );
				$row = mysql_fetch_array( $result );
				
				if ( $row['undone'] )
					die( "false,". $_GET['queryNum'] .",This event had already been undone!" );
				if ( $row['id'] > $undoID )
					die( "false,". $_GET['queryNum'] .",Undoing this event would have no effect (newer events have changed the value)!" );
					
				//do an actual query looking with `id`<undoID
				$sql = "SELECT *
						FROM `permissions_wipex`
						WHERE `action` LIKE '%$command%". $group ."%'
						  AND `id` < $undoID
						ORDER BY `id` DESC
						LIMIT 0 , 1";
				$result = mysql_query( $sql, $mcConn );
				if ( !$result ) 
					die( "false,". $_GET['queryNum'] .",No earlier value was set!" );
				$row = mysql_fetch_array( $result );
				$newVal = substr( $row['action'], ( 5 + strpos( $row['action'], "data=" ) ) );
				
				$permission = ( ( $command == "setRank" ) ? "rank" : "rank-ladder" );
				//*/update the entry for the permission
				$sql =	"UPDATE `permissions` 
						 SET `value`='". $newVal ."' 
						 WHERE `permission` = '". $permission ."'
						 AND	`name`= '$group'";
				$result = mysql_query( $sql, $mcConn );
				if ( !$result )
					die( "false,". $_GET['queryNum'] .",SQL update found no entry for $group's $permission. Was it removed in console?" );//*/
				else
					echo "true,". $_GET['queryNum'] .",$noticeMsg";
				
				//update the undone log
				wipeX_LogUndo( $undoID, $mcConn );
				
				//add log entry for undo
				wipeX_Log( "UNDID: <i>$undoAction</i>", $mcConn );
			}
			else {
				if ( $row['undone'] )
					die( "false,". $_GET['queryNum'] .",This even has already been undone!" );
					
					// The "+" in the replace array is because it will replace IN ORDER, 
					//	replacing newGrp->remGrp and then remGrp->newGrp
				$replace	= array( 	"?do=remParent+", "?do=newGrp+", "?do=remGrp+", "?do=remPerm+", "?do=addPerm+", 
										"?do=addMem+", "?do=remMem+" );
				$found		= array(	"?do=addParent&", "?do=remGrp&", "?do=newGrp&", "?do=addPerm&", "?do=remPerm&", 
										"?do=remMem&", "?do=addMem&" );
				$reversedCMD = str_replace( $found, $replace, $row['action'] );
				echo "retry,". $_GET['queryNum'] .",". $reversedCMD ."&isUndo=$undoID";
				
				wipeX_Log( "UNDID: <i>". $row['action'] ."</i>", $mcConn );
			}
		}
		else {
			$flag=true;
			echo "false,". $_GET['queryNum'] .",Unknown command!";
		}
		/*if (!$flag) {
			//wipeX_Log( "?". implode ( "&" , $_GET ) );
		}*/
	}
	//	echo "false,". $_GET['queryNum'] .",Some error message!";
	else
		echo "false,". $_GET['queryNum'] .",Command not set!";
}
else
echo "false,0,HELLO";
?>
