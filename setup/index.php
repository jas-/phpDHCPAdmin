<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * setup.php - Setup script
 */

// get our libraries
require '../scripts/classes/class.dbase.php';
require '../scripts/classes/class.errors.php';
require '../scripts/classes/class.validation.php';
require '../scripts/classes/class.libraries.php';

// init our libraries
$db = new dbConn;
$err = new GenerateErrors;
$val = new ValidateStrings;
$misc = new MiscFunctions;

// Assign some defaults since nothing is configured
$TITLE = "phpDHCPAdmin-0.9.5-beta Setup Wizard";
$STYLE = "../templates/black";
$DESCRIPTION = "I am here to help you import the database structure and setup the application defaults";
$DISCLAIMER = "All rights reserved 2009 &reg; Jason Gerfen";
$TABLES = array('admin_backup_conf','admin_config_algorithm','admin_logs','admin_sessions','auth_groups','auth_levels','auth_users','conf_adapters','conf_classes','conf_classes_options','conf_classes_opts','conf_dnssec_opts','conf_dns_opts','conf_failover','conf_global_opts','conf_hosts','conf_leases','conf_leases_properties','conf_pools','conf_pxe_groups','conf_pxe_opts','conf_shared_networks','conf_subnets','conf_traffic');
$REGEX = array('/\$defined[\'hostname\']\s\s\s\s=\s\"\";/', '/\$defined[\'dbhost\']\s\s\s\s\s\s=\s\"localhost\";/', '/\$defined[\'username\']\s\s\s\s=\s\"\";/', '/\$defined[\'password\']\s\s\s\s=\s\"\";/', '/\$defined[\'mail\']\s\s\s\s\s\s\s\s=\s\"\";/', '/\$defined[\'virpath\']\s\s\s\s\s=\s\"\";/');

// lets process the form
if( !empty( $_POST ) ) {
 
 // make sure we have a complete form submission
 if( ( !empty( $_POST['mysql_root_user'] ) ) && ( !empty( $_POST['mysql_root_passwd'] ) ) && ( !empty( $_POST['defined_hostname'] ) ) && ( !empty( $_POST['mysql_server_address'] ) ) && ( !empty( $_POST['mysql_server_username'] ) ) && ( !empty( $_POST['mysql_server_password'] ) ) && ( !empty( $_POST['configuration_path'] ) ) && ( !empty( $_POST['admin_email'] ) ) ) {
  
  // ensure nothing screwy is going on in regards to input
  if( ( $val->ValidateString( $_POST['mysql_root_user'] ) !== -1 ) && ( $val->ValidateParagraph( $_POST['mysql_root_passwd'] ) !== -1 ) && ( $val->ValidateDomain( $_POST['defined_hostname'] ) !== -1 ) && ( $val->ValidateDomain( $_POST['mysql_server_address'] ) !== -1 ) && ( $val->ValidateParagraph( $_POST['mysql_server_username'] ) !== -1 ) && ( $val->ValidateParagraph( $_POST['mysql_server_password'] ) !== -1 ) && ( $val->ValidateParagraph( $_POST['configuration_path'] ) !== -1 ) && ( $val->ValidateEmail( $_POST['admin_email'] ) !== -1 ) ) {
  
   // ensure our root username & password is correct
   $dbconn = $db->dbConnectOnly( $_POST['mysql_server_address'], $_POST['mysql_root_user'], $_POST['mysql_root_passwd'] );
   echo $db->dbCatchError();
   if( $dbconn === -1 ) {
    $error = $err->GenerateErrorLink( "../help/help.html", "#app_setup", '../templates/images/error.jpg', "Error connecting to the database during initial connection. Wrong username/password combination", NULL, NULL );
   } else {
    // perform our .sql file import (this fails if it exists already)
    $cmd = "mysql -u " . $_POST['mysql_root_user'] . " --password=" . $_POST['mysql_root_passwd'] . " < phpDHCPAdmin.sql";
    `$cmd`;
    
    // create a default user based on the form input
    $dbconn = $db->dbConnect( $_POST['mysql_server_address'], $_POST['mysql_root_user'], $_POST['mysql_root_passwd'], 'phpDHCPAdmin' );
    if( $dbconn === -1 ) {
     $error = $err->GenerateErrorLink( "../help/help.html", "#app_setup", '../templates/images/error.jpg', "Error connecting to the database. Wrong username/password combination", NULL, NULL );
    } else {
     $query = "GRANT SELECT,INSERT,UPDATE,DELETE,INDEX,REFERENCES ON phpDHCPAdmin.* TO '" . $_POST['mysql_server_username'] . "'@'" . $_POST['mysql_server_address'] . "' IDENTIFIED BY '" . $_POST['mysql_server_password'] . "'";
     if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
      $error = $err->GenerateErrorLink( "../help/help.html", "#create_admin_user", '../templates/images/error.jpg', "There was a problem when creating the default user that the phpDHCPAdmin application will use to keep persistant connections to the database", NULL, NULL );
     } else {
      $query = "FLUSH PRIVILEGES";
      if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $error = $err->GenerateErrorLink( "../help/help.html", "#create_admin_user", '../templates/images/error.jpg', "There was an error when flushing the priveleges table", NULL, NULL );
      } else {
       $error = $err->GenerateErrorLink( "../help/help.html", "#create_admin_user", '../templates/images/good.jpg', "Permissions have been set on the database 'phpDHCPAdmin' for the user '" . $_POST['mysql_server_username'] . "'", NULL, NULL );
      }
     }
    }
    
    // Do some cleaning before verifying installation
    $misc->CleanUpVars( $_POST, NULL );

 			// Free db handle and close connection(s)
    $db->dbFreeData( $dbconn );
    $db->dbCloseConn( $dbconn );
    
    // check results of import and application login
    $dbconn = $db->dbConnect( $_POST['mysql_server_address'], $_POST['mysql_server_username'], $_POST['mysql_server_password'], 'phpDHCPAdmin' );
    if( $dbconn === -1 ) {
     $error = $err->GenerateErrorLink( "../help/help.html", "#app_setup", '../templates/images/error.jpg', "Error connecting to the database with the newly entered username and password combination for the database 'phpDHCPAdmin'", NULL, NULL );
    } else {
     // verify tables exist
     $query = "SHOW TABLES IN `phpDHCPAdmin`";
     if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
      $error = $err->GenerateErrorLink( "../help/help.html", "#create_admin_user", '../templates/images/error.jpg', "There was an error when flushing the priveleges table", NULL, NULL );
     } else {
      $array = $db->dbArrayResults( $value );
      foreach( $array as $key => $value ) {
       $e = $err->GenerateErrorImg( '../templates/images/error.jpg', "../help/help.html#app_setup", '800', '800' );
			    $list .= "<ol>";
       if( !in_array( $value['Tables_in_phpDHCPAdmin'], $TABLES ) ) {
        $list .= "<li>Missing table information for '" . $value['Tables_in_phpDHCPAdmin'] . "'</li>";
        $flag = -1;
       }
      }
      $list .= "</ol>";
      if( $flag !== -1 ) {
       $error = $err->GenerateErrorLink( "../help/help.html", "#app_setup", '../templates/images/good.jpg', "Permissions have been set on the database 'phpDHCPAdmin' for the user '" . $_POST['mysql_server_username'] . "' and our table structure is intact.", NULL, NULL );
      } else {
       $error = $err->GenerateErrorLink( "../help/help.html", "#app_setup", '../templates/images/error.jpg', "An error occured when verifying database table schema" . $list, NULL, NULL );
      }
     }
    }

    // attempt to write our config file out
    if( file_exists( '../scripts/example.inc.config.php' ) ) {
     if( is_readable( '../scripts/example.inc.config.php' ) ) {
      if( !file_exists( '../scripts/inc.config.php' ) ) {
       // attempt to create our file for writting out the new conf
       if( ( $handle = fopen( '../scripts/inc.config.php', "w" ) ) === FALSE ) {
        $error = $err->GenerateErrorLink( "../help/help.html", "#create_admin_user", '../templates/images/error.jpg', "It seems I cannot create the '$_POST[configuration_path]scripts/inc.config.php' file. You will have to create it manually with the following settings:", NULL, NULL );
        $flag = -1;
       } else {
								$flag = 0;
							}
							// well looks like we can create it, so do it
							if( $flag !== -1 ) {
        // get the template and begin comparisons and writes
        $tmp_handle = fopen( '../scripts/example.inc.config.php', 'r' );
        while( $tmp_handle ) {
         $data = stream_get_contents( $tmp_handle );
         if( preg_match( '/.*hostname.*=.*\"(.*)\"/' , $data ) ) {
          //fwrite( $handle, $modified );
         } else {
          //fwrite( $handle, $data );
         }
        }
							} else {
								$data = "You are going to need to copy the template configuration file...<br><b>FROM:</b> <i>'$_POST[configuration_path]scripts/example.inc.config.php'</i><br><b>TO:</b> <i>'$_POST[configuration_path]scripts/inc.config.php'</i><br><br>Then copy these values into the file:<br><pre><textarea cols=80 rows=5>\$defined['hostname']    = \"$_POST[defined_hostname]\";\r\n\$defined['dbhost']         = \"$_POST[mysql_server_address]\";\r\n\$defined['username']    = \"$_POST[mysql_server_username]\";\r\n\$defined['password']     = \"$_POST[mysql_server_password]\";\r\n\$defined['mail']            = \"$_POST[admin_email]\";\r\n\$defined['virpath']        = \"$_POST[configuration_path]\";</textarea></pre><br><br>Once that has been done you can use the authentication credentials below to login:<br><b>Username:</b> <i>Admin</i><br><b>Password:</b> <i>phpDHCPAdmin</i>";
							}
      }
     }
    }
    
    // notify about security risks of leaving this script in place
    
   }
  } else {
   // find our errors and give some friendly errors
   $e = $err->GenerateErrorImg( '../templates/images/error.jpg', "../help/help.html#host_search", '800', '800' );
			$list .= "<ol>";
   if( $val->ValidateString( $_POST['mysql_root_user'] ) === -1 ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Root Username is in an invalid format</li>"; $mysql_root_user_err = $e; }
   if( $val->ValidateParagraph( $_POST['mysql_root_passwd'] ) === -1 ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Root Password is in an invalid format</li>"; $mysql_root_passwd_err = $e; }
   if( $val->ValidateDomain( $_POST['defined_hostname'] ) === -1 ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Defined hostname is in an invalid format</li>"; $defined_hostname_err = $e; }
   if( $val->ValidateDomain( $_POST['mysql_server_address'] ) === -1 ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Server is in an invalid format</li>"; $mysql_server_address_err = $e; }
   if( $val->ValidateParagraph( $_POST['mysql_server_username'] ) === -1 ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Server is in an invalid format</li>"; $mysql_server_username_err = $e; }
   if( $val->ValidateParagraph( $_POST['mysql_server_password'] ) === -1 ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Server is in an invalid format</li>"; $mysql_server_password_err = $e; }
   if( $val->ValidateParagraph( $_POST['configuration_path'] ) === -1 ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Configuration path is in an invalid format</li>"; $configuration_path_err = $e; }
   if( $val->ValidateEmail( $_POST['admin_email'] ) === -1 ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Administrative email is in an invalid format</li>"; $admin_email_err = $e; }
   $list .= "</ol>";
  	$error = $err->GenerateErrorLink( "../help/help.html", "#app_setup", '../templates/images/error.jpg', "An error occured with form data. Please correct syntax or modify entry" . $list, NULL, NULL );
  }
 } else {
  // find our errors and give some friendly errors
  $e = $err->GenerateErrorImg( '../templates/images/error.jpg', "../help/help.html#host_search", '800', '800' );
		$list .= "<ol>";
  if( empty( $_POST['mysql_root_user'] ) ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Root Username missing</li>"; $mysql_root_user_err = $e; }
  if( empty( $_POST['mysql_root_passwd'] ) ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Root Password missing</li>"; $mysql_root_passwd_err = $e; }
  if( empty( $_POST['defined_hostname'] ) ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Defined hostname missing</li>"; $defined_hostname_err = $e; }
  if( empty( $_POST['mysql_server_address'] ) ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Server address missing</li>"; $mysql_server_address_err = $e; }
  if( empty( $_POST['mysql_server_username'] ) ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Server username missing</li>"; $mysql_server_username_err = $e; }
  if( empty( $_POST['mysql_server_password'] ) ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MySQL Server password missing</li>"; $mysql_server_password_err = $e; }
  if( empty( $_POST['configuration_path'] ) ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Configuration path missing</li>"; $configuration_path_err = $e; }
  if( empty( $_POST['admin_email'] ) ) { $list .= "<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Administrative email missing</li>"; $admin_email_err = $e; }
  $list .= "</ol>";
		$error = $err->GenerateErrorLink( "../help/help.html", "#app_setup", '../templates/images/error.jpg', "An error occured, missing form data..." . $list, NULL, NULL );
 }
}

?>
<!-- header data -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?PHP echo $TITLE; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="author" content="Jason Gerfen [jason.gerfen@gmail.com]">
<meta name="robots" content="index,nofollow">
<link rel="shortcut icon" href="templates/images/dhcp.ico" type="image/x-icon">
<link rel="StyleSheet" href="<?PHP echo $STYLE; ?>/scripts/style.css" type="text/css">
<link rel="StyleSheet" href="<?PHP echo $STYLE; ?>/scripts/menu.css" type="text/css">
<link rel="StyleSheet" type="text/css" href="../scripts/css/style.css" media="screen">
<link rel="StyleSheet" type="text/css" href="../scripts/css/thickbox.css" media="screen">
<link rel="StyleSheet" type="text/css" href="../scripts/css/calendar.css">
<script language="JavaScript" type="text/javascript" src="../scripts/javascript/javascript.jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="../scripts/javascript/javascript.functions.js"></script>
<script language="JavaScript" type="text/javascript" src="../scripts/javascript/javascript.calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="../scripts/javascript/javascript.graphing.js"></script>
<script language="JavaScript" type="text/javascript" src="../scripts/javascript/javascript.lightboxform.js"></script>
<script language="JavaScript" type="text/javascript" src="../scripts/javascript/javascript.swfobject.js"></script>
<script language="JavaScript" type="text/javascript" src="../scripts/javascript/javascript.thickbox.js"></script>
<body onLoad="LoadTime(); montre(); initLightbox_lf();" onUnload="hideLightbox_lf();">
<!-- end header data -->
<!-- main table wrapper data -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <tr>
  <td>
   <table width="800" border="0" cellspacing="0" cellpadding="0">
    <tr>
     <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
       <tr>
        <td valign="top">
         <table cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" align="center">
          <tr valign="top">
           <td width="120" valign="top" background="<?PHP echo $STYLE; ?>/images/left_column_bg.gif">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
             <tr>
              <td height="15" colspan="3"><img src="<?PHP echo $STYLE; ?>/images/column_divider.gif" width="146" height="15" border="0" alt=""></td>
             </tr>
             <tr>
              <td width="5%"></td>
              <td><img src="<?PHP echo $STYLE; ?>/images/logo.jpg" hspace="0" vspace="0" border="0"></td>
              <td width="5%"></td>
             </tr>
             <tr>
              <td height="15" colspan="3"><img src="<?PHP echo $STYLE; ?>/images/column_divider.gif" width="146" height="15" border="0" alt=""></td>
             </tr>
             <tr>
              <td width="5%">&nbsp;</td>
              <td width="90%" valign="top">
               <table width="100%" border="0" cellspacing="5" cellpadding="0">
                <!-- menu message -->
                &nbsp;&nbsp;Designed to provide<br>easy administrative<br>access to the DHCP<br>services developed by the <a href="http://isc.org" target="_blank"><b>ISC</b></a> organization.<br><br>Please fill out the form in order to setup<br>application...
                <!-- end menu message -->
               </table>
              </td>
              <td width="5%"></td>
             </tr>
             <tr>
              <td height="15" colspan="3"><img src="<?PHP echo $STYLE; ?>/images/column_divider.gif" width="146" height="15" border="0" alt=""></td>
             </tr>
            </table>
           </td>
           <td width="876" valign="top">
            <table width="98%" border="0" cellspacing="0" cellpadding="0">
             <tr>
              <td height="8" colspan="3">&nbsp;</td>
             </tr>
             <tr>
              <td><img src="<?PHP echo $STYLE; ?>/images/lt_corner.gif" width="12" height="38" border="0" alt=""></td>
              <td width="100%" background="<?PHP echo $STYLE; ?>/images/header_tile_top.gif">
               <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                 <td width="95%"><b><?PHP echo $TITLE; ?></b><br>
                  <font class="articleheader"><?PHP echo $DESCRIPTION; ?></font>
                 </td>
                 <td width="5%" valign="right" nowrap>
                  <a href="javascript:popUp('../help/help.html','800','800')"><img src="<?PHP echo $STYLE; ?>/images/help01.jpg" border="0" alt=""></a>
                 </td>
                </tr>
               </table>
              </td>
              <td><img src="<?PHP echo $STYLE; ?>/images/header_r_cnr.gif" width="26" height="38" border="0" alt=""></td>
             </tr>
            </table>
            <table width="98%" border="0" cellspacing="0" cellpadding="0">
             <tr>
              <td><img src="<?PHP echo $STYLE; ?>/images/sub_corner.gif" width="112" height="28" border="0" alt=""></td>
              <td width="100%" background="<?PHP echo $STYLE; ?>/images/sub_tile.gif">&nbsp;</td>
              <td><img src="<?PHP echo $STYLE; ?>/images/top_r_corner.gif" width="50" height="28" border="0" alt=""></td>
             </tr>
            </table>
            <table width="98%" border="0" cellspacing="0" cellpadding="0">
             <tr>
              <td width="12" background="<?PHP echo $STYLE; ?>/images/l_tile.gif" nowrap>&nbsp;</td>
              <td valign="top">
               <table width="100%" cellspacing="5">
                <tr>
                 <td>
<!-- Global options configuration -->
<form action="<?PHP echo $_SERVER['PHP_FILE']; ?>" method="post" name="configGlobal">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td>
      <a href="javascript:popUp('../help/help.html#app_setup','800','800')">
       <img src="<?PHP echo $STYLE; ?>/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Global phpDHCPAdmin configuration options</b>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Use this form to assist you with importation of the database schema, configuring a default application user, setting folder permissions etc.
     </td>
    </tr>
    <tr>
     <td>
      <?PHP echo $error; ?><br><br><?PHP echo $data; ?>
     </td>
    </tr>
    <tr>
     <td width="60%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="3">
         <a href="javascript:popUp('../help/help.html#app_setup','800','800')">
          <img src="<?PHP echo $STYLE; ?>/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>The MySQL Administrative user</b><br>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;In order for this application to import the database schema, create a default user account used by the phpDHCPAdmin application you need to provide the username and password that has the 'create, modify and grant' privileges needed by the MySQL service.
        </td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>MySQL Root User:</b></td>
        <td><input type="text" name="mysql_root_user" value="<?PHP echo $val->ValidateXSS($_POST['mysql_root_user']); ?>" style="width: 100%"></td>
        <td class="copyright" nowrap><?PHP echo $mysql_root_user_err; ?>* root</td>
       </tr>
       <tr>
        <td nowrap><b>MySQL Root Password:</b></td>
        <td><input type="password" name="mysql_root_passwd" value="<?PHP echo $val->ValidateXSS($_POST['mysql_root_passwd']); ?>" style="width: 100%"></td>
        <td class="copyright" nowrap><?PHP echo $mysql_root_passwd_err; ?>* root password</td>
       </tr>
       <tr>
        <td nowrap><b>MySQL Server Address:</b></td>
        <td><input type="text" name="mysql_server_address" value="localhost" style="width: 100%"></td>
        <td class="copyright" nowrap><?PHP echo $mysql_server_address_err; ?>* default is localhost</td>
       </tr>
       <tr>
        <td colspan="3">
         &nbsp;
        </td>
       </tr>
       <tr>
        <td colspan="3">
         <hr>
        </td>
       </tr>
       <tr>
        <td colspan="3">
         &nbsp;
        </td>
       </tr>
       <tr>
        <td colspan="3">
         <a href="javascript:popUp('../help/help.html#app_setup','800','800')">
          <img src="<?PHP echo $STYLE; ?>/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>The phpDHCPAdmin Connection user</b><br>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You need to provide a unique username/password combination that phpDHCPAdmin will use as its default connection account. These fields are required for complete functionality.
        </td>
       </tr>
       <tr>
        <td nowrap><b>phpDHCPAdmin Username:</b></td>
        <td><input type="text" name="mysql_server_username" value="<?PHP echo $val->ValidateXSS($_POST['mysql_server_username']); ?>" style="width: 100%"></td>
        <td class="copyright" nowrap><?PHP echo $mysql_server_username_err; ?>* phpDHCPAdmin MySQL username</td>
       </tr>
       <tr>
        <td nowrap><b>phpDHCPAdmin Password:</b></td>
        <td><input type="password" name="mysql_server_password" value="<?PHP echo $val->ValidateXSS($_POST['mysql_server_password']); ?>" style="width: 100%"></td>
        <td class="copyright" nowrap><?PHP echo $mysql_server_password_err; ?>* phpDHCPAdmin MySQL password</td>
       </tr>
       <tr>
        <td colspan="3">
         &nbsp;
        </td>
       </tr>
       <tr>
        <td colspan="3">
         <hr>
        </td>
       </tr>
       <tr>
        <td colspan="3">
         &nbsp;
        </td>
       </tr>
       <tr>
        <td colspan="3">
         <a href="javascript:popUp('../help/help.html#app_setup','800','800')">
          <img src="<?PHP echo $STYLE; ?>/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>The phpDHCPAdmin Defaults</b><br>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;These settings are also required and provide information to the phpDHCPAdmin application about your server setup.
        </td>
       </tr>
       <tr>
        <td nowrap><b>Server Name or IP Address:</b></td>
        <td><input type="text" name="defined_hostname" value="<?PHP echo $val->ValidateXSS($_SERVER['SERVER_NAME']); ?>" style="width: 100%"></td>
        <td class="copyright" nowrap><?PHP echo $defined_hostname_err; ?>* myserver.com or 192.168.1.2</td>
       </tr>
       <tr>
        <td nowrap><b>Configuration Path:</b></td>
        <td><input type="text" name="configuration_path" value="<?PHP echo preg_replace('/setup\/index.php/', '', $_SERVER['SCRIPT_FILENAME']); ?>" style="width: 100%"></td>
        <td class="copyright" nowrap><?PHP echo $configuration_path_err; ?>* ex. /var/www/html/phpDHCPAdmin/</td>
       </tr>
       <tr>
        <td nowrap><b>Administrator email:</b></td>
        <td><input type="text" name="admin_email" value="<?PHP echo $val->ValidateXSS($_POST['admin_email']); ?>" style="width: 100%"></td>
        <td class="copyright" nowrap><?PHP echo $admin_email_err; ?>* admin@email.com</td>
       </tr>
       <tr>
        <td>&nbsp;</td>
        <td><input type="submit" name="Setup" value="Begin Setup" rel="lightboxform"></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end Global options configuration template -->
                 </td>
                </tr>
               </table>
              </td>
              <td background="<?PHP echo $STYLE; ?>/images/r_tile.gif" width="16" nowrap>&nbsp;</td>
             </tr>
            </table>
            <table width="98%" border="0" cellspacing="0" cellpadding="0">
             <tr>
              <td width="116"><img src="<?PHP echo $STYLE; ?>/images/btm_l_corner.gif" width="116" height="32" border="0"></td>
              <td width="100%" background="<?PHP echo $STYLE; ?>/images/btm_tile.gif">&nbsp;</td>
              <td width="77"><img src="<?PHP echo $STYLE; ?>/images/btm_r_corner.gif" width="84" height="32" border="0"></td>
             </tr>
            </table>
            <br>
           </td>
          </tr>
         </table>
        </td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
<!-- end main table wrapper data -->
<!-- footer data -->
<table width="800" cellpadding="1" cellspacing="0" border="0" class="btmTableBdr_1" align="left">
 <tr>
  <td width="100%">
   <table width="100%" cellpadding="3" cellspacing="0" border="0" class="btmTableBdr_2">
    <tr>
     <td align="left" width="10%" nowrap>&nbsp;</td>
     <td nowrap>&nbsp;</td>
     <td align="right" class="copyright" width="30%" nowrap><?PHP echo $DISCLAIMER; ?></td>
    </tr>
   </table>
  </td>
 </tr>
</table>
<!-- end footer data -->
