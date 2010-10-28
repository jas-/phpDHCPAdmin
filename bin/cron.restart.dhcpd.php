<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * cron.restart.dhcpd.php - Handle crontab restarting of the ISC DHCPD service
 */

// load our config data
if( file_exists( '../scripts/inc.config.php' ) ) {
 require '../scripts/inc.config.php';

 global $defined;

 // open up some handles
	$db = new dbConn;
 $val = new ValidateStrings;
 $misc = new MiscFunctions;
 
 // attempt to get a list of networks the net adapter(s) are listening on
	$misc->GetAdapters();
	
	// process lease data
 $misc->GetCurrentLeases( $defined['leases'] );
	
 // look to see if we need to recreate the leases file
 $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
 $query = "SELECT `recreate` FROM `conf_leases_properties` WHERE `id` = \"1\"";
 if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === 0 ) {
  $data = $db->dbArrayResults( $value );
 }

 // do we recreate?
 if( $data[0]['recreate'] === "TRUE" ) {
  echo "LEASES: We are going to recreate the current " . $defined['leases'] . "file. Please wait...\n";
 }

 // Check for file that flags a restart
 if( file_exists( $defined['virpath'] . "conf/restart" ) ) {
  // make sure we have a configuration file to use
  if( file_exists( $defined['virpath'] . "conf/dhcpd.conf" ) ) {
   // use the $defined[dhcpd_cmd] var to restart the service with our config file
   system( '/usr/bin/killall dhcpd' );
   system( "rm " . $defined['virpath'] . "conf/restart" );
   system( $defined['dhcpd_cmd'] );
   echo "RESTART: The ISC DHCPD service has been restarted.\n";
  } else {
		 echo "ERROR: The dhcpd.conf file could not be located\n";
		}
		
 } else {
	 echo "ERROR: Could not find flag file necessary to restart the ISC DHCPD service\n";
	}
 
} else {
 echo "ERROR: Could not locate the necessary libraries and configuration settings to execute\n";
}

?>
