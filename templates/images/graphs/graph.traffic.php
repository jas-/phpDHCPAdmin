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
   
    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
				
    $now = $misc->GenTime();
    $hour_old = $now - 3600;
    $six_hour_old = $now - 21000;
    
				// most recent traffic
    $sql = "SELECT * FROM `conf_traffic` WHERE `time` <= \"" . $now . "\" AND `time` >= \"" . $hour_old . "\"";
    if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
     $traffic_recent = $db->dbArrayResults( $value );
    }

    // traffic from last 6 hours
    $sql = "SELECT * FROM `conf_traffic` WHERE `time` <= \"" . $hour_old . "\" AND `time` >= \"" . $six_hour_old . "\"";
    if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
     $traffic_old = $db->dbArrayResults( $value );
    }

    // check to see if we have valid data to use
    if( count( $traffic_recent ) === 0 ) {
     $traffic_recent[0] = array( "interface" => "EMPTY", "bytes" => 0 );
    }

    if( ( count( $traffic_old ) === 0 ) || ( empty( $traffic_old ) ) ) {
     $traffic_old[0] = array( "interface" => "EMPTY", "bytes" => 0 );
    }

 			// generate our graphs but check for gd lib extensions first
				if( ( function_exists( 'imagedestroy' ) ) && ( count( $traffic_recent ) !== 0 ) ) {
  			$graph = new PHPGraphLib( 450, 200 );
     
     // process last 60 minute traffic array
     foreach( $traffic_recent as $key => $value ) {
      if( $value['interface'] !== 'lo' ) {
       $value['bytes'] = $value['bytes'] / 1024 / 1024;
  	 		 $recent[$value['interface']] = round( $value['bytes'] );
 						$total_new = round( $total + $value['bytes'] );
      }
 	 		}

     // process last 6 hour traffic array
     foreach( $traffic_old as $key_old => $value_old ) {
      if( $value_old['interface'] !== 'lo' ) {
       $value_old['bytes'] = $value_old['bytes'] / 1024 / 1024;
  	 		 $old[$value_old['interface']] = round( $value_old['bytes'] );
 						$total_old = round( $total_old + $value_old['bytes'] );
      }
 	 		}

     // get total from both data sets
     $total = $total_new + $total_old;

     // push the image and assign attributes
 	 		$graph->addData( $old, $recent );
     $graph->setTitle( "MBytes Total: " . $total );
     $graph->setTitleLocation("left");
     $graph->setLegendTitle( "Last 60 Minutes", "Last 6 Hours" );
     $graph->setBars( true );
     $graph->setLegend( true );
     $graph->setDataPoints( true );
     $graph->setDataPointColor( "red" );
     $graph->setDataValueColor( "gray" );
     $graph->setGoalLineColor( "red" );
 	 		$graph->createGraph();
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