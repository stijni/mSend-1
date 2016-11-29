<?php
/**
 * Simple database connection and query class.
 * Uses the information defined on sys.config.php.
 *
 * @package		ProjectSend
 * @subpackage	Classes
 *
 */
//require_once('sys.includes.php');
/* included aes class file by RJ-07-Oct-2016 */
include('aes_class.php');
/** Extension class to count the total of executed queries */
if ( DEBUG === true ) {
	class PDOEx extends PDO
	{
		private $queries = 0;
		
		public function query($query, $options = array()) {
			++$this->queries;
			return parent::query($query);
		}
	
		public function prepare($statement, $options = array()) {
			++$this->queries;
			return parent::prepare($statement);
		}
		
		public function GetCount() {
			return $this->queries;
		}
	}
}


/** Initiate the database connection */
global $dbh;
	$blockSize = 256;
	$inputKey = "project send encryption";
	$fileData1 = DB_PASSWORD;
    	$aes1 = new AES($fileData1, $inputKey, $blockSize);
    	$decryptedPassword = $aes1->decrypt();
	//echo $decryptedPassword;exit();
try {
	switch ( DB_DRIVER ) {
		default:
		case 'mysql':
			if ( DEBUG === true ) {
				$dbh = new PDOEx("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, $decryptedPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}
			else {
				$dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, $decryptedPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}
			break;

		case 'mssql':
			if ( DEBUG === true ) {
				$dbh = new PDOEx("mssql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, $decryptedPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}
			else {
				$dbh = new PDO("mssql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, $decryptedPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}
			break;
	}

	$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$dbh->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
}
catch(PDOException $e) {
/*
	print "Error!: " . $e->getMessage() . "<br/>";
	die();
*/
	return false;
}
?>
