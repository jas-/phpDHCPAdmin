<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * admin.import.hosts.php - DHCPD Import static hosts using xml or csv files
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
 	$tpl->assign( 'DESCRIPTION', "Import File of Static Hosts", NULL, NULL );
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
 			$JS = NULL; $error_template = NULL;
 			$FILE = "admin.import.hosts.tpl";
    //$FILE = "notfinished.tpl";

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // begin our validation on submitted data
    if( !empty( $_POST ) ) {

     // process file if present
     if( !empty( $_FILES ) ) {
      
      // re-assign vars for processing and template assignment
  				$file_name = $_FILES['file_name']['name'];
      $uploaded = $_FILES['file_name']['tmp_name'];
      $type = $_FILES['file_name']['type'];
      $size = $_FILES['file_name']['size'];

      if( ( !empty( $file_name ) ) && ( !empty( $uploaded ) ) && ( !empty( $type ) ) && ( !empty( $size ) ) ) {

       // begin validation of uploaded file
       if( $val->ValidateUploadedFile( $file_name, $uploaded, $type, $size, "csv", "text/csv", "928694" ) === 0 ) {

   					// copy file to directory and process further
        $upload_path = "conf/uploads/" . basename( $file_name );
        if( @move_uploaded_file( $uploaded, $upload_path ) ) { 
         // check for csv extension
         if( eregi( ".*\.[csv]", $upload_path ) ) {
          // open file and begin processing
          $handle = fopen( $upload_path, "r" );
          while( ( $data = fgetcsv( $handle, 1024, "," ) ) !== FALSE ) {
           // begin counting
           $row++;
           // assign to a temporary array for processing and validation
           $tmp[$row]['hostname'] = $data[0];
           $tmp[$row]['ip_address'] = $data[2];
           $tmp[$row]['mac_address'] = $data[1];
           $tmp[$row]['subnet'] = $data[3];
           $tmp[$row]['pxe_group'] = $data[4];
          }
         }
        } else {
         // could not move tmp uplodaded file
         $error = $err->GenerateErrorLink( "help/help.html", "#import_hosts", $defined['error'], "The file '" . $file_name . "' was not copied to specified directory due to security restrictions on upload folder", NULL, NULL );
        }
       } else {
        // file validation failed
  						$e = $err->GenerateErrorImg( $defined['error'], "help/help.html#import_hosts", '800', '800' );
   					$list .= "<ol>";
        if( $val->ValidateUploadedFile( $file_name, $uploaded, $type, $size, "csv", "text/csv", "928694" ) === -1 ) { $list .= "<li>File Name field is empty</li>"; $file_name_err = $e; }
        if( $val->ValidateUploadedFile( $file_name, $uploaded, $type, $size, "csv", "text/csv", "928694" ) === -2 ) { $list .= "<li>File Name field is not a file</li>"; $file_name_err = $e; }
        if( $val->ValidateUploadedFile( $file_name, $uploaded, $type, $size, "csv", "text/csv", "928694" ) === -3 ) { $list .= "<li>File Name field is not an allowed file type</li>"; $file_name_err = $e; }
        if( $val->ValidateUploadedFile( $file_name, $uploaded, $type, $size, "csv", "text/csv", "928694" ) === -4 ) { $list .= "<li>File Name field mime type is not valid</li>"; $file_name_err = $e; }
        if( $val->ValidateUploadedFile( $file_name, $uploaded, $type, $size, "csv", "text/csv", "928694" ) === -5 ) { $list .= "<li>File Name field file size is greater then allowed size</li>"; $file_name_err = $e; }
  						$list .= "</ol>";
  						$error = $err->GenerateErrorLink( "help/help.html", "#import_hosts", $defined['error'], $errors['val_str'] . $list, NULL, NULL );
       }
       // perform a little cleanup on file
       fclose( $handle );
       unlink( $upload_path );
      }
     }

     // check for form re-submission
     if( ( !empty( $_POST['record'] ) ) && ( count( $_POST['record'] ) > 0 ) ) {
      foreach( $_POST['record'] as $key => $value ) {
       $tmp[$key]['hostname'] = $val->ValidateXSS( $value['hostname'] );
       $tmp[$key]['ip_address'] = $val->ValidateXSS( $value['ip_address'] );
       $tmp[$key]['mac_address'] = $val->ValidateXSS( $value['mac_address'] );
       $tmp[$key]['subnet'] = $val->ValidateXSS( $value['subnet'] );
       $tmp[$key]['pxe_group'] = $val->ValidateXSS( $value['pxe_group'] );
      }
     }
    }

    if( ( !empty( $tmp ) ) && ( count( $tmp ) > 0 ) ) {

     // loop over our array and assign data to our template
     //for( $x = 1; $x <= count( $tmp ); $x++ ) {
     foreach( $tmp as $key => $value ) {

      // get count of records
      $total = count( $tmp );
      $imported = 0;
      $duplicates = 0;
      $errors = 0;

      // check each post element
      if( ( !empty( $tmp[$key]['hostname'] ) ) && ( !empty( $tmp[$key]['mac_address'] ) ) && ( !empty( $tmp[$key]['ip_address'] ) ) ) {

       // begin validation of file contents
       if( ( $val->ValidateParagraph( $tmp[$key]['hostname'] ) !== -1 ) && ( $val->ValidateMACFormats( $tmp[$key]['mac_address'] ) !== -1 ) && ( $val->ValidateIPv4( $tmp[$key]['ip_address'] ) !== -1 ) && ( ( $val->ValidateParagraph( $tmp[$key]['subnet'] ) !== -1 ) ) && ( $val->ValidateParagraph( $tmp[$key]['pxe_group'] ) !== -1 ) ) {

        // generate our sql command adding update on id if checkbox selected
        if( !empty( $_POST['overwrite'] ) ) {
         $insert = "INSERT INTO `conf_hosts` ( `hostname`, `mac-address`, `ip-address`, `subnet-name`, `pxe-group` ) VALUES ( \"" . $tmp[$key]['hostname'] . "\",\"" . $tmp[$key]['mac_address'] . "\", \"" . $tmp[$key]['ip_address'] . "\", \"" . $tmp[$key]['subnet'] . "\", \"" . $tmp[$key]['pxe_group'] . "\" ) ON DUPLICATE ID `hostname` = \"" . $tmp[$key]['hostname']  . "\", `mac-address` = \"" . $tmp[$key]['mac_address']  . "\", `ip-address` = \"" . $tmp[$key]['ip_address']  . "\", `subnet-name` = \"" . $tmp[$key]['subnet']  . "\", `pxe-group` = \"" . $tmp[$key]['pxe_group']  . "\"";
        } else {
         $insert = "INSERT INTO `conf_hosts` ( `hostname`, `mac-address`, `ip-address`, `subnet-name`, `pxe-group` ) VALUES ( \"" . $tmp[$key]['hostname'] . "\",\"" . $tmp[$key]['mac_address'] . "\", \"" . $tmp[$key]['ip_address'] . "\", \"" . $tmp[$key]['subnet'] . "\", \"" . $tmp[$key]['pxe_group'] . "\" )";
        }

        // insert records or prompt for duplicate errors
        if( ( $value = $db->dbQuery( $val->ValidateSQL( $insert, $dbconn ), $dbconn ) ) === -1 ) {

         // found an existing record?
         if( eregi( "duplicate", $db->dbCatchError() ) ) {

          // count of duplicates
          $duplicates++;

          // assign an error message
          $error = $err->GenerateErrorLink( "help/help.html", "#import_host", $defined['error'], "Duplicate records found during import, please review and modify the data below accordingly.", NULL, NULL );

          // since we have a duplicate and not an invalid record give them the correct template
          $error_template = "admin.import.hosts.errors.tpl";

          // find the duplicate record so the user can edit it
          $find = "SELECT * FROM `conf_hosts` WHERE `hostname` = \"" . $tmp[$key]['hostname'] . "\" OR `mac-address` = \"" . $tmp[$key]['mac_address'] . "\" OR `ip-address` = \"" . $tmp[$key]['mac_address'] . "\" LIMIT 1";
          if( ( $value = $db->dbQuery( $val->ValidateSQL( $find, $dbconn ), $dbconn ) ) === -1 ) {
           $error = $err->GenerateErrorLink( "help/help.html", "#import_host", $defined['error'], "An error occured when attempting to lookup the duplicate record in which '" . $tmp[$key]['hostname'] . "' conflicts with.", NULL, NULL );
          } else {
           $found = $db->dbArrayResultsAssoc( $value );
          }

          // populate our subnets list for our imported record
          $sub = "SELECT `subnet-name` FROM `conf_subnets` ORDER BY `subnet-name` ASC";
          if( ( $return = $db->dbQuery( $val->ValidateSQL( $sub, $dbconn ), $dbconn ) ) !== -1 ) {
           $subs = $db->dbArrayResultsAssoc( $return );
          }
          if( count( $subs ) === 0 ) {
           $subnet = "No subnets defined";
          } else {
           $subnet = $misc->GenDropMenuWSelectedSubnets( $subs, $tmp[$key]['subnet'], 'subnet_tmp' );
           // quick check to ensure subnet from import matches existing subnet group
           if( ( !in_array( $tmp[$key]['subnet'], $subs ) ) && ( !empty( $tmp[$key]['subnet'] ) ) ) {
            $subnet_err[$x] = $err->GenerateErrorImg( $defined['error'], "help/help.html#import_host", '800', '800' );
            $list .= "<li>Subnet field from import does match current list of existing subnet groups</li>";
           }
          }

          // populate our pxe group list
          $px = "SELECT `pxe-group-name` FROM `conf_pxe_groups` ORDER BY `pxe-group-name` ASC";
          if( ( $return = $db->dbQuery( $val->ValidateSQL( $px, $dbconn ), $dbconn ) ) !== -1 ) {
           $pxeg = $db->dbArrayResultsAssoc( $return );
          }
          if( count( $pxeg ) === 0 ) {
           $pxe_group = "No PXE Groups defined";
          } else {
           $pxe_group = $misc->GenDropMenuWSelectedPXE( $pxeg, $tmp[$key]['pxe_group'], 'pxe_group_tmp' );
           // quick check to ensure pxe group form import matches existin pxe group
           if( ( !in_array( $tmp[$key]['pxe_group'], $pxeg ) ) && ( !empty( $tmp[$key]['pxe_group'] ) ) ) {
            $pxe_group_err[$x] = $err->GenerateErrorImg( $defined['error'], "help/help.html#import_host", '800', '800' );
            $list .= "<li>PXE Group field from import does match current list of existing PXE groups</li>";
           }
          }

          // find matching fields and display as errors
  				    $e = $err->GenerateErrorImg( $defined['error'], "help/help.html#import_host", '800', '800' );
          if( $tmp[$key]['hostname'] === $found[0]['hostname'] ) { $list .= "<li>Hostname field already exists in database</li>"; $hostname_err[] = $e; }
          if( $tmp[$key]['mac_address'] === $found[0]['mac-address'] ) { $list .= "<li>MAC Address field already exists in database</li>"; $mac_address_err[] = $e; }
 	 			    if( $tmp[$key]['ip_address'] === $found[0]['ip-address'] ) { $list .= "<li>IP Address field already exists in database</li>"; $ip_address_err[] = $e; }
 	 			    $error = $err->GenerateErrorLink( "help/help.html", "#import_host", $defined['error'], $errors['val_missing'] . $list, NULL, NULL );

          // begin populating our duplicate record data
          $tpl->assign( 'hostname_dup', $val->ValidateXSS( $found[0]['hostname'] ), NULL, NULL );
          $tpl->assign( 'mac_address_dup', $val->ValidateXSS( $found[0]['mac-address'] ), NULL, NULL );
          $tpl->assign( 'ip_address_dup', $val->ValidateXSS( $found[0]['ip-address'] ), NULL, NULL );
          $tpl->assign( 'subnet_dup', $val->ValidateXSS( $found[0]['subnet'] ), NULL, NULL );
          $tpl->assign( 'pxe_group_dup', $val->ValidateXSS( $found[0]['pxe-group'] ), NULL, NULL );

          // If a duplicate record exists matching the hostname, ip address, or mac address field display it
          $tpl->assign( 'hostname_tmp', $val->ValidateXSS( $tmp[$key]['hostname'] ), NULL, NULL );
          $tpl->assign( 'mac_address_tmp', $val->ValidateXSS( $tmp[$key]['mac_address'] ), NULL, NULL );
          $tpl->assign( 'ip_address_tmp', $val->ValidateXSS( $tmp[$key]['ip_address'] ), NULL, NULL );
          $tpl->assign( 'subnet_tmp', $subnet_tmp, NULL, NULL );
          $tpl->assign( 'pxe_group_tmp', $pxe_group_tmp, NULL, NULL );

          // and the corresponding error
          $tpl->assign( 'hostname_tmp_err', $hostname_tmp_err, NULL, NULL );
          $tpl->assign( 'mac_address_tmp_err', $mac_address_tmp_err, NULL, NULL );
          $tpl->assign( 'ip_address_tmp_err', $ip_address_tmp_err, NULL, NULL );
          $tpl->assign( 'subnet_tmp_err', $subnet_tmp_err, NULL, NULL );
          $tpl->assign( 'pxe_group_tmp_err', $pxe_group_tmp_err, NULL, NULL );

          // duplicate entry error data
          $tpl->assign( 'hostname_dup_err', $hostname_dup_err, NULL, NULL );
          $tpl->assign( 'mac_address_dup_err', $mac_address_dup_err, NULL, NULL );
          $tpl->assign( 'ip_address_dup_err', $ip_address_dup_err, NULL, NULL );
          $tpl->assign( 'subnet_dup_err', $subnet_dup_err, NULL, NULL );
          $tpl->assign( 'pxe_group_dup_err', $pxe_group_dup_err, NULL, NULL );

          // generate our form data
          $form_data .= $misc->GenImportDuplicateHostForm( $num, $val->ValidateXSS( $_GET['skin'] ), $tmp[$key]['hostname'], $found[0]['hostname'], $tmp[$key]['ip_address'], $found[0]['ip-address'], $tmp[$key]['mac_address'], $found[0]['mac-address'], $subnet, $found[0]['subnet-name'], $pxe_group, $found[0]['pxe-group'], $hostname_err[$x], $hostname_dup_err, $ip_address_err[$x], $ip_address_dup_err, $mac_address_err[$x], $mac_address_dup_err, $subnet_err[$x], $subnet_dup_err, $pxe_group_err[$x], $pxe_group_dup_err );
          $error_footer = 'admin.import.hosts.errors.footer.tpl';

				     }
        } else {
         $imported++;
 						  $error_template = NULL;
         $error = $err->GenerateErrorLink( "help/help.html", "#import_host", $defined['good'], $errors['db_insert'], NULL, NULL );
 					  }

       } else {
        $total_errors++;
        // since we have a duplicate and not an invalid record give them the correct template
        $error_template = "admin.import.hosts.errors.tpl";
        // find validation errors
					   $e = $err->GenerateErrorImg( $defined['error'], "help/help.html#import_host", '800', '800' );
        if( $val->ValidateParagraph( $tmp[$key]['hostname'] ) === -1 ) { $hostname_regex = "<li>Hostname field is invalid</li>"; $hostname_tmp_err = $e; }
        if( $val->ValidateMACFormats( $tmp[$key]['mac_address'] ) === -1 ) { $mac_regex = "<li>MAC Address field is invalid</li>"; $mac_address_tmp_err = $e; }
        if( $val->ValidateIPv4( $tmp[$key]['ip_address'] ) === -1 ) { $ip_regex = "<li>IP Address field is invalid</li>"; $ip_address_tmp_err = $e; }
        if( $val->ValidateParagraph( $tmp[$key]['subnet'] ) === -1 ) { $sub_regex = "<li>Subnet Name field is invalid</li>"; $subnet_tmp_err = $e; }
        if( $val->ValidateParagraph( $tmp[$key]['pxe_group'] ) === -1 ) { $pxe_regex = "<li>PXE Group field is invalid</li>"; $pxe_group_tmp_err = $e; }
					   $error = $err->GenerateErrorLink( "help/help.html", "#import_host", $defined['error'], "At least one field is in the invalid format. Please correct and re-submit" . "<ol>" . $hostname_regex . $mac_regex . $ip_regex . $sub_regex . $pxe_regex . "</ol>", NULL, NULL );
        
        // create the form with our current record data
        $error_footer = 'admin.import.hosts.errors.footer.tpl';
        $form_data .= $misc->GenImportValidateHostForm( $key, $val->ValidateXSS( $_GET['skin'] ), $tmp[$key]['hostname'], $tmp[$key]['ip_address'], $tmp[$key]['mac_address'], $subnet_tmp, $pxe_group_tmp, $hostname_tmp_err, $ip_address_tmp_err, $mac_address_tmp_err, $subnet_tmp_err, $pxe_group_tmp_err );

       }
      } else {
       $total_errors++;
       // since we have a duplicate and not an invalid record give them the correct template
       $error_template = "admin.import.hosts.errors.tpl";
       // look to see which fields were empty
			    $e = $err->GenerateErrorImg( $defined['error'], "help/help.html#import_host", '800', '800' );
       if( empty( $tmp[$key]['hostname'] ) ) { $hostname_e = "<li>Hostname field is missing</li>"; $hostname_tmp_err = $e; }
       if( empty( $tmp[$key]['mac_address'] ) ) { $mac_e = "<li>MAC Address Field is missing</li>"; $mac_address_tmp_err = $e; }
			    if( empty( $tmp[$key]['ip_address'] ) ) { $ip_e = "<li>IP Address Field is missing</li>"; $ip_address_tmp_err = $e; }
			    $error = $err->GenerateErrorLink( "help/help.html", "#import_host", $defined['error'], "There are missing data fields. Please correct and re-submit" . "<ol>" . $hostname_e . $mac_e . $ip_e . "</ol>", NULL, NULL );

       // create the form with our current record data
       $error_footer = 'admin.import.hosts.errors.footer.tpl';
       $form_data .= $misc->GenImportValidateHostForm( $key, $val->ValidateXSS( $_GET['skin'] ), $tmp[$key]['hostname'], $tmp[$key]['ip_address'], $tmp[$key]['mac_address'], $subnet_tmp, $pxe_group_tmp, $hostname_tmp_err, $ip_address_tmp_err, $mac_address_tmp_err, $subnet_tmp_err, $pxe_group_tmp_err );

      }

      // assign a count of items
      $tpl->assign( 'count', $x, NULL, NULL );

      // populate our subnets list
      $sub = "SELECT `subnet-name` FROM `conf_subnets` ORDER BY `subnet-name` ASC";
      if( ( $return = $db->dbQuery( $val->ValidateSQL( $sub, $dbconn ), $dbconn ) ) !== -1 ) {
       $subs = $db->dbArrayResultsAssoc( $return );
      }
      if( count( $subs ) === 0 ) {
       $subnet_tmp = "No subnets defined";
      } else {
       $subnet_tmp = $misc->GenDropMenuWSelectedSubnets( $subs, $tmp[$key]['subnet'], 'subnet_tmp' );
       // quick check to ensure subnet from import matches existing subnet group
       if( ( !in_array( $tmp[$key]['subnet'], $subs ) ) && ( !empty( $tmp[$key]['subnet'] ) ) ) {
        $subnet_tmp_err = $err->GenerateErrorImg( $defined['error'], "help/help.html#import_host", '800', '800' );
        $list .= "<li>Subnet field from import does match current list of existing subnet groups</li>";
       }
      }

      // populate our pxe group list
      $px = "SELECT `pxe-group-name` FROM `conf_pxe_groups` ORDER BY `pxe-group-name` ASC";
      if( ( $return = $db->dbQuery( $val->ValidateSQL( $px, $dbconn ), $dbconn ) ) !== -1 ) {
       $pxeg = $db->dbArrayResultsAssoc( $return );
      }
      if( count( $pxeg ) === 0 ) {
       $pxe_group_tmp = "No PXE Groups defined";
      } else {
       $pxe_group_tmp = $misc->GenDropMenuWSelectedPXE( $pxeg, $tmp[$key]['pxe_group'], 'pxe_group_tmp' );
       // quick check to ensure pxe group form import matches existin pxe group
       if( ( !in_array( $tmp[$key]['pxe_group'], $pxeg ) ) && ( !empty( $tmp[$key]['pxe_group'] ) ) ) {
        $pxe_group_tmp_err = $err->GenerateErrorImg( $defined['error'], "help/help.html#import_host", '800', '800' );
        $list .= "<li>PXE Group field from import does match current list of existing PXE groups</li>";
       }
      }

      $total = '<hr><b>Total Records:</b> ' . $total . '<br>';
      $imported = '<b>Imported Records:</b> ' . $imported . '<br>';
      $total_error = '<b>Total Errors:</b> ' . $total_errors . '<br>';
      $duplicates = '<b>Duplicate Records:</b> ' . $duplicates . '<br>';

     }
    }

    // Assign form variables
 			$tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'file_name', $val->ValidateXSS( $file_name ), NULL, NULL );

    // assign error messages
    $tpl->assign( 'file_name_err', $file_name_err, NULL, NULL );

    $tpl->assign( 'total', $total, NULL, NULL );
    $tpl->assign( 'imported', $imported, NULL, NULL );
    $tpl->assign( 'errors', $total_error, NULL, NULL );
    $tpl->assign( 'duplicates', $duplicates, NULL, NULL );

    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_hosts", $dbconn );
			
 			// Free db handle and close connection(s)
    $db->dbFreeData( $dbconn );
    $db->dbCloseConn( $dbconn );
    
    // assign vars and call the validation template
    $tpl->assign( 'error_template', $tpl->assign( 'form_data', $form_data, $error_template, $flag ), NULL, $flag );
    $tpl->assign( 'error_template_resub', $tpl->assign( 'form_data_footer', NULL, $error_footer, NULL ), NULL, $flag );

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
$tpl->display( 'header.tpl', $flag, NULL, NULL );

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
$tpl->display( 'main.tpl', $flag, NULL, NULL );

// call our footer file
$tpl->assign( 'DISCLAIMER', $defined['disclaimer'], NULL, NULL );
$tpl->assign( 'SKIN_MENU', $skin->GenSkinMenu( $_GET['skin'], $defined['templates'] ), NULL, NULL );
$tpl->assign( 'SKIN_MENU_ERR', $skin_err, NULL, NULL );
$tpl->display( 'footer.tpl', $flag, NULL, NULL );
	
// show some debugging if enabled
if( $defined['debug'] === "TRUE" ) { $debug->ShowDebug( $_GET, $_POST, $_REQUEST, $_SESSION ); }
  
?>