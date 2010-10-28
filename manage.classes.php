<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * manage.classes.php - Manage user defined classes
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
 	$tpl->assign( 'DESCRIPTION', "Manage Classes", NULL, NULL );
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
 			$JS = " hidediv('perms');";
 			$FILE = "manage.classes.tpl";

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // decode our authentication token to get our group membership
				$user_details = $encrypt->DecodeAuthToken( $_SESSION['token'] );
				$group = base64_decode( $user_details[3] );

    // create list of class options
    $optsquery = "DESCRIBE `conf_classes_opts`";
    if( ( $current = $db->dbQuery( $val->ValidateSQL( $optsquery, $dbconn ), $dbconn ) ) !== -1 ) {
     $optslist = $db->dbArrayResultsAssoc( $current );
 		 }
    if( count( $optslist ) === 0 ) {
     $class_option = "Class Options table missing";
    } else {
     foreach( $optslist as $key => $value ) {
      if( $value['Field'] !== "id" ) {
       $encoded[$value['Field']] = $value['Type'];
      }
     }
     $encoded = json_encode( $encoded );
    }

    // Look for a GET id post to edit existing dnssec keys
    if( !empty( $_GET['id'] ) ) {
     if( $val->ValidateParagraph( $_GET['id'] ) === -1 ) {
      $error = $err->GenerateErrorLink( "help/help.html", "#config_classes", $defined['error'], $errors['val_num'], NULL, NULL );
     } else {
      // populate the form with database information if already configured
						if( $group === "admin" ) {
  				 $query = "SELECT * FROM `conf_classes` WHERE `class-name` = \"" . $_GET['id'] . "\" LIMIT 1";
       $options = "SELECT * FROM `conf_classes_options` WHERE `class-name` = \"" . $_GET['id'] . "\"";
						} else {
						 $query = "SELECT * FROM `conf_classes` WHERE `group` = \"" . $group . "\" OR `group` = \"\" AND `class-name` = \"" . $_GET['id'] . "\" LIMIT 1";
       $options = "SELECT * FROM `conf_classes_options` WHERE ( `group` = \"" . $group . "\" OR `group` = \"\" ) AND `class-name` = \"" . $_GET['id'] . "\"";
						}
 		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "help/help.html", "#config_classes", $defined['error'], $errors['db_select'], NULL, NULL );
      } else {
       $data = $db->dbArrayResultsAssoc( $value );
 				 	$id = $data[0]['id'];
 				 	$class_name = $data[0]['class-name'];
  		   if( ( $value = $db->dbQuery( $val->ValidateSQL( $options, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_classes", $defined['error'], $errors['db_select'], NULL, NULL );
       } else {
        $class_opts = $db->dbArrayResultsAssoc( $value );
        // generate array of match options
        $match_opts = array( "if", "option", "pick-first-value" );
        $num = 0;
        //echo "<pre>"; print_r( $class_opts ); echo "</pre>";
        for( $i = 0; $i <= count( $class_opts ); $i++ ) {
         // generate array of select boxes
         $class_option[] = $misc->GenDropMenuWSelectedClassOpts( $class_opts, $class_opts[$i]['class-option'], 'options[' . $i . '][option]' );
         $select2[] = $misc->GenDropMenuWSelectedClassOpts( $match_opts, $class_opts[$i]['class-match-option'], 'options[' . $i . '][match_opt]' );
         // generate radio option settings
         if( $class_opts[$num]['class-match'] === "FALSE" ) { $err1[$num]['match_disable'] = "checked"; } else { $err1[$num]['match_enable'] = "checked"; }
         if( $class_opts[$num]['class-substring'] === "FALSE" ) { $err1[$num]['substring_disable'] = "checked"; } else { $err1[$num]['substring_enable'] = "checked"; }
         $num++; $count = count( $class_opts );
        }
       }
       // populate form with errors if necessary
       $form = $misc->GenTableClassOptsAssoc( count( $class_opts ), $class_opts, $class_option, $select2, $err1 );
 		   }
     }
    }

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {
 				
     // apply a fix on class-options array by reindexing starting at 1 vs. 0
     $_POST['options'] = $misc->ReIndexArray( $_POST['options'] );
     //echo "<pre>"; print_r( $_POST['options'] ); echo "</pre>";
     
     // re-assign vars for processing and template assignment
     $id = $_POST['id'];
 				$class_name = $_POST['class_name'];
     $class_opts = $_POST['options'];
 				$class_option = $_POST['class_option'];
     $class_value = $_POST['class_value'];
				
     // generate array of match options
     $match_opts = array( "if", "pick-first-value" );

     // since we need an accurate count for the options lists
     for( $i = 1; $i <= count( $_POST['options'] ); $i++ ) {
      
      // generate array of select boxes
      $class_option[$i] = $misc->GenDropMenuWSelectedClassOpts( $optslist, $class_opts[$i]['option'], 'options[' . $i . '][option]' );
      $select2[$i] = $misc->GenDropMenuWSelectedClassOpts( $match_opts, $class_opts[$i]['match_opt'], 'options[' . $i . '][match_opt]' );
      
      // generate radio option settings
      if( $class_opts[$i]['match'] === "FALSE" ) { $err1[$i]['match_disable'] = "checked"; } else { $err1[$i]['match_enable'] = "checked"; }
      if( $class_opts[$i]['substring'] === "FALSE" ) { $err1[$i]['substring_disable'] = "checked"; } else { $err1[$i]['substring_enable'] = "checked"; }
      
      // generate error links if necessary
      $e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_class", '800', '800' );
      
      // make sure these options should be enabled first
      if( $class_opts[$i]['match'] === "TRUE" ) {
       if( empty( $class_opts[$i]['match_opt'] ) ) { $emp === "TRUE"; $err1[$i]['match_opt'] = $e; $listop_empty .= "<li>The match option field is empty</li>"; }
       if( $val->ValidateParagraph( $class_opts[$i]['match_opt'] ) === -1 ) { $stop = "TRUE"; $err1[$i]['match_opt'] = $e; $listop_val .= "<li>The match option field is invalid, please select a valid option from the list</li>"; }
      }
      
      if( $class_opts[$i]['substring'] === "TRUE" ) {
       // checking empty values to provide errors
       if( empty( $class_opts[$i]['substring_start'] ) ) { $emp = "TRUE"; $err1[$i]['substring'] = $e; $listop_empty .= "<li>The substring start field is empty</li>"; }
       if( empty( $class_opts[$i]['substring_end'] ) ) { $emp = "TRUE"; $err1[$i]['substring'] = $e; $listop_empty .= "<li>The substring end field is empty</li>"; }
       if( empty( $class_opts[$i]['substr_regex'] ) ) { $emp = "TRUE"; $err1[$i]['substr_regex'] = $e; $listop_empty .= "<li>The regex field is empty</li>"; }
       // check formating of data to provide errors
       if( $val->ValidateInteger( $class_opts[$i]['substring_start'] ) === -1 ) { $err1[$i]['substring'] = $e; $stop = "TRUE"; $listop_val .= "<li>The substring start field is invalid, integers only</li>"; }
       if( $val->ValidateInteger( $class_opts[$i]['substring_end'] ) === -1 ) { $err1[$i]['substring'] = $e; $stop = "TRUE"; $listop_val .= "<li>The substring end field is invalid, integers only</li>"; }
       if( $val->ValidateAlphaChar( $class_opts[$i]['substr_regex'] ) === -1 ) { $err1[$i]['substr_regex'] = $e; $stop = "TRUE"; $listop_val .= "<li>The regex field is invalid, alpha numeric characters only</li>";}
      }
     }

     // check each post element
     if( ( !empty( $class_name ) ) && ( $emp !== "TRUE" ) ) {
       
      // get field type based on $class_option
      $chk = "DESCRIBE `conf_classes_opts` `$class_option`";
      if( ( $value = $db->dbQuery( $val->ValidateSQL( $chk, $dbconn ), $dbconn ) ) !== -1 ) {
       $chkvals = $db->dbArrayResultsAssoc( $value );
      }

						// begin validation of configuration options
      if( ( $val->ValidateParagraph( $class_name ) !== -1 ) && ( $stop !== "TRUE" ) ) {
       
 						// define our sql statements (exclude the group field if user is member of admin group)
							if( $group !== "admin" ) {
  						$i_class = "INSERT INTO `conf_classes` ( `class-name`, `group` ) VALUES ( \"" . $class_name . "\", \"" . $group . "\" )";
        $u_class = "UPDATE `conf_classes` SET `class-name` = \"" . $class_name . "\" AND `group` = \"" . $group . "\" WHERE `class-name` = \"" . $class_name . "\" LIMIT 1";
        for( $i = 1; $i <= count( $class_opts ); $i++ ) {
   		    $i_options[] = "INSERT INTO `conf_classes_options` ( `class-name`, `class-option`, `class-match`, `class-match-option`, `class-substring`, `class-substring-start`, `class-substring-end`, `match-substring-regex`, `group` ) VALUES ( \"" . $class_name . "\", \"" . $class_opts[$i]['option'] . "\", \"" . $class_opts[$i]['match'] . "\", \"" . $class_opts[$i]['match_opt'] . "\", \"" . $class_opts[$i]['substring'] . "\", \"" . $class_opts[$i]['substring_start'] . "\", \"" . $class_opts[$i]['substring_end'] . "\", \"" . $class_opts[$i]['substr_regex'] . "\", \"" . $group . "\" )";
         if( empty( $_GET[$i]['id'] ) ) {
          $u_options[] = "UPDATE `conf_classes_options` SET `class-name` = \"" . $class_name . "\", `class-option` = \"" . $class_opts[$i]['option'] . "\", `class-match` = \"" . $class_opts[$i]['match'] . "\", `class-match-option` = \"" . $class_opts[$i]['match_opt'] . "\", `class-substring` = \"" . $class_opts[$i]['substring'] . "\", `class-substring-start` = \"" . $class_opts[$i]['substring_start'] . "\", `class-substring-end` = \"" . $class_opts[$i]['substring_end'] . "\", `match-substring-regex` = \"" . $class_opts[$i]['substr_regex'] . "\", `group` = \"" . $group . "\" WHERE `id` = \"" . $class_opts[$i]['id'] . "\" LIMIT 1";
         } else {
          $u_options[] = "INSERT INTO `conf_classes_options` ( `class-name`, `class-option`, `class-match`, `class-match-option`, `class-substring`, `class-substring-start`, `class-substring-end`, `match-substring-regex`, `group` ) VALUES ( \"" . $class_name . "\", \"" . $class_opts[$i]['option'] . "\", \"" . $class_opts[$i]['match'] . "\", \"" . $class_opts[$i]['match_opt'] . "\", \"" . $class_opts[$i]['substring'] . "\", \"" . $class_opts[$i]['substring_start'] . "\", \"" . $class_opts[$i]['substring_end'] . "\", \"" . $class_opts[$i]['substr_regex'] . "\", \"" . $group . "\" )";
         }
        }
       } else {
  						$i_class = "INSERT INTO `conf_classes` ( `class-name` ) VALUES ( \"" . $class_name . "\" )";
        $u_class = "UPDATE `conf_classes` SET `class-name` = \"" . $class_name . "\" WHERE `class-name` = \"" . $class_name . "\" LIMIT 1";
  						for( $i = 1; $i <= count( $class_opts ); $i++ ) {
   		    $i_options[] = "INSERT INTO `conf_classes_options` ( `class-name`, `class-option`, `class-match`, `class-match-option`, `class-substring`, `class-substring-start`, `class-substring-end`, `match-substring-regex` ) VALUES ( \"" . $class_name . "\", \"" . $class_opts[$i]['option'] . "\", \"" . $class_opts[$i]['match'] . "\", \"" . $class_opts[$i]['match_opt'] . "\", \"" . $class_opts[$i]['substring'] . "\", \"" . $class_opts[$i]['substring_start'] . "\", \"" . $class_opts[$i]['substring_end'] . "\", \"" . $class_opts[$i]['substr_regex'] . "\" )";
         if( !empty( $class_opts[$i]['id'] ) ) {
          $u_options[] = "UPDATE `conf_classes_options` SET `class-name` = \"" . $class_name . "\", `class-option` = \"" . $class_opts[$i]['option'] . "\", `class-match` = \"" . $class_opts[$i]['match'] . "\", `class-match-option` = \"" . $class_opts[$i]['match_opt'] . "\", `class-substring` = \"" . $class_opts[$i]['substring'] . "\", `class-substring-start` = \"" . $class_opts[$i]['substring_start'] . "\", `class-substring-end` = \"" . $class_opts[$i]['substring_end'] . "\", `match-substring-regex` = \"" . $class_opts[$i]['substr_regex'] . "\" WHERE `id` = \"" . $class_opts[$i]['id'] . "\" LIMIT 1";
         } else {
          $u_options[] = "INSERT INTO `conf_classes_options` ( `class-name`, `class-option`, `class-match`, `class-match-option`, `class-substring`, `class-substring-start`, `class-substring-end`, `match-substring-regex` ) VALUES ( \"" . $class_name . "\", \"" . $class_opts[$i]['option'] . "\", \"" . $class_opts[$i]['match'] . "\", \"" . $class_opts[$i]['match_opt'] . "\", \"" . $class_opts[$i]['substring'] . "\", \"" . $class_opts[$i]['substring_start'] . "\", \"" . $class_opts[$i]['substring_end'] . "\", \"" . $class_opts[$i]['substr_regex'] . "\" )";
         }
        }
       }
 						$d_class = "DELETE FROM `conf_classes` WHERE `class-name` = \"" . $class_name . "\" LIMIT 1";
       $d_options = "DELETE FROM `conf_classes_options` WHERE `id` = \"" . $class_opts[$i]['id'] . "\" LIMIT 1";

 						// determine which button was clicked
 						if( !empty( $_POST['AddClass'] ) ) { $query = $i_class; $sql_arr = $i_options; $db_msg_good = $errors['db_insert']; $db_msg_err = $errors['db_insert_err']; }
 						if( !empty( $_POST['EditClass'] ) ) { $query = $u_class; $sql_arr = $u_options; $db_msg_good = $errors['db_edit']; $db_msg_err = $errors['db_edit_err']; }
 						if( !empty( $_POST['DelClass'] ) ) { $query = $d_class; $sql_arr = $d_options; $db_msg_good = $errors['db_del']; $db_msg_err = $errors['db_del_err']; }

       // initialize a db connection handle
       $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

 						// process our query
 						if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['error'], $db_msg_err, NULL, NULL );
        // attempt to update if record exists
        if( eregi( "duplicate", $db->dbCatchError() ) ) {
 								if( ( $value = $db->dbQuery( $val->ValidateSQL( $u_class, $dbconn ), $dbconn ) ) === -1 ) {
 							  $error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['error'], $errors['db_edit_err'], NULL, NULL );
         } else {
 									$error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['good'], $errors['db_edit'], NULL, NULL );
          // process update on class options using the sql_arr data
          $sql_arr = $u_options;
          for( $i = 0; $i < count( $sql_arr ); $i++ ) {
     						if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql_arr[$i], $dbconn ), $dbconn ) ) === -1 ) {
            $error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['error'], $db_msg_err, NULL, NULL );
           } else {
            $error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['good'], $db_msg_good, NULL, NULL );
           }
          }
 								}
 							}
       } else {
 							$error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['good'], $db_msg_good, NULL, NULL );
        // process insert on class options using the sql_arr data
        for( $i = 0; $i < count( $sql_arr ); $i++ ) {
   						if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql_arr[$i], $dbconn ), $dbconn ) ) === -1 ) {
          $error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['error'], $db_msg_err, NULL, NULL );
         } else {
          $error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['good'], $db_msg_good, NULL, NULL );
         }
        }
 						}
      } else {
						 // find validation errors
 						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_class", '800', '800' );
  					$list .= "<ol>";
       if( $val->ValidateParagraph( $class_name ) === -1 ) { $list .= "<li>Class name field is invalid</li>"; $class_name_err = $e; }
       $list .= $listop_val;
							$list .= "</ol>";
       $error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['error'], "An error occured while validating fields, review details below:" . $list, NULL, NULL );
							// set our extras to be visable if one of them is broken
       if( $xtra === 1 ) { $JS = " showdiv( 'extras' );"; }
      }
     } else {
      // look to see which fields were empty
 					$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#config_class", '800', '800' );
 					$list .= "<ol>";
      if( empty( $class_name ) ) { $list .= "<li>Class name field is missing</li>"; $class_name_err = $e; }
      $list .= $listop_empty;
      $list .= "</ol>";
      $error = $err->GenerateErrorLink( "help/help.html", "#config_class", $defined['error'], "An error occured, fields missing, review details below:" . $list, NULL, NULL );
     }
     // populate form with errors if necessary
     $form = $misc->GenTableClassOpts( count( $_POST['options'] ), $_POST['options'], $class_option, $select2, $err1 );
    }

    // create current list of classes currently defined
				if( $group === "admin" ) {
     $classquery = "SELECT * FROM `conf_classes` ORDER BY `class-name` ASC";
				} else {
				 $classquery = "SELECT * FROM `conf_classes` WHERE `group` = \"" . $group . "\" OR `group` = \"\" ORDER BY `class-name` ASC";
				}
 		 if( ( $current = $db->dbQuery( $val->ValidateSQL( $classquery, $dbconn ), $dbconn ) ) !== -1 ) {
     $classlist = $db->dbArrayResultsAssoc( $current );
 		 }
 			$classes = $misc->GenJumpMenuBoxClasses( $classlist, 'classes', $_GET['skin'] );

    // provide a count value on class options
    if( count( $_POST['options'] ) !== 0 ) { $count = count( $_POST['options'] ); }

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

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'id', $val->ValidateXSS( $id ), NULL, NULL );
    $tpl->assign( 'class_name', $val->ValidateXSS( $class_name ), NULL, NULL );
    $tpl->assign( 'class_value', $val->ValidateXSS( $class_value ), NULL, NULL );
    $tpl->assign( 'class_option', $class_option, NULL, NULL );
    $tpl->assign( 'classes', $classes, NULL, NULL );
    $tpl->assign( 'encoded', $encoded, NULL, NULL );
    $tpl->assign( 'form', $form, NULL, NULL );
    $tpl->assign( 'count', $count, NULL, NULL );
				$tpl->assign( 'select_groups', $select_groups, NULL, NULL );
    $tpl->assign( 'ex_group', $val->ValidateXSS( $ex_group ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'class_name_err', $class_name_err, NULL, NULL );
    $tpl->assign( 'class_option_err', $class_option_err, NULL, NULL );
    $tpl->assign( 'class_value_err', $class_value_err, NULL, NULL );
    $tpl->assign( 'classes_err', $classes_err, NULL, NULL );
				$tpl->assign( 'select_groups_err', $select_groups_err, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_classes", $dbconn );
    $db->dbFixTable( "conf_classes_options", $dbconn );
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