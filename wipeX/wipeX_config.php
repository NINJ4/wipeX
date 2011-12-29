<?php
#####################################################
#	Configuration file for PEX Web Interface
#		Before using, PLEASE set all the variables
#		found in the Config Section, or else things will not
#		work correctly!
#
#	You only need to configure the security section
#		that you are using!
#
#		Author: NINJ4
#
#####################################################
#	Config begin

define("SORT_BY_LADDER",FALSE);						//Change to "TRUE" if you want to sort by rank-ladder and rank in the group list,
													//	instead of just rank.

	#Security Section:
define("SECURITY_MODULE", "simple-security");		//Insert the name of one of the security modules here.
													//The options are: no-security, simple-security, phpbb-standalone, phpbb-embedded
		#Simple Security Config:
	define("SIMPLE_PASSWORD", "DEFAULT");			//The password to use for Simple Security module. 
													//	CHANGE THIS IF USING SIMPLE SECURITY.
	
		#PHPBB Standalone Config:
	define("PHPBB_REL_PATH", "./");					//The directory where phpbb is located, relative to this directory.
	define("STANDALONE_GROUPS", "4,5" );			//The phpbb groups that are allowed to access WIPEX
													//	(default is Administrators only)
	
													

	#Connection Section:
define("MCMYSQL_SERVER", 'localhost');						// Server for database
define("MCMYSQL_USER", 'minecraft-user');					// Username for database
define("MCMYSQL_PASS", 'password');						// Password for database
define("MCMYSQL_DB", 'minecraft-db');						// Database	name

#	Config end
#####################################################
#	Common Constants:

define( "DOC_ROOT", $_SERVER['DOCUMENT_ROOT'] );

#	Constants end
#####################################################
#	Common Code:
		# Functions
require( "security-modules/". SECURITY_MODULE .".php" );  //security!
if ( !wipex_check_security() )
	die( "Security Error." );
	

function mcConnect() {
	// Connect
	if ( $connection = mysql_connect( MCMYSQL_SERVER, MCMYSQL_USER, MCMYSQL_PASS ) ) {
		if ( mysql_select_db( MCMYSQL_DB ) )
			return $connection;
		else
			return false;
	}
	else {
		return false;
	}
}
function loadGroups( $mcConn ) {
	# First get all the groups available to us, and assign basic data.
	$sql 	= "SELECT *
				FROM `permissions_entity`
				WHERE type = 0";
	$result = mysql_query( $sql, $mcConn );
	//echo mysql_error();
	//$perms_ary = 0;

	while( $row = mysql_fetch_array( $result ) ) {
		$name = $row['name'];
		$isDefault = $row['default'];
		
			//get rank
		$sql =	"SELECT *
				FROM `permissions`
				WHERE 	`permission` = 'rank'
				AND		`name`='$name'";
		$rankResult = mysql_query( $sql, $mcConn );
		if ( $rowRank = mysql_fetch_array( $rankResult ) )
			$rank = $rowRank['value'];
		else
			$rank = 0;
			
			//get rank ladder
		if ( SORT_BY_LADDER ) {
			$sql =	"SELECT *
					FROM `permissions`
					WHERE 	`permission` = 'rank-ladder'
					AND		`name`='$name'";
			$ladderResult = mysql_query( $sql, $mcConn );
			if ( $rowLadder = mysql_fetch_array( $ladderResult ) )
				$ladder = $rowLadder['value'];
			else
				$ladder = "zzzz";
		}
		else 
			$ladder = "";
			
		$groups_ary[ $ladder.$rank.$name ]['name'] 		=	$name;
		$groups_ary[ $ladder.$rank.$name ]['rank'] 		=	$rank;
		$groups_ary[ $ladder.$rank.$name ]['default'] 	=	$isDefault;
		
	}
	krsort( $groups_ary );
	return $groups_ary;
}
function wipeX_Install( $mcConn = false ) {
	if ( !$mcConn )
		die();
	$sql	=	"CREATE TABLE `permissions_wipex` (
				 `id` int( 11 ) NOT NULL AUTO_INCREMENT ,
				 `time` timestamp NOT NULL default CURRENT_TIMESTAMP ,
				 `user` varchar( 64 ) NOT NULL default 'Unknown',
				 `action` varchar( 250 ) NOT NULL default 'Unknown action',
				 `undone` varchar( 64 ) NOT NULL default '0',
				 PRIMARY KEY ( `id` )
				 ) ENGINE = MYISAM DEFAULT CHARSET = latin1;";
	$result	=	mysql_query( $sql, $mcConn );
	if ( mysql_error() )
		echo "Error installing logs: ". mysql_error();
	else {
		$sql	= "INSERT INTO `permissions_wipex` 
					( `user`, `action` )
					VALUES ( '". $GLOBALS['wipeX_User'] ."' , 'Installed wipeX' )";
		$result	=	mysql_query( $sql, $mcConn );
		if ( mysql_error() )
			echo "Error installing logs: ". mysql_error();
		else
			return true;
	}
}
			# Common Script:
// Make sure we're installed :)
$mcConn = mcConnect();
if ( $mcConn ) {
	$sql 	= "SELECT *
				FROM `permissions_wipex`
				WHERE id = 1";
	$result = mysql_query( $sql, $mcConn );
	if ( !$result )
		wipeX_Install( $mcConn );
	else if ( mysql_num_rows( $result ) == 0 ) 
		wipeX_Install( $mcConn );
}
else
	die("mySQL Connection failure.")



#	End of File
#####################################################
?>
