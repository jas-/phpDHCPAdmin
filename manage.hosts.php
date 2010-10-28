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
 	$tpl->assign( 'DESCRIPTION', "Manage Static Hosts", NULL, NULL );
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
 			$JS = " hidediv('perms');";
 			$FILE = "manage.hosts.tpl"; 

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // decode our authentication token to get our group membership
				$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$group = base64_decode( $user_details[3] );

    // Look for a GET id post to edit existing host records
    if( !empty( $_GET['id'] ) ) {
     if( $val->ValidateInteger( $_GET['id'] ) === -1 ) {
      $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
						if( ( $group === "admin" ) || ( !empty( $_GET['allow'] ) ) && ( $val->ValidateInteger( $_GET['allow'] ) === 0 ) ) {
  				 $query = "SELECT * FROM `conf_hosts` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						} else {
						 $query = "SELECT * FROM `conf_hosts` WHERE `group` = \"" . $group . "\" AND `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						}
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
       /* check resource permissions */
       if( $group !== "admin" ) {
        $resource = "SELECT * FROM `auth_groups_perms` WHERE ( `group` = \"" . $group . "\" OR `allowed` = \"" . $group . "\" ) AND `type` = \"host\" AND `resource` = \"" . $data[0]['mac-address'] . "\"";
       } else {
        $resource = "SELECT * FROM `auth_groups_perms` WHERE `resource` = \"" . $data[0]['mac-address'] . "\"";
       }
       if( ( $value = $db->dbQuery( $val->ValidateSQL( $resource, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], $errors['db_select'], NULL, NULL );
       } else {
        $ch = $db->dbArrayResultsAssoc( $value );
        if( ( count( $ch ) > 0 ) || ( $data[0]['group'] === $group ) ) {
   				 	$id = $data[0]['id'];
   				 	$hostname = $data[0]['hostname'];
   				  $mac_address = $data[0]['mac-address'];
   				  $ip_address = $data[0]['ip-address'];
         $subnet_name = $data[0]['subnet-name'];
         $pxe_group = $data[0]['pxe-group'];
         $ex_group = $data[0]['group'];
         $select_groups = $ch;
         if( count( $select_groups ) > 0 ) { $JS = " showdiv( 'perms' );"; }
         $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['good'], "You are currently editing record #" . $id, NULL, NULL );
        } else {
         $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], $errors['auth_res'], NULL, NULL );
        }
       }
	 	   }
     }
    }

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				// re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$hostname = $_POST['hostname'];
 				$mac_address = $_POST['mac_address'];
     $ip_address = $_POST['ip_address'];
 				$subnet_name = $_POST['subnet_name'];
     $pxe_group = $_POST['pxe_group'];
     $search = $_POST['search'];
     $modify = $_POST['modify'];
     $permissions = $_POST['select_groups'];
     $groups = $_POST['groups'];
    
     // perform search if not empty
     if( ( empty( $search ) ) && ( !empty( $_POST['srch'] ) ) ) {
      $search_err = $err->GenerateErrorImg( $defined['error'], "help/help.html#host_search", '800', '800' );
      $list = "<ol><li>Search string is empty. Allowed formats:<br>MAC Address: xx:xx:xx:xx:xx<br>IPv4 Address: xxx.xxx.xxx.xxx<br>Hostname: [0-9a-z]</li></ol>";
      $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['val_host'] . $list, NULL, NULL );
     } elseif( ( !empty( $search ) ) && ( !empty( $_POST['srch'] ) ) ) {
      // perform validation on search string
      if( ( $val->ValidateIPv4( $search ) !== -1 ) || ( $val->ValidateMACFormats( $search ) !== -1 ) || ( $val->ValidateParagraph( $search ) !== -1 ) ) { 

       // Gather all records belonging to other groups but where this user is allowed access
       $sql = "SELECT * FROM `auth_group_perms` WHERE `resource` = \"hosts\"";
       if( ( $z = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
        
       }

       // define our search query
							if( $group === "admin" ) {
        $query = "SELECT * FROM `conf_hosts` WHERE `hostname` LIKE \"" . $search . "\" OR `mac-address` LIKE \"" . $search . "\" OR `ip-address` LIKE \"" . $search . "\" ORDER BY `hostname` ASC";
       } else {
							 $query = "SELECT * FROM `conf_hosts` WHERE `hostname` LIKE \"" . $search . "\" OR `mac-address` LIKE \"" . $search . "\" OR `ip-address` LIKE \"" . $search . "\" HAVING `group` = \"" . $group . "\" ORDER BY `hostname` ASC";
							}

       // process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['db_search_err'], NULL, NULL );
       } else {
        // process results of search
        if( $db->dbNumRows( $value ) >= 1 ) {
         $data = $db->dbArrayResultsAssoc( $value );
         if( $db->dbNumRows( $value ) === 1 ) {
									 $id = $data[0]['id'];
          $hostname = $data[0]['hostname'];
          $mac_address = $data[0]['mac-address'];
          $ip_address = $data[0]['ip-address'];
          $subnet_name = $data[0]['subnet-name'];
          $pxe_group = $data[0]['pxe-group'];
										$error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['good'], "Your search for '" . $val->ValidateXSS( $search ) . "' returned '" . $db->dbNumRows( $value ) . "' results. The form has been populated for you to edit record #" . $id, NULL, NULL );
         } else {
          $hosts_list = $misc->GenJumpMenuBoxHOSTS( $data, 'hosts_list', $_GET['skin'], NULL );
          $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['good'], "Your search for '" . $val->ValidateXSS( $search ) . "' returned '" . $db->dbNumRows( $value ) . "' results. Please select the host you wish to edit from the select box below", NULL, NULL );
         } 

        } else {
         $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['sql_empty'], NULL, NULL );
        }
 						}
      } else {
       $search_err = $err->GenerateErrorImg( $defined['error'], "help/help.html#host_search", '800', '800' );
       $list = "<ol><li>Search string is invalid. Allowed formats:<br>MAC Address: xx:xx:xx:xx:xx<br>IPv4 Address: xxx.xxx.xxx.xxx<br>Hostname: [0-9a-z]</li></ol>";
       $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['val_host'] . $list, NULL, NULL );
      }
     } else {
				  $sql_success = -1;
      // check each post element
      if( ( !empty( $hostname ) ) && ( !empty( $mac_address ) ) && ( !empty( $ip_address ) ) && ( !empty( $subnet_name ) ) ) {

       // perform lookup of available scopes to prevent overlaps between static hosts and scope address
       $sql = "SELECT `scope-range-1`,`scope-range-2` FROM `conf_subnets` WHERE `scope-range-1` > '' AND `scope-range-2` > ''";
       if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
        if( $db->dbNumRows( $value ) !== 0 ) {
         $scopes = $db->dbArrayResultsAssoc( $value );
        }
       }

       // begin validation of configuration options
       if( ( $val->ValidateHostname( $hostname ) !== -1 ) && ( $val->ValidateMACFormats( $mac_address ) !== -1 ) && ( $val->ValidateIPv4( $ip_address ) !== -1 ) && ( ( $val->ValidateParagraph( $subnet_name ) !== -1 ) || ( $val->ValidateParagraph( $pxe_group ) !== -1 ) ) && ( $val->ValidateIPvsScope( $scopes, $ip_address ) !== -1 ) ) {

        // since no errors for the mac address were recieved assign the *possibly fixed value
        $mac_address = $val->ValidateMACFormats( $mac_address );

  						// define our sql statements (if group is admin, discard that field)
								if( $group === "admin" ) {
   						$insert = "INSERT INTO `conf_hosts` ( `hostname`, `mac-address`, `ip-address`, `subnet-name`, `pxe-group` ) VALUES ( \"" . $hostname . "\",\"" . $mac_address . "\", \"" . $ip_address . "\", \"" . $subnet_name . "\", \"" . $pxe_group . "\" )";
   		    $update = "UPDATE `conf_hosts` SET `hostname` = \"" . $hostname . "\", `mac-address` = \"" . $mac_address . "\", `ip-address` = \"" . $ip_address . "\", `subnet-name` = \"" . $subnet_name . "\", `pxe-group` = \"" . $pxe_group . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
								} else {
   						$insert = "INSERT INTO `conf_hosts` ( `hostname`, `mac-address`, `ip-address`, `subnet-name`, `pxe-group`, `group` ) VALUES ( \"" . $hostname . "\",\"" . $mac_address . "\", \"" . $ip_address . "\", \"" . $subnet_name . "\", \"" . $pxe_group . "\", \"" . $group . "\" )";
   		    if( !empty( $_POST['ex_group'] ) ) {
          $update = "UPDATE `conf_hosts` SET `hostname` = \"" . $hostname . "\", `mac-address` = \"" . $mac_address . "\", `ip-address` = \"" . $ip_address . "\", `subnet-name` = \"" . $subnet_name . "\", `pxe-group` = \"" . $pxe_group . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $id . "\" LIMIT 1";
         } else {
          $update = "UPDATE `conf_hosts` SET `hostname` = \"" . $hostname . "\", `mac-address` = \"" . $mac_address . "\", `ip-address` = \"" . $ip_address . "\", `subnet-name` = \"" . $subnet_name . "\", `pxe-group` = \"" . $pxe_group . "\", WHERE `id` = \"" . $id . "\" LIMIT 1";
         }
        }
  						$delete = "DELETE FROM `conf_hosts` WHERE `id` = \"" . $id . "\" LIMIT 1";

        // determine which button was clicked
  						if( !empty( $_POST['AddHosts'] ) ) { $query = $insert; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
  						if( !empty( $_POST['EditHosts'] ) ) { $query = $update; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
  						if( !empty( $_POST['DelHosts'] ) ) { $query = $delete; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }
      
  						// process our query
  						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
         $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $db_msg_err, NULL, NULL );
         
         // does the record exist? a duplicate mac, ip or hostname is not allowed
         if( ( eregi( "duplicate", $db->dbCatchError() ) ) && ( !empty( $id ) ) || ( !empty( $modify ) ) && ( $modify === "modify" ) ) {
          // look at error and grab the existing record
          @preg_match( '/.*\'(.*)\'.*/', $db->dbCatchError(), $dup );
          
          // make sure users can't edit other group records
          if( $group !== "admin" ) {
           $sql = "SELECT `id` FROM `conf_hosts` WHERE `ip-address` LIKE \"" . $dup[1] . "\" OR `hostname` LIKE \"" . $dup[1] . "\" OR `mac-address` LIKE \"" . $dup[1] . "\" HAVING `group` = \"" . $group . "\"";
          } else {
           $sql = "SELECT `id` FROM `conf_hosts` WHERE `ip-address` LIKE \"" . $dup[1] . "\" OR `hostname` LIKE \"" . $dup[1] . "\" OR `mac-address` LIKE \"" . $dup[1] . "\"";
          }
          
          // execute the database query for our matching record
  								if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
           $ids = $db->dbArrayResults( $value );
          }
          
          // create new `update` sql statement with matching id to overwrite record as per requested
          if( $group === "admin" ) {
           $update = "UPDATE `conf_hosts` SET `hostname` = \"" . $hostname . "\", `mac-address` = \"" . $mac_address . "\", `ip-address` = \"" . $ip_address . "\", `subnet-name` = \"" . $subnet_name . "\", `pxe-group` = \"" . $pxe_group . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $ids[0]['id'] . "\" LIMIT 1";
          } else {
           $update = "UPDATE `conf_hosts` SET `hostname` = \"" . $hostname . "\", `mac-address` = \"" . $mac_address . "\", `ip-address` = \"" . $ip_address . "\", `subnet-name` = \"" . $subnet_name . "\", `pxe-group` = \"" . $pxe_group . "\" WHERE `id` = \"" . $ids[0]['id'] . "\" LIMIT 1";
          }

          // perform update on record
          if( ( $value = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn ) ) === -1 ) {
  							  $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['db_edit_err'], NULL, NULL );
          } else {
           
           // was anything updated?
           if( $db->dbAffectedRows( $dbconn ) === 0 ) {
            $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['db_edit_err'], NULL, NULL );
           } else {
   									$error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['good'], $errors['db_edit'], NULL, NULL );
           }
  								}
  							} else {
          // we will perform a search for the existing record and allow the user to proceed on `updating` duplicate
          @preg_match( '/.*\"(.*)\".*/', $db->dbCatchError(), $dup );

          if( ( $group !== "admin" ) || ( empty( $_POST['allow'] ) ) ) {
           $sql = "SELECT * FROM `conf_hosts` WHERE `id` = \"" . $dup[1] . "\" AND `group` = \"" . $group . "\"";
          } else {
           $sql = "SELECT * FROM `conf_hosts` WHERE `id` = \"" . $dup[1] . "\"";
          }

          // execute the database query for our matching record
  								if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
           $ids = $db->dbArrayResults( $value );
          }
          
          // determine which fields match and assign to an array of errors
          $e = $err->GenerateErrorImg( $defined['error'], "help/help.html#host_duplicate", '800', '800' );
          if( count( $ids ) > 0 ) {
           foreach( $ids as $key => $value ) {
            if( $value['hostname'] === $hostname ) { $duperrs['hostname'] = $e; $hostname_err = $e; }
            if( $value['ip-address'] === $ip_address ) { $duperrs['ip-address'] = $e; $ip_address_err = $e; }
            if( $value['mac-address'] === $mac_address ) { $duperrs['mac-address'] = $e; $mac_address_err = $e; }
           }
          }
          
          // assign to a table generator & error about duplicate record
          $duplicate = $misc->GenTableFromAssocArrayDuplicateHost( $ids[0], $duperrs );
  								$error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $db_msg_err . " A record exists in the database that matches at least one field being processed", NULL, NULL );
         }
        } else {
  							// database update/insert/delete sucessful
         $error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['good'], $db_msg_good, NULL, NULL );
         $sql_success = 0;
  						} 

       } else {
        // find validation errors
  						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#host_search", '800', '800' );
   					$list .= "<ol>";
        if( $val->ValidateHostname( $hostname ) === -1 ) { $list .= "<li>" . $errors['val_hostname'] . "</li>"; $hostname_err = $e; }
        if( $val->ValidateMACFormats( $mac_address ) === -1 ) { $list .= "<li>" . $errors['val_mac'] . "</li>"; $mac_address_err = $e; }
        if( $val->ValidateIPv4( $ip_address ) === -1 ) { $list .= "<li>" . $errors['val_ipaddr'] . "</li>"; $ip_address_err = $e; }
        if( $val->ValidateParagraph( $subnet_name ) === -1 ) { $list .= "<li>Subnet Name field is invalid</li>"; $subnet_name_err = $e; }
  						if( $val->ValidateIPvsScope( $scopes, $ip_address ) === -1 ) { $list .= "<li>IP Address is in use within a currently defined scope for dynamic addressing</li>"; $ip_address_err = $e; }
        $list .= "</ol>";
  						$error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], "An error occured with form data. Please correct syntax or modify entry" . $list, NULL, NULL );
       }
      } else {
       // look to see which fields were empty
  					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#host_search", '800', '800' );
  					$list .= "<ol>";
       if( empty( $hostname ) ) { $list .= "<li>Hostname field is missing</li>"; $hostname_err = $e; }
       if( empty( $mac_address ) ) { $list .= "<li>MAC Address Field is missing</li>"; $mac_address_err = $e; }
	  				if( empty( $ip_address ) ) { $list .= "<li>IP Address Field is missing</li>"; $ip_address_err = $e; }
       if( empty( $subnet_name ) ) { $list .= "<li>Subnet Name Field is missing</li>"; $subnet_name_err = $e; }
	  				$list .= "</ol>";
 	 				$error = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );
      }
     }
    }

    /* grab the additional host records if permissions allow first */
    if( $group !== "admin" ) {
     $sql = "SELECT `resource` FROM `auth_groups_perms` WHERE ( `group` = \"" . $group . "\" OR `allowed` = \"" . $group . "\" ) AND `type` = \"host\"";
    } else {
     $sql = "SELECT `resource` FROM `auth_groups_perms` WHERE `type` = \"host\"";
    }
    if( ( $r = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
     if( $db->dbNumRows( $r ) !== 0 ) {
      $add_rec = $db->dbArrayResultsAssoc( $r );
      foreach( $add_rec as $key => $value ) {
       $sqls[] = "SELECT * FROM `conf_hosts` WHERE `mac-address` = \"" . $value['resource'] . "\" LIMIT 1";
       foreach( $sqls as $k => $v ) {
        if( ( $r = $db->dbQuery( $val->ValidateSQL( $v, $dbconn ), $dbconn ) ) !== -1 ) {
         $hostslist_additional[] = $db->dbArrayResultsAssoc( $r );
         $allow = 1;
        }
       }
      }
     }
    }

    // clean it up first
    if( count( $hostslist_additional ) > 0 ) {
     $hostslist_fixed = array();
     foreach( $hostslist_additional as $key => $value ) {
      $hostslist_fixed = array_merge( $hostslist_fixed, $value );
     }
    }

    // create current list of static hosts if $hosts_list is empty
    if( count( $hosts_list ) === 0 ) {
     if( $group === "admin" ) {
 					$hostsquery = "SELECT * FROM `conf_hosts` ORDER BY `hostname` ASC";
					} else {
					 $hostsquery = "SELECT * FROM `conf_hosts` WHERE `group` = \"" . $group . "\" OR `group` = '' ORDER BY `hostname` ASC"; 
					}
  		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $hostsquery, $dbconn ), $dbconn ) ) !== -1 ) {
      $hostslist = $db->dbArrayResultsAssoc( $current );
      if( count( $hostslist_additional ) > 0 ) {
       $hostslist = array_merge( $hostslist, $hostslist_fixed );
      }
  		 }
  			$hosts_list = $misc->GenJumpMenuBoxHOSTS( $hostslist, 'hosts_list', $_GET['skin'], $allow );
    }
  
    /* check for additional subnets this group can access */
    if( $group === "admin" ) {
     $sql = "SELECT `resource`, `allowed` FROM `auth_groups_perms` WHERE `type` = \"subnet\"";
    } else {
     $sql = "SELECT `resource`, `allowed` FROM `auth_groups_perms` WHERE `type` = \"subnet\" AND `allowed` = \"" . $group . "\" OR `group` = \"" . $group . "\"";
    }
    if( ( $w = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
     $extra = $db->dbArrayResultsAssoc( $w );
     if( count( $extra ) > 0 ) {
      $subnetlist_remainder = array();
      foreach( $extra as $key => $value ) {
       $sql = "SELECT * FROM `conf_subnets` WHERE `subnet-name` = \"" . $value['resource'] . "\" LIMIT 1";
       if( ( $s = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
        $subnetlist_remainder = array_merge( $subnetlist_remainder, $db->dbArrayResultsAssoc( $s ) );
        $subnetlist_remainder = $subnetlist_remainder;
        if( $group === "admin" ) {
         $sql = "SELECT * FROM `conf_subnets` ORDER BY `subnet-name` ASC";
        } else {
         $sql = "SELECT * FROM `conf_subnets` WHERE `group` = \"" . $group . "\" ORDER BY `subnet-name` ASC";
        }
        if( ( $s = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
         $subnetlist_remainder = array_merge( $subnetlist_remainder, $db->dbArrayResultsAssoc( $s ) );
         $subnetlist_remainder = $misc->EliminiateDuplicates( $subnetlist_remainder );
        }
       }
      }
     } else {
      if( $group !== "admin" ) {
       $sql = "SELECT * FROM `conf_subnets` WHERE `group` = \"" . $group . "\"";
      } else {
       $sql = "SELECT * FROM `conf_subnets` ORDER BY `subnet-name` ASC";
      }
      if( ( $s = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
       $subnetlist_remainder = /*array_merge( $subnetlist_remainder,*/ $db->dbArrayResultsAssoc( $s );// );
      }
     }     
    }
    if( count( $subnet_name ) > 0 ) {
     $subnet_name = $misc->GenDropMenuWSelectedSubnets( $subnetlist_remainder, $subnet_name, 'subnet_name' );
    } else {
     $subnet_name = "No Subnets defined";
    }
   
    // populate our pxe group list
				if( $group === "admin" ) {
     $px = "SELECT `pxe-group-name` FROM `conf_pxe_groups` ORDER BY `pxe-group-name` ASC";
				} else {
				 $px = "SELECT `pxe-group-name` FROM `conf_pxe_groups` WHERE `group` = \"" . $group . "\" OR `group` = '' ORDER BY `pxe-group-name` ASC";
				}
 		 if( ( $return = $db->dbQuery( $val->ValidateSQL( $px, $dbconn ), $dbconn ) ) !== -1 ) {
     $pxeg = $db->dbArrayResultsAssoc( $return );
 		 }
    if( count( $pxeg ) === 0 ) {
 			 $pxe_group = "No PXE Groups defined";
    } else {
     $pxe_group = $misc->GenDropMenuWSelectedPXE( $pxeg, $pxe_group, 'pxe_group' );
    }

    // do some calculations on the list of available subnets
				$sql = "SELECT * FROM `conf_subnets`";
 		 if( ( $return = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
     $data = $db->dbArrayResultsAssoc( $return );
	   }
				if( count( $data ) !== 0 ) {
					foreach( $data as $key => $value ) {
						if( ( $value['enable-scope'] === "true" ) && ( !empty( $value['scope-range-1'] ) ) && ( !empty( $value['scope-range-2'] ) ) ) {
							$ip_counts[] = $misc->GetAvailableIPAddresses( $value, $value['subnet-name'] );
						} else {
 						if( !empty( $value['pool'] ) ) {
 							// process assigned pool IP addresses minus IP's engaged in `conf_leases` table
								$sql = "SELECT * FROM `conf_pools` WHERE `pool-name` = \"" . $value['pool'] . "\"";
								if( ( $return = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
									$pool_array = $db->dbArrayResultsAssoc( $return );
									foreach( $pool_array as $k => $v ) {
 									$ip_counts[] = $misc->GetAvailableIPAddresses( $v, $value['subnet-name'] );
									}
								}
 						} else {
								// look at broadcast and mask to determine range first
								// process everything else by looking up all static hosts and comparing to broadcast and subnet mask
								$sql = "SELECT * FROM `conf_hosts` WHERE `subnet-name` = \"" . $value['subnet-name'] . "\"";
								if( ( $return = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
									$hosts_array = $db->dbArrayResultsAssoc( $return );
									$ip_counts[] = $misc->GetAvailableIPAddressesStatic( $hosts_array, $value['subnet-name'] );
								}
							}
      }
					}
					if( count( $ip_counts ) !== 0 ) {
						$x = 1;
						foreach( $ip_counts as $key => $value ) {
							foreach( $value as $k => $v ) {
								if( $x <= 6 ) {
									// pass each array to a format specialist
 								$available .= $misc->GenDivHiddenContent( $k, $v );
								} else {
									$available .= "</ul><br><br><ul>" . $misc->GenDivHiddenContent( $k, $v );
									$x = 1;
								}
        $x++;
							}
						}
					}
				}
				
    // check for additional subnets this group can access
    $sql = "SELECT `resource`, `allowed` FROM `auth_groups_perms` WHERE `type` = \"host\" AND `allowed` = \"" . $group . "\" AND `group` != \"" . $group . "\"";
    if( ( $extra = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
     $extra = $db->dbArrayResultsAssoc( $extra );
     foreach( $extra as $key => $value ) {
      $sql = "SELECT * FROM `conf_subnets` WHERE `subnet-name` = \"" . $value['resource'] . "\" LIMIT 1";
      if( ( $s = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
       if( ( $db->dbNumRows( $s ) !== -1 ) && ( $db->dbNumRows( $s ) > 0 ) ) {
        $subnetlist_remainder = array_merge( $misc->EliminiateDuplicates( $db->dbArrayResultsAssoc( $s ) ) );
       }
      }
     }
    }    
    
    // do the checkboxes for available groups
				$groupsquery = "SELECT * FROM `auth_groups` WHERE `group` != \"admin\" AND `group` != \"" . $group . "\" ORDER BY `group` ASC";
    if( ( $res = $db->dbQuery( $val->ValidateSQL( $groupsquery, $dbconn ), $dbconn ) ) !== -1 ) {
					$groups = $db->dbArrayResultsAssoc( $res );
     $groups = $misc->EliminiateDuplicates( $groups );
     $sql = "SELECT * FROM `auth_groups_perms` WHERE `resource` = \"" . $mac_address . "\"";
     $sql_res = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn );
     $select_groups = $db->dbArrayResultsAssoc( $sql_res );
     $chk_grp = $select_groups;
     if( count( $groups ) !== 0 ) {
						$select_groups = $misc->GenGroupsCheckBoxes( $groups, 'select_groups', $_GET['skin'], $select_groups, $group );
					} else {
					 $select_groups = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], "No groups defined", NULL, NULL );
					}
				} else {
     $select_groups = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['db_select_err'], NULL, NULL );
    }
    
    if( $sql_success === 0 ) {
     // do the permissions bit
     foreach( $permissions as $key => $value ) {
      if( !empty( $value['group'] ) ) {
       if( !in_array( $value['resource'], $chk_grp ) ) {
         $set_permissions[] = "INSERT INTO `auth_groups_perms` ( `group`, `resource`, `type`, `allowed` ) VALUES ( \"" . $group . "\", \"" . $mac_address . "\", \"host\", \"" . $value['group'] . "\" )";
       } else {
        $set_permissions[] = "UPDATE `auth_groups_perms` SET `allowed` = \"" . $value['group'] . "\" WHERE `resource` = \"" . $mac_address . "\"";
        $delete_permissions[] = "DELETE FROM `auth_groups_perms` WHERE `id` = \"" . $value['id'] . "\" LIMIT 1";
       }
      }
     }
     
     // which sql query do we run?
     if( !empty( $_POST['AddHosts'] ) ) { $grp_prms = $set_permissions; }
     if( !empty( $_POST['EditHosts'] ) ) { $grp_prms = $set_permissions; }
     if( !empty( $_POST['DelHosts'] ) ) { $grp_prms = $set_permissions; }
     
     // work on the permissions table
     if( count( $grp_prms ) > 0 ) {
      foreach( $grp_prms as $key => $value ) {
       if( ( $value = $db->dbQuery( $val->ValidateSQL( $value, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], $db_msg_err, NULL, NULL );
       } else {
 					  $error = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['good'], $db_msg_good, NULL, NULL );
 				  }
      }
     }    
    }

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
    $tpl->assign( 'hostname', $val->ValidateXSS( $hostname ), NULL, NULL );
    $tpl->assign( 'mac_address', $val->ValidateXSS( $mac_address ), NULL, NULL );
	 		$tpl->assign( 'ip_address', $val->ValidateXSS( $ip_address ), NULL, NULL );
	 		$tpl->assign( 'subnet_name', $subnet_name, NULL, NULL );
    $tpl->assign( 'pxe_group', $pxe_group, NULL, NULL );
    $tpl->assign( 'hosts_list', $hosts_list, NULL, NULL );
    $tpl->assign( 'duplicate', $duplicate, NULL, NULL );
				$tpl->assign( 'available', $available, NULL, NULL );
    $tpl->assign( 'select_groups', $select_groups, NULL, NULL );
    $tpl->assign( 'ex_group', $val->ValidateXSS( $ex_group ), NULL, NULL );
    $tpl->assign( 'allow', $val->ValidateXSS( $allow ), NULL, NULL );
    $tpl->assign( 'host_count', count( $hostslist ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'hostname_err', $hostname_err, NULL, NULL );
    $tpl->assign( 'mac_address_err', $mac_address_err, NULL, NULL );
    $tpl->assign( 'ip_address_err', $ip_address_err, NULL, NULL );
    $tpl->assign( 'subnet_name_err', $subnet_name_err, NULL, NULL );
    $tpl->assign( 'pxe_group_err', $pxe_group_err, NULL, NULL );
    $tpl->assign( 'hosts_list_err', $hosts_list_err, NULL, NULL );
    $tpl->assign( 'search_err', $search_err, NULL, NULL );
    $tpl->assign( 'select_groups_err', $select_groups_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
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