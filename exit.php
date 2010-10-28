<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * exit.php - exit the application
 */

// load our config data
if( file_exists( "scripts/inc.config.php" ) ) {
 require 'scripts/inc.config.php';

 // ensure we are being called from our configured host
 if( $defined['hostname'] === $_SERVER['SERVER_NAME'] ) {

  $exit = new ExitApp;
  $session = new dbSession;
  $exit->ExitApplication( $_SESSION['token'] );
  $session->destroy( session_id() );
  @header( "Location: index.php?skin=$_GET[skin]" );
  
  $debug->ShowDebug( $_GET, $_POST, $_REQUEST, $_SESSION );
 } else {
  // Possible XSS attack
 }
} else {
 // File is missing for configuration params
}
?>