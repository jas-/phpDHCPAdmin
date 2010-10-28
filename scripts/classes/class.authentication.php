<?PHP

/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * class.authentication.php - Handle user authentication
 */

class Authenticate
{

 function AuthUser( $user, $pass, $token )
 {
  // our global config opts
  global $defined;

  // initialize classes
  $db = new dbConn();
  $val = new ValidateStrings();
  $lib = new Authenticate();
  $auth = new Encryption();
  $sess = new Sessions();
  $misc = new MiscFunctions();
  $exit = new ExitApp();

  // check our authentication requirements
  if( ( empty( $user ) ) && ( empty( $pass ) ) && ( empty( $token ) ) ) {
   return -1;
  }

  // we have an existing authentication token present
  if( ( !empty( $token ) ) && ( empty( $user ) ) && ( empty( $pass ) ) ) {
   $array = $auth->DecodeAuthToken( $token );
   $user = base64_decode( $array[0] );
   $pass = base64_decode( $array[1] );
   $time = $array[4];
   $current = $misc->GenTime();
   if( ( $lib->AuthTimeOut( $defined['timeout'], $time, $current ) ) === -1 ) {
    return -2;
   }
  }

  // perform validation on username and password
  if( ( $val->ValidateAlphaChar( $user ) === -1 ) || ( $val->ValidateParagraph( $pass ) === -1 ) ) {
   return -3;
  }

  // see if the user exists for authenticaiton
  $data = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
  $query = "SELECT * FROM `auth_users` WHERE `username` = \"$user\" AND `password` = sha1( \"$pass\" )";
  $query = $val->ValidateSQL( $query, $data );
  
  // database problem
  if( ( $value = $db->dbQuery( $query, $data ) ) === -1 ) {
   return -5;
  }

  // check user match
  if( ( $db->dbNumRows( $value ) === -1 ) || ( $db->dbNumRows( $value ) === 0 ) ) {
   return -4;
  } else {
   $return = 0;
  }

  // create our authentication session token
  if( empty( $token ) ) {
   $array = $db->dbArrayResults( $value );
   $x = $auth->GeneratePrivateKey( $defined['enckeygen'] );
   $access_date = $misc->GenDate();
   $access_time = $misc->GenTimeRead();
   $query = "UPDATE `auth_users` SET `access_date` = \"" . $access_date . "\", `access_time` = \"" . $access_time . "\", `session` = \"$x\" WHERE `id` = \"" . $array[0]['id'] . "\"";
   $value = $val->ValidateSQL( $query, $data );
   if( ( $value = $db->dbQuery( $value, $data ) ) === -1 ) {
    return -5;
   }
   $x = $auth->EncodePrivToHex( $x );
   if( ( $token = $auth->EncodeAuthToken( $array[0]['username'], $pass, $array[0]['level'], $array[0]['group'], $misc->GenTime(), $x ) ) !== -1 ) {
    $sess->RegisterSession( "token", $token );
    $return = 0;
   }
  }
  $db->dbFreeData( $query );
  $db->dbCloseConn( $data );
  return $return;
 }

 function AuthTimeOut( $constant, $time, $current )
 {
  if( ( $current - $time ) > $constant ) {
   $data->value = -1;
  } else {
   $data->value = 0;
  }
  return $data->value;
 }

} 

?>