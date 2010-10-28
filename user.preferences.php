<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * admin.manage.users.php - Manage users
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
 	$tpl->assign( 'DESCRIPTION', "User Prefrences", NULL, NULL );
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
   if( $level->ChkLevel( $_SESSION['token'] ) === "user" ) {

 			// define some variables for the template etc.
 			$JS = NULL;
    $FILE = "user.preferences.tpl";
				$user_ip = $_SERVER['REMOTE_ADDR'];
				$user_host = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
				$user_create_date = $misc->GenDate();
    $user_create_time = $misc->GenTimeRead();

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

				// decode our authentication token to get our group membership
				$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$group = base64_decode( $user_details[3] );
    $user = base64_decode( $user_details[0] );
    
    // default errors for required fields
    $user_username_err = "*";
    $user_fname_err = "*";
    $user_lname_err = "*";
    $user_department_err = "*";
    $user_contact_err = "*";
    $user_phone_err = "*";
    $user_email_err = "*";

    // populate the form with database information if already configured
			 $query = "SELECT * FROM `auth_users` WHERE `username` = \"" . $user . "\" LIMIT 1";
	   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) { echo $query;
     $message = $err->GenerateErrorLink( "help/help.html", "#user_edit", $defined['error'], $errors['db_select'], NULL, NULL );
    } else {
     $data = $db->dbArrayResultsAssoc( $value );
			 	$user_id = $data[0]['id'];
			 	$user_username = $data[0]['username'];
			  $user_fname = $data[0]['first'];
			  $user_lname = $data[0]['last'];
     $user_department = $data[0]['dept'];
     $user_contact = $data[0]['contact'];
     $user_phone = $data[0]['phone'];
     $user_email = $data[0]['email'];
     $message = $err->GenerateErrorLink( "help/help.html", "#edit_user", $defined['good'], "You are currently editing record #" . $user_id, NULL, NULL );
	   }

    // check for form submission first
    if( !empty( $_POST ) ) {

     // setup our form variables
					$user_id = $_POST['user_id'];
     $user_username = $_POST['user_username'];
     $user_fname = $_POST['user_fname'];
     $user_lname = $_POST['user_lname'];
     $user_department = $_POST['user_department'];
     $user_contact = $_POST['user_contact'];
     $user_address = $_POST['user_address'];
     $user_phone = $_POST['user_phone'];
     $user_email = $_POST['user_email'];
					$user_pw_list = $_POST['user_pw_list'];
     $user_pw_1 = $_POST['user_pw_1'];
					$user_pw_2 = $_POST['user_pw_2'];

     // check for our form type
     if(  !empty( $_POST['EditUser'] ) ) {

      // check for empty variables
      if( ( !empty( $user_username ) ) && ( !empty( $user_fname ) ) && ( !empty( $user_lname ) ) && ( !empty( $user_department ) ) && ( !empty( $user_fname ) ) && ( !empty( $user_lname ) ) && ( !empty( $user_phone ) ) && ( !empty( $user_email ) ) ) {

       // do some validation checks on submitted data
       if( ( $val->ValidateAlphaChar( $user_username ) !== -1 ) && ( $val->ValidateString( $user_fname ) !== -1 ) && ( $val->ValidateString( $user_lname ) !== -1 ) && ( $val->ValidateParagraph( $user_department ) !== -1 ) && ( $val->ValidateString( $user_contact ) !== -1 ) && ( $val->ValidatePhone( $user_phone ) !== -1 ) && ( $val->ValidateEmail( $user_email ) !== -1 ) ) {

        // leave the owner assignment alone if owner is not admin
 							if( $group === "admin" ) { $group = $user_group; }
							
        $update = "UPDATE `auth_users` SET `username` = \"" . $user_username . "\",  `dept` = \"" . $user_department . "\", `first` = \"" . $user_fname . "\", `last` = \"" . $user_lname . "\", `phone` = \"" . $user_phone . "\", `email` = \"" . $user_email . "\", `ip` = \"" . $user_ip . "\", `host` = \"" . $user_host . "\", `owner` = \"" . $group . "\" WHERE `id` = \"" . $user_id . "\" LIMIT 1";

        // now perform a check to see which statement to use
        if( !empty( $_POST['EditUser'] ) ) { $sql = $update; $send_email = "FALSE"; }

        // begin processing our SQL object
        if( ( $sql_res = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) === -1 ) {
         if( eregi( "duplicate", $db->dbCatchError() ) ) {
          $sql = $update;
          $sql = $val->ValidateSQL( $sql, $dbconn );
          if( ( $sql_res = $db->dbQuery( $sql, $dbconn ) ) === -1 ) {
           $message = $err->GenerateErrorLink( "help/help.php", "#db_edit", $defined['error'], $errors['db_edit_err'], '600', '600' );
          }
         } else {
          $message = $err->GenerateErrorLink( "help/help.php", "#db_insert", $defined['good'], $errors['db_edit'], '600', '600' );
         }
        } else {
         $message = $err->GenerateErrorLink( "help/help.php", "#db_insert", $defined['good'], $errors['db_insert'], '600', '600' );
									
         // check to see if adding or editing to send email
         if( $send_email === "TRUE" ) {
 									
          // define some email header options
 									$headers .= "MIME-Version: 1.0\r\n";
 									$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
 									$headers .= "From: " . $defined['mail'] . "\r\n";
 									$headers .= "Reply-To: " . $defined['mail'] . "\r\n";
									
 									// format the message
 									$email_message .= "A new phpDHCPAdmin account has been setup on <b>'" . $defined['hostname'] . "'</b> for <b>'" . $user_username . "'</b> with password <b>'" . $user_password . "'</b>.<br><br>";
 									$email_message .= "In order to begin using this new account you must confirm and change your password. To do so please follow the link(s) below:<br><br>";
 									$email_message .= "http://" . $defined['hostname'] . " (without SSL)<br>";
 									$email_message .= "https://" . $defined['hostname'] . " (with SSL)<br>";
  								$email_message = wordwrap( $email_message, 20 );
									
 									// proceed to send the new user an email with account registration info
 									if( mail( $user_email, "Your phpDHCPAdmin Account", $email_message, $headers  ) ) {
 										$message = $err->GenerateErrorLink( "help/help.php", "#new_acct", $defined['good'], $errors['new_acct'], '600', '600' );	
 									} else {
 										$message = $err->GenerateErrorLink( "help/help.php", "#new_acct", $defined['error'], $errors['new_acct_err'], '600', '600' );
 									}
	        }								
        }

       } else {
        // create a reusable error link
        $erlink = $err->GenerateErrorImg( $defined['error'], "help/help.html#val_par", "#val_par", '600', '600' );
        $list = "<ol>";
        // determine our validate errors
        if( $val->ValidateAlphaChar( $user_username ) === -1 ) { $list .= "<li>Username field is invalid</li>"; $user_username_err = $erlink; }
        if( $val->ValidateString( $user_fname ) === -1 ) { $list .= "<li>User's first name field is invalid</li>"; $user_fname_err = $erlink; }
        if( $val->ValidateString( $user_lname ) === -1 ) { $list .= "<li>User's last name field is invalid</li>"; $user_lname_err = $erlink; }
        if( $val->ValidateParagraph( $user_department ) === -1 ) { $list .= "<li>Department field is invalid</li>"; $user_department_err = $erlink; }
        if( $val->ValidatePhone( $user_phone ) === -1 ) { $list .= "<li>The Phone field is invalid</li>"; $user_phone_err = $erlink; }
        if( $val->ValidateEmail( $user_email ) === -1 ) { $list .= "<li>Email field is invalid</li>"; $user_email_err = $erlink; }
        $list .= "</ol>";
        // give them an error message with link to the help file
        $message = $err->GenerateErrorLink( "help/help.php", "#val_par", $defined['error'], $errors['val_par'] . $list, '600', '600' );
       }
      } else {
       // create a reusable error link
       $erlink = $err->GenerateErrorImg( $defined['error'], "help/help.html#val_empty", "#val_empty", '600', '600' );
       // determine which fields are missing
       $list = "<ol>";
       if( empty( $user_username ) ) { $list .= "<li>Username field is empty</li>"; $user_username_err = $erlink; }
       if( empty( $user_fname ) ) { $list .= "<li>First name field is empty</li>"; $user_fname_err = $erlink; }
       if( empty( $user_lname ) ) { $list .= "<li>Last name field is empty</li>"; $user_lname_err = $erlink; }
       if( empty( $user_department ) ) { $list .= "<li>Department field is empty</li>"; $user_department_err = $erlink; }
       if( empty( $user_phone ) ) { $list .= "<li>The Phone field is empty</li>"; $user_phone_err = $erlink; }
       if( empty( $user_email ) ) { $list .= "<li>Email field is empty</li>"; $user_email_err = $erlink; }
       $list .= "</ol>";
       // give them an error message with link to the help file
       $message = $err->GenerateErrorLink( "help/help.php", "#val_missing", $defined['error'], $errors['val_missing'] . $list, '600', '600' );
      }
     }
    }
				
				// check for a simple password reset
				if( !empty( $_POST['ResetPassword'] ) ) {

     // check for required fields on password reset
					if( ( !empty( $user_pw_list ) ) && ( !empty( $user_pw_1 ) ) && ( !empty( $user_pw_2 ) ) ) {
 						
      // validate form data prior to password reset
						if( ( $val->ValidateAlphaChar( $user_pw_list ) !== -1 ) && ( $val->ValidatePasswordFields( $user_pw_1, $user_pw_2 ) === 0 ) ) {
 							
       // just one sql statement for password resets
							$sql = "UPDATE `auth_users` SET `password` = \"" . sha1( $user_pw_1 ) . "\", `reset` = \"FALSE\" WHERE `username` = \"" . $user_pw_list . "\" LIMIT 1";

       // begin processing our SQL object
       if( ( $sql_res = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) === -1 ) {
        if( eregi( "duplicate", $db->dbCatchError() ) ) {
         $sql = $update;
         $sql = $val->ValidateSQL( $sql, $dbconn );
         if( ( $sql_res = $db->dbQuery( $sql, $dbconn ) ) === -1 ) {
          $message = $err->GenerateErrorLink( "help/help.php", "#db_edit", $defined['error'], $errors['db_edit_err'], '600', '600' );
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
       if( $val->ValidateAlphaChar( $user_up_list ) === -1 ) { $list .= "<li>Username field is invalid</li>"; $user_pw_list_err = $erlink; }
       if( $val->ValidatePasswordFields( $user_pw_1, $user_pw_2 ) === -1 ) { $list .= "<li>" . $errors['val_pass_mtch'] . "</li>"; $user_pw_1_err = $erlink; $user_pw_2_err = $erlink; }
       if( $val->ValidatePasswordFields( $user_pw_1, $user_pw_2 ) === -2 ) { $list .= "<li>" . $errors['val_pass_fmt'] . "</li>"; $user_pw_1_err = $erlink; $user_pw_2_err = $erlink; }
       if( $val->ValidatePasswordFields( $user_pw_1, $user_pw_2 ) === -3 ) { $list .= "<li>Password fields are invalid, you cannot use the default as a password</li>"; $user_pw_1_err = $erlink; $user_pw_2_err = $erlink; }
							$list .= "</ol>";
							// give them an error message with link to the help file
       $message = $err->GenerateErrorLink( "help/help.php", "#val_par", $defined['error'], $errors['val_pw_reset'] . $list, '600', '600' );
						}
					} else {
						// create a reusable error link
      $erlink = $err->GenerateErrorImg( $defined['error'], "help/help.html#val_empty", "#val_empty", '600', '600' );
      // determine which fields are missing
      $list = "<ol>";
      if( empty( $user_pw_list ) ) { $list .= "<li>Username field is empty</li>"; $user_pw_list_err = $erlink; }
      if( empty( $user_pw_1 ) ) { $list .= "<li>Password field is empty</li>"; $user_pw_1_err = $erlink; }
      if( empty( $user_pw_2 ) ) { $list .= "<li>Password field is empty</li>"; $user_pw_2_err = $erlink; }
      $list .= "</ol>";
						// give them an error message with link to the help file
      $message = $err->GenerateErrorLink( "help/help.php", "#val_missing", $defined['error'], $errors['val_missing'] . $list, '600', '600' );
					}
				}
				
    // assign our data to the template
    $tpl->assign( 'message', $message, NULL, NULL );
    $tpl->assign( 'user_id', $val->ValidateXSS( $user_id ), NULL, NULL );
    $tpl->assign( 'user_username', $val->ValidateXSS ($user_username ), NULL, NULL );
    $tpl->assign( 'user_fname', $val->ValidateXSS( $user_fname ), NULL, NULL );
    $tpl->assign( 'user_lname', $val->ValidateXSS( $user_lname ), NULL, NULL );
    $tpl->assign( 'user_department', $val->ValidateXSS( $user_department ), NULL, NULL );
    $tpl->assign( 'user_contact', $val->ValidateXSS( $user_contact ), NULL, NULL );
    $tpl->assign( 'user_phone', $val->ValidateXSS( $user_phone ), NULL, NULL );
    $tpl->assign( 'user_email', $val->ValidateXSS( $user_email ), NULL, NULL );
    
    // and the corresponding errors if any
    $tpl->assign( 'user_username_err', $user_username_err, NULL, NULL );
    $tpl->assign( 'user_pw_list_err', $user_pw_list_err, NULL, NULL );
				$tpl->assign( 'user_pw_1_err', $user_pw_1_err, NULL, NULL );
				$tpl->assign( 'user_pw_2_err', $user_pw_2_err, NULL, NULL );
    $tpl->assign( 'user_fname_err', $user_fname_err, NULL, NULL );
    $tpl->assign( 'user_lname_err', $user_lname_err, NULL, NULL );
    $tpl->assign( 'user_department_err', $user_department_err, NULL, NULL );
    $tpl->assign( 'user_phone_err', $user_phone_err, NULL, NULL );
    $tpl->assign( 'user_email_err', $user_email_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "auth_users", $dbconn );
 			
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