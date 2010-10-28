<?PHP
/*
 * skin.inc.php
 *
 * Handle page skinning
 *
 * Copyright Jason Gerfen <jason.gerfen@gmail.com>
 *
 */

class Maps
{
 var $id;
 var $skin;
 var $menu;
 var $url;
 var $path;
 var $data;

	function GenMapMenu( $table, $field, $order )
 {
  global $defined;
  $db = new dbConn();
  $val = new ValidateStrings();
  if( ( empty( $table ) ) || ( empty( $field ) ) ) {
   return -1;
  }
  $conn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
  if( empty( $order ) ) {
   $query = "SELECT $field FROM `$table`";
  } else {
   $query = "SELECT $field FROM `$table` ORDER BY `$order`";
  }
  $query = $val->ValidateSQL( $query, $conn );
  if( ( $value = $db->dbQuery( $query, $conn ) ) === -1 ) { 
   return -1;
  }
  if( ( $db->dbNumRows( $value ) === -1 ) || ( $db->dbNumRows( $value ) === 0 ) ) {
   return -1;
  } else {
		 $list = "<form method=\"get\" action=\"$_SERVER[PHP_SELF]\"><b>Existing rides:</b> <select name=\"mapper\" onChange=\"jumpMenu('parent',this,0)\"><option value=\"NULL\">Select Map / Route...</option>";
   $list .= "<option>------------------------------</option>";
   foreach( $db->dbArrayResultsAssoc( $value ) as $key => $val ) {
    $url = $_SERVER['PHP_SELF'] . "?lat=" . $val['lat'] . "&lon=" . $val['lon'] . "&z=" . $val['zoom'] . "&mType=" . $val['type'] . "&driveFrom=" . $val['from'] . "&driveTo=" . $val['to'] . "&driveVia=" . $val['via'] . "&locale=en";
    $list .= "<option name=\"$url\" value=\"$url\">" . $val['name'] . "</option>";
   }
			$list .= "</select></form>";
   $data = $list;
  }
  $db->dbFreeData( $conn );
  $db->dbCloseConn( $conn );
  return $data;
 }
}

?>