<?PHP
/*
 * levels.inc.php
 *
 * Handle level of access
 *
 * Copyright Jason Gerfen <jason.gerfen@gmail.com>
 *
 */

class AccessLevels
{
 var $token;
 var $value;
 var $level;
 var $data;

 function ChkLevel( $token )
 {
  global $defined;
  if( empty( $token ) ) {
   $level->value = -1;
  } else {
   $auth = new Encryption();
   $db = new dbConn();
   $val = new ValidateStrings();
   $array = $auth->DecodeAuthToken( $token );
   $data = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
   $query = "SELECT `level` FROM `auth_users` WHERE `level` = \"" . base64_decode( $array[2] ) . "\"";
   $value = $db->dbQuery( $val->ValidateSQL( $query, $data ), $data );
   $array = $db->dbArrayResults( $value );
   $level->value = $array[0]['level'];
   $db->dbFreeData( $query );
   $db->dbCloseConn( $data );
  }
  return $level->value;
 }

}

?>