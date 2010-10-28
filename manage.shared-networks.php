<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * subnet.add.php - DHCPD Add a new subnet
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
 	$tpl->assign( 'DESCRIPTION', "Manage Subnets", NULL, NULL );
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
 			$JS = " hidediv('extras'); hidediv('perms');";
 			$FILE = "manage.shared-networks.tpl";

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
      $error = $err->GenerateErrorLink( "help/help.html", "#config_shared", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
						if( $group === "admin" ) {
  				 $query = "SELECT * FROM `conf_shared_networks` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						} else {
						 $query = "SELECT * FROM `conf_shared_networks` WHERE `group` = \"" . $group . "\" AND `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						}
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#config_shared", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
 				 	$id = $data[0]['id'];
 				 	$shared_network = $data[0]['shared-network-name'];
       // populate list of assigned subnets by shared-network-name
       if( $group === "admin" ) {
        $sql = "SELECT `subnet-name`, `shared-network` FROM `conf_subnets` WHERE `shared-network` = \"" . $shared_network . "\"";
       } else {
        $sql = "SELECT `subnet-name`, `shared-network` FROM `conf_subnets` WHERE `shared-network` = \"" . $shared_network . "\" AND `group` = \"" . $group . "\"";
       }
       if( ( $nets = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_shared", $defined['error'], $errors['db_select'], NULL, NULL );
       } else {
        $subnet_checkboxes = $db->dbArrayResultsAssoc( $nets );
        $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], "You are currently editing the shared-network named '" . $shared_network . "'", NULL, NULL );
       }
 		   }
     }
    }

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$shared_network = $_POST['shared_network_name'];
     $subnet_checkboxes = $_POST['subnet_checkboxes'];
     
     // check each post element
     if( ( !empty( $shared_network ) ) && ( count( $subnet_checkboxes ) !== 0 ) ) {
      
						// begin validation of configuration options
      if( $val->ValidateHostname( $shared_network ) !== -1 ) {
       
 						// define our sql statements (exclude the group field if user is member of admin group)
							if( $group !== "admin" ) {
  						$insert = "INSERT INTO `conf_shared_networks` ( `shared-network-name`, `group` ) VALUES ( \"" . $shared_network . "\", \"" . $group . "\" )";
  		    $update = "UPDATE `conf_shared_networks` SET `shared-network-name` = \"" . $shared_network . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
       } else {
  						$insert = "INSERT INTO `conf_shared_networks` ( `shared-network-name` ) VALUES ( \"" . $shared_network . "\" )";
  		    $update = "UPDATE `conf_shared_networks` SET `shared-network-name` = \"" . $shared_network . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
 						}
 						$delete = "DELETE FROM `conf_shared_networks` WHERE `id` = \"" . $id . "\" LIMIT 1";
       
 						// determine which button was clicked
 						if( !empty( $_POST['AddShared'] ) ) { $new = TRUE; $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
 						if( !empty( $_POST['EditShared'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelShared'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }
       //echo "MAIN: " . $query . "<hr>";
       // initialize a db connection handle
       $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

 						// process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['error'], $db_msg_err, NULL, NULL );
        
        // attempt to update if record exists
        if( ( eregi( "duplicate", $db->dbCatchError() ) ) || ( !empty( $id ) ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
          $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $errors['db_edit'], NULL, NULL );
          
          // process array of subnets to update the `conf_subnets.shared-network` field
          foreach( $subnet_checkboxes as $key => $value ) {
           // figure out if things need to be removed from the `conf_subnets` table
           if( $group === "admin" ) {
            $sql = "SELECT `subnet-name`, `shared-network` FROM `conf_subnets` WHERE `shared-network` = \"" . $shared_network . "\"";
           } else {
            $sql = "SELECT `subnet-name`, `shared-network` FROM `conf_subnets` WHERE `shared-network` = \"" . $shared_network . "\" AND `group` = \"" . $group . "\"";
           }
           if( ( $nets = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
            $remove_items = $db->dbArrayResultsAssoc( $nets );
            if( count( $remove_items ) !== 0 ) {
             foreach( $remove_items as $key => $value ) {
              if( ( !in_array( $shared_network, $value ) ) && ( $new !== TRUE ) ) {
               $update_subnet = "UPDATE `conf_subnets` SET `shared-network` = \"\" WHERE `subnet-name` = \"" . $value['subnet-name'] . "\" LIMIT 1";
              } else {
               $update_subnet = "UPDATE `conf_subnets` SET `shared-network` = \"" . $value['subnet-name'] . "\" WHERE `subnet-name` = \"" . $value['subnet-name'] . "\" LIMIT 1";
              }
              if( ( $value = $db->dbQuery( $val->ValidateSQL( $update_subnet, $dbconn ), $dbconn ) ) !== -1 ) {
    				  					$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $errors['db_edit'], NULL, NULL );
              } else {
               $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $errors['db_edit_err'], NULL, NULL );
              }
             }
            }
           }
  								}
         }
 							}
       } else {
        
        // process checkboxes
        foreach( $subnet_checkboxes as $key => $value ) {
         if( $group === "admin" ) {
          $sql = "UPDATE `conf_subnets` SET `shared-network` = \"" . $shared_network . "\" WHERE `subnet-name` = \"" . $value . "\" LIMIT 1";
         } else {
          $sql = "UPDATE `conf_subnets` SET `shared-network` = \"" . $shared_network . "\", `group` = \"" . $group . "\" WHERE `subnet-name` = \"" . $value . "\" LIMIT 1";
         }
         if( ( $nets = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
          $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $errors['db_insert'], NULL, NULL );
         } else {
          $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $errors['db_insert_err'], NULL, NULL );
         }
        }
        
        // figure out if things need to be removed from the `conf_subnets` table
        if( $group === "admin" ) {
         $sql = "SELECT `subnet-name`, `shared-network` FROM `conf_subnets` WHERE `shared-network` = \"" . $shared_network . "\"";
        } else {
         $sql = "SELECT `subnet-name`, `shared-network` FROM `conf_subnets` WHERE `shared-network` = \"" . $shared_network . "\" AND `group` = \"" . $group . "\"";
        }
        if( ( $nets = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
         $remove_items = $db->dbArrayResultsAssoc( $nets );
         if( count( $remove_items ) !== 0 ) {
          foreach( $remove_items as $key => $value ) {
           if( ( !in_array( $value['subnet-name'], $subnet_checkboxes ) ) && ( $new !== TRUE ) ) {
            $update_subnet = "UPDATE `conf_subnets` SET `shared-network` = \"\" WHERE `subnet-name` = \"" . $value['subnet-name'] . "\" LIMIT 1";
           } else {
            $update_subnet = "UPDATE `conf_subnets` SET `shared-network` = \"" . $shared_network . "\" WHERE `subnet-name` = \"" . $value['subnet-name'] . "\" LIMIT 1";
           }
           if( ( $value = $db->dbQuery( $val->ValidateSQL( $update_subnet, $dbconn ), $dbconn ) ) !== -1 ) {
    							 $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $errors['db_edit'], NULL, NULL );
           } else {
            $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $errors['db_edit_err'], NULL, NULL );
           }
          }
         }
        }
        
        // delete data from `conf_subnets` if shared network is removed
        if( !empty( $_POST['DelShared'] ) ) {
         foreach( $subnet_checkboxes as $key => $value ) {
          $update_subnet = "UPDATE `conf_subnets` SET `shared-network` = \"\" WHERE `subnet-name` = \"" . $value . "\" LIMIT 1";
          if( ( $value = $db->dbQuery( $val->ValidateSQL( $update_subnet, $dbconn ), $dbconn ) ) !== -1 ) {
   								$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $errors['db_del'], NULL, NULL );            
          } else {
           $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $errors['db_del_err'], NULL, NULL );
          }
         }
        }
 						}
      } else {

						 // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_subnet", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateHostname( $shared_network ) === -1 ) { $list .= "<li>Shared Network field is invalid</li>"; $subnet_err = $e; }
       $list .= "</ol>";
 						$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], "An error occured while validating fields, review details below:" . $list, NULL, NULL );
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_subnet", '800', '800' );
 					$list .= "<ol>";
      if( empty( $shared_network ) ) { $list .= "<li>Shared network field is missing</li>"; $subnet_err = $e; }
	 				$list .= "</ol>";
 					$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
     }
    }

    // create current list of shared networks
				if( $group === "admin" ) {
     $sharedquery = "SELECT * FROM `conf_shared_networks` ORDER BY `shared-network-name` ASC";
				} else {
				 $sharedquery = "SELECT * FROM `conf_shared_networks` WHERE `group` = \"" . $group . "\" ORDER BY `shared-network-name` ASC";
				}
 		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $sharedquery, $dbconn ), $dbconn ) ) !== -1 ) {
     $sharedlist = $db->dbArrayResultsAssoc( $current );
 		 }
 			$shared_networks = $misc->GenJumpMenuBoxSharedNetworks( $sharedlist, 'shared_networks', $_GET['skin'] );

    // create current list of subnets
				if( $group === "admin" ) {
     $subnetquery = "SELECT * FROM `conf_subnets` ORDER BY `subnet-name` ASC";
				} else {
				 $subnetquery = "SELECT * FROM `conf_subnets` WHERE `group` = \"" . $group . "\" ORDER BY `subnet-name` ASC";
				}
 		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $subnetquery, $dbconn ), $dbconn ) ) !== -1 ) {
     $subnetlist = $db->dbArrayResultsAssoc( $current );
 		 }

    // fix $_POST['checkboxes'] array
    if( ( empty( $_GET['id'] ) ) && ( count( $subnet_checkboxes ) !== 0 ) ) {
     foreach( $subnet_checkboxes as $key => $value ) {
      $fixed[$key]['subnet-name'] = $value;
      $fixed[$key]['shared-network'] = $shared_network;
     }
     $subnet_checkboxes = $fixed;
    }
    
 			$subnet_checks = $misc->GenSubnetCheckBoxes( $subnetlist, 'subnet_checkboxes[]', $_GET['skin'], $subnet_checkboxes );

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

    // configure radio buttons
    if( $enable_forwarding === "true" ) { $enable_forwarding_true = "checked"; }
    if( ( $enable_forwarding === "false" ) || ( empty( $enable_forwarding ) ) ) { $enable_forwarding_false = "checked"; }

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
    $tpl->assign( 'shared_network_name', $val->ValidateXSS( $shared_network ), NULL, NULL );
    $tpl->assign( 'shared_networks', $shared_networks, NULL, NULL );
    $tpl->assign( 'subnet_checkboxes', $subnet_checks, NULL, NULL );
    $tpl->assign( 'dns_server_1', $val->ValidateXSS( $dns_server_1 ), NULL, NULL );
 			$tpl->assign( 'dns_server_2', $val->ValidateXSS( $dns_server_2 ), NULL, NULL );
    $tpl->assign( 'router', $val->ValidateXSS( $router ), NULL, NULL );
    $tpl->assign( 'bootp_filename', $val->ValidateXSS( $bootp_filename ), NULL, NULL );
				$tpl->assign( 'bootp_server', $val->ValidateXSS( $bootp_server ), NULL, NULL );
    $tpl->assign( 'subnets', $subnets, NULL, NULL );
				$tpl->assign( 'enable_forwarding_true', $val->ValidateXSS( $enable_forwarding_true ), NULL, NULL );
				$tpl->assign( 'enable_forwarding_false', $val->ValidateXSS( $enable_forwarding_false ), NULL, NULL );
    $tpl->assign( 'broadcast_address', $val->ValidateXSS( $broadcast_address ), NULL, NULL );
    $tpl->assign( 'netbios_servers', $val->ValidateXSS( $netbios_servers ), NULL, NULL );
				$tpl->assign( 'ntp_servers', $val->ValidateXSS( $ntp_servers ), NULL, NULL );
				$tpl->assign( 'default_lease', $val->ValidateXSS( $default_lease ), NULL, NULL );
    $tpl->assign( 'min_lease', $val->ValidateXSS( $min_lease ), NULL, NULL );
				$tpl->assign( 'max_lease', $val->ValidateXSS( $max_lease ), NULL, NULL );
				$tpl->assign( 'select_groups', $select_groups, NULL, NULL );
    $tpl->assign( 'ex_group', $val->ValidateXSS( $ex_group ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'shared_network_name_err', $shared_network_name_err, NULL, NULL );
    $tpl->assign( 'shared_networks_err', $shared_networks_err, NULL, NULL );
    $tpl->assign( 'dns_server_1_err', $dns_server_1_err, NULL, NULL );
    $tpl->assign( 'dns_server_2_err', $dns_server_2_err, NULL, NULL );
    $tpl->assign( 'router_err', $router_err, NULL, NULL );
    $tpl->assign( 'bootp_filename_err', $val->ValidateXSS( $bootp_filename_err ), NULL, NULL );
				$tpl->assign( 'bootp_server_err', $val->ValidateXSS( $bootp_server_err ), NULL, NULL );
    $tpl->assign( 'subnets_err', $subnets_err, NULL, NULL );
    $tpl->assign( 'enable_forwarding_err', $enable_forwarding_err, NULL, NULL );
				$tpl->assign( 'broadcast_address_err', $broadcast_address_err, NULL, NULL );
				$tpl->assign( 'netbios_servers_err', $netbios_servers_err, NULL, NULL );
				$tpl->assign( 'ntp_servers_err', $ntp_servers_err, NULL, NULL );
				$tpl->assign( 'default_lease_err', $default_lease_err, NULL, NULL );
				$tpl->assign( 'max_lease_err', $max_lease_err, NULL, NULL );
				$tpl->assign( 'min_lease_err', $min_lease_err, NULL, NULL );
    $tpl->assign( 'subnet_checkboxes_err', $subnet_checkboxes_err, NULL, NULL );
				$tpl->assign( 'select_groups_err', $select_groups_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_shared_networks", $dbconn );
    $db->dbFixTable( "conf_subnets", $dbconn );
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