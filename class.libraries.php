<?PHP
/*
 * phpMyOrdering - All rights reserved.
 *
 * Author:       Jason Gerfen
 * Email:        <jason.gerfen@gmail.com>
 *
 * Description:  class.libraries.php - Miscellaneous functions
 *
 */

class MiscFunctions
{

 function GenDate()
 {
  return date( "Y-m-d" );
 }

 function GenTime()
 {
  return time();
 }
 
 function GenTimeRead()
 {
  return date( "G:i:s A" );
 }

 function GenSub30Date( $date )
 {
  return mktime( 0, 0, 0, date( "m" ) - 1, date( "d" ), date( "Y" ) );
 }
	
 /*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelected( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    $value['name'] = $this->TrimString( $value['name'], 25 );
    $list .= "<option value=\"" . $value['name'] . "\">" . $value['name'] . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }
 
 /*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedSubnets( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    $value['subnet-name'] = $this->TrimString( $value['subnet-name'], 25 );
    $list .= "<option value=\"" . $value['subnet-name'] . "\">" . $value['subnet-name'] . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }

 /*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedPools( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    $modified = $this->TrimString( $value['pool-name'], 25 );
    $list .= "<option value=\"" . $value['pool-name'] . "\">" . $modified . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }

 /*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedPoolOpts( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   for( $x = 0; $x < count( $array ); $x++ ) {
			//foreach( $array as $key => $value ) {
    $array[$x] = $this->TrimString( $array[$x], 25 );
    $list .= "<option value=\"" . $array[$x] . "\">" . $array[$x] . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }
 
 /*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedPXE( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    $value['name'] = $this->TrimString( $value['name'], 25 );
    $list .= "<option value=\"" . $value['pxe-group-name'] . "\">" . $value['pxe-group-name'] . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }
 
 /*
  * Generate a simple drop down menu from array (config.dns.php only)
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedDNS( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    $value['key-name'] = $this->TrimString( $value['key-name'], 25 );
    $list .= "<option value=\"" . $value['key-name'] . "\">" . $value['key-name'] . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }
 
 /*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedLevels( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    $value['level'] = $this->TrimString( $value['level'], 25 );
    $list .= "<option value=\"" . $value['level'] . "\">" . $value['level'] . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }
 
 /*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedGroups( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    $value['group'] = $this->TrimString( $value['group'], 25 );
    $list .= "<option value=\"" . $value['group'] . "\">" . $value['group'] . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }
 
	/*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedUsers( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    $value['username'] = $this->TrimString( $value['username'], 25 );
    $list .= "<option value=\"" . $value['username'] . "\">" . $value['username'] . "</option>";
   }
   $list .= "</select>";
  }
  return $list;
 }
	
 /*
  * Generate a simple drop down menu from array
  * @array => Nested array of data to use in form
  * @selected => Option to show if $_POST or $_GET processed
  * @name => Name of drop down menu
  */
 function GenDropMenuWSelectedClassOpts( $array, $selected, $name )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  if( count( $array[0] ) !== 0 ) {
   $list .= "<select name=\"". $name . "\" style=\"width: 100%\">";
   if( !empty( $selected ) ) {
    $list .= "<option value=\"" . $selected . "\">" . $selected . "</option>";
   }
   $list .= "<option>---------------</option>";
   foreach( $array as $key => $value ) {
    if( $value['Field'] !== "id" ) {
     $value['Field'] = $this->TrimString( $value['Field'], 25 );
     if( $value['Type'] === "tinyint(1)" ) { $value['Type'] = "Boolean (1|0)"; }
     $list .= "<option value=\"" . $value['Field'] . "\">" . $value['Field'] . " (" . $value['Type'] . ")</option>";
    }
   }
   $list .= "</select>";
  }
  return $list;
 }
 
 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxDNSSEC( $array, $name, $skin )
 { //print_r( $array );
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\">";
  if( count( $array ) === 0 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No DNS Keys Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['key-name'] = $this->TrimString( $value['key-name'], 20 );
    $value['algorithm'] = $this->TrimString( $value['algorithm'], 15 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['key-name'] . " - " . $value['algorithm'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxDNS( $array, $name, $skin )
 { //echo count( $array );
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\">";
  if( count( $array ) === 0 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No DNS Zones Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['zone'] = $this->TrimString( $value['zone'], 20 );
				$value['primary'] = $this->TrimString( $value['primary'], 20 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['zone'] . " | " . $value['type'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxSubnets( $array, $name, $skin )
 { //echo count( $array );
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\">";
  if( count( $array ) <= 1 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No Subnets Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['subnet-name'] = $this->TrimString( $value['subnet-name'], 15 );
				$value['subnet'] = $this->TrimString( $value['subnet'], 20 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['subnet-name'] . " | " . $value['subnet'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
 /*
  * Perform nested array in_array() function
  */
 function array_search_recursive( $needle, $haystack )
 { //echo "$needle<pre>"; print_r( $haystack ); echo "</pre><hr>";
  if( count( $haystack ) !== 0 ) {
   foreach( $haystack as $k => $v ) {
    if( $v['subnet-name'] === $needle ) { 
     return TRUE;
     break;
    }
   }
  }
 }
 
 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenSubnetCheckBoxes( $array, $name, $skin, $checked )
 { //echo "<pre>"; print_r( $checked ); echo "</pre>";
  if( count( $array ) <= 1 ) {
   $frm .= "No Subnets Defined";
  } else {
   foreach( $array as $key => $value ) {
    if( $this->array_search_recursive( $value['subnet-name'], $checked ) === TRUE ) {
     $frm .= "<tr><td nowrap><b>" .$value['subnet-name'] . "</b>:</td><td><input type=\"checkbox\" name=\"" . $name . "\" value=\"" . $value['subnet-name'] . "\" checked></td></tr>";
    } else {
     $frm .= "<tr><td nowrap><b>" .$value['subnet-name'] . "</b>:</td><td><input type=\"checkbox\" name=\"" . $name . "\" value=\"" . $value['subnet-name'] . "\"></td></tr>";
    }
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxSharedNetworks( $array, $name, $skin )
 {
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\">";
  if( count( $array ) < 1 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No Shared Networks Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['shared-network-name'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
	/*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxPools( $array, $name, $skin )
 { //echo "<pre>"; print_r( $array ); echo "</pre>";
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\">";
  if( count( $array ) < 1 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No Pools Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['pool-name'] = $this->TrimString( $value['pool-name'], 15 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['pool-name'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
	
 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxClasses( $array, $name, $skin )
 {
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\">";
  if( count( $array[0] ) <= 0 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No Classes Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $val = $this->TrimString( $value['class-name'], 35 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['class-name'] . "\">" . $val . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxPXE( $array, $name, $skin )
 { //echo count( $array );
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\">";
  if( count( $array ) < 1 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No PXE Groups Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['pxe-group-name'] = $this->TrimString( $value['pxe-group-name'], 15 );
    $value['pxe-server'] = $this->TrimString( $value['pxe-server'], 30 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['pxe-group-name'] . " | " . $value['pxe-server'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxHOSTS( $array, $name, $skin )
 { //echo count( $array );
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\" multiple>";
  if( count( $array ) < 1 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No Static Hosts Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['hostname'] = $this->TrimString( $value['hostname'], 30 );
    $value['ip-address'] = $this->TrimString( $value['ip-address'], 25 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['hostname'] . " | " . $value['ip-address'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
	 /*
  * Generate a jump menu list box
  * @array => Nested array of data to use in form
  * @name => Name of drop down menu
  */
 function GenJumpMenuBoxLEASES( $array, $name, $skin )
 { //echo count( $array );
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\" multiple>";
  if( count( $array ) < 1 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No Leases Found</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['ip'] = $this->TrimString( $value['ip'], 30 );
    $value['start'] = $this->TrimString( $value['start'], 25 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['ip'] . " | " . $value['start'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
	
	function GenJumpMenuBoxGROUPS( $array, $name, $skin )
 {
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\" multiple>";
  if( count( $array ) < 1 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No Groups Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['group'] = $this->TrimString( $value['group'], 15 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['group'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
	
 function GenJumpMenuBoxUSERS( $array, $name, $skin )
 {
  $frm .= "<select name=\"" . $name . "\" size=\"8\" onClick=\"jumpMenu('parent',this,0)\" multiple>";
  if( count( $array ) < 1 ) {
   $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=NULL\">No Users Defined</option>";
  } else {
   foreach( $array as $key => $value ) {
    $value['username'] = $this->TrimString( $value['username'], 30 );
				$value['first'] = $this->TrimString( $value['first'], 12 );
    $value['last'] = $this->TrimString( $value['last'], 12 );
    $frm .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?skin=" . $skin . "&id=" . $value['id'] . "\">" . $value['username'] . " | " . $value['first'] . " " . $value['last'] . "</option>";
   }
  }
  $frm .= "</select>";
  $data = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"3\">
            <tr>
             <td valign=\"top\">$frm</td>
            <tr>
           </table>";
  return $data;
 }
 
	function GenDropMenuURL( $table, $field, $order, $id )
 {
  global $defined;
  $db = new dbConn();
  $val = new ValidateStrings();
  if( ( empty( $table ) ) || ( empty( $field ) ) ) {
   return -1;
  }
  $conn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
  if( empty( $order ) ) {
   $query = "SELECT `$field` FROM `$table`";
  } else {
   $query = "SELECT `$field` FROM `$table` ORDER BY `$order`";
  }
  $query = $val->ValidateSQL( $query, $conn );
  if( ( $value = $db->dbQuery( $query, $conn ) ) === -1 ) {
   return -1;
  }
  if( ( $db->dbNumRows( $value ) === -1 ) || ( $db->dbNumRows( $value ) === 0 ) ) {
   return -1;
  } else {
   $list = "<option name=\"----------\" value=\"----------\">----------</option>";
   foreach( $db->dbArrayResults( $value ) as $key => $val ) {
    foreach( $val as $key => $val ) {
				 if( strlen( $val ) >= 40 ) {
					 $list .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?" . $id . "=" . $val . "\">" . substr_replace( $val, '...', 40 ) . "</option>";
					} else {
      $list .= "<option value=\"" . $_SERVER['PHP_SELF'] . "?" . $id . "=" . $val . "\">$val</option>";
					}
    }
   }
   $data = $list;
  }
  $db->dbFreeData( $conn );
  $db->dbCloseConn( $conn );
  return $data;
 }
	
	function GenGroupList( $group )
 {
  global $defined;
  $db = new dbConn();
  $conn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
  $query = "SELECT * FROM `groups` WHERE `group` = \"$group\" LIMIT 1";
  if( ( $value = $db->dbQuery( $query, $conn ) ) === -1 ) {
   return -1;
  }
  if( ( $db->dbNumRows( $value ) === -1 ) || ( $db->dbNumRows( $value ) === 0 ) ) {
   return -1;
  } else {
   $data = $db->dbArrayResults( $value );
  }
  $db->dbFreeData( $conn );
  $db->dbCloseConn( $conn );
  return $data;
 }
	
	function GenUserList( $user )
 {
  global $defined;
  $db = new dbConn();
  $conn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
  $query = "SELECT * FROM `users` WHERE `username` = \"$user\" LIMIT 1";
  if( ( $value = $db->dbQuery( $query, $conn ) ) === -1 ) {
   return -1;
  }
  if( ( $db->dbNumRows( $value ) === -1 ) || ( $db->dbNumRows( $value ) === 0 ) ) {
   return -1;
  } else {
   $data = $db->dbArrayResults( $value );
  }
  $db->dbFreeData( $conn );
  $db->dbCloseConn( $conn );
  return $data;
 }
	
 /*
  * Generate new URI with $_GET params
  * @get => Current list of $_GET vars
  */
 function BuildGETParams( $get )
	{
	 $val = new ValidateStrings();
	 $data = "?";
	 foreach( $get as $key => $value ) {
		 $data .= $val->ValidateXSS( $key ) . "=" . $val->ValidateXSS( $value ) . "&";
		}
		$data = substr_replace( $data, '', -1, count( $data ) );
		return $data;
	}

 function DetArrayType( $array ) {
  $val = 0;
  $keys = array_keys( $array[0] );
  foreach( $keys as $key ) {
   if( !is_int( $key ) ) {
    $val = 1;
   }
  }
  return $val;
 }

 /*
  * return a reversed dns lookup
  */
 function GenRevAddr( $ip ) {
  $adapter = @dns_get_record( implode( '.', array_reverse( explode( '.', $ip ) ) ) . '.in-addr.arpa.', DNS_PTR );
  if( empty( $adapter ) ) {
   $adapter = "ERROR DETERMINING REVERSE HOST RECORD";
  }
  return $adapter;
 }

 /*
  * test the status up/down of dhcpd service
  */
 function GetDHCPDStatus()
 {
  global $defined;
  $cmd = $defined['ps'] . ' -aux';
  $res = shell_exec( $cmd );
  if( eregi( 'dhcpd', $res ) ) {
   return 0;
  } else {
   return -1;
  }
 }

 /*
  * process lease file
  */
 function GetCurrentLeases( $file )
 {
	 global $defined;
  $db = new dbConn;
  $val = new ValidateStrings;
		
		// initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );
		
		// get timestamp, filesize and md5 data
		$check = "SELECT * FROM `conf_leases_properties`";
		$res = $db->dbQuery( $val->ValidateSQL( $check, $dbconn ), $dbconn );
		$chk = $db->dbArrayResults( $res );

  // see if results are empty to place data
		if( count( $chk ) === 0 ) {
		 $date = $this->GenDate();
		 $check = "INSERT INTO `conf_leases_properties` ( `date`, `size`, `hash` ) VALUES ( \"" . $date . "\", \"" . filesize( $file ) . "\", \"" . md5( $file ) . "\" )";
			$db->dbQuery( $val->ValidateSQL( $check, $dbconn ), $dbconn );
		}
		
		// perform check on file data
		if( file_exists( $file ) ) {
 		if( ( $chk[0]['size'] !== filesize( $file ) ) && ( $chk[0]['hash'] !== md5( $file ) ) ) {

   // execute update on lease data
    if( !empty( $file ) ) {
     if( ( $tmp = $this->SafeReadFileNoDir( $file ) ) !== -1 ) {
      foreach( $tmp as $key => $value ) {
       $setflag = "UPDATE `conf_leases_properties` SET `recreate` = \"FALSE\" WHERE `id` = 1 LIMIT 1";
       $db->dbQuery( $val->ValidateSQL( $setflag, $dbconn ), $dbconn );
             
       $group = $this->DetermineGroup( $value['ip'] );
  				 $value['uid'] = addslashes( $value['uid'] );
  					$insert = "INSERT INTO `conf_leases` ( `ip`, `start`, `end`, `cltt`, `current-state`, `next-state`, `hardware`, `hostname`, `abandoned`, `circut-id`, `remote-id`, `ddns-text`, `ddns-fwd-name`, `ddns-client-fqdn`, `ddns-rev-name`, `uid`, `group` ) VALUES ( \"" . $value['ip'] . "\", \"" . $value['starts'] . "\", \"" . $value['ends'] . "\", \"" . $value['cltt'] . "\", \"" . $value['current_state'] . "\", \"" . $value['next_state'] . "\", \"" . $value['hardware'] . "\", \"" . $value['hostname'] . "\", \"" . $value['abandoned'] . "\", \"" . $value['circut_id'] . "\", \"" . $value['remote_id'] . "\", \"" . $value['ddns_text'] . "\", \"" . $value['ddns_fwd_name'] . "\", \"" . $value['ddns_client_fqdn'] . "\", \"" . $value['ddns_rev_name'] . "\", \"" . mysql_real_escape_string( $value['uid'] ) . "\", \"" . $group . "\" )";
  					$update = "UPDATE `conf_leases` SET `ip` = \"" . $value['ip'] . "\", `start` = \"" . $value['starts'] . "\", `end` = \"" . $value['ends'] . "\", `cltt` = \"" . $value['cltt'] . "\", `current-state` = \"" . $value['current_state'] . "\", `next-state` = \"" . $value['next_state'] . "\", `hardware` = \"" . $value['hardware'] . "\", `hostname` = \"" . $value['hostname'] . "\", `abandoned` = \"" . $value['abandoned'] . "\", `circut-id` = \"" . $value['circut_id'] . "\", `remote-id` = \"" . $value['remote_id'] . "\", `ddns-text` = \"" . $value['ddns_text'] . "\", `ddns-fwd-name` = \"" . $value['ddns_fwd_name'] . "\", `ddns-client-fqdn` = \"" . $value['ddns_client_fqdn'] . "\", `ddns-rev-name` = \"" . $value['ddns_rev_name'] . "\", `uid` = \"" . mysql_real_escape_string( $value['uid'] ) . "\", `group` = \"" . $group . "\" WHERE `ip` = \"" . $value['ip'] . "\" LIMIT 1";
 					 
       if( ( $res = $db->dbQuery( $val->ValidateSQL( $insert, $dbconn ), $dbconn ) ) === -1 ) {
   					if( eregi( "duplicate", $db->dbCatchError() ) ) {
   					 $res = $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn );
   					}
  					}
  				}
     } else {
						$data = -3;
					}
    } else {
     $data = -2;
    }
   } else {
    $data = -1;
   }
  }
  return $data;
 }

 /*
  * Attempt to determine which group the lease belongs to
  * based on the group ownership of the subnet config data
  */
 function DetermineGroup( $ip )
 {
  global $defined;
  $db = new dbConn;
  $val = new ValidateStrings;

  // initialize a db connection handle  
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

  // break ip apart
  preg_match( '/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\.([0-9]{1,3})/', $ip, $octet );

  $last_octet = $octet[2];

  // Attempt lookup on ip vs. subnet group ownership
  $sql = "SELECT `group`,`subnet`,`subnet-mask` FROM `conf_subnets` WHERE `subnet` LIKE \"" . $octet[1] . ".%\"";
  if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql, $dbconn ), $dbconn ) ) !== -1 ) {
   $array = $db->dbArrayResults( $value );
  }

  // look for multiple results and filter further if necessary
  if( count( $array ) > 1 ) {
   foreach( $array as $key => $data ) {
    preg_match( '/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\.([0-9]{1,3})/', $data['subnet-mask'], $msk );
    preg_match( '/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\.([0-9]{1,3})/', $data['subnet'], $sub );
    $max = 256 - $msk[2]; $min = $sub[2];
    if( $min == $max ) { $max = 255; }
    // is the ip within a specified range?
    // if so assign the group id belong to that particular subnet to our lease
    if( ( $last_octet <= $max ) && ( $last_octet >= $min ) ) {
     return $data['group'];
    }
   }
  }
  return;
 } 

 /*
	 * attempt to get list of networks machine is configured for
		*/
	function GetAdapters()
	{
  global $defined;
	 $cmd = $defined['netstat'] . ' -inWa';
		$res = shell_exec( $cmd );
		if( $this->SafeWriteFile( $defined['confpath'], 'adapters.conf', $res ) === 0 ) {
		 $tmp = $this->SafeReadFile( $defined['confpath'], 'adapters.conf' );
   @unlink( $defined['confpath'] . 'adapters.conf' );

			// process for list of adapters
   $array = preg_split( '/[\r\n]+/', $tmp );
   // list of adapters and their attributes
   for( $i = 0; $i < count( $array ); $i++ ) {
    if( !empty( $array[$i] ) ) {
     $adapters[] = preg_split( '/[\t\s]+/', $array[$i] );
    }
   }

   // process list of adapters for changes
   if( $adapters !== -1 ) {
    // loop over results
    for( $x = 0; $x <= count( $adapters ); $x++ ) {
     // filter non adapters and headers
     if( !eregi( "kernel|iface", $adapters[$x][0] ) ) {
      if( !empty( $adapters[$x][0] ) ) {
       // now grab properties of each
       $arr[$adapters[$x][0]] = $this->GetAdaptersProperties( $adapters[$x][0] );
      }
     }
    }
   } else {
    return -1;
   }
		} else {
		 return -1;
		}
	}

 /*
  * extended properties for adapters
  */
 function GetAdaptersProperties( $adapter )
 {
  if( !empty( $adapter ) ) {
   global $defined;
   $cmd = array(); $res = array(); $array = array();
			// define our commands to eliminate shitty filters
   $cmd['encap'] = $defined['ifconfig'] . " " . $adapter . " | awk '/encap/ { print $3 }'";
			$cmd['hwaddr'] = $defined['ifconfig'] . " " . $adapter . " | awk '/HWaddr/ { print $5 }'";
			$cmd['ipv4'] = $defined['ifconfig'] . " " . $adapter . " | awk '/inet addr/ { print $2 }'";
			$cmd['broadcast'] = $defined['ifconfig'] . " " . $adapter . " | awk '/Bcast/ { print $3 }'";
   $cmd['mask'] = $defined['ifconfig'] . " " . $adapter . " | awk '/Mask/ { print $4 }'";
			$cmd['ipv6'] = $defined['ifconfig'] . " " . $adapter . " | awk '/inet6 addr/ { print $3 }'";
			$cmd['flags'] = $defined['ifconfig'] . " " . $adapter . " | awk '/BROADCAST/ { print $1 $2 $3 $4 }'";
			$cmd['RX_packets'] = $defined['ifconfig'] . " " . $adapter . " | awk '/RX packets/ { print $2 }'";
			$cmd['RX_errors'] = $defined['ifconfig'] . " " . $adapter . " | awk '/RX packets/ { print $3 }'";
			$cmd['RX_dropped'] = $defined['ifconfig'] . " " . $adapter . " | awk '/RX packets/ { print $4 }'";
			$cmd['RX_overruns'] = $defined['ifconfig'] . " " . $adapter . " | awk '/RX packets/ { print $5 }'";
			$cmd['RX_frame'] = $defined['ifconfig'] . " " . $adapter . " | awk '/RX packets/ { print $6 }'";
			$cmd['TX_packets'] = $defined['ifconfig'] . " " . $adapter . " | awk '/TX packets/ { print $2 }'";
			$cmd['TX_errors'] = $defined['ifconfig'] . " " . $adapter . " | awk '/TX packets/ { print $3 }'";
			$cmd['TX_dropped'] = $defined['ifconfig'] . " " . $adapter . " | awk '/TX packets/ { print $4 }'";
			$cmd['TX_overruns'] = $defined['ifconfig'] . " " . $adapter . " | awk '/TX packets/ { print $5 }'";
			$cmd['TX_carrier'] = $defined['ifconfig'] . " " . $adapter . " | awk '/TX packets/ { print $6 }'";
			$cmd['RX_total'] = $defined['ifconfig'] . " " . $adapter . " | awk '/RX bytes/ { print $2 }'";
			$cmd['TX_total'] = $defined['ifconfig'] . " " . $adapter . " | awk '/RX bytes/ { print $6 }'";
   // loop over commands and store in an array
			foreach( $cmd as $key => $value ) {
 			$x = shell_exec( $value );
				$res[$adapter][$key] = rtrim( $x );
			}
   // now send it off for processing
			$array = $this->FilterSplitDetails( $res );
  }
		// and last but not least put them in the damn db
		$this->ProcessAdapterDetails( $array );
 }

 /*
	 * stick our results in the db
		*/
	function ProcessAdapterDetails( $array )
	{
	 global $defined;
  $db = new dbConn;
  $val = new ValidateStrings;
  $lib = new MiscFunctions;

  $insert = ''; $update = ''; $key = ''; $value = array();

  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

		// begin loop
		foreach( $array as $key => $value ) {

		 // sql statements
			$insert = "INSERT INTO `conf_adapters` ( `name`, `encap`, `hwaddr`, `ipv4`, `broadcast`, `mask`, `ipv6`, `flags`, `rx_packets`, `rx_errors`, `rx_dropped`, `rx_overruns`, `rx_frame`, `tx_packets`, `tx_errors`, `tx_dropped`, `tx_overruns`, `tx_carrier`, `rx_bytes`, `tx_bytes` ) VALUES ( \"" . $key . "\", \"" . $value['encap'] . "\", \"" . $value['hwaddr'] . "\", \"" . $value['ipv4'] . "\", \"" . $value['broadcast'] . "\", \"" . $value['mask'] . "\", \"" . $value['ipv6'] . "\", \"" . $value['flags'] . "\", \"" . $value['RX_packets'] . "\", \"" . $value['RX_errors'] . "\", \"" . $value['RX_dropped'] . "\", \"" . $value['RX_overruns'] . "\", \"" . $value['RX_frame'] . "\", \"" . $value['TX_packets'] . "\", \"" . $value['TX_errors'] . "\", \"" . $value['TX_dropped'] . "\", \"" . $value['TX_overruns'] . "\", \"" . $value['TX_carrier'] . "\", \"" . $value['RX_total'] . "\", \"" . $value['TX_total'] . "\" )";
		 $update = "UPDATE `conf_adapters_details` SET `name` = \"" . $key . "\", `encap` = \"" . $value['encap'] . "\", `hwaddr` = \"" . $value['hwaddr'] . "\", `ipv4` = \"" . $value['ipv4'] . "\", `broadcast` = \"" . $value['broadcast'] . "\", `mask` = \"" . $value['mask'] . "\", `ipv6` = \"" . $value['ipv6'] . "\", `flags` = \"" . $value['flags'] . "\", `rx_packets` = \"" . $value['RX_packets'] . "\", `rx_errors` = \"" . $value['RX_errors'] . "\", `rx_dropped` = \"" . $value['RX_dropped'] . "\", `rx_overruns` = \"" . $value['RX_overruns'] . "\", `rx_frame` = \"" . $value['RX_frame'] . "\", `tx_packets` = \"" . $value['TX_packets'] . "\", `tx_errors` = \"" . $value['TX_errors'] . "\", `tx_dropped` = \"" . $value['TX_dropped'] . "\", `tx_overruns` = \"" . $value['TX_overruns'] . "\", `tx_carrier` = \"" . $value['TX_carrier'] . "\", `rx_bytes` = \"" . $value['RX_total'] . "\", `tx_bytes` = \"" . $value['TX_total'] . "\" WHERE `name` = \"" . $key . "\" LIMIT 1";
   
			// determine if an traffic table update should occur
   if( $value['RX_total'] !== 0 ) {

    // time machine calcs
    // (only run once an hour)
    $now = $this->GenTime();

    // grab some current traffic data
    $res = $db->dbQuery( $val->ValidateSQL( "SELECT `time` FROM `conf_traffic` WHERE `time` < \"" . $now . "\" AND `bytes` > 0 AND `interface` = \"" . $key . "\" LIMIT 1", $dbconn ), $dbconn );
  		$times = $db->dbArrayResults( $res );

    // get a value we can compare against an hour
    $test = abs( $now - $times[0]['time'] );

    // insert some data
    if( ( $test > 3600 ) && ( !empty( $value['RX_total'] ) ) ) {
  			$traffic = "INSERT INTO `conf_traffic` ( `interface`, `bytes`, `time` ) VALUES ( \"" . $key . "\", \"" . $value['RX_total'] . "\", UNIX_TIMESTAMP() )";
     $db->dbQuery( $val->ValidateSQL( $traffic, $dbconn ), $dbconn );
    }
	  }
 		
		 // do the db stuff for our adapters
   if( $db->dbQuery( $val->ValidateSQL( $insert, $dbconn ), $dbconn ) !== 0 ) {
				if( eregi( "duplicate", $db->dbCatchError() ) ) {
     $db->dbQuery( $val->ValidateSQL( $update, $dbconn ), $dbconn );
 	 	}
		 }
			
 	}
	}

 /*
	 * Filter and split adapter details array
		*/
 function FilterSplitDetails( $array )
 {
	 foreach( $array as $key => $value ) {
   foreach( $value as $index => $data ) {
 		 if( !eregi( "ipv6|hwaddr|flags", $index ) ) {
 				list( $blank, $data ) = split( ":", $data );
				}
    $return[$key][$index] = $data;
			}
		}
		return $return;
	}
	
 /*
  * trim string length
  */
 function TrimString( $string, $len )
 { //echo "STRING: " . $string . " LENGTH: " . strlen( $string ) . " ALLOWED LENGTH: " . $len . " FIXED: " . substr( $string, 0, $len - 5 ) . "<br>";
  $strlen = strlen( $string );
		if( $strlen < $len ) {
		 return $string;
		} else {
   return substr( $string, 0, $len - 5 ) . "...";
		}
 }

 /*
  * Generate an HTML table from array
  */
 function GenTableFromAssocArray( $array )
 {
  if( count( $array ) === 0 ) {
   $table = -1;
  } else {
   $table = "<table border=0 width=80% cellspacing=5>";
   foreach( $array as $key => $value ) {
    $table .= "<tr align=center><td nowrap><b>$key</b></td><td>&nbsp;=>&nbsp;<td>$value</td></tr>";
   }
   $table .= "</table>";
  }
  return $table;
 }

/*
  * Generate an HTML table from array
  */
 function GenTableFromAssocArrayDuplicateHost( $array, $match )
 {
  if( count( $array ) === 0 ) {
   $table = -1;
  } else {
   $table = "<table border=0 width=80% cellspacing=5>";
   $table .= "<tr><td nowrap><b>Overwrite existing?</b></td><td><input type=checkbox name=modify value=modify></td></tr>";
   $table .= "<tr><td nowrap><b>Hostname:</b></td><td>" . $array['hostname'] . "</td><td>" . $match['hostname'] . "</td></tr>";
   $table .= "<tr><td nowrap><b>MAC Address:</b></td><td>" . $array['mac-address'] . "</td><td>" . $match['mac-address'] . "</td></tr>";
   $table .= "<tr><td nowrap><b>IP Address:</b></td><td>" . $array['ip-address'] . "</td><td>" . $match['ip-address'] . "</td></tr>";
   $table .= "</table><hr>";
  }
  return $table;
 }

 /*
	 * generate table of class options
		*/
 function GenTableClassOpts( $count, $array, $select1, $select2, $error )
	{ //echo "<pre>"; print_r( $array ); echo "</pre>";
   //echo "<pre>"; print_r( $error ); echo "</pre>";
	 if( $count === 0 ) {
		 return;
		}
		for( $x = 1; $x <= $count; $x++ ) {
		 $table .= "<tr>
			            <td align=\"center\" colspan=\"6\"><hr><b>Option #" . $x . "</b></td>
													 </tr>
			           <tr>
			            <td nowrap><b>Select Option:</b></td>
													 	<td colspan=\"4\">" . $select1[$x] . "</td>
													 	<td colspan=\"2\" class=\"copyright\" nowrap>" . $error[$x]['option'] . "* Select Option?</td>
													 </tr>
													 <tr>
			            <td nowrap><b>Match?</b></td>
													 	<td>True:</td>
													 	<td><input type=\"radio\" name=\"options[$x][match]\" value=\"TRUE\" " . $error[$x]['match_enable'] . "></td>
													 	<td>False:</td>
													 	<td><input type=\"radio\" name=\"options[$x][match]\" value=\"FALSE\" " . $error[$x]['match_disable'] . "></td>
													 	<td class=\"copyright\" nowrap>" . $error[$x]['match'] . "* REGEX?</td>
													 </tr>
													 <tr>
			            <td nowrap><b>Match Option:</b></td>
													 	<td colspan=\"4\">" . $select2[$x] . "</td>
													 	<td colspan=\"2\" class=\"copyright\" nowrap>" . $error[$x]['option'] . "* Match Option?</td>
													 </tr>
								 					<tr>
			            <td nowrap><b>Substring?</b></td>
								 						<td>True:</td>
								 						<td><input type=\"radio\" name=\"options[$x][substring]\" value=\"TRUE\" " . $error[$x]['substring_enable'] . "></td>
								 						<td>False:</td>
								 						<td><input type=\"radio\" name=\"options[$x][substring]\" value=\"FALSE\" " . $error[$x]['substring_disable'] . "></td>
								 						<td class=\"copyright\" nowrap>* Substring?</td>
								 					</tr>
								 					<tr>
			            <td nowrap><b>Substring values?</b></td>
								 						<td>Start:</td>
								 						<td><input type=\"text\" name=\"options[$x][substring_start]\" value=\"" . $array[$x]['substring_start'] . "\" style=\"width: 100%\"></td>
								 						<td>End:</td>
								 						<td><input type=\"text\" name=\"options[$x][substring_end]\" value=\"" . $array[$x]['substring_end'] . "\" style=\"width: 100%\"></td>
								 						<td class=\"copyright\" nowrap>" . $error[$x]['substring'] . "* Start/End</td>
								 					</tr>
								 					<tr>
			            <td nowrap><b>REGEX Value:</b></td>
								 						<td colspan=\"4\"><input type=\"text\" name=\"options[$x][substr_regex]\" value=\"" . $array[$x]['substr_regex'] . "\" style=\"width: 100%\"></td>
								 						<td colspan=\"2\" class=\"copyright\" nowrap>" . $error[$x]['substr_regex'] . "* REGEX String?</td>
								 					</tr>";
		}
		return $table;
	}

 /*
	 * generate table of class options
		*/
 function GenTableClassOptsAssoc( $count, $array, $select1, $select2, $error )
	{ //echo "<pre>"; print_r( $array ); echo "</pre>";
   //echo "<pre>"; print_r( $error ); echo "</pre>";
	 if( $count === 0 ) {
		 return;
		}
		for( $x = 0; $x < $count; $x++ ) {
		 $table .= "<tr>
			            <td align=\"center\" colspan=\"6\"><hr><b>Option #" . $x . "</b></td>
													 </tr>
			           <tr>
			            <td nowrap><b>Select Option:</b></td>
													 	<td colspan=\"4\">" . $select1[$x] . "<input type=\"hidden\" name=\"options[$x][id]\" value=\"" . $array[$x]['id'] . "\"></td>
													 	<td colspan=\"2\" class=\"copyright\" nowrap>" . $error[$x]['option'] . "* Select Option?</td>
													 </tr>
													 <tr>
			            <td nowrap><b>Match?</b></td>
													 	<td>True:</td>
													 	<td><input type=\"radio\" name=\"options[$x][match]\" value=\"TRUE\" " . $error[$x]['match_enable'] . "></td>
													 	<td>False:</td>
													 	<td><input type=\"radio\" name=\"options[$x][match]\" value=\"FALSE\" " . $error[$x]['match_disable'] . "></td>
													 	<td class=\"copyright\" nowrap>" . $error[$x]['match'] . "* REGEX?</td>
													 </tr>
													 <tr>
			            <td nowrap><b>Match Option:</b></td>
													 	<td colspan=\"4\">" . $select2[$x] . "</td>
													 	<td colspan=\"2\" class=\"copyright\" nowrap>" . $error[$x]['option'] . "* Match Option?</td>
													 </tr>
								 					<tr>
			            <td nowrap><b>Substring?</b></td>
								 						<td>True:</td>
								 						<td><input type=\"radio\" name=\"options[$x][substring]\" value=\"TRUE\" " . $error[$x]['substring_enable'] . "></td>
								 						<td>False:</td>
								 						<td><input type=\"radio\" name=\"options[$x][substring]\" value=\"FALSE\" " . $error[$x]['substring_disable'] . "></td>
								 						<td class=\"copyright\" nowrap>* Substring?</td>
								 					</tr>
								 					<tr>
			            <td nowrap><b>Substring values?</b></td>
								 						<td>Start:</td>
								 						<td><input type=\"text\" name=\"options[$x][substring_start]\" value=\"" . $array[$x]['class-substring-start'] . "\" style=\"width: 100%\"></td>
								 						<td>End:</td>
								 						<td><input type=\"text\" name=\"options[$x][substring_end]\" value=\"" . $array[$x]['class-substring-end'] . "\" style=\"width: 100%\"></td>
								 						<td class=\"copyright\" nowrap>" . $error[$x]['substring'] . "* Start/End</td>
								 					</tr>
								 					<tr>
			            <td nowrap><b>REGEX Value:</b></td>
								 						<td colspan=\"4\"><input type=\"text\" name=\"options[$x][substr_regex]\" value=\"" . $array[$x]['match-substring-regex'] . "\" style=\"width: 100%\"></td>
								 						<td colspan=\"2\" class=\"copyright\" nowrap>" . $error[$x]['substr_regex'] . "* REGEX String?</td>
								 					</tr>";
		}
		return $table;
	}

 /*
  * apply re-index of array keys
  */
 function ReIndexArray( $array ) {
  $index = 1;
  if( count( $array ) === 0 ) {
		 return;
		}
		foreach( $array as $keys => $values ) {
   $data[$index] = $values;
   $index++;
  }
  return $data;
 }

 /*
  * Generate our import form with applicable elements and values
  * $num => Number of elements
  * $hostname, $hostname_err => hostname and error variable
  * $ip_address, $ip_address_err => ip address and error variable
  * $mac_address, $mac_address_err => mac address and error variable
  * $subnet, $subnet_err => subnet drop list and error variable
  * $pxe_group, $pxe_group_err => pxe group drop list and error variable
  */
 function GenImportValidateHostForm( $num, $skin, $hostname, $ip_address, $mac_address, $subnet, $pxe_group, $hostname_err, $ip_address_err, $mac_address_err, $subnet_err, $pxe_group_err )
 {
  $form = "<tr>
            <td colspan=\"3\"><hr></td>
           </tr>
           <tr>
            <td colspan=\"3\">
             <TABLE width=\"100%\" cellspacing=\"5\" border=\"0\" cellpadding=\"0\" summary=\"globalForm\">
              <tr>
               <td colspan=\"3\">
                <a href=\"javascript:popUp('help/help.html#import_host_record','800','800')\">
                 <img src=\"templates/$skin/images/help02.jpg\" border=\"0\" alt=\"\">
                </a>
                &nbsp;&nbsp;<b>Error with record #" . $num . " from imported file</b><br>
                <div class=\"copyright\">** Please correct incorrect syntax on imported data to import this records</div>
               </td>
              </tr>
              <tr>
               <td width=\"5%\" nowrap><b>Hostname:</b></td>
               <td><input type=\"text\" name=\"record[$num][hostname]\" value=\"$hostname\" style=\"width: 100%\"></td>
               <td class=\"copyright\" nowrap>$hostname_err* Hostname of machine</td>
              </tr>
              <tr>
               <td width=\"5%\" nowrap><b>IP Address:</b></td>
               <td><input type=\"text\" name=\"record[$num][ip_address]\" value=\"$ip_address\" style=\"width: 100%\"></td>
               <td class=\"copyright\" nowrap>$ip_address_err* IP Address</td>
              </tr>
              <tr>
               <td nowrap><b>MAC Address:</b></td>
               <td><input type=\"text\" name=\"record[$num][mac_address]\" value=\"$mac_address\" style=\"width: 100%\"></td>
               <td class=\"copyright\" nowrap>$mac_address_err* MAC Address</td>
              </tr>
              <tr>
               <td nowrap><b>Assign Subnet:</b></td>
               <td>$subnet</td>
               <td class=\"copyright\" nowrap>$subnet_err* Assign Subnet</td>
              </tr>
              <tr>
               <td nowrap><b>Assign PXE Group:</b></td>
               <td>$pxe_group</td>
               <td class=\"copyright\" nowrap>$pxe_group_err* Assign to PXE Group</td>
              </tr>
             </td>
            </tr>
           </table>
          </td>
         </tr>";
  return $form;
 }

 /*
  * Generate our import form with applicable elements and values
  * $num => Number of elements
  * $hostname, $hostname_err => hostname and error variable
  * $ip_address, $ip_address_err => ip address and error variable
  * $mac_address, $mac_address_err => mac address and error variable
  * $subnet, $subnet_err => subnet drop list and error variable
  * $pxe_group, $pxe_group_err => pxe group drop list and error variable
  */
 function GenImportDuplicateHostForm( $num, $skin, $hostname, $hostname_dup, $ip_address, $ip_address_dup, $mac_address, $mac_address_dup, $subnet, $subnet_dup, $pxe_group, $pxe_group_dup, $hostname_err, $hostname_dup_err, $ip_address_err, $ip_address_dup_err, $mac_address_err, $mac_address_dup_err, $subnet_err, $subnet_dup_err, $pxe_group_err, $pxe_group_dup_err )
 {
  $form = "<tr>
            <td colspan=\"3\"><hr></td>
           </tr>
           <tr>
           <TD width=\"60%\" valign=\"top\">
            <TABLE width=\"100%\" cellspacing=\"5\" border=\"0\" cellpadding=\"0\" summary=\"globalForm\">
             <tr>
              <TD colspan=\"3\">
               <a href=\"javascript:popUp('help/help.html#import_host_record','800','800')\">
                <img src=\"templates/$skin/images/help02.jpg\" border=\"0\" alt=\"\">
               </a>
               &nbsp;&nbsp;<b>Importing records</b><br>
               <div class=\"copyright\">** There seems to be an existing record found during import</div>
              </TD>
             </tr>
             <tr>
              <td width=\"5%\" nowrap><b>Hostname:</b></td>
              <td><input type=\"text\" name=\"hostname_tmp\" value=\"$hostname\" style=\"width: 100%\"></td>
              <td class=\"copyright\" nowrap>$hostname_err* Hostname of machine</td>
             </tr>
             <tr>
              <td width=\"5%\" nowrap><b>IP Address:</b></td>
              <td><input type=\"text\" name=\"ip_address_tmp\" value=\"$ip_address\" style=\"width: 100%\"></td>
              <td class=\"copyright\" nowrap>$ip_address_err* IP Address</td>
             </tr>
             <tr>
              <td nowrap><b>MAC Address:</b></td>
              <td><input type=\"text\" name=\"mac_address_tmp\" value=\"$mac_address\" style=\"width: 100%\"></td>
              <td class=\"copyright\" nowrap>$mac_address_err* MAC Address</td>
             </tr>
             <tr>
              <td nowrap><b>Assign Subnet:</b></td>
              <td>$subnet</td>
              <td class=\"copyright\" nowrap>$subnet_err* Assign Subnet</td>
             </tr>
             <tr>
              <td nowrap><b>Assign PXE Group:</b></td>
              <td>$pxe_group</td>
              <td class=\"copyright\" nowrap>$pxe_group_err* Assign to PXE Group</td>
             </tr>
            </table>
           </TD>
           <TD width=\"40%\" valign=\"top\">
            <TABLE width=\"100%\" cellspacing=\"5\" border=\"0\" cellpadding=\"0\" summary=\"globalForm\">
             <tr>
              <TD colspan=\"3\">
               <a href=\"javascript:popUp('help/help.html#import_host_duplicate','800','800')\">
                <img src=\"templates/$skin/images/help02.jpg\" border=\"0\" alt=\"\">
               </a>
               &nbsp;&nbsp;<b>Duplicate Entry</b><br>
               <div class=\"copyright\">** This is the duplicate entry data</div>
              </TD>
             </tr>
             <tr>
              <td width=\"5%\" nowrap><b>Hostname:</b></td>
              <td>$hostname_dup</td>
              <td class=\"copyright\" nowrap>$hostname_dup_err</td>
             </tr>
             <tr>
              <td width=\"5%\" nowrap><b>IP Address:</b></td>
              <td>$ip_address_dup</td>
              <td class=\"copyright\" nowrap>$ip_address_dup_err</td>
             </tr>
             <tr>
              <td nowrap><b>MAC Address:</b></td>
              <td>$mac_address_dup</td>
              <td class=\"copyright\" nowrap>$mac_address_dup_err</td>
             </tr>
             <tr>
              <td nowrap><b>Subnet:</b></td>
              <td>$subnet_dup</td>
              <td class=\"copyright\" nowrap>$subnet_dup_err</td>
             </tr>
             <tr>
              <td nowrap><b>PXE Group:</b></td>
              <td>$pxe_group_dup</td>
              <td class=\"copyright\" nowrap>$pxe_group_dup_err</td>
             </tr>
            </table>
           </TD>";
  return $form;
 }

 function DetDup( $array ) {
  if( count( $array ) === 0 ) {
   $data = -1;
  } else {
   for( $x = 0; $x < count( $array ); $x++ ) {
    foreach( $array[$x] as $key => $value ) {
     if( @array_search( $array[$x]['ordernum'], $data ) === FALSE ) {
      $data[] = $array[$x]['ordernum'];
     }
    }
   }
  }
  return $data;
 }

 function EliminiateDuplicates( $array )
 {
  foreach( $array as $key => $value ) {
   $arrayOfArrays[$key] = "'" . serialize( $value ) . "'";
  }
  $arrayOfArrays = array_unique( $arrayOfArrays );
  foreach( $arrayOfArrays as $key => $value ) {
   $arrayOfArrays[$key] = unserialize( trim( $value, "'" ) );
  }
  sort( $arrayOfArrays );
  return $arrayOfArrays;
 }


 function ExitApplication( $token ) {
  if( !empty( $token ) ) {
   $session = new Sessions();
   $session->ClearSession( $token );
  }
  return;
 }

 function CleanUpVars( $array, $single )
 {
  if( !empty( $single ) ) { unset( $single ); }
  if( count( $array < 0 ) ) {
   for( $i = 0; $i < count( $array ); $i++ ) {
    unset( $array[$i] );
   }
  }
 }
	
	
	function parseToXML( $htmlStr )
 { 
  $xmlStr = str_replace( '<', '&lt;', $htmlStr ); 
  $xmlStr = str_replace( '>', '&gt;', $xmlStr ); 
  $xmlStr = str_replace( '"', '&quot;', $xmlStr ); 
  $xmlStr = str_replace( "'", '&#39;', $xmlStr ); 
  $xmlStr = str_replace( "&", '&amp;', $xmlStr ); 
  return $xmlStr;
 }
	
	function ArrayDimension( $array, $option = NULL ) {
	 $img = new ImageGallery();
  if( !is_array( $array ) ) {
   return 0;
  } elseif( !$array ) {
   return 1;
  } else {
   switch( strtolower( $option ) ) {
   case 'first':
    $keys = array_keys( $array );
    return $img->ArrayDimension( $array[$keys[0]], $option ) + 1;
   case 'last':
    $keys = array_keys( $array );
    return $img->ArrayDimension( $array[$keys[count( $keys ) - 1]], $option ) + 1;
   case 'min':
    foreach( $array as $key => $val ) {
     $dems[$key] = $img->ArrayDimension( $val, $option );
    }
    return min( $dems ) + 1;
   case 'max':
   default:
    foreach( $array as $key => $val ) {
     $dems[$key] = $img->ArrayDimension( $val, $option );
    }
    return max( $dems ) + 1;
   }
  }
 }
	
 function SafeReadFileNoDir( $file )
	{
  if( !empty( $file ) ) {
   if( ( $handle = @fopen( $file, "r" ) ) !== FALSE ) {
    if( @flock( $handle, LOCK_EX ) ) {
     while( !feof( $handle ) ) {
					 $line = @fgets( $handle, filesize( $file ) );
      if( preg_match( "/^(\#)/", $line ) ) { continue; }
						if( preg_match( "/^lease\s+([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\s+{/", $line, $lease ) ) {	$data[$lease[1]]['ip'] = $lease[1]; $ip = $lease[1]; }
      if( preg_match( "/^\s\sstarts\s\d+\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['starts'] = $lease[1]; }
      if( preg_match( "/^\s\sends\s\d+\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['ends'] = $lease[1]; }
						if( preg_match( "/^\s\scltt\s\d+\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['cltt'] = $lease[1]; }
						if( preg_match( "/^\s\sbinding\sstate\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['current_state'] = $lease[1]; }
						if( preg_match( "/^\s\snext\sbinding\sstate\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['next_state'] = $lease[1]; }
						if( preg_match( "/^\s\shardware\sethernet\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['hardware'] = $lease[1]; }
      if( preg_match( "/^\s\suid\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['uid'] = $lease[1]; }
						if( preg_match( "/^\s\shostname\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['hostname'] = $lease[1]; }
						if( preg_match( "/^\s\s(abandoned)\;/", $line, $lease ) ) {	$data[$ip]['abandoned'] = $lease[1]; }
						if( preg_match( "/^\s\soption\sagent.circut-id\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['circut_id'] = $lease[1]; }
						if( preg_match( "/^\s\soption\sagent.remote-id\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['remote_id'] = $lease[1]; }
						if( preg_match( "/^\s\sddns-text\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['ddns_text'] = $lease[1]; }
						if( preg_match( "/^\s\sddns-fwd-name\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['ddns_fwd_name'] = $lease[1]; }
						if( preg_match( "/^\s\sddns-client-fqdn\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['ddns_client_fqdn'] = $lease[1]; }
						if( preg_match( "/^\s\sddns-rev-name\s(.*)\;/", $line, $lease ) ) {	$data[$ip]['ddns_rev_name'] = $lease[1]; }
						@flock( $handle, LOCK_UN );
     }
    } else {
     $data = -3;
    }
   } else {
    $data = -2;
   }
  } else {
   $data = -1;
  }
  return $data;	
	}
 
	function SafeReadFile( $path, $file )
	{
  if( ( !empty( $file ) ) || ( !empty( $path ) ) ) {
   if( is_dir( $path ) ) {
    if( ( $handle = @fopen( $path . $file, "r" ) ) !== FALSE ) {
     if( @flock( $handle, LOCK_EX ) ) {
      while( !feof( $handle ) ) {
						 $data .= @fread( $handle, filesize( $path. $file ) );
       @flock( $handle, LOCK_UN );
      }
     } else {
      $data = -4;
     }
    } else {
     $data = -3;
    }
   } else {
    $data = -2;
   }
  } else {
   $data = -1;
  }
  return $data;	
	}
	
 function SafeWriteFile( $path, $file, $string )
 {
  if( ( !empty( $path ) ) && ( !empty( $file ) ) && ( !empty( $string ) ) ) {
   if( is_dir( $path ) ) {
    if( ( $handle = @fopen( $path . $file, "w+b" ) ) !== FALSE ) {
     if( @flock( $handle, LOCK_EX ) ) {
      if( @fwrite( $handle, $string ) !== FALSE ) {
       $data = 0;
       @flock( $handle, LOCK_UN );
      } else {
       $data = -5;
      }
     } else {
      $data = -4;
     }
    } else {
     $data = -3;
    }
   } else {
    $data = -2;
   }
  } else {
   $data = -1;
  }
  return $data;
 }
 
}
?>