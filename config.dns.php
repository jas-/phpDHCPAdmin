<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * config.dns.php - DHCPD Global DNS configuration options
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
		$encrypt = new Encryption;
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
 	$tpl->assign( 'DESCRIPTION', "Manage DNS Zones", NULL, NULL );
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
 			$FILE = "config.dns.tpl";

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // decode our authentication token to get our group membership
				$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$group = base64_decode( $user_details[3] );

    // Look for a GET id post to edit existing dnssec keys
    if( !empty( $_GET['id'] ) ) {
     if( $val->ValidateInteger( $_GET['id'] ) === -1 ) {
      $error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
						//if( $group === "admin" ) {
  				 $query = "SELECT * FROM `conf_dns_opts` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						//} else {
						// $query = "SELECT * FROM `conf_dns_opts` WHERE `id` = \"" . $_GET['id'] . "\" AND `group`	= \"" . $group . "\" LIMIT 1";
						//}
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
 				 	$id = $data[0]['id'];
 				 	$zone = $data[0]['zone'];
 				  $primary = $data[0]['type'];
       $dnssec_enabled = $data[0]['dnssec-enabled'];
 						$dnssec_key = $data[0]['dnssec-key'];
 		   }
     }
    }

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$zone = $_POST['zone'];
     $primary = $_POST['primary'];
 				$dnssec_enabled = $_POST['dnssec_enabled'];
 				$dnssec_key = $_POST['dnssec_key'];
				
     // check each post element
     if( ( !empty( $zone ) ) && ( !empty( $primary ) ) ) {
      // begin validation of configuration options
      if( ( $val->ValidateDomain( $zone ) !== -1 ) && ( $val->ValidateDomain( $primary ) !== -1 ) && ( $val->ValidateString( $dnssec_enabled ) !== -1 ) && ( $val->ValidateParagraph( $dnssec_key ) !== -1 ) ) {
       
 						// define our sql statements
 						$insert = "INSERT INTO `conf_dns_opts` ( `zone`, `type`,`dnssec-enabled`, `dnssec-key`, `group` ) VALUES ( \"" . $zone . "\",\"" . $primary . "\", \"" . $dnssec_enabled . "\", \"" . $dnssec_key . "\", \"" . $group . "\" )";
 		    $update = "UPDATE `conf_dns_opts` SET `zone` = \"" . $zone . "\", `type` = \"" . $primary . "\", `dnssec-enabled` = \"" . $dnssec_enabled . "\", `dnssec-key` = \"" . $dnssec_key . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
 						$delete = "DELETE FROM `conf_dns_opts` WHERE `id` = \"" . $id . "\" LIMIT 1";

       // determine which query to use
       if( !empty( $_POST['AddDNSConfOpts'] ) ) { $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
       if( !empty( $_POST['EditDNSConfOpts'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelDNSConfOpts'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }

 						// process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) { echo $db->dbCatchError();
        $error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['error'], $db_msg_err, NULL, NULL );
        // attempt to update if record exists
        if( ( eregi( "duplicate", $db->dbCatchError() ) ) || ( !empty( $id ) ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
 									$error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['good'], $errors['db_edit'], NULL, NULL );
 								}
 							}
       } else {
 							$error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['good'], $db_msg_good, NULL, NULL );
 						}

      } else {
       // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_dns", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateDomain( $zone ) !== -1 ) { $list .= "<li>Zone field is invalid</li>"; $zone_err = $e; }
 						if( $val->ValidateDomain( $primary ) !== -1 ) { $list .= "<li>Primary Server Field is invalid</li>"; $primary_err = $e; }
 						if( $val->ValidateParagraph( $file_name ) !== -1 ) { $list .= "<li>File Name Field is invalid</li>"; $file_name_err = $e; }
 						if( $val->ValidateString( $dnssec_enabled ) !== -1 ) { $list .= "<li>DNSSEC Enabled Field is invalid</li>"; $dnssec_enabled_err = $e; }
 						if( $val->ValidateParagraph( $dnssec_key ) !== -1 ) { $list .= "<li>DNSSEC Key Field is invalid</li>"; $dnssec_key_err = $e; }
 						$list .= "</ol>";
 						$error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['error'], $errors['val_str'] . $list, NULL, NULL );
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_dns", '800', '800' );
 					$list .= "<ol>";
      if( empty( $zone ) ) { $list .= "<li>Zone field is missing</li>"; $zone_err = $e; }
      if( empty( $primary ) ) { $list .= "<li>Primary Server Field is missing</li>"; $type_err = $e; }
 					if( empty( $dnssec_enabled ) ) { $list .= "<li>DNSSEC Enabled Field is missing</li>"; $dnssec_enabled_err = $e; }
 					if( empty( $dnssec_key ) ) { $list .= "<li>DNSSEC Key Field is missing</li>"; $dnssec_key_err = $e; }
 					$list .= "</ol>";
 					$error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
     }
    }

    // create current list of dnssec zones
				//if( $group === "admin" ) {
     $dnsopts = "SELECT * FROM `conf_dns_opts`";
				//} else {
				// $dnsopts = "SELECT * FROM `conf_dns_opts` WHERE `group` = \"" . $group . "\"";
				//}
 		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $dnsopts, $dbconn ), $dbconn ) ) !== -1 ) {
     $dnsopts = $db->dbArrayResultsAssoc( $current );
 		 }
 			$dns_opt = $misc->GenJumpMenuBoxDNS( $dnsopts, 'dnsopt', $_GET['skin'] );

    // populate our `key` list
    $list = "SELECT `key-name` FROM `conf_dnssec_opts`";
 		 if( ( $return = $db->dbQuery( $val->ValidateSQL( $list, $dbconn ), $dbconn ) ) === -1 ) {
     $error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['error'], $errors['db_select'], NULL, NULL );
    } else {
				 $list = $db->dbArrayResultsAssoc( $return );
	 			if( count( $list ) === 0 ) {
      $dnssec_key = "Empty key list (Required)";
     } else {
      $dnssec_key = $misc->GenDropMenuWSelectedDNS( $list, $dnssec_key, 'dnssec_key' );      
					}
 		 }

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'dns_opt', $dns_opt, NULL, NULL );
    if( $dnssec_enabled === "true" ) { $dnssec_enabled_true = "checked"; }
    if( ( $dnssec_enabled === "false" ) || ( empty( $dnssec_enabled ) ) ) { $dnssec_enabled_false = "checked"; }
    $tpl->assign( 'dnssec_enabled_true', $dnssec_enabled_true, NULL, NULL );
    $tpl->assign( 'dnssec_enabled_false', $dnssec_enabled_false, NULL, NULL );
 			$tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
 			$tpl->assign( 'zone', $val->ValidateXSS( $zone ), NULL, NULL );
 			$tpl->assign( 'primary', $val->ValidateXSS( $primary ), NULL, NULL );
    $tpl->assign( 'dnssec_key', $dnssec_key, NULL, NULL );

    // assign error messages
    $tpl->assign( 'dns_opt_err', $dns_opt_err, NULL, NULL );
    $tpl->assign( 'dnssec_enabled_err', $dnssec_enabled_err, NULL, NULL );
 			$tpl->assign( 'zone_err', $zone_err, NULL, NULL );
 			$tpl->assign( 'primary_err', $primary_err, NULL, NULL );
    $tpl->assign( 'file_name_err', $file_name_err, NULL, NULL );
 			$tpl->assign( 'dnssec_key_err', $dnssec_key_err, NULL, NULL );

    // Do some cleaning before leaving 
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_dns_opts", $dbconn );
			
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