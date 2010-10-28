<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * pxe.add.php - DHCPD Add a new PXE Group
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
 	$tpl->assign( 'DESCRIPTION', "Manage PXE Groups", NULL, NULL );
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
 			$JS = " hidediv('perms');";
 			$FILE = "manage.pxe.tpl";

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
      $error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
						if( $group === "admin" ) {
  				 $query = "SELECT * FROM `conf_pxe_groups` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						} else {
						 $query = "SELECT * FROM `conf_pxe_groups` WHERE `group` = \"" . $group . "\" AND `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						}
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
 				 	$id = $data[0]['id'];
 				 	$pxe_group_name = $data[0]['pxe-group-name'];
 				  $pxe_server = $data[0]['pxe-server'];
 				  $bootp_filename = $data[0]['bootp-filename'];
       $assigned_subnet = $data[0]['assigned-subnet'];
 		   }
     }
    }

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$pxe_group_name = $_POST['pxe_group_name'];
 				$pxe_server = $_POST['pxe_server'];
     $bootp_filename = $_POST['bootp_filename'];
 				$assigned_subnet = $_POST['assigned_subnet'];
				
     // check each post element
     if( ( !empty( $pxe_group_name ) ) && ( !empty( $pxe_server ) ) && ( !empty( $bootp_filename ) ) ) {
      // begin validation of configuration options
      if( ( $val->ValidateString( $pxe_group_name ) !== -1 ) && ( ( $val->ValidateIPv4( $pxe_server ) !== -1 ) || ( $val->ValidateDomain( $pxe_server ) !== -1 ) ) && ( ( $val->ValidateParagraph( $bootp_filename ) !== -1 ) ) || ( $val->ValidateParagraph( $assigned_subnet ) !== -1 ) ) {
      
 						// define our sql statements (filter out the group field if user group is admin)
							if( $group === "admin" ) {
  						$insert = "INSERT INTO `conf_pxe_groups` ( `pxe-group-name`, `pxe-server`, `bootp-filename`, `assigned-subnet` ) VALUES ( \"" . $pxe_group_name . "\",\"" . $pxe_server . "\", \"" . $bootp_filename . "\", \"" . $assigned_subnet . "\" )";
  		    $update = "UPDATE `conf_pxe_groups` SET `pxe-group-name` = \"" . $pxe_group_name . "\", `pxe-server` = \"" . $pxe_server . "\", `bootp-filename` = \"" . $bootp_filename . "\", `assigned-subnet` = \"" . $assigned_subnet . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
       } else {
  						$insert = "INSERT INTO `conf_pxe_groups` ( `pxe-group-name`, `pxe-server`, `bootp-filename`, `assigned-subnet`, `group` ) VALUES ( \"" . $pxe_group_name . "\",\"" . $pxe_server . "\", \"" . $bootp_filename . "\", \"" . $assigned_subnet . "\", \"" . $group . "\" )";
  		    $update = "UPDATE `conf_pxe_groups` SET `pxe-group-name` = \"" . $pxe_group_name . "\", `pxe-server` = \"" . $pxe_server . "\", `bootp-filename` = \"" . $bootp_filename . "\", `assigned-subnet` = \"" . $assigned_subnet . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
       }
 						$delete = "DELETE FROM `conf_pxe_groups` WHERE `id` = \"" . $id . "\" LIMIT 1";
       $update_hosts = "UPDATE `conf_hosts` SET `pxe-group` = \"\" WHERE `pxe-group` = \"" . $pxe_group_name . "\"";

 						// determine which button was clicked
 						if( !empty( $_POST['AddPXEGroup'] ) ) { $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
 						if( !empty( $_POST['EditPXEGroup'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelPXEGroup'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }
						
 						// process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['error'], $db_msg_err, NULL, NULL );
        // attempt to update if record exists
        if( ( eregi( "duplicate", $db->dbCatchError() ) ) || ( !empty( $id ) ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
 									$error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['good'], $errors['db_edit'], NULL, NULL );
 								}
 							}
       } else {
 							$error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['good'], $db_msg_good, NULL, NULL );
 						}

       // make sure we remove the pxe group from any assigned hosts
       if( !empty( $_POST['DelPXEGroup'] ) ) {
        if( ( $value = $db->dbQuery( $val->ValidateSQL( $update_hosts, $dbconn ), $dbconn ) ) === -1 ) {
         $error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['error'], $db_msg_err, NULL, NULL );
        }
       }

      } else {
       // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_pxegroup", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateString( $pxe_group_name ) === -1 ) { $list .= "<li>PXE Group Name field is invalid</li>"; $pxe_group_name_err = $e; }
       if( ( $val->ValidateIPv4( $pxe_server ) === -1 ) || ( $val->ValidateDomain( $pxe_server ) === -1 ) ) { $list .= "<li>PXE Server field is invalid</li>"; $pxe_server_err = $e; }
       if( $val->ValidateParagraph( $bootp_filename ) === -1 ) { $list .= "<li>BOOTP Filename field is invalid</li>"; $bootp_filename_err = $e; }
       if( $val->ValidateParagraph( $assign_subnet ) === -1 ) { $list .= "<li>Assign Subnet field is invalid</li>"; $assign_subnet_err = $e; }
 						$list .= "</ol>";
 						$error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['error'], $errors['val_str'] . $list, NULL, NULL );
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_pxegroup", '800', '800' );
 					$list .= "<ol>";
      if( empty( $pxe_group_name ) ) { $list .= "<li>PXE Group Name field is missing</li>"; $pxe_group_name_err = $e; }
      if( empty( $pxe_server ) ) { $list .= "<li>PXE Server Field is missing</li>"; $pxe_server_err = $e; }
 					if( empty( $bootp_filename ) ) { $list .= "<li>BOOTP File Name Field is missing</li>"; $bootp_filename_err = $e; }
 					$list .= "</ol>";
 					$error = $err->GenerateErrorLink( "help/help.html", "#config_pxegroup", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
     }
    }

    // create current list of dnssec security options
				if( $group === "admin" ) {
     $pxequery = "SELECT * FROM `conf_pxe_groups` ORDER BY `pxe-group-name` ASC";
				} else {
				 $pxequery = "SELECT * FROM `conf_pxe_groups` WHERE `group` = \"" . $group . "\" ORDER BY `pxe-group-name` ASC";
				}
 		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $pxequery, $dbconn ), $dbconn ) ) !== -1 ) {
     $pxelist = $db->dbArrayResultsAssoc( $current );
 		 }
 			$pxe_groups = $misc->GenJumpMenuBoxPXE( $pxelist, 'pxe_groups', $_GET['skin'] );

    // populate our subnets list
    $sub = "SELECT `subnet-name` FROM `conf_subnets` ORDER BY `subnet-name` ASC";
 		 if( ( $return = $db->dbQuery( $val->ValidateSQL( $sub, $dbconn ), $dbconn ) ) !== -1 ) {
     $subs = $db->dbArrayResultsAssoc( $return );
 		 }
    if( count( $subs ) === 0 ) {
     $assign_subnet = "No subnets defined";
    } else {
 			 $assign_subnet = $misc->GenDropMenuWSelectedSubnets( $subs, $assign_subnet, 'assign_subnet' );
    }

    /* create checkbox list of available groups */
				$groupsquery = "SELECT * FROM `auth_groups` WHERE `group` != \"admin\" AND `group` != \"" . $group . "\" ORDER BY `group` ASC";
    if( ( $res = $db->dbQuery( $val->ValidateSQL( $groupsquery, $dbconn ), $dbconn ) ) !== -1 ) {
					$groups = $db->dbArrayResultsAssoc( $res );
     $groups = $misc->EliminiateDuplicates( $groups );
     if( count( $groups ) !== 0 ) {
      // figure out which boxes are currently enabled
      if( !empty( $_GET['id'] ) ) {
       $sql = "SELECT * FROM `auth_groups_perms` WHERE `resource` = \"" . $subnet_name . "\"";
       if( ( $sql_res = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
        $select_groups = $db->dbArrayResultsAssoc( $sql_res );
       }
      }
						$select_groups = $misc->GenGroupsCheckBoxes( $groups, 'select_groups', $_GET['skin'], $select_groups, $group );
					} else {
					 $select_groups = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], "No groups defined", NULL, NULL );
					}
				} else {
     $select_groups = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['db_select_err'], NULL, NULL );
    }

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
    $tpl->assign( 'pxe_group_name', $val->ValidateXSS( $pxe_group_name ), NULL, NULL );
    $tpl->assign( 'pxe_server', $val->ValidateXSS( $pxe_server ), NULL, NULL );
 			$tpl->assign( 'bootp_filename', $val->ValidateXSS( $bootp_filename ), NULL, NULL );
 			$tpl->assign( 'assign_subnet', $assign_subnet, NULL, NULL );
    $tpl->assign( 'pxe_groups', $pxe_groups, NULL, NULL );
    $tpl->assign( 'select_groups', $select_groups, NULL, NULL );
    $tpl->assign( 'ex_group', $val->ValidateXSS( $ex_group ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'pxe_group_name_err', $pxe_group_name_err, NULL, NULL );
    $tpl->assign( 'pxe_server_err', $pxe_server_err, NULL, NULL );
    $tpl->assign( 'bootp_filename_err', $bootp_filename_err, NULL, NULL );
    $tpl->assign( 'assign_subnet_err', $assign_subnet_err, NULL, NULL );
    $tpl->assign( 'pxe_groups_err', $pxe_err, NULL, NULL );
    $tpl->assign( 'select_groups_err', $select_groups_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_pxe_groups", $dbconn );
    $db->dbFixTable( "auth_groups_perms", $dbconn );
			
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