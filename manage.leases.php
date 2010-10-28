<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * hosts.search.php - DHCPD Search for static host
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
 	$tpl->assign( 'DESCRIPTION', "Manage Leases", NULL, NULL );
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
   if( ( $level->ChkLevel( $_SESSION['token'] ) === "admin" ) || ( $level->ChkLevel( $_SESSION['token'] ) === "user" ) ) {
    
 			// define some variables for the template etc.
 			$JS = " hidediv('extras'); hidediv('perms');";
 			$FILE = "manage.leases.tpl"; 

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // decode our authentication token to get our group membership
				$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$group = base64_decode( $user_details[3] );

    // attempt to process leases if file changed
    $misc->GetCurrentLeases( $defined['leases'] );

    // Look for a GET id post to edit existing dnssec keys
    if( !empty( $_GET['id'] ) ) {
     if( $val->ValidateInteger( $_GET['id'] ) === -1 ) {
      $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
						if( $group === "admin" ) {
  				 $query = "SELECT * FROM `conf_leases` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						} else {
						 $query = "SELECT * FROM `conf_leases` WHERE `group` = \"" . $group . "\" AND `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						}
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
 				 	$id = $data[0]['id'];
 				 	$hostname = $data[0]['hostname'];
 				  $hardware = $data[0]['hardware'];
 				  $ip = $data[0]['ip'];
       $state = $data[0]['current-state'];
							$next_state = $data[0]['next-state'];
							$start = $data[0]['start'];
							$end = $data[0]['end'];
       $cltt = $data[0]['cltt'];
							$abandoned = $data[0]['abandoned'];
							$circut_id = $data[0]['circut-id'];
							$remote_id = $data[0]['remote-id'];
							$ddns_text = $data[0]['ddns-text'];
							$ddns_fwd_name = $data[0]['ddns-fwd-name'];
							$ddns_client_fqdn = $data[0]['ddns-client-fqdn'];
							$ddns_rev_name = $data[0]['ddns-rev-name'];
       $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['good'], "You are currently editing record #" . $id, NULL, NULL );
	 	   }
     }
    }

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				
					// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$hostname = $_POST['hostname'];
 				$hardware = $_POST['hardware'];
     $ip = $_POST['ip'];
     $state = $_POST['state'];
					$next_state = $_POST['next_state'];
					$start = $_POST['start'];
					$end = $_POST['end'];
     $cltt = $_POST['cltt'];
					$abandoned = $_POST['abandoned'];
					$circut_id = $_POST['circut_id'];
					$remote_id = $_POST['remote_id'];
					$ddns_text = $_POST['ddns_text'];
					$ddns_fwd_name = $_POST['ddns_fwd_name'];
					$ddns_client_fqdn = $_POST['ddns_client_fqdn'];
					$ddns_rev_name = $_POST['ddns_rev_name'];
     $search = $_POST['search'];
     $startdate = $_POST['startdate'];
     $enddate = $_POST['enddate'];
    
     // perform search if not empty
     if( !empty( $_POST['SrchLeases'] ) ) {
      
      if( ( empty( $search ) ) && ( empty( $startdate ) ) && ( empty( $enddate ) ) ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['error'], "Empty search fields, please enter an IP or Hostname to search or enter a start and end date for a list of leases between those dates", NULL, NULL );
      } else {
 						// perform validation on search string(s)
       if( ( $val->ValidateIPv4( $search ) !== -1 ) || ( $val->ValidateMACFormats( $search ) !== -1 ) || ( $val->ValidateHostname( $search ) !== -1 ) && ( $val->ValidateDate( $startdate ) !== -1 ) && ( $val->ValidateDate( $enddate ) !== -1 ) ) {

        /* define our search query */
 							if( $group !== "admin" ) {
         $having = " HAVING `group` = \"" . $group . "\"";
  						}

        // search by dates
        if( ( !empty( $startdate ) ) || ( !empty( $enddate ) ) ) {
         $dates = " `start` > \"" . $startdate . "\" AND `end` < \"" . $enddate . "\"";
        }
        
        // provide fields search
        if( !empty( $search ) ) {
         $main = "`hostname` LIKE \"" . $search . "\" OR `hardware` LIKE \"" . $search . "\" OR `ip` LIKE \"" . $search . "\"";
        }
        
        // and apply the attributes
        $query = "SELECT * FROM `conf_leases` WHERE $main$dates$having ORDER BY `hostname` ASC";

        // process our query
  						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
         $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['error'], $errors['db_search_err'], NULL, NULL );
        } else {
         // process results of search
         if( $db->dbNumRows( $value ) >= 1 ) {
          $data = $db->dbArrayResultsAssoc( $value );
          if( $db->dbNumRows( $value ) === 1 ) {
 									 $id = $data[0]['id'];
           $id = $data[0]['id'];
     				 	$hostname = $data[0]['hostname'];
     				  $hardware = $data[0]['hardware'];
     				  $ip = $data[0]['ip'];
           $state = $data[0]['current-state'];
    							$next_state = $data[0]['next-state'];
    							$start = $data[0]['start'];
    							$end = $data[0]['end'];
           $cltt = $data[0]['cltt'];
    							$abandoned = $data[0]['abandoned'];
    							$circut_id = $data[0]['circut-id'];
    							$remote_id = $data[0]['remote-id'];
    							$ddns_text = $data[0]['ddns-text'];
    							$ddns_fwd_name = $data[0]['ddns-fwd-name'];
    							$ddns_client_fqdn = $data[0]['ddns-client-fqdn'];
    							$ddns_rev_name = $data[0]['ddns-rev-name'];
 										$error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['good'], "Your search for '" . $val->ValidateXSS( $search ) . "' returned '" . $db->dbNumRows( $value ) . "' results. The form has been populated for you to edit record #" . $id, NULL, NULL );
          } else {
           $lease_list = $misc->GenJumpMenuBoxLEASES( $data, 'lease_list', $_GET['skin'] );
           $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['good'], "Your search for '" . $val->ValidateXSS( $search ) . "' returned '" . $db->dbNumRows( $value ) . "' results. Please select the host you wish to edit from the select box below", NULL, NULL );
          }

         } else {
          $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['error'], $errors['sql_empty'], NULL, NULL );
         }
  						}
       } else {
        $search_err = $err->GenerateErrorImg( $defined['error'], "help/help.html#lease_search", '800', '800' );
        $list = "<ol><li>Search string is invalid. Allowed formats:<br>MAC Address: xx:xx:xx:xx:xx<br>IPv4 Address: xxx.xxx.xxx.xxx<br>Hostname: [0-9a-z]</li></ol>";
        $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['error'], $errors['val_host'] . $list, NULL, NULL );
       }
      }
     } else {
				
      // check each post element
      if( ( !empty( $hardware ) ) && ( !empty( $ip ) ) && ( !empty( $start ) ) && ( !empty( $end ) ) ) {

       // begin validation of configuration options
       if( ( $val->ValidateMACFormats( $hardware ) !== -1 ) && ( $val->ValidateIPv4( $ip ) !== -1 ) && ( ( $val->ValidateParagraph( $start ) !== -1 ) || ( $val->ValidateParagraph( $end ) !== -1 ) ) ) {

        // since no errors for the mac address were recieved assign the *possibly fixed value
        $mac_address = $val->ValidateMACFormats( $mac_address );

  						// define our sql statements (if group is admin, discard that field)
								if( $group === "admin" ) {
         $insert = "INSERT INTO `conf_leases` ( `ip`, `start`, `end`, `cltt`, `current-state`, `next-state`, `hardware`, `hostname`, `abandoned`, `circut-id`, `remote-id`, `ddns-text`, `ddns-fwd-name`, `ddns-client-fqdn`, `ddns-rev-name` ) VALUES ( \"" . $ip . "\", \"" . $start . "\", \"" . $end . "\", \"" . $cltt . "\", \"" . $state . "\", \"" . $next_state . "\", \"" . $hardware . "\", \"" . $hostname . "\", \"" . $abandoned . "\", \"" . $circut_id . "\", \"" . $remote_id . "\", \"" . $ddns_text . "\", \"" . $ddns_fwd_name . "\", \"" . $ddns_client_fqdn . "\", \"" . $ddns_rev_name . "\" )";
 					   $update = "UPDATE `conf_leases` SET `ip` = \"" . $ip . "\", `start` = \"" . $start . "\", `end` = \"" . $end . "\", `cltt` = \"" . $cltt . "\", `current-state` = \"" . $state . "\", `next-state` = \"" . $next_state . "\", `hardware` = \"" . $hardware . "\", `hostname` = \"" . $hostname . "\", `abandoned` = \"" . $abandoned . "\", `circut-id` = \"" . $circut_id . "\", `remote-id` = \"" . $remote_id . "\", `ddns-text` = \"" . $ddns_text . "\", `ddns-fwd-name` = \"" . $ddns_fwd_name . "\", `ddns-client-fqdn` = \"" . $ddns_client_fqdn . "\", `ddns-rev-name` = \"" . $ddns_rev_name . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
  						} else {
         $insert = "INSERT INTO `conf_leases` ( `ip`, `start`, `end`, `cltt`, `current-state`, `next-state`, `hardware`, `hostname`, `abandoned`, `circut-id`, `remote-id`, `ddns-text`, `ddns-fwd-name`, `ddns-client-fqdn`, `ddns-rev-name`, `group` ) VALUES ( \"" . $ip . "\", \"" . $start . "\", \"" . $end . "\", \"" . $cltt . "\", \"" . $state . "\", \"" . $next_state . "\", \"" . $hardware . "\", \"" . $hostname . "\", \"" . $abandoned . "\", \"" . $circut_id . "\", \"" . $remote_id . "\", \"" . $ddns_text . "\", \"" . $ddns_fwd_name . "\", \"" . $ddns_client_fqdn . "\", \"" . $ddns_rev_name . "\", \"" . $group . "\" )";
 					   $update = "UPDATE `conf_leases` SET `ip` = \"" . $ip . "\", `start` = \"" . $start . "\", `end` = \"" . $end . "\", `cltt` = \"" . $cltt . "\", `current-state` = \"" . $state . "\", `next-state` = \"" . $next_state . "\", `hardware` = \"" . $hardware . "\", `hostname` = \"" . $hostname . "\", `abandoned` = \"" . $abandoned . "\", `circut-id` = \"" . $circut_id . "\", `remote-id` = \"" . $remote_id . "\", `ddns-text` = \"" . $ddns_text . "\", `ddns-fwd-name` = \"" . $ddns_fwd_name . "\", `ddns-client-fqdn` = \"" . $ddns_client_fqdn . "\", `ddns-rev-name` = \"" . $ddns_rev_name . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
  						}
								$delete = "DELETE FROM `conf_leases` WHERE `id` = \"" . $id . "\" LIMIT 1";

  						// determine which button was clicked
  						if( !empty( $_POST['AddLease'] ) ) { $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
  						if( !empty( $_POST['EditLease'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
  						if( !empty( $_POST['DelLease'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }

  						// process our query
  						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
         $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['error'], $db_msg_err, NULL, NULL );
         // attempt to update if record exists
         if( ( eregi( "duplicate", $db->dbCatchError() ) ) || ( !empty( $id ) ) ) {
  								if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
  							  $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['error'], $errors['db_edit_err'], NULL, NULL );
          }
  							}
        } else {
         // set a flag to rewrite the leases file
         $db->dbQuery( $val->ValidateSQL( "UPDATE `conf_leases_properties` SET `recreate` = \"TRUE\" WHERE `id` = 1 LIMIT 1", $dbconn ), $dbconn );
         
  							// database update/insert/delete sucessful
         $error = $err->GenerateErrorLink( "help/help.html", "#lease_search", $defined['good'], $db_msg_good, NULL, NULL );
  						} 

       } else {
        // find validation errors
  						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#lease_search", '800', '800' );
   					$list .= "<ol>";
        if( $val->ValidateHostname( $hostname ) === -1 ) { $list .= "<li>" . $errors['val_hostname'] . "</li>"; $hostname_err = $e; }
        if( $val->ValidateMACFormats( $hardware ) === -1 ) { $list .= "<li>" . $errors['val_mac'] . "</li>"; $hardware_err = $e; }
        if( $val->ValidateIPv4( $ip_address ) === -1 ) { $list .= "<li>" . $errors['val_ipaddr'] . "</li>"; $ip_err = $e; }
        if( $val->ValidateParagraph( $start ) === -1 ) { $list .= "<li>Start Date field is invalid</li>"; $start_err = $e; }
								if( $val->ValidateParagraph( $end ) === -1 ) { $list .= "<li>End Date field is invalid</li>"; $end_err = $e; }
  						$list .= "</ol>";
  						$error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['val_str'] . $list, NULL, NULL );
       }
      } else {
       // look to see which fields were empty
  					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#host_search", '800', '800' );
  					$list .= "<ol>";
       if( empty( $hardware ) ) { $list .= "<li>MAC Address Field is missing</li>"; $hardware_err = $e; }
	  				if( empty( $ip ) ) { $list .= "<li>IP Address Field is missing</li>"; $ip_err = $e; }
       if( empty( $start ) ) { $list .= "<li>Start date Field is missing</li>"; $start_err = $e; }
							if( empty( $end ) ) { $list .= "<li>End date Field is missing</li>"; $end_err = $e; }
	  				$list .= "</ol>";
 	 				$error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
      }
     }
    }

    // create current list of static hosts if $hosts_list is empty
    if( count( $lease_list ) === 0 ) {
     if( $group === "admin" ) {
 					$leasequery = "SELECT * FROM `conf_leases` ORDER BY `hostname` ASC";
					} else {
					 $leasequery = "SELECT * FROM `conf_leases` WHERE `group` = \"" . $group . "\" OR `group` = '' ORDER BY `hostname` ASC"; 
					}
  		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $leasequery, $dbconn ), $dbconn ) ) !== -1 ) {
      $leaselist = $db->dbArrayResultsAssoc( $current );
  		 }
  			$lease_list = $misc->GenJumpMenuBoxLEASES( $leaselist, 'lease_list', $_GET['skin'] );
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
	
	   // determine radio fields
				if( $state === "active" ) { $state_true = "checked"; }
    if( ( $state === "free" ) || ( empty( $state ) ) ) { $state_false = "checked"; }
				if( $next_state === "active" ) { $next_state_true = "checked"; }
    if( ( $next_state === "free" ) || ( empty( $next_state ) ) ) { $next_state_false = "checked"; }
				if( $abandoned === "true" ) { $abandoned_true = "checked"; }
    if( ( $abandoned === "false" ) || ( empty( $abandoned ) ) ) { $abandoned_false = "checked"; }
			
    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
    $tpl->assign( 'hostname', $val->ValidateXSS( $hostname ), NULL, NULL );
    $tpl->assign( 'hardware', $val->ValidateXSS( $hardware ), NULL, NULL );
	 		$tpl->assign( 'ip', $val->ValidateXSS( $ip ), NULL, NULL );
				$tpl->assign( 'state_true', $val->ValidateXSS( $state_true ), NULL, NULL );
				$tpl->assign( 'state_false', $val->ValidateXSS( $state_false ), NULL, NULL );
				$tpl->assign( 'next_state_true', $val->ValidateXSS( $next_state_true ), NULL, NULL );
				$tpl->assign( 'next_state_false', $val->ValidateXSS( $next_state_false ), NULL, NULL );
				$tpl->assign( 'start', $val->ValidateXSS( $start ), NULL, NULL );
				$tpl->assign( 'end', $val->ValidateXSS( $end ), NULL, NULL );
				$tpl->assign( 'cltt', $val->ValidateXSS( $cltt ), NULL, NULL );
				$tpl->assign( 'abandoned_true', $val->ValidateXSS( $abandoned_true ), NULL, NULL );
				$tpl->assign( 'abandoned_false', $val->ValidateXSS( $abandoned_false ), NULL, NULL );
				$tpl->assign( 'circut_id', $val->ValidateXSS( $circut_id ), NULL, NULL );
				$tpl->assign( 'remote_id', $val->ValidateXSS( $remote_id ), NULL, NULL );
				$tpl->assign( 'ddns_text', $val->ValidateXSS( $ddns_text ), NULL, NULL );
				$tpl->assign( 'ddns_fwd_name', $val->ValidateXSS( $ddns_fwd_name ), NULL, NULL );
				$tpl->assign( 'ddns_client_fqdn', $val->ValidateXSS( $ddns_client_fqdn ), NULL, NULL );
				$tpl->assign( 'ddns_rev_name', $val->ValidateXSS( $ddns_rev_name ), NULL, NULL );
    $tpl->assign( 'lease_list', $lease_list, NULL, NULL );
				$tpl->assign( 'select_groups', $select_groups, NULL, NULL );
    $tpl->assign( 'ex_group', $val->ValidateXSS( $ex_group ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'hostname_err', $hostname_err, NULL, NULL );
    $tpl->assign( 'hardware_err', $hardware_err, NULL, NULL );
    $tpl->assign( 'ip_err', $ip_err, NULL, NULL );
    $tpl->assign( 'state_err', $state_err, NULL, NULL );
    $tpl->assign( 'next_state_err', $next_state_err, NULL, NULL );
    $tpl->assign( 'start_err', $start_err, NULL, NULL );
    $tpl->assign( 'end_err', $end_err, NULL, NULL );
				$tpl->assign( 'cltt_err', $cltt_err, NULL, NULL );
				$tpl->assign( 'abandoned_err', $abandoned_err, NULL, NULL );
				$tpl->assign( 'circut_id_err', $circut_id_err, NULL, NULL );
				$tpl->assign( 'remote_id_err', $remote_id_err, NULL, NULL );
				$tpl->assign( 'ddns_text_err', $ddns_text_err, NULL, NULL );
				$tpl->assign( 'ddns_fwd_name_err', $ddns_fwd_name_err, NULL, NULL );
				$tpl->assign( 'ddns_client_fqdn_err', $ddns_client_fqdn_err, NULL, NULL );
				$tpl->assign( 'ddns_rev_name_err', $ddns_rev_name_err, NULL, NULL );
    $tpl->assign( 'lease_list_err', $lease_list_err, NULL, NULL );
    $tpl->assign( 'search_err', $search_err, NULL, NULL );
				$tpl->assign( 'select_groups_err', $select_groups_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_leases", $dbconn );
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