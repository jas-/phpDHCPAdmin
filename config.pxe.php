<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * config.pxe.php - DHCPD Global PXE/BOOTP configuration options
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
 	$tpl->assign( 'DESCRIPTION', "Enable Extra PXE Bootp Options", NULL, NULL );
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
 			$FILE = "config.pxe.tpl";

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$option_space = $_POST['option_space'];
 				$mtftp_ip = $_POST['mtftp_ip'];
 				$mtftp_cport = $_POST['mtftp_cport'];
 				$mtftp_sport = $_POST['mtftp_sport'];
 				$mtftp_tmout = $_POST['mtftp_tmout'];
 				$mtftp_delay = $_POST['mtftp_delay'];
     $discovery_control = $_POST['discovery_control'];
     $discovery_mcast_addr = $_POST['discovery_mcast_addr'];
 				$pxe_enabled = $_POST['pxe_enabled'];
				
     // check each post element
     if( ( !empty( $pxe_enabled ) ) && ( !empty( $option_space ) ) && ( !empty( $mtftp_ip ) ) && ( !empty( $mtftp_cport ) ) && ( !empty( $mtftp_sport ) ) && ( !empty( $mtftp_tmout ) ) && ( !empty( $mtftp_delay ) ) && ( !empty( $discovery_control ) ) && ( !empty( $discovery_mcast_addr ) ) ) {
      // begin validation of configuration options
      if( ( $val->ValidateString( $pxe_enabled ) !== -1 ) && ( $val->ValidateParagraph( $option_space ) !== -1 ) && ( $val->ValidateParagraph( $mtftp_ip ) !== -1 ) && ( $val->ValidateParagraph( $mtftp_cport ) !== -1 ) && ( $val->ValidateParagraph( $mtftp_sport ) !== -1 ) && ( $val->ValidateParagraph( $mtftp_tmout ) !== -1 ) && ( $val->ValidateParagraph( $mtftp_delay ) !== -1 ) && ( $val->ValidateParagraph( $discovery_control ) !== -1 ) && ( $val->ValidateParagraph( $discovery_mcast_addr ) !== -1 ) ) {
       
 						// define our sql statements
 						$insert = "INSERT INTO `conf_pxe_opts` ( `option-space`, `mtftp-ip`, `mtftp-cport`, `mtftp-sport`, `mtftp-tmout`, `mtftp-delay`, `discovery-control`, `discovery-mcast-addr`, `pxe-enabled` ) VALUES ( \"" . $option_space . "\",\"" . $mtftp_ip . "\", \"" . $mtftp_cport . "\", \"" . $mtftp_sport . "\", \"" . $mtftp_tmout . "\", \"" . $mtftp_delay . "\", \"" . $discovery_control . "\", \"" . $discovery_mcast_addr . "\", \"" . $pxe_enabled . "\" )";
 		    $update = "UPDATE `conf_pxe_opts` SET `option-space` = \"" . $option_space . "\", `mtftp-ip` = \"" . $mtftp_ip . "\", `mtftp-cport` = \"" . $mtftp_cport . "\", `mtftp-sport` = \"" . $mtftp_sport . "\", `mtftp-tmout` = \"" . $mtftp_tmout . "\", `mtftp-delay` = \"" . $mtftp_delay . "\", `discovery-control` = \"" . $discovery_control . "\", `discovery-mcast-addr` = \"" . $discovery_mcast_addr . "\", `pxe-enabled` = \"" . $pxe_enabled . "\" LIMIT 1";
 						$delete = "DELETE FROM `conf_pxe_opts` WHERE `id` = \"" . $id . "\" LIMIT 1";
						
 						// determine which button was clicked
 						if( !empty( $_POST['AddPXEConfOpts'] ) ) { $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
 						if( !empty( $_POST['EditPXEConfOpts'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelPXEConfOpts'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }
 						
 						// process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_pxe", $defined['error'], $db_msg_err, NULL, NULL );
        // attempt to update if record exists
        if( ( eregi( "duplicate", $db->dbCatchError() ) ) || ( !empty( $id ) ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_pxe", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
 									$error = $err->GenerateErrorLink( "help/help.html", "#config_pxe", $defined['good'], $errors['db_edit'], NULL, NULL );
 								}
 							}
       } else {
 							$error = $err->GenerateErrorLink( "help/help.html", "#config_pxe", $defined['good'], $db_msg_good, NULL, NULL );
 						}

      } else {
       // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_pxe", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateString( $pxe_enabled ) !== -1 ) { $list .= "<li>PXE Enabled field is invalid</li>"; $pxe_enabled_err = $e; }
 						if( $val->ValidateParagraph( $option_space ) !== -1 ) { $list .= "<li>Option Space Field is invalid</li>"; $option_space_err = $e; }
 						if( $val->ValidateParagraph( $mtftp_ip ) !== -1 ) { $list .= "<li>MTFTP-IP Field is invalid</li>"; $mtftp_ip_err = $e; }
 						if( $val->ValidateParagraph( $mtftp_cport ) !== -1 ) { $list .= "<li>MTFTP-CPORT Field is invalid</li>"; $mtftp_cport_err = $e; }
 						if( $val->ValidateParagraph( $mtftp_sport ) !== -1 ) { $list .= "<li>MTFTP-SPORT Field is missing</li>"; $mtftp_sport_err = $e; }
 						if( $val->ValidateParagraph( $mtftp_tmout ) !== -1 ) { $list .= "<li>MTFTP-TMOUT Field is invalid</li>"; $mtftp_tmout_err = $e; }
 						if( $val->ValidateParagraph( $mtftp_delay ) !== -1 ) { $list .= "<li>MTFTP-DELAY Field is invalid</li>"; $authoritative_err = $e; }
 						if( $val->ValidateParagraph( $discovery_control ) !== -1 ) { $list .= "<li>Discovery Control Field is invalid</li>"; $discovery_control_err = $e; }
       if( $val->ValidateParagraph( $discovery_mcast_addr ) !== -1 ) { $list .= "<li>Discovery MCAST ADDR Field is invalid</li>"; $discovery_mcast_addr_err = $e; }
 						$list .= "</ol>";
 						$error = $err->GenerateErrorLink( "help/help.html", "#config_pxe", $defined['error'], $errors['val_str'] . $list, NULL, NULL );
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_pxe", '800', '800' );
 					$list .= "<ol>";
      if( empty( $pxe_enabled ) ) { $list .= "<li>PXE Option enabled is missing</li>"; $pxe_enabled_err = $e; }
      if( empty( $option_space ) ) { $list .= "<li>Option Space Field is missing</li>"; $option_space_err = $e; }
 					if( empty( $mtftp_ip ) ) { $list .= "<li>MTFTP-IP Field is missing</li>"; $mtftp_ip_err = $e; }
 					if( empty( $mtftp_cport ) ) { $list .= "<li>MTFTP-CPORT Field is missing</li>"; $mtftp_cport_err = $e; }
 					if( empty( $mtftp_sport ) ) { $list .= "<li>MTFTP-SPORT Field is missing</li>"; $mtftp_sport_err = $e; }
 					if( empty( $mtftp_tmout ) ) { $list .= "<li>MTFTP-TMOUT Field is missing</li>"; $mtftp_tmout_err = $e; }
 					if( empty( $mtftp_delay ) ) { $list .= "<li>MTFTP-DELAY Field is missing</li>"; $mtftp_delay_err = $e; }
 					if( empty( $discovery_control ) ) { $list .= "<li>Discovery Control Field is missing</li>"; $discovery_control_err = $e; }
 					if( empty( $discovery_mcast_addr ) ) { $list .= "<li>Discovery MCAST ADDR Field is missing</li>"; $discovery_mcast_addr_err = $e; }
 					$list .= "</ol>";
 					$error = $err->GenerateErrorLink( "help/help.html", "#config_pxe", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
     }
    } else {
 				// populate the form with database information if already configured
 				$query = "SELECT * FROM `conf_pxe_opts`";
 		  if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
      $error = $err->GenerateErrorLink( "help/help.html", "#config_pxe", $defined['error'], $errors['db_select'], NULL, NULL );
     } else {
      $data = $db->dbArrayResultsAssoc( $value );
 					$id = $data[0]['id'];
 					$pxe_enabled = $data[0]['pxe-enabled'];
 				 $option_space = $data[0]['option-space'];
 				 $mtftp_ip = $data[0]['mtftp-ip'];
 				 $mtftp_cport = $data[0]['mtftp-cport'];
 				 $mtftp_sport = $data[0]['mtftp-sport'];
 				 $mtftp_tmout = $data[0]['mtftp-tmout'];
 				 $mtftp_delay = $data[0]['mtftp-delay'];
	 			 $discovery_control = $data[0]['discovery-control'];
 				 $discovery_mcast_addr = $data[0]['discovery-mcast-addr'];
 		  }
 			}

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    if( $pxe_enabled === "true" ) { $pxe_enabled_true = "checked"; }
    if( ( $pxe_enabled === "false" ) || ( empty( $pxe_enabled ) ) ) { $pxe_enabled_false = "checked"; }
    $tpl->assign( 'pxe_enabled_true', $pxe_enabled_true, NULL, NULL );
    $tpl->assign( 'pxe_enabled_false', $pxe_enabled_false, NULL, NULL );
 			$tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
 			$tpl->assign( 'option_space', $val->ValidateXSS( $option_space ), NULL, NULL );
 			$tpl->assign( 'mtftp_ip', $val->ValidateXSS( $mtftp_ip ), NULL, NULL );
 			$tpl->assign( 'mtftp_cport', $val->ValidateXSS( $mtftp_cport ), NULL, NULL );
    $tpl->assign( 'mtftp_sport', $val->ValidateXSS( $mtftp_sport ), NULL, NULL );
 			$tpl->assign( 'mtftp_tmout', $val->ValidateXSS( $mtftp_tmout ), NULL, NULL );
 			$tpl->assign( 'mtftp_delay', $val->ValidateXSS( $mtftp_delay ), NULL, NULL );
 			$tpl->assign( 'discovery_control', $val->ValidateXSS( $discovery_control ), NULL, NULL );
 			$tpl->assign( 'discovery_mcast_addr', $val->ValidateXSS( $discovery_mcast_addr ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'pxe_enabled_err', $pxe_enabled_err, NULL, NULL );
 			$tpl->assign( 'option_space_err', $option_space_err, NULL, NULL );
 			$tpl->assign( 'mtftp_ip_err', $mtftp_ip_err, NULL, NULL );
 			$tpl->assign( 'mtftp_cport_err', $mtftp_cport_err, NULL, NULL );
    $tpl->assign( 'mtftp_sport_err', $mtftp_sport_err, NULL, NULL );
 			$tpl->assign( 'mtftp_tmout_err', $mtftp_tmout_err, NULL, NULL );
 			$tpl->assign( 'mtftp_delay_err', $mtftp_delay_err, NULL, NULL );
 			$tpl->assign( 'discovery_control_err', $discovery_control_err, NULL, NULL );
 			$tpl->assign( 'discovery_mcast_addr_err', $discovery_mcast_addr_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_pxe_opts", $dbconn );
 			
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