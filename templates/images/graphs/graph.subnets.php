<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * index.php - index or main page
 */

// load our config data
if( file_exists( "../../../scripts/inc.config.php" ) ) {
 require '../../../scripts/inc.config.php';

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
  $tpl->strTemplateDir = '../../../../templates';
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
  $tpl->assign( 'TITLE', $defined['title'], NULL, NULL );
 	$tpl->assign( 'DESCRIPTION', $defined['description'], NULL, NULL );
  $tpl->assign( 'STYLE', $style, NULL, NULL );
 
  // javascript to set focus on login form
  $JS = " document.login.user.focus();";
 
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
   if( ( $level->ChkLevel( $_SESSION['token'] ) === "root" ) || ( $level->ChkLevel( $_SESSION['token'] === "user" ) ) ) {
   
    // decode our authentication token to get our group membership
				$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$group = base64_decode( $user_details[3] );

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
				
				// Get array of subnets to assign hosts to
				if( $group === "admin" ) {
				 $query = "SELECT * FROM `conf_subnets` ORDER BY `subnet-name` ASC";
				} else {
	 			$query = "SELECT * FROM `conf_subnets` WHERE `group` = \"" . $group . "\" ORDER BY `subnet-name` ASC";
				}
				if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) !== -1 ) {
     $subdata = $db->dbArrayResults( $value );
    }

    // are there subnets? if so populate sql queries to look up hosts per subnet
				if( count( $subdata ) ) {
 				foreach( $subdata as $key => $value ) {
 				if( $group === "admin" ) {
  				 $sql[$value['subnet-name']] = "SELECT * FROM `conf_hosts` WHERE `subnet-name` = \"" . $value['subnet-name'] . "\"$filter";
						} else {
  				 $sql[$value['subnet-name']] = "SELECT * FROM `conf_hosts` WHERE `subnet-name` = \"" . $value['subnet-name'] . "\" AND `group` = \"" . $group . "\"";
						}
 				}

 				// execute as many database queries as we need for our host to subnet assignment
 				foreach( $sql as $key => $value ) {
 				 if( ( $res = $db->dbQuery( $val->ValidateSQL( $value, $dbconn ), $dbconn ) ) !== -1 ) {
       $hostdata[$key] = $db->dbArrayResults( $res );
      }
 				}
				
				 // now build an unassigned list
     $sql['hosts'] = "SELECT * FROM `conf_hosts` WHERE `subnet-name` IS NULL OR `subnet-name` = ''";
     if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql['hosts'], $dbconn ), $dbconn ) ) !== -1 ) {
      $hostdata['unassigned'] = $db->dbArrayResults( $value );
     }
				
 				// generate our graphs but check for gd lib extensions first
					if( ( function_exists( 'imagedestroy' ) ) && ( count( $hostdata ) !== 0 ) ) {
  				$graph = new PHPGraphLibPie( 450, 200 );
      foreach( $hostdata as $key => $value ) {
 	 			 $array[$key] = count( $value );
							$total = $total + count( $value );
 	 			}
 	 			$graph->addData( $array );
      $graph->setTitle( "Hosts to Subnet Assignments: Total static hosts #" . $total );
      $graph->setLabelTextColor( "50, 50, 50" );
      $graph->setLegendTextColor( "50, 50, 50" );
 	 			$graph->setLegendOutlineColor( "black" );	
 	 			$graph->createGraph();
     }
				}

   } else {
    // page view restricted by access level
 			$ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['level'] );
   }
  } else {
 		// general authentication error
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['auth_n'] );
  }

 } else {
  // Possible XSS attack
  $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['xss_config'], NULL, NULL );
 }
} else {
 // File is missing for configuration params
 $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['config_file'], NULL, NULL );
}

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
$tpl->assign( 'STYLE', $style, NULL, NULL );

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