<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * config.global.php - DHCPD Global configuration options
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
 	$tpl->assign( 'DESCRIPTION', "Common Global DHCPD Options", NULL, NULL );
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
 			$FILE = "config.global.tpl";

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
 				$domain_name = $_POST['domain_name'];
 				$dns_server_list = $_POST['dns_server_list'];
 				$default_lease_time = $_POST['default_lease_time'];
 				$max_lease_time = $_POST['max_lease_time'];
 				$time_offset = $_POST['time_offset'];
 				$routers = $_POST['routers'];
 				$lpr_server_list = $_POST['lpr_server_list'];
 				$broadcast_addr = $_POST['broadcast_addr'];
 				$subnet_mask_addr = $_POST['subnet_mask_addr'];
 				$server_ident = $_POST['server_ident'];
 				$time_serv = $_POST['time_serv'];
 				$ddns_update_style = $_POST['ddns_update_style'];
 				$authoritative = $_POST['authoritative'];
 				$bootp = $_POST['bootp'];
				
     // check each post element
     if( ( !empty( $domain_name ) ) && ( !empty( $default_lease_time ) ) && ( !empty( $max_lease_time ) ) && ( ( !empty( $ddns_update_style ) ) || ( $ddns_update_style === "---------" ) ) && ( ( !empty( $authoritative ) ) || ( $authoritative === "---------" ) ) && ( ( !empty( $bootp ) ) || ( $bootp === "---------" ) ) ) {

						// begin validation of configuration options
      if( ( $val->ValidateDomain( $domain_name ) !== -1 ) && ( $val->ValidateParagraph( $dns_server_list ) !== -1 ) && ( $val->ValidateInteger( $default_lease_time ) !== -1 ) && ( $val->ValidateInteger( $max_lease_time ) !== -1 ) && ( $val->ValidateParagraph( $routers ) !== -1 ) && ( $val->ValidateParagraph( $ddns_update_style ) !== -1 ) && ( $val->ValidateString( $authoritative ) !== -1 ) && ( $val->ValidateString( $bootp ) !== -1 ) ) {
       
 						// define our sql statements
 						$insert = "INSERT INTO `conf_global_opts` ( `option domain-name`, `option subnet-mask`, `default-lease-time`, `max-lease-time`, `option time-offset`, `option routers`, `option domain-name-servers`, `option lpr-servers`, `option-broadcast-addr`, `server-identifier`, `option time-serv`, `ddns-update-style`, `authoritative`, `bootp` ) VALUES ( \"" . $domain_name . "\", \"" . $subnet_mask_addr . "\", \"" . $default_lease_time . "\", \"" . $max_lease_time . "\", \"" . $time_offset . "\", \"" . $routers . "\", \"" . $dns_server_list . "\", \"" . $lpr_server_list . "\", \"" . $broadcast_addr . "\", \"" . $server_ident . "\", \"" . $time_serv . "\", \"" . $ddns_update_style . "\", \"" . $authoritative . "\", \"" . $bootp . "\" )";
 		    $update = "UPDATE `conf_global_opts` SET `option domain-name` = \"" . $domain_name . "\", `option subnet-mask` = \"" . $subnet_mask_addr . "\", `default-lease-time` = \"" . $default_lease_time . "\", `max-lease-time` = \"" . $max_lease_time . "\", `option time-offset` = \"" . $time_offset . "\", `option routers` = \"" . $routers . "\", `option domain-name-servers` = \"" . $dns_server_list . "\", `option lpr-servers` = \"" . $lpr_server_list . "\", `option-broadcast-addr` = \"" . $broadcast_addr . "\", `server-identifier` = \"" . $server_ident . "\", `option time-serv` = \"" . $time_serv . "\", `ddns-update-style` = \"" . $ddns_update_style . "\", `authoritative` = \"" . $authoritative . "\", `bootp` = \"" . $bootp . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
 						$delete = "DELETE FROM `conf_global_opts` WHERE `id` = \"" . $id . "\" LIMIT 1";
 						
 						// determine which button was clicked
 						if( !empty( $_POST['AddGlobalConfOpts'] ) ) { $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
 						if( !empty( $_POST['EditGlobalConfOpts'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelGlobalConfOpts'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }

 						// process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_global", $defined['error'], $db_msg_err, NULL, NULL );
        // attempt to update if record exists
        if( eregi( "duplicate", $db->dbCatchError() ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_global", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
 									$error = $err->GenerateErrorLink( "help/help.html", "#config_global", $defined['good'], $errors['db_edit'], NULL, NULL );
	 							}
	 						}
       } else {
	 						$error = $err->GenerateErrorLink( "help/help.html", "#config_global", $defined['good'], $db_msg_good, NULL, NULL );
	 					}

      } else {
       // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_global", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateDomain( $domain_name ) === -1 ) { $list .= "<li>Domain Name Field is invalid. Could not locate a valid 'A' type record in DNS</li>"; $domain_name_err = $e; }
 						if( $val->ValidateParagraph( $dns_server_list ) === -1 ) { $list .= "<li>DNS Server List Field is invalid</li>"; $dns_server_list_err = $e; }
 						if( $val->ValidateInteger( $default_lease_time ) === -1 ) { $list .= "<li>Default Lease Time Field is invalid</li>"; $default_lease_time_err = $e; }
 						if( $val->ValidateInteger( $max_lease_time ) === -1 ) { $list .= "<li>Max Lease Time Field is invalid</li>"; $max_lease_time_err = $e; }
 						if( $val->ValidateParagraph( $routers ) === -1 ) { $list .= "<li>Routers Field is invalid</li>"; $routers_err = $e; }
 						if( $val->ValidateParagraph( $ddns_update_style ) === -1 ) { $list .= "<li>ddns update style Field is invalid</li>"; $ddns_update_style_err = $e; }
 						if( $val->ValidateParagraph( $authoritative ) === -1 ) { $list .= "<li>authoritative style Field is invalid</li>"; $authoritative_err = $e; }
 						if( $val->ValidateString( $bootp ) === -1 ) { $list .= "<li>BOOTP Field is invalid</li>"; $bootp_err = $e; }
 						$list .= "</ol>";
 						$error = $err->GenerateErrorLink( "help/help.html", "#config_global", $defined['error'], $errors['val_par'] . $list, NULL, NULL );
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_global", '800', '800' );
 					$list .= "<ol>";
      if( empty( $domain_name ) ) { $list .= "<li>Domain Name Field is missing</li>"; $domain_name_err = $e; }
 					if( empty( $default_lease_time ) ) { $list .= "<li>Default Lease Time Field is missing</li>"; $default_lease_time_err = $e; }
 					if( empty( $max_lease_time ) ) { $list .= "<li>Max Lease Time Field is missing</li>"; $max_lease_time_err = $e; }
 					if( ( empty( $ddns_update_style ) ) || ( $ddns_update_style === "---------" ) ) { $list .= "<li>ddns update style Field is missing</li>"; $ddns_update_style_err = $e; }
 					if( ( empty( $authoritative ) ) || ( $authoritative === "---------" ) ) { $list .= "<li>authoritative style Field is missing</li>"; $authoritative_err = $e; }
 					if( ( empty( $bootp ) ) || ( $ddns_update_style === "---------" ) ) { $list .= "<li>BOOTP Field is missing</li>"; $bootp_err = $e; }
 					$list .= "</ol>";
 					$error = $err->GenerateErrorLink( "help/help.html", "#config_global", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
     }
    } else {
 				// populate the form with database information if already configured
 				$query = "SELECT * FROM `conf_global_opts`";
 		  if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
      $error = $err->GenerateErrorLink( "help/help.html", "#config_global", $defined['error'], $errors['db_select'], NULL, NULL );
     } else {
      $data = $db->dbArrayResultsAssoc( $value );
 					$id = $data[0]['id'];
 					$domain_name = $data[0]['option domain-name'];
 				 $dns_server_list = $data[0]['option domain-name-servers'];
 				 $default_lease_time = $data[0]['default-lease-time'];
 				 $max_lease_time = $data[0]['max-lease-time'];
 				 $time_offset = $data[0]['option time-offset'];
 				 $routers = $data[0]['option routers'];
 				 $lpr_server_list = $data[0]['option lpr-servers'];
 				 $broadcast_addr = $data[0]['option broadcast-addr'];
 				 $server_ident = $data[0]['server-identifier'];
 				 $ddns_update_style = $data[0]['ddns-update-style'];
 				 $authoritative = $data[0]['authoritative'];
 				 $bootp = $data[0]['bootp'];
 		  }
 			}

    // quick fix for option elements
 			if( ( !empty( $ddns_update_style ) ) && ( $ddns_update_style !== "---------" ) ) { $ddns_update_style = "<option value=$ddns_update_style>$ddns_update_style</option>"; }
 			if( ( !empty( $authoritative ) ) && ( $authoritative !== "---------" ) ) { $authoritative = "<option value=$authoritative>$authoritative</option>"; }
 			if( ( !empty( $bootp ) ) && ( $bootp !== "---------" ) ) { $bootp = "<option value=$bootp>$bootp</option>"; }

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
 			$tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
 			$tpl->assign( 'domain_name', $val->ValidateXSS( $domain_name ), NULL, NULL );
 			$tpl->assign( 'dns_server_list', $val->ValidateXSS( $dns_server_list ), NULL, NULL );
 			$tpl->assign( 'default_lease_time', $val->ValidateXSS( $default_lease_time ), NULL, NULL );
    $tpl->assign( 'max_lease_time', $val->ValidateXSS( $max_lease_time ), NULL, NULL );
 			$tpl->assign( 'time_offset', $val->ValidateXSS( $time_offset ), NULL, NULL );
 			$tpl->assign( 'routers', $val->ValidateXSS( $routers ), NULL, NULL );
 			$tpl->assign( 'lpr_server_list', $val->ValidateXSS( $lpr_server_list ), NULL, NULL );
 			$tpl->assign( 'broadcast_addr', $val->ValidateXSS( $broadcast_addr ), NULL, NULL );
 			$tpl->assign( 'subnet_mask_addr', $val->validateXSS( $subnet_mask_addr ), NULL, NULL );
 			$tpl->assign( 'server_ident', $val->ValidateXSS( $server_ident ), NULL, NULL );
 			$tpl->assign( 'ddns_update_style', $ddns_update_style, NULL, NULL );
 			$tpl->assign( 'authoritative', $authoritative, NULL, NULL );
 			$tpl->assign( 'bootp', $bootp, NULL, NULL );

    // assign error messages
 			$tpl->assign( 'domain_name_err', $domain_name_err, NULL, NULL );
 			$tpl->assign( 'dns_server_list_err', $dns_server_list_err, NULL, NULL );
 			$tpl->assign( 'default_lease_time_err', $default_lease_time_err, NULL, NULL );
    $tpl->assign( 'max_lease_time_err', $max_lease_time_err, NULL, NULL );
 			$tpl->assign( 'time_offset_err', $time_offset_err, NULL, NULL );
 			$tpl->assign( 'routers_err', $routers_err, NULL, NULL );
 			$tpl->assign( 'lpr_server_list_err', $lpr_server_list_err, NULL, NULL );
 			$tpl->assign( 'broadcast_addr_err', $broadcast_addr_err, NULL, NULL );
 			$tpl->assign( 'subnet_mask_addr_err', $val->validateXSS( $subnet_mask_addr_err ), NULL, NULL );
 			$tpl->assign( 'server_ident_err', $server_ident_err, NULL, NULL );
 			$tpl->assign( 'ddns_update_style_err', $ddns_update_style_err, NULL, NULL );
 			$tpl->assign( 'authoritative_err', $authoritative_err, NULL, NULL );
 			$tpl->assign( 'bootp_err', $bootp_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_global_opts", $dbconn );
 			
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