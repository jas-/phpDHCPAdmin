<?PHP
/*
 * phpMyOrdering
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * class.dbase.php - Handle database connections, queries, result sets, etc.
 */

class dbConn
{
 var $db;
 var $server;
 var $username;
 var $password;
 var $database;
 var $query;
 var $rows;
 var $array;
 var $num_rows;
 var $affected_rows;
 var $error;
 var $errno;

 function dbConnect( $server, $username, $password, $database )
 {
  $data->db = @mysql_pconnect( $server, $username, $password );
  if( !$data->db ) {
   $data->db = -1;
  } else {
   if( !@mysql_select_db( $database ) ) {
    $data->db = -1;
   }
  }
  return $data->db;
 }

 function dbConnectOnly( $server, $username, $password )
 {
  $data->db = @mysql_pconnect( $server, $username, $password );
  if( !$data->db ) {
   $data->db = -1;
  } else {
   $data->db = 0;
  }
  return $data->db;
 }

 function dbQuery( $query, $db )
 {
  $data->query = @mysql_query( $query, $db );//or die( "<br><br><b>QUERY:</b> " . $query . "<br><b>ERROR:</b> " . mysql_error() . "<br>" );
  if( !$data->query ) {
   $data->query = -1;
  }
  return $data->query;
 }

 function dbNumRows( $id )
 {
  $data->num_rows = mysql_num_rows( $id );
  if( !$data->num_rows === 0 ) {
   $data->num_rows = -1;
  }
  return $data->num_rows;
 }

 function dbNumRowsAffected( $id )
 {
  $data->affected_rows = mysql_affected_rows( $id );
  if( !$data->affected_rows === 0 ) {
   $data->affected_rows = -1;
  }
  return $data->affected_rows;
 }

 function dbArrayResults( $sql )
 {
  $data->array = array();
  while( $rows = @mysql_fetch_array( $sql, MYSQL_ASSOC ) ) {
   if( !$rows ) {
    $data->array = -1;
   }
   array_push( $data->array, $rows );
  }
  return $data->array;
 }
 
 function dbArrayResultsAssoc( $sql )
 {
  $data->array = array();
  while( $rows = @mysql_fetch_assoc( $sql ) ) {
   if( !$rows ) {
    $data->array = -1;
   }
   array_push( $data->array, $rows );
  }
  return $data->array;
 }

 function dbAffectedRows( $sql )
 {
  $rows = @mysql_affected_rows( $sql );
   if( $rows === 0 ) {
    $data->array = -1;
   }
   $data->array = 0;
  return $data->array;
 }

 function dbCatchErrno()
 {
  return @mysql_errno();
 }

 function dbCatchError()
 {
  return @mysql_error();
 }

 function dbFreeData( $sql )
 {
  return @mysql_free_result( $sql );
 }

 function dbCloseConn( $sql )
 {
  return @mysql_close( $sql );
 }

 function dbFixTable( $table, $db )
 {
  @mysql_query( "REPAIR TABLE `" . $table . "`", $db );
  @mysql_query( "OPTIMIZE TABLE `" . $table . "`", $db );
  @mysql_query( "FLUSH TABLE `" . $table . "`", $db );
 }

}
