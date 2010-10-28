<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * config.dnssec.php - DHCPD Global DNSSEC configuration options
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
 	$tpl->assign( 'DESCRIPTION', "Manage DNSSEC Keys", NULL, NULL );
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
 			$FILE = "config.dnssec.tpl";

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
  				 $query = "SELECT * FROM `conf_dnssec_opts` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						//} else {
						// $query = "SELECT * FROM `conf_dnssec_opts` WHERE `id` = \"" . $_GET['id'] . "\" WHERE `group` = \"" . $group . "\" LIMIT 1";
						//}
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
 				 	$id = $data[0]['id'];
 				 	$key_name = $data[0]['key-name'];
 				  $algorithm = $data[0]['algorithm'];
 				  $key = $data[0]['key'];
 		   }
     }
    }

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$key_name = $_POST['key_name'];
 				$algorithm = $_POST['algorithm'];
     $key = $_POST['key'];
 	
     // check each post element
     if( ( !empty( $key_name ) ) && ( !empty( $algorithm ) ) && ( !empty( $key ) ) ) {
      // begin validation of configuration options
      if( ( $val->ValidateString( $key_name ) !== -1 ) && ( $val->ValidateParagraph( $algorithm ) !== -1 ) && ( $val->ValidateParagraph( $key ) !== -1 ) ) {
     
 						// define our sql statements
 						$insert = "INSERT INTO `conf_dnssec_opts` ( `key-name`, `algorithm`, `key`, `group` ) VALUES ( \"" . $key_name . "\",\"" . $algorithm . "\", \"" . $key . "\", \"" . $group . "\" )";
 		    $update = "UPDATE `conf_dnssec_opts` SET `key-name` = \"" . $key_name . "\", `algorithm` = \"" . $algorithm . "\", `key` = \"" . $key . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
 						$delete = "DELETE FROM `conf_dnssec_opts` WHERE `id` = \"" . $id . "\" LIMIT 1";
						
 						// determine which button was clicked
 						if( !empty( $_POST['AddDNSSECConfOpts'] ) ) { $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
 						if( !empty( $_POST['EditDNSSECConfOpts'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelDNSSECConfOpts'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }
 						
 						// process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $db_msg_err, NULL, NULL );
        // attempt to update if record exists
        if( ( eregi( "duplicate", $db->dbCatchError() ) ) || ( !empty( $id ) ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
 									$error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['good'], $errors['db_edit'], NULL, NULL );
 								}
 							}
       } else {
 							$error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['good'], $db_msg_good, NULL, NULL );
 						} 

      } else {
       // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_dnssec", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateString( $key_name ) === -1 ) { $list .= "<li>Key Name Field is invalid</li>"; $key_name_err = $e; }
 						if( $val->ValidateParagraph( $algorithm ) === -1 ) { $list .= "<li>Algorithm Field is invalid</li>"; $algorithm_err = $e; }
 						if( $val->ValidateParagraph( $key ) === -1 ) { $list .= "<li>Key Field is invalid</li>"; $key_err = $e; }
 						$list .= "</ol>";
 						$error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $errors['val_str'] . $list, NULL, NULL );
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_dnssec", '800', '800' );
 					$list .= "<ol>";
      if( empty( $key_name ) ) { $list .= "<li>Key Name Field is missing</li>"; $key_name_err = $e; }
      if( empty( $algorithm ) ) { $list .= "<li>Algorithm Field is missing</li>"; $algorithm_err = $e; }
 					if( empty( $key ) ) { $list .= "<li>Key Field is missing</li>"; $key_err = $e; }
 					$list .= "</ol>";
 					$error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
     }
    }

    // create current list of dnssec security options
				//if( $group === "admin" ) {
     $secopts = "SELECT * FROM `conf_dnssec_opts` ORDER BY `key-name`";
				//} else {
				// $secopts = "SELECT * FROM `conf_dnssec_opts` WHERE `group` = \"" . $group . "\" ORDER BY `key-name`";
				//}
 		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $secopts, $dbconn ), $dbconn ) ) !== -1 ) {
     $dnssecopts = $db->dbArrayResultsAssoc( $current );
 		 }
 			$dnssec_opt = $misc->GenJumpMenuBoxDNSSEC( $dnssecopts, 'dnssecopt', $_GET['skin'] );

    // populate our `algorithm` list
    $alg = "SELECT `name` FROM `admin_config_algorithm` ORDER BY `name`";
 		 if( ( $return = $db->dbQuery( $val->ValidateSQL( $alg, $dbconn ), $dbconn ) ) === -1 ) {
     $error = $err->GenerateErrorLink( "help/help.html", "#config_dnssec", $defined['error'], $errors['db_select'], NULL, NULL );
    } else {
     $algs = $db->dbArrayResultsAssoc( $return );
 		 }
 			$algorithm = $misc->GenDropMenuWSelected( $algs, $algorithm, 'algorithm' );

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'dnssec_opt', $dnssec_opt, NULL, NULL );
    $tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
    $tpl->assign( 'key_name', $val->ValidateXSS( $key_name ), NULL, NULL );
    $tpl->assign( 'algorithm', $algorithm, NULL, NULL );
 			$tpl->assign( 'key', $val->ValidateXSS( $key ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'dnssec_opt_err', $dnssec_opt_err, NULL, NULL );
    $tpl->assign( 'key_name_err', $key_name_err, NULL, NULL );
 			$tpl->assign( 'algorithm_err', $algorithm_err, NULL, NULL );
 			$tpl->assign( 'key_err', $key_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_dnssec_opts", $dbconn );
		
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