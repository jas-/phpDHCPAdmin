<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * admin.manage.groups.php - Manage groups
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
  $enc = new Encryption;

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
 	$tpl->assign( 'DESCRIPTION', "Manage Groups", NULL, NULL );
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
			// decode our authentication token to get our group membership
			$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
			$group = base64_decode( $user_details[3] );
   if( ( $level->ChkLevel( $_SESSION['token'] ) === "admin" ) && ( $group === "admin" ) ) {
   
 			// define some variables for the template etc.
 			$JS = NULL;
    $FILE = "admin.manage.groups.tpl";
    $group_name_err = "*";
    $group_manager_err = "*";
    $group_contact_err = "*";
    $group_description_err = "*";

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // Look for a GET id post to edit existing dnssec keys
    if( !empty( $_GET['id'] ) ) {
     if( $val->ValidateInteger( $_GET['id'] ) === -1 ) {
      $message = $err->GenerateErrorLink( "help/help.html", "#group_edit", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
 				 $query = "SELECT * FROM `auth_groups` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) { echo $query;
       $message = $err->GenerateErrorLink( "help/help.html", "#group_edit", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
 				 	$group_id = $data[0]['id'];
 				 	$group_name = $data[0]['group'];
 				  $group_manager = $data[0]['manager'];
 				  $group_contact = $data[0]['contact'];
       $group_description = $data[0]['description'];
       $message = $err->GenerateErrorLink( "help/help.html", "#host_search", $defined['good'], "You are currently editing record #" . $group_id, NULL, NULL );
	 	   }
     }
    }

    // check for form submission first
    if( !empty( $_POST ) ) {

     // setup our form variables
     $group_name = $_POST['group_name'];
     $group_manager = $_POST['group_manager'];
     $group_contact = $_POST['group_contact'];
     $group_description = $_POST['group_description'];
     $group_id = $_POST['group_id'];

     // check for empty variables
     if( ( !empty( $group_name ) ) && ( !empty( $group_manager ) ) && ( !empty( $group_description ) ) && ( !empty( $group_contact ) ) ) {

      // do some validation checks on submitted data
      if( ( $val->ValidateParagraph( $group_name ) !== -1 ) && ( $val->ValidatePhone( $group_contact ) !== -1 ) && ( $val->ValidateParagraph( $group_description ) !== -1 ) && ( $val->ValidateParagraph( $group_manager ) !== -1 ) ) {

       // setup our SQL statements for add, edit and deleting records
       $insert = "INSERT INTO `auth_groups` ( `group`, `manager`, `contact`, `description`  ) VALUES ( \"" . $group_name . "\", \"" . $group_manager . "\", \"" . $group_contact . "\", \"" . $group_description . "\" )";
       $update = "UPDATE `auth_groups` SET `group` = \"" . $group_name . "\", `contact` = \"" . $group_contact . "\", `description` = \"" . $group_description . "\", `manager` = \"" . $group_manager . "\" WHERE `id` = \"" . $group_id . "\" LIMIT 1";
       $delete = "DELETE FROM `auth_groups` WHERE `id` = \"" . $group_id . "\" LIMIT 1";

       // now perform a check to see which statement to use
       if( !empty( $_POST['AddGroup'] ) ) { $sql = $insert; }
       if( !empty( $_POST['EditGroup'] ) ) { $sql = $update; }
       if( !empty( $_POST['DelGroup'] ) ) { $sql = $delete; }

       // begin processing our SQL object
       if( ( $sql_res = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) === -1 ) {
        if( eregi( "duplicate", $db->dbCatchError() ) ) {
         $sql = $update;
         $sql = $val->ValidateSQL( $sql, $dbconn );
         if( ( $sql_res = $db->dbQuery( $sql, $dbconn ) ) === -1 ) {
          $message = $err->GenerateErrorLink( "help/help.php", "#sql_error", $defined['error'], $errors['db_edit_err'], '600', '600' );
         }
        } else {
         $message = $err->GenerateErrorLink( "help/help.php", "#db_insert", $defined['good'], $errors['db_edit'], '600', '600' );
        }
       } else {
        $message = $err->GenerateErrorLink( "help/help.php", "#db_insert", $defined['good'], $errors['db_insert'], '600', '600' );
       }
       
      } else {
       // create a reusable error link
       $erlink = $err->GenerateErrorImg( $defined['error'], "help/help.html#val_par", "#val_par", '600', '600' );
       $list = "<ol>";
       // determine our validate errors
       if( $val->ValidateParagraph( $group_name ) === -1 ) { $list .= "<li>Name field is invalid</li>"; $group_name_err = $erlink; }
       if( $val->ValidatePhone( $group_contact ) === -1 ) { $list .= "<li>Contact field is invalid, phone number expected xxx-xxx-xxxx</li>"; $group_contact_err = $erlink; }
       if( $val->ValidateParagraph( $group_description ) === -1 ) { $list .= "<li>Description field is invalid</li>"; $group_description_err = $erlink; }
       if( $val->ValidateParagraph( $group_manager ) === -1 ) { $list .= "<li>Manager field is invalid</li>"; $group_manager_err = $erlink; }
       $list .= "</ol>";
       // give them an error message with link to the help file
       $message = $err->GenerateErrorLink( "help/help.php", "#val_par", $defined['error'], $errors['val_par'] . $list, '600', '600' );
      }
     } else {
      // create a reusable error link
      $erlink = $err->GenerateErrorImg( $defined['error'], "help/help.html#val_empty", "#val_empty", '600', '600' );
      // determine which fields are missing
      $list = "<ol>";
      if( empty( $group_name ) ) { $list .= "<li>Name field is missing data</li>"; $group_name_err = $erlink; }
      if( empty( $group_contact ) ) { $list .= "<li>Contact field is missing data</li>"; $group_contact_err = $erlink; }
      if( empty( $group_description ) ) { $list .= "<li>Description field is missing data</li>"; $group_description_err = $erlink; }
      if( empty( $group_manager ) ) { $list .= "<li>Manager field is missing data</li>"; $group_manager_err = $erlink; }
      $list .= "</ol>";
      // give them an error message with link to the help file
      $message = $err->GenerateErrorLink( "help/help.php", "#val_missing", $defined['error'], $errors['val_missing'] . $list, '600', '600' );
     }
    }
    
    // get a list of current groups
    if( count( $group_list ) === 0 ) {
     $group_query = "SELECT * FROM `auth_groups` ORDER BY `group` ASC";
  		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $group_query, $dbconn ), $dbconn ) ) !== -1 ) {
      $group_list = $db->dbArrayResultsAssoc( $current );
  		 }
  			$group_list = $misc->GenJumpMenuBoxGROUPS( $group_list, 'group_list', $_GET['skin'] );
    }
    
    // assign our data to the template
    $tpl->assign( 'message', $message, NULL, NULL );
    $tpl->assign( 'group_id', $val->ValidateXSS( $group_id ), NULL, NULL );
    $tpl->assign( 'group_list', $group_list, NULL, NULL );
    $tpl->assign( 'group_name', $val->ValidateXSS( $group_name ), NULL, NULL );
    $tpl->assign( 'group_manager', $val->ValidateXSS ($group_manager ), NULL, NULL );
    $tpl->assign( 'group_contact', $val->ValidateXSS( $group_contact ), NULL, NULL );
    $tpl->assign( 'group_description', $val->ValidateXSS( $group_description ), NULL, NULL );
    
    // and the corresponding errors if any
    $tpl->assign( 'group_list_err', $group_list_err, NULL, NULL );
    $tpl->assign( 'group_name_err', $group_name_err, NULL, NULL );
    $tpl->assign( 'group_manager_err', $group_manager_err, NULL, NULL );
    $tpl->assign( 'group_contact_err', $group_contact_err, NULL, NULL );
    $tpl->assign( 'group_description_err', $group_description_err, NULL, NULL );


    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "auth_groups", $dbconn );
 			
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