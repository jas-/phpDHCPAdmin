<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * config.replication.php - DHCPD Replication configuration
 */

// load our config data
if( file_exists( "scripts/inc.config.php" ) ) {
 require 'scripts/inc.config.php';

 // ensure we are being called from our configured host
 if( $defined['hostname'] === $_SERVER['SERVER_NAME'] ) {

  // Initialize classes
 	$db = new dbConn;
  $err = new GenerateErrors;
  $tpl = new Template;
  $skin = new PageSkinner;
  $val = new ValidateStrings;
  $menu = new GenerateNavMenu;
  $auth = new Authenticate;
  $level = new AccessLevels;
 	$misc = new MiscFunctions;
 	$debug = new DebugData;

  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

  // ensure our sessions are present
  if( empty( $_SESSION['token'] ) ) {
   $sessions = new dbSession;
  }

  //define the template and cache directories
  $tpl->strTemplateDir = $defined['virpath'] . 'templates';
  $tpl->strCacheDir    = '/tmp';
  if( ( !empty( $_GET ) ) || ( !empty( $_POST ) ) ) { $flag = "TRUE"; } else { $flag = "FALSE"; }

  // setup our template style data
  if( ( $val->ValidateString( $_GET['skin'] ) === -1 ) || ( empty( $_GET['skin'] ) ) ) {
   $style = $defined['templates'] . "/black";
   if( !empty( $_GET['skin'] ) ) {
    $skin_err = $err->GenerateErrorLink( "help/help.php", "#val_xss", $defined['error_small'], $errors['val_xss'], NULL, NULL );
   }
  } else {
   $style = $skin->SelectSkin( $defined['templates'], $_GET['skin'], $_COOKIE['skin'] );
  }

  // call our header file and pass it some variables
  $tpl->assign( 'TITLE', $defined['title'], NULL, NULL );
 	$tpl->assign( 'DESCRIPTION', "Manage Master/Peer Failover Options", NULL, NULL );
  $tpl->assign( 'STYLE', $style, NULL, NULL );
 
  // authentication template
  $FILE = "auth.tpl";
 
  // default is no error just diplay login form
  if( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -1 ) {
   // well looks like at least one login attempt has been processed, show empty field error
   if( $_SESSION['x']++ >= 1 ) {
    $ERROR = $err->GenerateErrorLink( "help/help.html", "#missing", $defined['error'], $errors['auth_e'], NULL, NULL );
   }
  // timeout with authentication token
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -2 ) {
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#timeout", $defined['error'], $errors['auth_to'], NULL, NULL );
   $misc->ExitApplication( $_SESSION['token'] );
  // error in validation of authentication data
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -3 ) {
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#alphanum", $defined['error'], $errors['val_alp'], NULL, NULL );
  // authentication data not found in database
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -4 ) {
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#user", $defined['error'], $errors['auth_n'], NULL, NULL );
  // error in database query
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -5 ) {
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['undef_sql'], NULL, NULL );
  // valid user found
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === 0 ) {
   // perform permissions check with access level and group data
   if( $level->ChkLevel( $_SESSION['token'] ) === "admin" ) {
    
 			// define some variables for the template etc.
 			$JS = NULL;
 			$FILE = "config.replication.tpl";

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 	   $peer_name = $_POST['peer_name'];
					$primary = $_POST['primary'];
					$address = $_POST['address'];
					$port = $_POST['port'];
					$peer_address = $_POST['peer_address'];
					$peer_port = $_POST['peer_port'];
					$max_response_delay = $_POST['max_response_delay'];
					$max_unacked_updates = $_POST['max_unacked_updates'];
					$mclt = $_POST['mclt'];
					$split = $_POST['split'];
					$load_balance_max_seconds = $_POST['load_balance_max_seconds'];
		
     // check each post element
     if( ( !empty( $peer_name ) ) && ( !empty( $primary ) ) && ( !empty( $address ) ) && ( !empty( $port ) ) && ( !empty( $peer_address ) ) && ( !empty( $peer_port ) ) ) {
      // begin validation of configuration options
      if( ( $val->ValidateDomain( $peer_name ) !== -1 ) && ( $val->ValidateString( $primary ) !== -1 ) && ( $val->ValidateDomain( $address ) !== -1 ) && ( $val->ValidateInteger( $port ) !== -1 ) && ( $val->ValidateDomain( $peer_address ) !== -1 ) && ( $val->ValidateInteger( $peer_port ) !== -1 ) && ( $val->ValidateInteger( $max_response_delay ) !== -1 ) && ( $val->ValidateInteger( $max_unacked_updates ) !== -1 ) && ( $val->ValidateInteger( $mclt ) !== -1 ) && ( $val->ValidateInteger( $split ) !== -1 ) && ( $val->ValidateInteger( $load_balance_max_seconds ) !== -1 ) ) {
     
 						// define our sql statements
 						$insert = "INSERT INTO `conf_failover` ( `peer name`, `type`, `address`, `port`, `peer address`, `peer port`, `max-response-delay`, `max-unacked-updates`, `mclt`, `split`, `load balance max seconds` ) VALUES ( \"" . $peer_name . "\",\"" . $primary . "\", \"" . $address . "\", \"" . $port . "\", \"" . $peer_address . "\", \"" . $peer_port . "\", \"" . $max_response_delay . "\", \"" . $max_unacked_updates . "\", \"" . $mclt . "\", \"" . $split . "\", \"" . $load_balance_max_seconds . "\" )";
 		    $update = "UPDATE `conf_failover` SET `peer name` = \"" . $peer_name . "\", `type` = \"" . $primary . "\", `address` = \"" . $address . "\", `port` = \"" . $port . "\", `peer address` = \"" . $peer_address . "\", `peer port` = \"" . $peer_port. "\", `max-response-delay` = \"" . $max_response_delay . "\", `max-unacked-updates` = \"" . $max_unacked_updates . "\", `mclt` = \"" . $mclt . "\", `split` = \"" . $split . "\", `load balance max seconds` = \"" . $load_balance_max_seconds . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
 						$delete = "DELETE FROM `conf_failover` WHERE `id` = \"" . $id . "\" LIMIT 1";
						
 						// determine which button was clicked
 						if( !empty( $_POST['AddFailOverOpts'] ) ) { $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
 						if( !empty( $_POST['EditFailOverOpts'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelFailOverOpts'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }
 						
 						// process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) { echo $db->dbCatchError();
        $error = $err->GenerateErrorLink( "help/help.html", "#config_failover", $defined['error'], $db_msg_err, NULL, NULL );
        // attempt to update if record exists
        if( ( eregi( "duplicate", $db->dbCatchError() ) ) || ( !empty( $id ) ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_failover", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
 									$error = $err->GenerateErrorLink( "help/help.html", "#config_failover", $defined['good'], $errors['db_edit'], NULL, NULL );
 								}
 							}
       } else {
 							$error = $err->GenerateErrorLink( "help/help.html", "#config_failover", $defined['good'], $db_msg_good, NULL, NULL );
 						} 

      } else {
       // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_failover", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateDomain( $peer_name ) === -1 ) { $list .= "<li>Peer Name Field is an invalid string</li>"; $peer_name_err = $e; }
 						if( $val->ValidateString( $primary ) === -1 ) { $list .= "<li>Primary Field is an invalid string</li>"; $primary_err = $e; }
 						if( $val->ValidateDomain( $address ) === -1 ) { $list .= "<li>Address Field is not a valid domain, IPv4 or hostname</li>"; $address_err = $e; }
       if( $val->ValidateInteger( $port ) === -1 ) { $list .= "<li>Port Field is an invalid number</li>"; $port_err = $e; }
							if( $val->ValidateDomain( $peer_address ) === -1 ) { $list .= "<li>Peer address field is an invalid dommain, IPv4 or hostname</li>"; $peer_address_err = $e; }
							if( $val->ValidateInteger( $peer_port ) === -1 ) { $list .= "<li>Peer port is an invalid number</li>"; $peer_port_err = $e; }
							if( $val->ValidateInteger( $max_response_delay ) === -1 ) { $list .= "<li>Max Response Delay Field is invalid</li>"; $max_response_delay_err = $e; }
							if( $val->ValidateInteger( $max_unacked_updates ) === -1 ) { $list .= "<li>Max Unpacked Packets Field is invalid</li>"; $max_unacked_updates_err = $e; }
							if( $val->ValidateInteger( $mclt ) === -1 ) { $list .= "<li>Mac Client Lead Time Field is invalid</li>"; $mclt_err = $e; }
							if( $val->ValidateInteger( $split ) === -1 ) { $list .= "<li>Split Index Field is invalid</li>"; $split_err = $e; }
							if( $val->ValidateInteger( $load_balance_max_seconds ) === -1 ) { $list .= "<li>Load Balance Seconds Index Field is invalid</li>"; $load_balance_seconds_err = $e; }
 						$list .= "</ol>";
 						$error = $err->GenerateErrorLink( "help/help.html", "#config_failover", $defined['error'], $errors['val_str'] . $list, NULL, NULL );
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_failover", '800', '800' );
 					$list .= "<ol>";
      if( empty( $peer_name ) ) { $list .= "<li>Peer Name Field is missing</li>"; $peer_name_err = $e; }
      if( empty( $primary ) ) { $list .= "<li>Primary Field is missing</li>"; $primary_err = $e; }
 					if( empty( $address ) ) { $list .= "<li>Address Field is missing</li>"; $address_err = $e; }
      if( empty( $port ) ) { $list .= "<li>Port Field is missing</li>"; $port_err = $e; }
						if( empty( $peer_address ) ) { $list .= "<li>Peer Address Field is missing</li>"; $peer_address_err = $e; }
						if( empty( $peer_port ) ) { $list .= "<li>Peer Port Field is missing</li>"; $peer_port_err = $e; }
 					$list .= "</ol>";
 					$error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
     }
    } else {
				 // perform a lookup on current values to display for editing
					$query = "SELECT * FROM `conf_failover`";
 		  if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
      $error = $err->GenerateErrorLink( "help/help.html", "#config_failover", $defined['error'], $errors['db_select'], NULL, NULL );
     } else {
      $data = $db->dbArrayResultsAssoc( $value );
 					$id = $data[0]['id'];
 					$peer_name = $data[0]['peer name'];
 				 $primary = $data[0]['type'];
 				 $address = $data[0]['address'];
 				 $port = $data[0]['port'];
 				 $peer_address = $data[0]['peer address'];
 				 $peer_port = $data[0]['peer port'];
 				 $max_response_delay = $data[0]['max-response-delay'];
 				 $max_unacked_updates = $data[0]['max-unacked-updates'];
 				 $mclt = $data[0]['mclt'];
 				 $split = $data[0]['split'];
 				 $load_balance_max_seconds = $data[0]['load balance max seconds'];
 		  }
				}

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
    $tpl->assign( 'peer_name', $val->ValidateXSS( $peer_name ), NULL, NULL );
    $tpl->assign( 'primary', $val->ValidateXSS( $primary ), NULL, NULL );
    $tpl->assign( 'address', $val->ValidateXSS( $address ), NULL, NULL );
 			$tpl->assign( 'port', $val->ValidateXSS( $port ), NULL, NULL );
    $tpl->assign( 'peer_address', $val->ValidateXSS( $peer_address ), NULL, NULL );
    $tpl->assign( 'peer_port', $val->ValidateXSS( $peer_port ), NULL, NULL );
    $tpl->assign( 'max_response_delay', $val->ValidateXSS( $max_response_delay ), NULL, NULL );
    $tpl->assign( 'max_unacked_updates', $val->ValidateXSS( $max_unacked_updates ), NULL, NULL );
    $tpl->assign( 'mclt', $val->ValidateXSS( $mclt ), NULL, NULL );
    $tpl->assign( 'split', $val->ValidateXSS( $split ), NULL, NULL );
				$tpl->assign( 'load_balance_max_seconds', $val->ValidateXSS( $load_balance_max_seconds ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'peer_name_err', $peer_name_err, NULL, NULL );
    $tpl->assign( 'primary_err', $primary_err, NULL, NULL );
    $tpl->assign( 'address_err', $address_err, NULL, NULL );
 			$tpl->assign( 'port_err', $port_err, NULL, NULL );
    $tpl->assign( 'peer_address_err', $peer_address_err, NULL, NULL );
    $tpl->assign( 'peer_port_err', $peer_port_err, NULL, NULL );
    $tpl->assign( 'max_response_delay_err', $max_response_delay_err, NULL, NULL );
    $tpl->assign( 'max_unacked_updates_err', $max_unacked_updates_err, NULL, NULL );
    $tpl->assign( 'mclt_err', $mclt_err, NULL, NULL );
    $tpl->assign( 'split_err', $split_err, NULL, NULL );
				$tpl->assign( 'load_balance_max_seconds_err', $load_balance_max_seconds_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_failover", $dbconn );
			
 			// Free db handle and close connection(s)
    $db->dbFreeData( $dbconn );
    $db->dbCloseConn( $dbconn );

   } else {
    // page view restricted by access level
 			$ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['level'], NULL, NULL );
   }
  } else {
 		// general authentication error
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['auth_n'], NULL, NULL );
  }

 } else {
  // Possible XSS attack
  $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['xss_config'], NULL, NULL );
 }

} else {
 // File is missing for configuration params
 $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['config_file'], NULL, NULL );
}


// include our header file
$tpl->assign( 'JS', $JS, NULL, NULL );
$tpl->display( 'header.tpl', $flag, NULL );

// determine our menu system
$MENU = $menu->CreateNav( $_SESSION['token'], $_GET['skin'] );
$tpl->assign( 'MENU', $tpl->assign( 'SKIN', preg_replace( '/templates\//', '', $style ), $MENU, $flag ), NULL, NULL );

// assign some vars to our main template
$tpl->assign( 'URL', $_SERVER['PHP_SELF'] . "?skin=" . $_GET['skin'], NULL, NULL );
$tpl->assign( 'IP_ADDRESS', $_SERVER['REMOTE_ADDR'], NULL, NULL );
$tpl->assign( 'ERROR', $ERROR, NULL, NULL );
$tpl->assign( 'STYLE', $style, NULL, NULL );
 $tpl->assign( 'usersonline', "<b>Users:</b> " . $usersoline . " online", NULL, NULL );

// call our main body template data
$tpl->assign( 'DATA', $tpl->assign( NULL, NULL, $FILE, $flag ), NULL, NULL );
$tpl->display( 'main.tpl', $flag, NULL );

// call our footer file
$tpl->assign( 'DISCLAIMER', $defined['disclaimer'], NULL, NULL );
$tpl->assign( 'SKIN_MENU', $skin->GenSkinMenu( $_GET['skin'], $defined['templates'] ), NULL, NULL );
$tpl->assign( 'SKIN_MENU_ERR', $skin_err, NULL, NULL );
$tpl->display( 'footer.tpl', $flag, NULL );

// show some debugging if enabled
if( $defined['debug'] === "TRUE" ) { $debug->ShowDebug( $_GET, $_POST, $_REQUEST, $_SESSION ); }

?>