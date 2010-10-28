<?PHP

/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * class.menu.php - Determines which menu template to load based on permissions
 */

class GenerateNavMenu
{
 var $menu;
 var $level;
 var $id;
 var $skin;

 function CreateNav( $token, $skin ) {
  global $defined;

  $auth = new Authenticate;
  $level = new AccessLevels;

  if( empty( $token ) ) {
   $data->menu = 'menu.default.tpl';
  } else {
   if( ( $auth->AuthUser( NULL, NULL, $token ) === -1 ) || ( $auth->AuthUser( NULL, NULL, $token ) === -2 ) ) {
//    $data->menu = $msg;
    $data->menu = 'menu.default.tpl';
   } else {
    if( $level->ChkLevel( $token ) === "admin" ) {
     $data->menu = 'menu.admin.tpl';
    } elseif( $level->ChkLevel( $token ) === "user" ) {
     $data->menu = 'menu.user.tpl';
    } elseif( $level->ChkLevel( $token ) === "view" ) {
     $data->menu = 'menu.view.tpl';
    } else {
     $data->menu = 'menu.default.tpl';
    }
   }
  }
  return $data->menu;
 }

}
?>