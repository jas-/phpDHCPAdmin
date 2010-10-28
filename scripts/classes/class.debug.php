<?PHP

/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * class.debug.php - Print global debugging data
 */
	
class DebugData
{
 function ShowDebug( $get, $post, $request, $session )
	{
	 global $defined;
		if( $defined['debug'] === "TRUE" ) {
 	 echo "<br><br>";
			echo "<b>DEBUGGING OUTPUT:</b><br>";
			echo "<hr><b>_GET Global Array Data</b>";
 	 echo "<pre>"; print_r( $_GET ); echo "</pre>";
 	 echo "<hr><b>_POST Global Array Data</b>";
 	 echo "<pre>"; print_r( $_POST ); echo "</pre>";
 	 echo "<hr><b>_REQUEST Global Array Data</b>";
 	 echo "<pre>"; print_r( $_REQUEST ); echo "</pre>";
			echo "<hr><b>_SESSION Global Array Data</b>";
 	 echo "<pre>"; print_r( $_SESSION ); echo "</pre>";
			echo "<hr><b>_FILE Global Array Data</b>";
 	 echo "<pre>"; print_r( $_FILES ); echo "</pre>";
			echo "<hr><b>_SERVER Global Array Data</b>";
 	 echo "<pre>"; print_r( $_SERVER ); echo "</pre>";
		}
 }
}
?>