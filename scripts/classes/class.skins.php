<?PHP
/*
 * skin.inc.php
 *
 * Handle page skinning
 *
 * Copyright Jason Gerfen <jason.gerfen@gmail.com>
 *
 */

class PageSkinner
{
 function GenSkinMenu( $skin, $path )
 {
  $chk = new ValidateStrings();
  if( empty( $id ) ) { $id = "default"; }
  if( ( $chk->ValidateString( $skin ) !== -1 ) || ( $chk->ValidateString( $path ) !== -1 ) ) {
   if( is_dir( $path ) ) {
    if( $dh = opendir( $path ) ) {
     $data .= "<form method=\"get\" action=\"$_SERVER[PHP_SELF]?skin=$skin\"><select name=\"skin\" onChange=\"jumpMenu('parent',this,0)\"><option value=\"NULL\">Pick your poison...</option>";
     while( ( $file = readdir( $dh ) ) !== false ) {
      if( ( $file !== "." ) && ( $file !== ".." ) && ( $file !== "index.html" ) && ( $file !== "images" ) && ( !eregi( ".tpl", $file ) ) ) {
       $data .= "<OPTION NAME=\"skin\" VALUE=\"$_SERVER[PHP_SELF]?skin=$file\">$file</OPTION>";
      }
     }
     $data .= "</select></form>";
     closedir( $dh );
    }
   }
  }
  return $data;
 }

 function SelectSkin( $path, $skin, $cookie )
 {
  $chk = new ValidateStrings();
  $err = new GenerateErrors();
  if( ( $chk->ValidateString( $path ) !== -1 ) || ( $chk->ValidateString( $skin ) !== -1 ) ) {
   $data = $path . "/" . $skin;
   if( empty( $skin ) ) { $data = $path . "/red"; }
   if( !empty( $cookie ) ) { $data = $path . "/" . $cookie; }
  } else {
   $data = $path . "/red";
  }
  return $data;
 }

}

?>