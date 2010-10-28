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
 			$FILE = "manage.subnets.tpl";

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // decode our authentication token to get our group membership
				$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$group = base64_decode( $user_details[3] );

    // get an array of subnets the ISC DHCPD service may listen on
    $query = "SELECT `name`, `broadcast` FROM `conf_adapters` ORDER BY `broadcast` ASC";
    if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
     $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], $errors['db_select'], NULL, NULL );
    } else {
     $tmp = $db->dbArrayResultsAssoc( $value );
     // filter for empty stuff
     if( count( $tmp ) > 0 ) {
      for( $x = 0; $x < count( $tmp ); $x++ ) {
       if( !empty( $tmp[$x]['broadcast'] ) ) {
        $interface_list[$tmp[$x]['name']] = $tmp[$x]['broadcast'];
       }
      }
     } else {
      $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], "It seems the list of available network interfaces that the DHCPD service may bind to is unavailable", NULL, NULL );
     }
    }

    /* get array of resources available for this users group membership */
    if( $group === "admin" ) {
     $sql = "SELECT * FROM `auth_groups_perms` WHERE `type` = \"subnet\" AND `allowed` = \"" . $group . "\"";
    } else {
     $sql = "SELECT * FROM `auth_groups_perms` WHERE `type` = \"subnet\"";
    }
    if( ( $x = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
     if( $db->dbNumRows( $x ) > 0 ) { $resources = $db->dbArrayResultsAssoc( $x ); }
    }

    // Look for a GET id post to edit existing dnssec keys
    if( !empty( $_GET['id'] ) ) {
     if( $val->ValidateInteger( $_GET['id'] ) === -1 ) {
      $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
			   $query = "SELECT * FROM `conf_subnets` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
	     if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
       /* check resource permissions */
       if( $group !== "admin" ) {
        $resource = "SELECT * FROM `auth_groups_perms` WHERE ( `group` != \"" . $group . "\" OR `allowed` = \"" . $group . "\" ) AND `resource` = \"" . $data[0]['subnet-name'] . "\"";
       } else {
        $resource = "SELECT * FROM `auth_groups_perms` WHERE `resource` = \"" . $data[0]['subnet-name'] . "\"";
       }
       if( ( $value = $db->dbQuery( $val->ValidateSQL( $resource, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], $errors['db_select'], NULL, NULL );
       } else {
        $ch = $db->dbArrayResultsAssoc( $value );
        if( ( count( $ch[0] ) === 0 ) && ( $group !== "admin" ) && ( $data[0]['group'] !== $group ) ) {
         $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], $errors['auth_res'], NULL, NULL );
        } else {

 				 	  $id = $data[0]['id'];
 				 	  $subnet = $data[0]['subnet'];
 	  			  $subnet_mask = $data[0]['subnet-mask'];
 			  	  $dns_server_1 = $data[0]['dns-server-1'];
         $dns_server_2 = $data[0]['dns-server-2'];
 	  					$router = $data[0]['router'];
   						$subnet_name = $data[0]['subnet-name'];
	  						$pool_name = $data[0]['pool'];
         $enable_scope = $data[0]['enable-scope'];
	  						$enable_forwarding = $data[0]['ip-forwarding'];
	  						$scope_range_1 = $data[0]['scope-range-1'];
         $scope_range_2 = $data[0]['scope-range-2'];
									$bootp_filename = $data[0]['bootp-filename'];
    					$bootp_server = $data[0]['bootp-server'];
	  						$broadcast_address = $data[0]['broadcast-address'];
	  						$ntp_servers = $data[0]['ntp-servers'];
	  						$netbios_servers = $data[0]['netbios-name-servers'];
	  						$default_lease = $data[0]['default-lease-time'];
	  						$min_lease = $data[0]['min-lease-time'];
	  						$max_lease = $data[0]['max-lease-time'];
         $ex_group = $data[0]['group'];
         $select_groups = $ch;
   		   }        
       }
      }
     }
    }

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$subnet = $_POST['subnet'];
 				$subnet_mask = $_POST['subnet_mask'];
     $dns_server_1 = $_POST['dns_server_1'];
 				$dns_server_2 = $_POST['dns_server_2'];
 				$router = $_POST['router'];
     $subnet_name = $_POST['subnet_name'];
					$pool_name = $_POST['pool_name'];
     $enable_scope = $_POST['enable_scope'];
					$enable_forwarding = $_POST['enable_forwarding'];
     $scope_range_1 = $_POST['scope_range_1'];
     $scope_range_2 = $_POST['scope_range_2'];
					$bootp_filename = $_POST['bootp_filename'];
					$bootp_server = $_POST['bootp_server'];
					$broadcast_address = $_POST['broadcast_address'];
 				$ntp_servers = $_POST['ntp_servers'];
					$netbios_servers = $_POST['netbios_servers'];
 				$default_lease = $_POST['default_lease'];
					$min_lease = $_POST['min_lease'];
					$max_lease = $_POST['max_lease'];
				 $permissions = $_POST['select_groups'];
     $groups = $_POST['groups'];
    
     // check each post element
     if( ( !empty( $subnet ) ) && ( !empty( $subnet_mask ) ) && ( !empty( $dns_server_1 ) ) && ( !empty( $dns_server_2 ) ) && ( !empty( $router ) ) && ( !empty( $subnet_name ) ) && ( !empty( $enable_scope ) ) ) {
      
						// begin validation of configuration options
      if( ( $val->ValidateIPv4( $subnet ) !== -1 ) && ( $val->ValidateIPv4( $subnet_mask ) !== -1 ) && ( $val->ValidateDomain( $dns_server_1 ) !== -1 ) && ( $val->ValidateDomain( $dns_server_2 ) !== -1 ) && ( $val->ValidateIPv4( $router ) !== -1 ) && ( $val->ValidateParagraph( $subnet_name ) !== -1 ) && ( $val->ValidateAlphaChar( $pool_name ) !== -1 ) || ( $pool_name === "---------------" ) && ( $val->ValidateString( $enable_scope ) !== -1 ) && ( $val->ValidateIPv4( $scope_range_1 ) !== -1 ) &&  ( $val->ValidateIPv4( $scope_range_2 ) !== -1 ) && ( $val->ValidateParagraph( $bootp_filename ) !== -1 ) && ( $val->ValidateDomain( $bootp_server ) !== -1 ) && ( $val->ValidateString( $enable_forwarding ) !== -1 ) && ( $val->ValidateDomain( $broadcast_address ) !== -1 ) && ( $val->ValidateDomain( $ntp_servers ) !== -1 ) && ( $val->ValidateDomain( $netbios_servers ) !== -1 ) && ( $val->ValidateInteger( $default_lease ) !== -1 ) && ( $val->ValidateInteger( $min_lease ) !== -1 ) && ( $val->ValidateInteger( $max_lease ) !== -1 ) && ( $val->ValidateBroadcast2List( $interface_list, $subnet ) === 0 ) && ( $val->ValidateBroadcast2List( $interface_list, $broadcast_address ) === 0 ) ) {
       
       // fix pool var
       if( $pool_name === "---------------" ) { $pool_name = ""; }
       
 						// define our sql statements (exclude the group field if user is member of admin group)
							if( $group !== "admin" ) {
  						$insert = "INSERT INTO `conf_subnets` ( `subnet`, `subnet-mask`, `dns-server-1`, `dns-server-2`, `router`, `subnet-name`, `pool`, `enable-scope`, `scope-range-1`, `scope-range-2`, `ip-forwarding`, `broadcast-address`, `ntp-servers`, `netbios-name-servers`, `default-lease-time`, `min-lease-time`, `max-lease-time`, `group` ) VALUES ( \"" . $subnet . "\",\"" . $subnet_mask . "\", \"" . $dns_server_1 . "\", \"" . $dns_server_2 . "\", \"" . $router . "\", \"" . $subnet_name . "\", \"" . $pool_name . "\", \"" . $enable_scope . "\", \"" . $scope_range_1 . "\", \"" . $scope_range_2 . "\", \"" . $enable_forwarding . "\", \"" . $broadcast_address . "\", \"" . $ntp_servers . "\", \"" . $netbios_servers . "\", \"" . $default_lease . "\", \"" . $min_lease . "\", \"" . $max_lease . "\",  \"" . $group . "\" )";
  		    if( empty( $_POST['ex_group'] ) ) {
         $update = "UPDATE `conf_subnets` SET `subnet` = \"" . $subnet . "\", `subnet-mask` = \"" . $subnet_mask . "\", `dns-server-1` = \"" . $dns_server_1 . "\", `dns-server-2` = \"" . $dns_server_2 . "\", `router` = \"" . $router . "\", `subnet-name` = \"" . $subnet_name . "\", `pool` = \"" . $pool_name . "\", `enable-scope` = \"" . $enable_scope . "\", `scope-range-1` = \"" . $scope_range_1 . "\", `scope-range-2` = \"" . $scope_range_2 . "\", `ip-forwarding` = \"" . $enable_forwarding . "\", `broadcast-address` = \"" . $broadcast_address . "\", `ntp-servers` = \"" . $ntp_servers . "\", `netbios-name-servers` = \"" . $netbios_servers . "\", `default-lease-time` = \"" . $default_lease . "\", `min-lease-time` = \"" . $min_lease . "\", `max-lease-time` = \"" . $max_lease . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
        } else {
         $update = "UPDATE `conf_subnets` SET `subnet` = \"" . $subnet . "\", `subnet-mask` = \"" . $subnet_mask . "\", `dns-server-1` = \"" . $dns_server_1 . "\", `dns-server-2` = \"" . $dns_server_2 . "\", `router` = \"" . $router . "\", `subnet-name` = \"" . $subnet_name . "\", `pool` = \"" . $pool_name . "\", `enable-scope` = \"" . $enable_scope . "\", `scope-range-1` = \"" . $scope_range_1 . "\", `scope-range-2` = \"" . $scope_range_2 . "\", `bootp-filename` = \"" . $bootp_filename . "\", `bootp-server` = \"" . $bootp_server . "\", `ip-forwarding` = \"" . $enable_forwarding . "\", `broadcast-address` = \"" . $broadcast_address . "\", `ntp-servers` = \"" . $ntp_servers . "\", `netbios-name-servers` = \"" . $netbios_servers . "\", `default-lease-time` = \"" . $default_lease . "\", `min-lease-time` = \"" . $min_lease . "\", `max-lease-time` = \"" . $max_lease . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
        }
       } else {
  						$insert = "INSERT INTO `conf_subnets` ( `subnet`, `subnet-mask`, `dns-server-1`, `dns-server-2`, `router`, `subnet-name`, `enable-scope`, `scope-range-1`, `scope-range-2`, `bootp-filename`, `bootp_server`, `ip-forwarding`, `broadcast-address`, `ntp-servers`, `netbios-name-servers`, `default-lease-time`, `min-lease-time`, `max-lease-time` ) VALUES ( \"" . $subnet . "\",\"" . $subnet_mask . "\", \"" . $dns_server_1 . "\", \"" . $dns_server_2 . "\", \"" . $router . "\", \"" . $subnet_name . "\", \"" . $pool_name . "\", \"" . $enable_scope . "\", \"" . $scope_range_1 . "\", \"" . $scope_range_2 . "\", \"" . $bootp_filename . "\", \"" . $bootp_server . "\", \"" . $enable_forwarding . "\", \"" . $broadcast_address . "\", \"" . $ntp_servers . "\", \"" . $netbios_servers . "\", \"" . $default_lease . "\", \"" . $min_lease . "\", \"" . $max_lease . "\" )";
  		    $update = "UPDATE `conf_subnets` SET `subnet` = \"" . $subnet . "\", `subnet-mask` = \"" . $subnet_mask . "\", `dns-server-1` = \"" . $dns_server_1 . "\", `dns-server-2` = \"" . $dns_server_2 . "\", `router` = \"" . $router . "\", `subnet-name` = \"" . $subnet_name . "\", `pool` = \"" . $pool_name . "\", `enable-scope` = \"" . $enable_scope . "\", `scope-range-1` = \"" . $scope_range_1 . "\", `scope-range-2` = \"" . $scope_range_2 . "\", `bootp-filename` = \"" . $bootp_filename . "\", `bootp-server` = \"" . $bootp_server . "\", `ip-forwarding` = \"" . $enable_forwarding . "\", `broadcast-address` = \"" . $broadcast_address . "\", `ntp-servers` = \"" . $ntp_servers . "\", `netbios-name-servers` = \"" . $netbios_servers . "\", `default-lease-time` = \"" . $default_lease . "\", `min-lease-time` = \"" . $min_lease . "\", `max-lease-time` = \"" . $max_lease . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
 						}
 						$delete = "DELETE FROM `conf_subnets` WHERE `id` = \"" . $id . "\" LIMIT 1";
       $update_hosts = "UPDATE `conf_hosts` SET `subnet-name` = \"\" WHERE `subnet-name` = \"" . $subnet_name . "\"";

       /* do the permissions bit */
       foreach( $permissions as $key => $value ) {
        //if( empty( $value['perm_id'] ) ) {
        if( sizeof( $resources ) === 0 || in_array( $subnet_name, $resources ) ) {
         $set_permissions[] = "INSERT INTO `auth_groups_perms` ( `group`, `resource`, `type`, `allowed` ) VALUES ( \"" . $group . "\", \"" . $subnet_name . "\", \"subnet\", \"" . $value['group'] . "\" )";
        } else {
         $set_permissions[] = "UPDATE `auth_groups_perms` SET `allowed` = \"" . $value['group'] . "\" WHERE `id` = \"" . $value['perm_id'] . "\"";
         $delete_permissions[] = "DELETE FROM `auth_groups_perms` WHERE `id` = \"" . $value['id'] . "\" LIMIT 1";
        }
       }

 						// determine which button was clicked
 						if( !empty( $_POST['AddSubnet'] ) ) { $query = $insert; $grp_prms = $set_permissions; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
 						if( !empty( $_POST['EditSubnet'] ) ) { $query = $update; $grp_prms = $set_permissions; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelSubnet'] ) ) { $query = $delete; $grp_prms = $delete_permissions; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }

 						// process our querys
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_dns", $defined['error'], $db_msg_err, NULL, NULL );
        // attempt to update if record exists
        if( ( eregi( "duplicate", $db->dbCatchError() ) ) || ( !empty( $id ) ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
 									$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $errors['db_edit'], NULL, NULL );
 								}
 							}
       } else {
        if( !empty( $_POST['DelSubnet'] ) ) {
         if( ( $value = $db->dbQuery( $val->ValidateSQL( $delete, $dbconn ), $dbconn ) ) === -1 ) {
          $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $db_msg_err, NULL, NULL );
         }
        }
 							$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $db_msg_good, NULL, NULL );
 						}

       /* work on the permissions table */
       //echo "<pre>"; print_r( $grp_prms ); echo "</pre>";
       foreach( $grp_prms as $key => $value ) {
        if( ( $value = $db->dbQuery( $val->ValidateSQL( $value, $dbconn ), $dbconn ) ) === -1 ) {
         $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $db_msg_err, NULL, NULL );
        } else {
  							$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $db_msg_good, NULL, NULL );
  						}
       }

      } else {

						 // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_subnet", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateIPv4( $subnet ) === -1 ) { $list .= "<li>Subnet field is invalid</li>"; $subnet_err = $e; }
       if( $val->ValidateParagraph( $subnet_mask ) === -1 ) { $list .= "<li>Subnet mask field is invalid</li>"; $subnet_mask_err = $e; }
       if( $val->ValidateDomain( $dns_server_1 ) === -1 ) { $list .= "<li>DNS Server 1 field is invalid</li>"; $dns_server_1_err = $e; }
       if( $val->ValidateDomain( $dns_server_2 ) === -1 ) { $list .= "<li>DNS Server 2 field is invalid</li>"; $dns_server_2_err = $e; }
       if( $val->ValidateIPv4( $router ) === -1 ) { $list .= "<li>Router field is invalid</li>"; $router_err = $e; }
       if( $val->ValidateParagraph( $subnet_name ) === -1 ) { $list .= "<li>Subnet Name field is invalid</li>"; $subnet_name_err = $e; }
							if( $val->ValidateAlphaChar( $pool_name ) === -1 ) { $list .= "<li>Pool Name field is invalid</li>"; $pool_err = $e; }
       if( $val->ValidateString( $enable_scope ) === -1 ) { $list .= "<li>Enable Scope selection is invalid</li>"; $enable_scope_err = $e; }
       if( $val->ValidateIPv4( $scope_range_1 ) === -1 ) { $list .= "<li>Scope Range 1 field is invalid</li>"; $scope_range_1_err = $e; }
       if( $val->ValidateIPv4( $scope_range_2 ) === -1 ) { $list .= "<li>Scope Range 2 field is invalid</li>"; $scope_range_2_err = $e; }
							if( $val->ValidateParagraph( $bootp_name ) === -1 ) { $list .= "<li>BOOTP Filename field is invalid</li>"; $bootp_filename_err = $e; }
							if( $val->ValidateDomain( $bootp_server ) === -1 ) { $list .= "<li>BOOTP Server field is invalid</li>"; $bootp_server_err = $e; $xtra = 1; }
							if( $val->ValidateString( $enable_forwarding ) === -1 ) { $list .= "<li>Enable Forwarding selection is invalid</li>"; $enable_forwarding_err = $e; $xtra = 1; }
							if( $val->ValidateIPv4( $broadcast_address ) === -1 ) { $list .= "<li>Broadcast Address field is invalid</li>"; $broadcast_address_err = $e; $xtra = 1; }
							if( $val->ValidateDomain( $netbios_servers ) === -1 ) { $list .= "<li>Netbios Name Servers field is invalid</li>"; $netbios_servers_err = $e; $xtra = 1; }
							if( $val->ValidateDomain( $ntp_servers ) === -1 ) { $list .= "<li>NTP Servers field is invalid</li>"; $ntp_servers_err = $e; $xtra = 1; }
							if( $val->ValidateInteger( $default_lease ) === -1 ) { $list .= "<li>Default Lease field is invalid</li>"; $default_lease_err = $e; $xtra = 1; }
							if( $val->ValidateInteger( $max_lease ) === -1 ) { $list .= "<li>Max Lease field is invalid</li>"; $max_lease_err = $e; $xtra = 1; }
							if( $val->ValidateInteger( $min_lease ) === -1 ) { $list .= "<li>Min Lease field is invalid</li>"; $min_lease_err = $e; $xtra = 1; }
       if( $val->ValidateBroadcast2List( $interface_list, $subnet ) === -1 ) { $list .= "<li>You are attempting to add an subnet definition which is not configured on the local list of interface(s).<br><span class=copyright>** See list of adapters and their broadcast address at bottom right of page</span></li>"; $subnet_err = $e; }
       if( $val->ValidateBroadcast2List( $interface_list, $broadcast_address ) === -1 ) { $list .= "<li>You are attempting to add an subnet definition which is not configured on the local list of interface(s).<br><span class=copyright>** See list of adapters and their broadcast address at bottom right of page</span></li>"; $broadcast_address_err = $e; $xtra = 1; }
       $list .= "</ol>";
 						$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], "An error occured while validating fields, review details below:" . $list, NULL, NULL );
							// set our extras to be visable if one of them is broken
       if( $xtra === 1 ) { $JS .= " showdiv( 'extras' );"; }
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_subnet", '800', '800' );
 					$list .= "<ol>";
      if( empty( $subnet ) ) { $list .= "<li>Subnet field is missing</li>"; $subnet_err = $e; }
      if( empty( $subnet_mask ) ) { $list .= "<li>Subnet Mask Field is missing</li>"; $subnet_mask_err = $e; }
 					if( empty( $dns_server_1 ) ) { $list .= "<li>DNS Server 1 Field is missing</li>"; $dns_server_1_err = $e; }
 					if( empty( $dns_server_2 ) ) { $list .= "<li>DNS Server 2 Field is missing</li>"; $dns_server_2_err = $e; }
 					if( empty( $router ) ) { $list .= "<li>Router Field is missing</li>"; $router_err = $e; }
      if( empty( $subnet_name ) ) { $list .= "<li>Subnet Name Field is missing</li>"; $subnet_name_err = $e; }
      if( empty( $enable_scope ) ) { $list .= "<li>Enable Scope Selection is missing</li>"; $enable_scope_err = $e; }
      if( empty( $scope_range_1 ) ) { $list .= "<li>Subnet Name Field is missing</li>"; $scope_range_1_err = $e; }
      if( empty( $scope_range_2 ) ) { $list .= "<li>Subnet Name Field is missing</li>"; $scope_range_2_err = $e; }
	 				$list .= "</ol>";
 					$error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
     }
    }

    // create drop list of pools
				if( $group === "admin" ) {
					$poolquery = "SELECT `pool-name` FROM `conf_pools` ORDER BY `pool-name` ASC";
				} else {
					$poolquery = "SELECT `pool-name` FROM `conf_pools` WHERE `group` = \"" . $group . "\" OR `group` = \"\" ORDER BY `pool-name` ASC";
				}
				if( ( $res = $db->dbQuery( $val->ValidateSQL( $poolquery, $dbconn ), $dbconn ) ) !== -1 ) {
					$pools = $db->dbArrayResultsAssoc( $res );
					if( count( $pools ) !== 0 ) {
						$pool = $misc->GenDropMenuWSelectedPools( $pools, $pool_name, 'pool_name' );
					} else {
					 $pool = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], "No pools defined", NULL, NULL );;	
					}
				}

    // create current list of dnssec security options
				if( $group === "admin" ) {
     $subnetquery = "SELECT * FROM `conf_subnets` ORDER BY `subnet-name` ASC";
				} else {
				 $subnetquery = "SELECT * FROM `conf_subnets` WHERE `group` = \"" . $group . "\" ORDER BY `subnet-name` ASC";
				}
 		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $subnetquery, $dbconn ), $dbconn ) ) !== -1 ) {
     $subnetlist = $db->dbArrayResultsAssoc( $current );
 		 }

    /* check for additional subnets this group can access */
    $sql = "SELECT `resource`, `allowed` FROM `auth_groups_perms` WHERE `type` = \"subnet\" AND `allowed` = \"" . $group . "\" AND `group` != \"" . $group . "\"";
    if( ( $extra = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
     $extra = $db->dbArrayResultsAssoc( $extra );
     foreach( $extra as $key => $value ) {
      $sql = "SELECT * FROM `conf_subnets` WHERE `subnet-name` = \"" . $value['resource'] . "\" LIMIT 1";
      if( ( $s = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
       $subnetlist_remainder = array_merge( $misc->EliminiateDuplicates( $db->dbArrayResultsAssoc( $s ) ) );
      }
     }
    }

    // combine our arrays
    if( count( $subnetlist_remainder ) !== 0 ) {
     $subnetlist = array_merge( $subnetlist, $subnetlist_remainder );
    }
    //echo "<pre>"; print_r( $resources ); echo "</pre>";
 			$subnets = $misc->GenJumpMenuBoxSubnets( $subnetlist, 'subnets', $_GET['skin'] );

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

    // show the user the list of interfaces and their broadcast address
    if( ( $ilist = $misc->GenTableFromAssocArray( $interface_list ) ) === -1 ) {
     $adapters = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], "There are no interfaces configured", NULL, NULL );
    } else {
     $adapters = $misc->GenTableFromAssocArray( $interface_list );
    }

    // figure out our radio buttons
				if( $enable_scope === "true" ) { $enable_scope_true = "checked"; }
    if( ( $enable_scope === "false" ) || ( empty( $enable_scope ) ) ) { $enable_scope_false = "checked"; }
				if( $enable_forwarding === "true" ) { $enable_forwarding_true = "checked"; }
    if( ( $enable_forwarding === "false" ) || ( empty( $enable_forwarding ) ) ) { $enable_forwarding_false = "checked"; }

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
    $tpl->assign( 'subnet_name', $val->ValidateXSS( $subnet_name ), NULL, NULL );
    $tpl->assign( 'subnet', $val->ValidateXSS( $subnet ), NULL, NULL );
 			$tpl->assign( 'subnet_mask', $val->ValidateXSS( $subnet_mask ), NULL, NULL );
 			$tpl->assign( 'dns_server_1', $val->ValidateXSS( $dns_server_1 ), NULL, NULL );
 			$tpl->assign( 'dns_server_2', $val->ValidateXSS( $dns_server_2 ), NULL, NULL );
    $tpl->assign( 'router', $val->ValidateXSS( $router ), NULL, NULL );
    $tpl->assign( 'pool', $pool, NULL, NULL );
    $tpl->assign( 'enable_scope_true', $enable_scope_true, NULL, NULL );
    $tpl->assign( 'enable_scope_false', $enable_scope_false, NULL, NULL );
    $tpl->assign( 'scope_range_1', $val->ValidateXSS( $scope_range_1 ), NULL, NULL );
    $tpl->assign( 'scope_range_2', $val->ValidateXSS( $scope_range_2 ), NULL, NULL );
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
    $tpl->assign( 'adapters', $adapters, NULL, NULL );
    $tpl->assign( 'select_groups', $select_groups, NULL, NULL );
    $tpl->assign( 'ex_group', $val->ValidateXSS( $ex_group ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'subnet_err', $subnet_err, NULL, NULL );
    $tpl->assign( 'subnet_mask_err', $subnet_mask_err, NULL, NULL );
    $tpl->assign( 'dns_server_1_err', $dns_server_1_err, NULL, NULL );
    $tpl->assign( 'dns_server_2_err', $dns_server_2_err, NULL, NULL );
    $tpl->assign( 'router_err', $router_err, NULL, NULL );
    $tpl->assign( 'subnet_name_err', $subnet_name_err, NULL, NULL );
				$tpl->assign( 'pool_err', $pool_name_err, NULL, NULL );
    $tpl->assign( 'enable_scope_err', $enable_scope_err, NULL, NULL );
    $tpl->assign( 'scope_range_1_err', $scope_range_1_err, NULL, NULL );
    $tpl->assign( 'scope_range_2_err', $scope_range_2_err, NULL, NULL );
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
    $tpl->assign( 'select_groups_err', $select_groups_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_subnets", $dbconn );
    $db->dbFixTable( "conf_hosts", $dbconn );
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