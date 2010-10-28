<?PHP

/*
 * phpMyOrdering - All rights reserved.
 *
 * Author:       Jason Gerfen
 * Email:        <jason.gerfen@gmail.com>
 *
 * Description:  config.inc.php - Global defined options
 *
 */

class ExitApp
{
 function ExitApplication( $token ) {
  if( !empty( $token ) ) {
   $session = new Sessions;
   $session->ClearSession( $token );
  }
  return;
 }
}
?>