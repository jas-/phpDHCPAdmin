<?PHP

/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * class.phplib.checks.php - Perform necessary checks on phplibs
 */

class phpLIBChecks
{
 
 function RequiredLibs() {
  // our various libs and the functions we use
  $array['required']['mysql'] = array( 'is_resource', 'mysql_pconnect', 'mysql_connect', 'mysql_select_db', 'mysql_query', 'mysql_num_rows', 'mysql_fetch_array', 'mysql_fetch_assoc', 'mysql_affected_rows', 'mysql_error', 'mysql_errno', 'mysql_free_result', 'mysql_close', 'mysql_escape_string' );
  $array['required']['gd'] = array( 'imagepng', 'imagedestroy', 'imagestring', 'imagestringup', 'imagecolorsforindex', 'imagecolorallocate', 'imageline', 'imagefilledellipse', 'imagefilledrectangle', 'imagerectangle', 'imagefilledarc' );
  $array['required']['mhash'] = array( 'mhash', 'sha1', 'md5', 'rand', 'srand', 'count', 'bin2hex', 'chr', 'hexdec', 'base64_encode', 'base64_decode', 'hash' );
  $array['required']['mcrypt'] = array( 'mcrypt_get_iv_size', 'mcrypt_create_iv', 'mcrypt_get_key_size', 'mcrypt_encrypt', 'mcrypt_decrypt' );
  $array['required']['sessions'] = array( 'session_start', 'session_unset', 'session_destroy', 'session_regenerate_id', 'session_id' );
  $array['required']['system'] = array( 'ini_set', 'ini_get', 'exec', 'shell_exec' );
  $array['required']['networking'] = array( 'dns_get_record', 'gethostbyaddr', 'gethostbyname' );
  $array['required']['strings'] = array( 'explode', 'addcslashes', 'addslashes', 'chop', 'html_entity_decode', 'htmlentities', 'htmlspecialchars_decode', 'htmlspecialchars', 'implode', 'substr', 'stripslashes' );
  $array['required']['regex'] = array( 'preg_replace', 'preg_match', 'eregi' );
  return $array;
 }

 function GetPHPLIBS()
 {
  return get_defined_functions();
 }

 function CheckPHPLIBS( $functions, $array )
 {
  $val = "<b>Performing lookup for required PHP functions and libraries...</b><br>";
  foreach( $array as $key => $value ) {
   foreach( $value as $class => $func ) {
    foreach( $func as $class1 => $func1 ) {
     if( !in_array( $func1, $functions['internal'] ) ) {
      $return['error']['error'] = "There seems to be some missing libraries necessary for phpDHCPAdmin to work properly";
      $return['error']['errno'] = -1;
      $return['error']['data'][$class] = $func1;
     }
    }
   }
  }
  return $return;
 }
}