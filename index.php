<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * index.php - index or main page
 */

// load our config data
if( file_exists( "scripts/inc.config.php" ) ) {
 require 'scripts/inc.config.php';

 // required php lib checks
 $chks = new phpLIBChecks;
	$required = $chks->RequiredLibs();
	$functions = $chks->GetPHPLIBS();
	$a = $chks->CheckPHPLIBS( $functions, $required );
	if( $a['error']['errno'] === -1 ) {
		foreach( $a as $b => $c ) {
			foreach( $c['data'] as $key => $value ) {
			 $liberrors .= "Missing func '$value' from '$key' module<br>";
			}
		}
	}

 // configuration check
	if( ( empty( $defined['hostname'] ) ) || ( empty( $defined['dbhost'] ) ) || ( empty( $defined['username'] ) ) || ( empty( $defined['password'] ) ) || ( empty( $defined['dbname'] ) ) || ( empty( $defined['virpath'] ) ) ) {
		$configerrors = "The inc.config.php file is missing configuration directives.";
  echo $configerrors; exit;
	}
 
 // just some defs
 $LIBERROR = NULL;
 $CONFIGERRORS = NULL;

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
   if( !empty( $_GET['skin'] ) ) { $skin_err = $err->GenerateErrorLink( "help/help.php", "#val_xss", $defined['error_small'], $errors['val_xss'], NULL, NULL ); }
  } else {
   $style = $skin->SelectSkin( $defined['templates'], $_GET['skin'], $_COOKIE['skin'] );
  }

  // call our header file and pass it some variables
  $tpl->assign( 'TITLE', $defined['title'] . " >> Main Page", NULL, NULL );
 	$tpl->assign( 'DESCRIPTION', $defined['description'], NULL, NULL );
  $tpl->assign( 'STYLE', $style, NULL, NULL );
 
  // javascript to set focus on login form
  $JS = " document.login.user.focus();";
 
  // authentication template
  $FILE = "auth.tpl";

  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
    
		// provide count of online users
		$online = "SELECT * FROM `admin_sessions`";
		$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
		$usersoline = $db->dbNumRows( $ret );

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
   if( ( $level->ChkLevel( $_SESSION['token'] ) === "root" ) || ( $level->ChkLevel( $_SESSION['token'] === "user" ) ) ) {
   
 			// define some variables for the template etc.
 			$FILE = "loggedin.tpl";
    //hidediv( 'graphs' ); preLoader( 'templates/images/graphs/graph.hosts.php', 'templates/images/graphs/graph.leases.php', 'templates/images/graphs/graph.pxe.php', 'templates/images/graphs/graph.subnets.php', 'templates/images/graphs/graph.traffic.php' );
    $JS = " hidediv( 'subnets'); hidediv( 'adapters' );";
    $FORM = NULL;
    $page = "admin.manage.users.php";
    
    // decode the auth token for our username data
    $user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$username = base64_decode( $user_details[0] );

    // get an array of subnets the ISC DHCPD service may listen on
    $query = "SELECT `name`, `broadcast` FROM `conf_adapters` ORDER BY `broadcast` ASC";
    if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
     $error = $err->GenerateErrorLink( "help/help.html", "#config_subnets", $defined['error'], $errors['db_select'], NULL, NULL );
    } else {
     $tmp = $db->dbArrayResultsAssoc( $value );
     // filter for empty stuff
     for( $x = 0; $x < count( $tmp ); $x++ ) {
      if( !empty( $tmp[$x]['broadcast'] ) ) {
       $interface_list[$tmp[$x]['name']] = $tmp[$x]['broadcast'];
      }
     }
    }
    
    // show the user the list of interfaces and their broadcast address
    if( ( $ilist = $misc->GenTableFromAssocArray( $interface_list ) ) === -1 ) {
     $adapters = $err->GenerateErrorLink( "help/help.html", "#config_subnet", $defined['error'], "There are no interfaces configured", NULL, NULL );
    } else {
     $adapters = $misc->GenTableFromAssocArray( $interface_list );
    }

    // check service status
    if( $misc->GetDHCPDStatus() === 0 ) {
     $dhcpd_status = $err->GenerateErrorLink( "help/help.html", "#status", $defined['good'], $errors['dhcpd_status'], NULL, NULL );
    } else {
     $dhcpd_status = $err->GenerateErrorLink( "help/help.html", "#status", $defined['error'], $errors['dhcpd_status_err'], NULL, NULL );
    }
    
    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
    
				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );
				
    // Check to see if user has a the 'reset' flag for their password
    $query = "SELECT * FROM `auth_users` WHERE `username` = \"" . $username . "\" LIMIT 1";

    if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
     $error = $err->GenerateErrorLink( "help/help.html", "#loggin", $defined['error'], $errors['db_select'], NULL, NULL );
    } else {
     $data = $db->dbArrayResultsAssoc( $value );
     if( $data[0]['reset'] === "TRUE" ) { $FORM = "reset.tpl"; }
     if( $data[0]['level'] !== "admin" ) { $page = "user.preferences.php"; }
     $username = $data[0]['username'];
     $level = $data[0]['level'];
     $group = $data[0]['group'];
     $dept = $data[0]['dept'];
     $create = $data[0]['create_date'] . " @ " . $data[0]['create_time'];
     $access = $data[0]['access_date'] . " @ " . $data[0]['access_time'];
     $session = $data[0]['session'];
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
									$ip_counts[] = $misc->GetAvailableIPAddressesStatic( $db->dbArrayResultsAssoc( $return ), $value['subnet-name'] );
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
					} else {
      $available = $err->GenerateErrorLink( "help/help.html", "#auth", $defined['error'], "No data present because the phpDHCPAdmin  application has not been properly configured", '600', '600' );
     }
				} else {
     $available = $err->GenerateErrorLink( "help/help.html", "#auth", $defined['error'], "Nothing to generate because there are not any subnets / IP pools available", '600', '600' );
    }

    // nice message
    $string = "Welcome '" . $username . "'! You last login was '" . $access . "'.";

    // give a message on authentication regarding various parameters
    $message = $err->GenerateErrorLink( "help/help.html", "#auth", $defined['good'], $string, '600', '600' );

    $tpl->assign( 'page', $page, NULL, NULL );
				$tpl->assign( 'available', $available, NULL, NULL );
    $tpl->assign( 'message', $message, NULL, NULL );
    $tpl->assign( 'adapters', $adapters, NULL, NULL );
    $tpl->assign( 'dhcpd_status', $dhcpd_status, NULL, NULL );
				$tpl->assign( 'username', $username, NULL, NULL );
    $tpl->assign( 'user_pw_1_err', NULL, NULL, NULL );
    $tpl->assign( 'user_pw_2_err', NULL, NULL, NULL );
    $tpl->assign( 'FORM', $tpl->assign( NULL, NULL, $FORM, $flag ), NULL, NULL );

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

if( $a['error']['errno'] === -1 ) { $LIBERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $liberrors, NULL, NULL ); }
if( !empty( $configerrors ) ) { $CONFIGERRORS = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $configerrors, NULL, NULL ); }

if( !empty( $_GET['register'] ) ) { $FILE = "user.changepw.tpl"; $ERROR =''; }

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
$tpl->assign( 'LIBERROR', $LIBERROR, NULL, NULL );
$tpl->assign( 'CONFIGERRORS', $CONFIGERRORS, NULL, NULL );
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