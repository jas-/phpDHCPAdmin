<?PHP
/*
 * sessions.inc.php
 *
 * Handle sessions
 *
 * Copyright Jason Gerfen <jason.gerfen@gmail.com>
 *
 */

class Sessions
{
 var $id;
 var $data;
 var $token;
 var $name;
 var $var;

 function GenSession( $token )
 {
  if( empty( $token ) ) {
   $session->data = -1;
  } else {
   session_start();
   $session->data = 0;
  }
  return $session->data;
 }

 function ClearSession( $token )
 {
  if( !empty( $token ) ) {
   unset( $token );
   @session_destroy();
  }
 }

 function UnsetSessionVar( $var )
 {
  if( !empty( $var ) ) {
   unset( $var );
  }
 }

 function RegisterSession( $name, $var )
 {
  return $_SESSION[$name] = $var;
 }

}

/*
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @version    1.0.6 (last revision: October 01, 2007)
 *  @copyright  (c) 2006 - 2007 Stefan Gabos
*/
class dbSession
{

 function dbSession( $gc_maxlifetime = "", $gc_probability = "", $gc_divisor = "", $securityCode = 'wuka wuka', $tableName = "admin_sessions" )
 {

  // if $gc_maxlifetime is specified and is an integer number
  if( $gc_maxlifetime != "" && is_integer( $gc_maxlifetime ) ) {
   // set the new value
   @ini_set( 'session.gc_maxlifetime', $gc_maxlifetime );
  }

  // if $gc_probability is specified and is an integer number
  if( $gc_probability != "" && is_integer( $gc_probability ) ) {
   // set the new value
   @ini_set( 'session.gc_probability', $gc_probability );
  }

  // if $gc_divisor is specified and is an integer number
  if( $gc_divisor != "" && is_integer( $gc_divisor ) ) {
   // set the new value
   @ini_set( 'session.gc_divisor', $gc_divisor );
  }

  // get session lifetime
  $this->sessionLifetime = ini_get( "session.gc_maxlifetime" );

  // we'll use this later on in order to try to prevent HTTP_USER_AGENT spoofing
  $this->securityCode = $securityCode;
  $this->tableName = $tableName;

  // register the new handler
  session_set_save_handler(
   array( &$this, 'open' ),
   array( &$this, 'close' ),
   array( &$this, 'read' ),
   array( &$this, 'write' ),
   array( &$this, 'destroy' ),
   array( &$this, 'gc' )
  );
  register_shutdown_function( 'session_write_close' );

  // start the session
  @session_start();
 }

 function stop()
 {
  $this->regenerate_id();
  session_unset();
  session_destroy();
 }

 function regenerate_id()
 {
  // saves the old session's id
  $oldSessionID = session_id();
  session_regenerate_id();
  $this->destroy( $oldSessionID );
 }

 function get_users_online()
 {
  // some var and classes
  global $defined;
  $db = new dbConn;
  $val = new ValidateStrings;
  
  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
  
  $this->gc( $this->sessionLifetime );
  
  $query = "SELECT COUNT( `session_id` ) AS 'count' FROM " . $this->tableName . "";
  $result = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn );

  // return the number of found rows
  return $result["count"];
 }

 function open( $save_path, $session_name )
 {
  return true;
 }

 function close()
 {
  return true;
 }

 function read( $session_id )
 {
  // some var and classes
  global $defined;
  $db = new dbConn;
  $val = new ValidateStrings;
  
  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

  $query = "SELECT `session_data` FROM `" . $this->tableName . "` WHERE `session_id` = \"" . mysql_real_escape_string( $session_id ) . "\" AND `http_user_agent` = \"" . mysql_real_escape_string( md5( $_SERVER["HTTP_USER_AGENT"] . $this->securityCode ) ) . "\" AND `session_expire` > \"" . mysql_real_escape_string( time() ) . "\" LIMIT 1";
  $result = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn );

  if( is_resource( $result ) && @mysql_num_rows( $result ) > 0 ) {
   // return found data
   $fields = @mysql_fetch_assoc( $result );
   // don't bother with the unserialization - PHP handles this automatically
   return stripslashes( $fields["session_data"] );
  }
  // if there was an error return an empty string - this HAS to be an empty string
  return "";
 }

 function write( $session_id, $session_data )
 {
  // some var and classes
  global $defined;
  $db = new dbConn;
  $val = new ValidateStrings;
  
  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

  $query = "INSERT INTO `" . $this->tableName . "` ( `session_id`, `http_user_agent`, `session_data`, `session_expire` ) VALUES ( \"" . mysql_real_escape_string( $session_id ) . "\", \"" . mysql_real_escape_string( md5( $_SERVER["HTTP_USER_AGENT"] . $this->securityCode ) ) . "\", \"" . mysql_real_escape_string( addslashes( $session_data ) ) . "\", \"" . mysql_real_escape_string( time() + $this->sessionLifetime ) . "\" ) ON DUPLICATE KEY UPDATE `session_data` = \"" . mysql_real_escape_string( addslashes( $session_data ) ) . "\", `session_expire` = \"" . mysql_real_escape_string( time() + $this->sessionLifetime ) . "\"";
  $result = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn );

  if( $result ) {
   // if the row was updated
   if( @mysql_affected_rows() > 1 ) {
    // return TRUE
    return true;
   } else {
    // return an empty string
    return "";
   }
  }
  // if something went wrong, return false
  return false;
 }

 function destroy( $session_id )
 {
  // some var and classes
  global $defined;
  $db = new dbConn;
  $val = new ValidateStrings;
  
  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

  $query = "DELETE FROM `" . $this->tableName . "` WHERE `session_id` = \"" . mysql_real_escape_string( $session_id ) . "\"";
  $result = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn );

  // if anything happened
  if( @mysql_affected_rows() ) {
   // return true
   return true;
  }
  return false;
 }

 function gc( $maxlifetime )
 {
  // some var and classes
  global $defined;
  $db = new dbConn;
  $val = new ValidateStrings;
  
  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

  $query = "DELETE FROM `" . $this->tableName . "` WHERE `session_expire` < \"" . mysql_real_escape_string( time() - $maxlifetime ) . "\"";
  $result = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn );
 }

}

