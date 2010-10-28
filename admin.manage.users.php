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
 	$tpl->assign( 'DESCRIPTION', "Manage Users", NULL, NULL );
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
 			$JS = NULL;
    $FILE = "admin.manage.users.tpl";
				$user_ip = $_SERVER['REMOTE_ADDR'];
				$user_host = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
				$user_create_date = $misc->GenDate();
    $user_create_time = $misc->GenTimeRead();;
    
				    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

				// decode our authentication token to get our group membership
				$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$group = base64_decode( $user_details[3] );
    
    // default errors for required fields
    $user_username_err = "*";
    $user_password_1_err = "*";
    $user_password_2_err = "*";
    $user_fname_err = "*";
    $user_lname_err = "*";
    $user_access_level_err = "*";
    $user_group_level_err = "*";
    $user_department_err = "*";
    $user_contact_err = "*";
    $user_phone_err = "*";
    $user_email_err = "*";

    // Look for a GET id post to edit existing dnssec keys
    if( !empty( $_GET['id'] ) ) {
     if( $val->ValidateInteger( $_GET['id'] ) === -1 ) {
      $message = $err->GenerateErrorLink( "help/help.html", "#user_edit", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
						if( $group === "admin" ) {
  				 $query = "SELECT * FROM `auth_users` WHERE `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						} else {
						 $query = "SELECT * FROM `auth_users` WHERE `group` = \"" . $group . "\" AND `id` = \"" . $_GET['id'] . "\" LIMIT 1";
						}
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) { echo $query;
       $message = $err->GenerateErrorLink( "help/help.html", "#user_edit", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
 				 	$user_id = $data[0]['id'];
 				 	$user_username = $data[0]['username'];
 				  $user_fname = $data[0]['first'];
 				  $user_lname = $data[0]['last'];
       $user_access_level = $data[0]['level'];
       $user_group = $data[0]['group'];
       $user_department = $data[0]['dept'];
       $user_contact = $data[0]['contact'];
       $user_phone = $data[0]['phone'];
       $user_email = $data[0]['email'];
       $message = $err->GenerateErrorLink( "help/help.html", "#edit_user", $defined['good'], "You are currently editing record #" . $user_id, NULL, NULL );
	 	   }
     }
    }

    // check for form submission first
    if( !empty( $_POST ) ) {

     // setup our form variables
					$user_id = $_POST['user_id'];
     $user_username = $_POST['user_username'];
     $user_fname = $_POST['user_fname'];
     $user_lname = $_POST['user_lname'];
     $user_access_level = $_POST['user_access_level'];
     $user_group = $_POST['user_group'];
     $user_department = $_POST['user_department'];
     $user_contact = $_POST['user_contact'];
     $user_address = $_POST['user_address'];
     $user_phone = $_POST['user_phone'];
     $user_email = $_POST['user_email'];
					$user_pw_list = $_POST['user_pw_list'];
     $user_pw_1 = $_POST['user_pw_1'];
					$user_pw_2 = $_POST['user_pw_2'];
     $GenRandomPw = $_POST['GenRandomPw'];

     // check for our form type
     if( ( !empty( $_POST['AddUser'] ) ) || ( !empty( $_POST['EditUser'] ) ) || ( !empty( $_POST['DelUser'] ) ) ) {

      // check for empty variables
      if( ( !empty( $user_username ) ) && ( !empty( $user_fname ) ) && ( !empty( $user_lname ) ) && ( !empty( $user_access_level ) ) && ( !empty( $user_group ) ) && ( !empty( $user_department ) ) && ( !empty( $user_fname ) ) && ( !empty( $user_lname ) ) && ( !empty( $user_phone ) ) && ( !empty( $user_email ) ) ) {

       // do some validation checks on submitted data
       if( ( $val->ValidateAlphaChar( $user_username ) !== -1 ) && ( $val->ValidateString( $user_fname ) !== -1 ) && ( $val->ValidateString( $user_lname ) !== -1 ) && ( $val->ValidateString( $user_access_level ) !== -1 ) && ( $val->ValidateString( $user_group ) !== -1 ) && ( $val->ValidateParagraph( $user_department ) !== -1 ) && ( $val->ValidateString( $user_contact ) !== -1 ) && ( $val->ValidatePhone( $user_phone ) !== -1 ) && ( $val->ValidateEmail( $user_email ) !== -1 ) ) {

        // leave the owner assignment alone if owner is not admin
 							if( $group === "admin" ) { $group = $user_group; }
							
        // generate random password if this is a new user
        if( !empty( $_POST['AddUser'] ) ) { 	$user_password = $val->GenerateRandomPassword( "12", "normal" ); }
       
 							// setup our SQL statements for add, edit and deleting records
        $insert = "INSERT INTO `auth_users` ( `username`, `password`, `level`, `group`, `dept`, `first`, `last`, `phone`, `email`, `ip`, `host`, `create_date`, `create_time`, `access_date`, `access_time`, `session`, `reset`, `owner` ) VALUES ( \"" . $user_username . "\", \"" . sha1( $user_password ) . "\", \"" . $user_access_level . "\", \"" . $user_group . "\", \"" . $user_deptartment . "\", \"" . $user_fname . "\", \"" . $user_lname . "\", \"" . $user_phone . "\", \"" . $user_email . "\", \"" . $user_ip . "\", \"" . $user_host . "\", \"" . $user_create_date . "\", \"" . $user_create_time . "\", \"" . $user_access_date . "\", \"" . $user_access_time . "\", \"" . $user_session . "\", \"TRUE\", \"" . $group . "\" )";
        $update = "UPDATE `auth_users` SET `username` = \"" . $user_username . "\", `level` = \"" . $user_access_level . "\", `group` = \"" . $user_group . "\", `dept` = \"" . $user_department . "\", `first` = \"" . $user_fname . "\", `last` = \"" . $user_lname . "\", `phone` = \"" . $user_phone . "\", `email` = \"" . $user_email . "\", `ip` = \"" . $user_ip . "\", `host` = \"" . $user_host . "\", `owner` = \"" . $group . "\" WHERE `id` = \"" . $user_id . "\" LIMIT 1";
        $delete = "DELETE FROM `auth_users` WHERE `id` = \"" . $user_id . "\" LIMIT 1";

        // now perform a check to see which statement to use
        if( !empty( $_POST['AddUser'] ) ) { $sql = $insert; $send_email = "TRUE"; }
        if( !empty( $_POST['EditUser'] ) ) { $sql = $update; $send_email = "FALSE"; }
        if( !empty( $_POST['DelUser'] ) ) { $sql = $delete; $send_email = "FALSE"; }

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
 									if( mail( $user_email, "New phpDHCPAdmin Account", $email_message, $headers  ) ) {
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
        if( $val->ValidateString( $user_access_level ) === -1 ) { $list .= "<li>The access level field is invalid</li>"; $user_access_level_err = $erlink; }
        if( $val->ValidateString( $user_group ) === -1 ) { $list .= "<li>The Group field is invalid</li>"; $user_group_err = $erlink; }
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
       if( empty( $user_access_level ) ) { $list .= "<li>The access level field is empty</li>"; $user_access_level_err = $erlink; }
       if( empty( $user_group ) ) { $list .= "<li>The Group field is empty</li>"; $user_group_err = $erlink; }
       if( empty( $user_department ) ) { $list .= "<li>Department field is empty</li>"; $user_department_err = $erlink; }
       if( empty( $user_address ) ) { $list .= "<li>Address field is empty</li>"; $user_address_err = $erlink; }
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

     // get the email address of the user so this doesnt fail
     $sql = "SELECT `email`, `reset` FROM `auth_users` WHERE `username` = \"" . $user_pw_list . "\" LIMIT 1";
     if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) === -1 ) {
      $message = $err->GenerateErrorLink( "help/help.php", "#db_select", $defined['error'], $errors['db_select'], '600', '600' );
     } else {
      $data = $db->dbArrayResultsAssoc( $value );
      $user_email =  $data[0]['email'];
      if( $data[0]['reset'] === "TRUE" ) { $reset = "FALSE"; }
     }

     // generate a random one to email to user?
     if( ( !empty( $GenRandomPw ) ) && ( !empty( $user_pw_list ) ) ) {
      
      // generate a random pw and setup our sql statement
      if( !empty( $GenRandomPw ) ) {
       $user_password = $val->GenerateRandomPassword( "12", "normal" ); $reset = "TRUE";
      } else {
       $user_password = $user_pw_1; $reset = "TRUE";
      }
      
      // setup our sql statement
      $sql = "UPDATE `auth_users` SET `password` = \"" . sha1( $user_password ) . "\", `reset` = \"" . $reset . "\" WHERE `username` = \"" . $user_pw_list . "\" LIMIT 1";

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

       // define some email header options
 						$headers .= "MIME-Version: 1.0\r\n";
 						$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
 						$headers .= "From: " . $defined['mail'] . "\r\n";
 						$headers .= "Reply-To: " . $defined['mail'] . "\r\n";
									
 						// format the message
 						$email_message .= "Your phpDHCPAdmin account has had the password reset on <b>'" . $defined['hostname'] . "'</b> for <b>'" . $user_username . "'</b> with password <b>'" . $user_password . "'</b>.<br><br>";
 						$email_message .= "Once you login we suggest you change your password to something you will remember. To do so please follow the link(s) below:<br><br>";
 						$email_message .= "http://" . $defined['hostname'] . " (without SSL)<br>";
 						$email_message .= "https://" . $defined['hostname'] . " (with SSL)<br>";
  					$email_message = wordwrap( $email_message, 20 );
							
 						// proceed to send the new user an email with account registration info
 						if( mail( $user_email, "New phpDHCPAdmin Account", $email_message, $headers  ) ) {
 							$message = $err->GenerateErrorLink( "help/help.php", "#new_acct", $defined['good'], $errors['new_acct'], '600', '600' );	
 						} else {
 							$message = $err->GenerateErrorLink( "help/help.php", "#new_acct", $defined['error'], $errors['new_acct_err'], '600', '600' );
 						}
      }
   
     // use the form to our users password
     } else {
 					
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
         
         // define some email header options
 	  					$headers .= "MIME-Version: 1.0\r\n";
 	  					$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
 	  					$headers .= "From: " . $defined['mail'] . "\r\n";
 	  					$headers .= "Reply-To: " . $defined['mail'] . "\r\n";
									
 	  					// format the message
   						$email_message .= "Your phpDHCPAdmin account has had the password reset on <b>'" . $defined['hostname'] . "'</b> for <b>'" . $user_username . "'</b> with password <b>'" . $user_pw_1 . "'</b>.<br><br>";
   						$email_message .= "Once you login we suggest you change your password to something you will remember. To do so please follow the link(s) below:<br><br>";
   						$email_message .= "http://" . $defined['hostname'] . " (without SSL)<br>";
   						$email_message .= "https://" . $defined['hostname'] . " (with SSL)<br>";
    					$email_message = wordwrap( $email_message, 20 );
									
   						// proceed to send the new user an email with account registration info
   						if( mail( $user_email, "New phpDHCPAdmin Account", $email_message, $headers  ) ) {
   							$message = $err->GenerateErrorLink( "help/help.php", "#new_acct", $defined['good'], $errors['new_acct'], '600', '600' );	
   						} else {
 	  						$message = $err->GenerateErrorLink( "help/help.php", "#new_acct", $defined['error'], $errors['new_acct_err'], '600', '600' );
   						}
         
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
    }
				
    // get a list of current groups
    if( count( $user_list ) === 0 ) {
				 if( $group === "admin" ) {
      $user_query = "SELECT * FROM `auth_users` ORDER BY `group` ASC";
					} else {
					 $user_query = "SELECT * FROM `auth_users` WHERE `owner` = \"" . $group . "\" ORDER BY `group` ASC";
					}
  		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $user_query, $dbconn ), $dbconn ) ) !== -1 ) {
      $user_list = $db->dbArrayResultsAssoc( $current );
  		 }
					$user_pw_list = $misc->GenDropMenuWSelectedUsers( $user_list, $user_pw_list, 'user_pw_list' );
  			$user_list = $misc->GenJumpMenuBoxUSERS( $user_list, 'user_list', $_GET['skin'] );
    }

    // Generate a current list of access levels to select from
    $lvl = "SELECT `level` FROM `auth_levels` ORDER BY `level` ASC";
 		 if( ( $return = $db->dbQuery( $val->ValidateSQL( $lvl, $dbconn ), $dbconn ) ) !== -1 ) {
     $lvls = $db->dbArrayResultsAssoc( $return );
 		 }
    if( count( $lvls ) === 0 ) {
     $user_access_level = "No Access Levels Defined";
    } else {
 			 $user_access_level = $misc->GenDropMenuWSelectedLevels( $lvls, $user_access_level, 'user_access_level' );
    }
   
    // Generate a list of user groups to select from (limit by group)
    if( $group === "admin" ) {
     $grp = "SELECT `group` FROM `auth_groups` ORDER BY `group` ASC";
    } else {
     $grp = "SELECT `group` FROM `auth_groups` WHERE `group` = \"" . $group . "\" ORDER BY `group` ASC";
    }
 		 if( ( $return = $db->dbQuery( $val->ValidateSQL( $grp, $dbconn ), $dbconn ) ) !== -1 ) {
     $grps = $db->dbArrayResultsAssoc( $return );
 		 }
    if( count( $grps ) === 0 ) {
 			 $user_group = "No Groups defined";
    } else {
     $user_group = $misc->GenDropMenuWSelectedGroups( $grps, $user_group, 'user_group' );
    }

    // assign our data to the template
    $tpl->assign( 'message', $message, NULL, NULL );
    $tpl->assign( 'user_id', $val->ValidateXSS( $user_id ), NULL, NULL );
    $tpl->assign( 'user_group', $user_group, NULL, NULL );
    $tpl->assign( 'user_list', $user_list, NULL, NULL );
				$tpl->assign( 'user_pw_list', $user_pw_list, NULL, NULL );
    $tpl->assign( 'user_access_level', $user_access_level, NULL, NULL );
    $tpl->assign( 'user_username', $val->ValidateXSS ($user_username ), NULL, NULL );
    $tpl->assign( 'user_fname', $val->ValidateXSS( $user_fname ), NULL, NULL );
    $tpl->assign( 'user_lname', $val->ValidateXSS( $user_lname ), NULL, NULL );
    $tpl->assign( 'user_department', $val->ValidateXSS( $user_department ), NULL, NULL );
    $tpl->assign( 'user_contact', $val->ValidateXSS( $user_contact ), NULL, NULL );
    $tpl->assign( 'user_address', $val->ValidateXSS( $user_address ), NULL, NULL );
    $tpl->assign( 'user_phone', $val->ValidateXSS( $user_phone ), NULL, NULL );
    $tpl->assign( 'user_email', $val->ValidateXSS( $user_email ), NULL, NULL );
    
    // and the corresponding errors if any
    $tpl->assign( 'user_group_err', $user_group_err, NULL, NULL );
    $tpl->assign( 'user_access_level_err', $user_access_level_err, NULL, NULL );
    $tpl->assign( 'user_username_err', $user_username_err, NULL, NULL );
    $tpl->assign( 'user_password_1_err', $user_password_1_err, NULL, NULL );
    $tpl->assign( 'user_password_2_err', $user_password_2_err, NULL, NULL );
    $tpl->assign( 'user_pw_list_err', $user_pw_list_err, NULL, NULL );
				$tpl->assign( 'user_pw_1_err', $user_pw_1_err, NULL, NULL );
				$tpl->assign( 'user_pw_2_err', $user_pw_2_err, NULL, NULL );
    $tpl->assign( 'user_fname_err', $user_fname_err, NULL, NULL );
    $tpl->assign( 'user_lname_err', $user_lname_err, NULL, NULL );
    $tpl->assign( 'user_department_err', $user_department_err, NULL, NULL );
    $tpl->assign( 'user_contact_err', $user_contact_err, NULL, NULL );
    $tpl->assign( 'user_address_err', $user_address_err, NULL, NULL );
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