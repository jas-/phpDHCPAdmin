<?PHP

/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * inc.errors.php - Pre-defined error messages
 */

// Error codes
$errors['dhcpd_status']  = "The ISC DHCPD service is currently running.";
$errors['dhcpd_status_err']  = "The ISC DHCPD service is currently not running.";
$errors['db_config']     = "Database configuration error, please contact administrator.";
$errors['db_conn']       = "Database connection error, please check configuration.";
$errors['db_search_err'] = "There was an error when performing search, syntax error.";
$errors['db_select']     = "Database selection error, please check configuration.";
$errors['db_select_err'] = "There was an error looking up data from the database";
$errors['db_insert']     = "The information listed below has been entered into the database successfully";
$errors['db_insert_err'] = "There was a problem inserting the new record.";
$errors['db_edit']       = "The information listed below was modified within the database successfully";
$errors['db_edit_err']   = "There was an error when modifying the database entry";
$errors['db_del']        = "The information listed below has been deleted from the database records";
$errors['db_del_err']    = "There was an error when attempting to remove database record";
$errors['db_fix']        = "The table was analyzed, checked and optimized";
$errors['db_index']      = "Any order records that were unlinked from the items list or vice versa have been repaired";
$errors['new_acct']      = "A new user has been created and an email confirmation has been sent to the email address for this user to activate the account and reset their password.";
$errors['new_acct_err']  = "There was an error when attempting to send out the users confirmation email, you may need to delete this user from the database and create a new user with a valid email address or check the web servers email configuration.";
$errors['val_missing']   = "Missing data, please try again...";
$errors['val_str']       = "Invalid string detected, allowable types are [a-z].";
$errors['val_pass_fmt']  = "Your password information is invalid. Allowed characters are [a-z0-9]+[-!#$%&\'*+\\./=?^_`{|}~<>] with a max length of 25";
$errors['val_pass_mtch'] = "Your password information is invalid. The two password fields do not match, please try again.";
$errors['val_pw_reset']  = "An error occured when resetting the password information, details follow...";
$errors['val_hostname']  = "Invalid Hostname, allowable types are [a-z0-9-]";
$errors['val_ipaddr']    = "Invalid IPv4 address detected, allowable types are [0-255].[0-255].[0-255].[0-255].";
$errors['val_hosts']     = "Invalid data detected. IPv4, MAC or Hostname is invalid (please review the below for acceptable formats).";
$errors['val_mac']       = "Invalid MAC format detected, allowable types are [a-f0-9](:|-) x 6. Example: 00:aa:11:bb:22:cc";
$errors['val_xss']       = "Invalid data found, detected possible XSS/SQL injection attack.";
$errors['val_url']       = "Invalid data found, must be a valid FQDN, possible XSS attack.";
$errors['val_sql']       = "Invalid data found, detected possible XSS/SQL injection attack.";
$errors['val_bfr']       = "Invalid data found, detected possible Buffer Overflow detected. Data must be reset. Re-directing...";
$errors['val_alp']       = "Invalid alphanumeric data found, allowable character sets are [a-z] and [0-9].";
$errors['val_num']       = "Invalid number data found, allowable characters are [0-9].";
$errors['val_mny']       = "Invalid monetary amount detected, allowable format is [0-9]{0,40}.[0-9]{0,2}";
$errors['val_par']       = "Invalid paragraph format detected, only UTF-8 and alpha numeric characters allowed.";
$errors['val_host']      = "Invalid data found for MAC address, IPv4 address or system hostname";
$errors['auth_n']        = "Invalid user, the credentials you entered were not found in the database.";
$errors['auth_e']        = "Invalid user, you did not enter a username and password combination.";
$errors['auth_to']       = "Timed out. Your session has been timed out due to inactivity.";
$errors['level']         = "Unauthorized access detected. Your access level is restricted.";
$errors['log_out']       = "You have chosen to log out. All authentication data has been destroyed and you will be re-directed to the log in page momentarily.";
$errors['undef']         = "Undefined error.";
$errors['undef_sql']     = "Undefined SQL error. You may wish to check the configuration setup.";
$errors['search_res']    = "Select the order you wish to edit/print from the list below";
$errors['sql_empty']     = "Your database query returned '0' results";
$errors['config_file']   = "The configuration file is missing";
$errors['xss_config']    = "You configuration is invalid or this script is attempting to be accessed from another domain or IP address";

?>