<?PHP
/*
 * errors.inc.php
 *
 * Handle generation of error messages
 *
 * Copyright Jason Gerfen <jason.gerfen@gmail.com>
 *
 */

class GenerateErrors
{
 var $help;
 var $section;
 var $image;
 var $message;

 function GenerateErrorLink( $help, $section, $image, $message, $width, $height )
 {
	 if( empty( $width ) ) { $width = "600"; }
		if( empty( $height ) ) { $height = "600"; }
  return "<a href=\"javascript:popUp('$help$section', '$width', '$height')\"><img src=\"$image\" border=\"0\">&nbsp;&nbsp;$message</a>";
 }

 function GenerateErrorMsg( $image, $message )
 {
  return "<img src=\"$image\" border=\"0\">&nbsp;&nbsp;$message</a>";
 }

 function GenerateErrorImg( $image, $link, $width, $height )
 {
  return "<a href=\"javascript:popUp('$link$section', '$width', '$height')\"><img src=\"$image\" border=\"0\"></a>";
 }

}

?>