<?PHP
/*
 * strings.inc.php
 *
 * Validate various string formats
 *
 * Copyright Jason Gerfen <jason.gerfen@gmail.com>
 *
 */

class ValidateStrings
{
 var $data;
 var $string;
 var $integer;
 var $alphachar;
 var $money;
 var $phone;
 var $zipcode;
 var $ip_v4;
 var $ip_v6;
 var $mac_address;
 var $domain;
 var $hostname;
 var $paragraph;
 var $uri;
 var $db;
 var $sql;
 var $xss;

 public function ValidateString( $string )
 {
  if( ( eregi( "^[a-z]{1,35}$", $string ) ) || ( empty( $string ) ) ) {
   $data->string = 0;
  } else {
   $data->string = -1;
  }
  return $data->string;
 }

 public function ValidateInteger( $integer )
 {
  if( ( eregi( "^[0-9]{1,20}$", $integer ) ) || ( empty( $integer ) ) ) {
   $data->integer = 0;
  } else {
   $data->integer = -1;
  }
  return $data->integer;
 }

 public function ValidateAlphaChar( $alphachar )
 {
  if( ( eregi( "^[0-9a-z]{1,45}$", $alphachar ) ) || ( empty( $alphachar ) ) ) {
   $data->alphachar = 0;
  } else {
   $data->alphachar = -1;
  }
  return $data->alphachar;
 }

 public function ValidateMoney( $money )
 {
  if( ( eregi( "^[0-9]{1,4}\.[0-9]{2}$", $money ) ) || ( empty( $money ) ) ) {
   $data->money = 0;
  } else {
   $data->money = -1;
  }
  return $data->money;
 }
	
	public function ValidateDecimal( $decimal )
	{
	 if( ( is_numeric( $decimal ) ) || ( empty( $decimal ) ) ) {
		 $data->decimal = 0;
		} else {
		 $data->decimal = -1;
		}
		return $data->decimal;
	}

 public function ValidatePhone( $phone ) {
  if( ( eregi( "^[0-9]{3}\-[0-9]{3}\-[0-9]{4}$", $phone ) ) || ( empty( $phone ) ) ) {
   $data->phone = 0;
  } else {
   $data->phone = -1;
  }
  return $data->phone;
 }

 public function ValidateZip( $zipcode ) {
  if( ( eregi( "^[0-9]{5}$", $zipcode ) ) || ( empty( $zipcode ) ) ) {
   $data->zipcode = 0;
  } else {
   $data->zipcode = -1;
  }
  return $data->zipcode;
 }

 public function ValidateIPv4( $ip_v4 = NULL )
 {
  $ip_v4 = rtrim( $ip_v4 );
  if( ( eregi( "^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $ip_v4 ) ) || ( empty( $ip_v4 ) ) ) {
   $data->ip_v4 = 0;
   for( $i = 1; $i <= 3; $i++ ) {
    if( !( substr( $ip_v4, 0, strpos( $ip_v4, "." ) ) >= "0" && substr( $ip_v4, 0, strpos( $ip_v4, "." ) ) <= "255" ) ) {
     $data->ip_v4 = -1;
    }
    $ip_v4 = substr( $ip_v4, strpos( $ip_v4, "." ) + 1 );
   }
   if( !( $ip_v4 >= "0" && $ip_v4 <= "255" ) ) {
    $data->ip_v4 = -1;
   }
/*
   $octets = explode( ".", $ip_v4 );
   foreach( $octets as $values ) {
    if( ( intval( $values ) > 255 ) || ( intval( $values ) < 0 ) ) {
     $data->ip_v4 = -1;
    }
   }
*/
  } else {
   $data->ip_v4 = -1;
  }
  return $data->ip_v4;
 }

public function ValidateIPv6( $ip_v6 )
 {
  $data->ip_v6 = 0;
  return $data->ip_v6;
 }

 function ValidateEmail( $email )
 { // "^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$"
	  // "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$"
			// "^[a-z0-9.-_]{1,20}\@[a-z0-9.-_]{1,20}\.[a-z]{2,5}$"
  if( ( eregi( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$", $email ) ) || ( empty( $email ) ) ) {
   $data = 0;
  } else {
   $data = -1;
  }
  return $data;
 }

 public function ValidateMACFormats( $mac_address = NULL ) {
  $mac_address = rtrim( $mac_address );
  if( eregi( "^[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = $mac_address;
  } elseif( eregi( "^[0-9a-f]{2}\.[0-9a-f]{2}\.[0-9a-f]{2}\.[0-9a-f]{2}\.[0-9a-f]{2}\.[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = $this->FixMACAddr( $mac_address );
  } elseif( eregi( "^[0-9a-f]{2}\-[0-9a-f]{2}\-[0-9a-f]{2}\-[0-9a-f]{2}\-[0-9a-f]{2}\-[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = $this->FixMACAddr( $mac_address );
  } elseif( eregi( "^[0-9a-f]{2}\_[0-9a-f]{2}\_[0-9a-f]{2}\_[0-9a-f]{2}\_[0-9a-f]{2}\_[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = $this->FixMACAddr( $mac_address );
  } elseif( eregi( "^[0-9a-f]{12}$", $mac_address ) ) {
   $data->mac_address = $this->FixMACAddr( $mac_address );
  } elseif( ( eregi( "^[0-9a-z/-/_]{1,35}$", $mac_address ) ) || ( eregi( "^[0-9a-z]{1,35}$", $mac_address ) ) ) {
   $data->mac_address = -1;
  } elseif( eregi( "^[0-9a-z%-_:.]{1,45}$", $mac_address ) ) {
   $data->mac_address = -1;
  } elseif( eregi( "[g-z]", $mac_address ) ) {
   $data->mac_address = -1;
  } else {
   $data->mac_address = -1;
  }
  return $data->mac_address;
 }

 public function FixMACAddr( $mac_address = NULL ) {
  if( eregi( "^[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = $mac_address;
  } elseif( eregi( "^[0-9a-f]{12}$", $mac_address ) ) {
   $data->mac_address = str_split( $mac_address, 2 );
   $data->mac_address = implode( ':', $data->mac_address );
  } elseif( eregi( "^[0-9a-f]{2}\-[0-9a-f]{2}\-[0-9a-f]{2}\-[0-9a-f]{2}\-[0-9a-f]{2}\-[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = str_replace( '-', ':', $mac_address );
  } elseif( eregi( "^[0-9a-f]{2}\_[0-9a-f]{2}\_[0-9a-f]{2}\_[0-9a-f]{2}\_[0-9a-f]{2}\_[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = str_replace( '_', ':', $mac_address );
  } elseif( eregi( "^[0-9a-f]{2}\.[0-9a-f]{2}\.[0-9a-f]{2}\.[0-9a-f]{2}\.[0-9a-f]{2}\.[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = str_replace( '.', ':', $mac_address );
  } elseif( eregi( "[g-z]", $mac_address ) ) {
   $data->mac_address = -1;
  } elseif( !eregi( "^[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}\:[0-9a-f]{2}$", $mac_address ) ) {
   $data->mac_address = -1;
  }
  return $data->mac_address;
 }

 public function ValidateDomain( $domain )
 {
  if( ( eregi( "^[a-z0-9.]+$", $domain ) ) || ( empty( $domain ) ) ) {
   if( ( @checkdnsrr( $domain, "A" ) ) || ( $this->ValidateHostname( $domain ) !== -1 ) || ( $this->ValidateIPv4( $domain ) !== -1 ) || ( $this->ValidateHostnameNonRFC( $domain ) !== -1 ) ) {
    $data->domain = 0;
   } else {
    $data->domain = -1;
   }
  } else {
   $data->domain = -1;
  }
  return $data->domain;
 }

 public function ValidateBroadcast2List( $array, $broadcast )
	{
		if( ( !empty( $broadcast ) ) && ( count( $array ) !== 0 ) ) {
			$octets = explode( '.', $broadcast );
   $flag = -1;
			foreach( $array as $key => $value ) {
				$tmp = explode( '.', $value );
				if( ( $octets[0] === $tmp[0] ) && ( $octets[1] === $tmp[1] ) && ( $octets[2] === $tmp[2] ) ) { $flag = 0; }
			}
			if( $flag === 0 ) {
				$data = 0;
			} else {
				$data = $flag;
			}
		} else {
			$data = 0;
		} //echo "<pre>"; print_r( func_get_args() ); echo "</pre><br>" . $broadcast . " => " . $data . "<hr>";
		return $data;
	}

 /* Validate an IP for static host not existing in scope */
 public function ValidateIPvsScope( $array, $ip )
 {
  $ret = 0;
  $ip_octets = explode( '.', $ip );
  foreach( $array as $key => $value ) {
   $scope_1_octets = explode( '.', $value['scope-range-1'] );
   $scope_2_octets = explode( '.', $value['scope-range-2'] );
   if( ( $scope_1_octets[0] === $ip_octets[0] ) && ( $scope_1_octets[1] === $ip_octets[1] ) &&  ( $scope_1_octets[2] === $ip_octets[2] ) && ( $scope_1_octets[3] <= $ip_octets[3] ) && ( $scope_2_octets[3] >= $ip_octets[3] ) || ( $scope_2_octets[0] === $ip_octets[0] ) && ( $scope_2_octets[1] === $ip_octets[1] ) && ( $scope_2_octets[2] === $ip_octets[2] ) && ( $scope_2_octets[3] <= $ip_octets[3] ) && ( $scope_2_octets[1] >= $ip_octets[3] ) ) {
    $ret = -1;
   }
  }
  return $ret;
 }

 /*
  * Follows RFC 608
  */
 public function ValidateHostname( $hostname )
 {
  if( ( eregi( "^[a-z0-9-]{1,48}$", $hostname ) ) || ( empty( $hostname ) ) ) {
   $data->hostname = 0;
  } else {
   $data->hostname = -1;
  }
  return $data->hostname;
 }

 public function ValidateHostnameNonRFC( $hostname )
 {
  if( ( eregi( "^[.a-z0-9-]{1,48}$", $hostname ) ) || ( empty( $hostname ) ) ) {
   $data->hostname = 0;
  } else {
   $data->hostname = -1;
  }
  return $data->hostname;
 }

 public function ValidateParagraph( $paragraph )
 { 
  if( ( eregi( "[ -!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~<>.,]", $paragraph ) ) || ( empty( $paragraph ) ) ) {
   $data->paragraph = 0;
  } else {
   $data->paragraph = -1;
  }
  return $data->paragraph;
 }

 public function ValidateDate( $date )
 {
  if( ( eregi( "[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}$", $date ) ) || ( empty( $date ) ) ) {
   $data = 0;
  } else {
   $data = -1;
  }
  return $data;
 }
 
 public function ValidateClassOpt( $value, $array )
 {
  preg_match( '/(\w+)\((\d+)\)|(\w+)/', $array[0]['Type'], $matches );
  $ret = 0;
  switch( $matches[1] ) {
   case "varchar":
    if( $this->ValidateParagraph( $value ) === -1 ) { $ret = -1; } break;
   case "text":
    if( $this->ValidateString( $value ) === -1 ) { $ret = -2; } break;
   case "int":
    if( $this->ValidateInteger( $value ) === -1 ) { $ret = -3; } break;
   case "tinyint":
    if( $this->ValidateInteger( $value ) === -1 ) { $ret = -4; } break;
   default:
    $ret = -5;
  }
  return $ret;
 }
 
	public function GenerateRandomPassword( $length, $complexity )
	{
		$normal = "abcdefghijklmnopqrstuvwyxz0123456789";
		$complex = "abcdefghijklmnopqrstuvwyxz0123456789-!#$%&\'*+\\./=?^_{|}~<>";
		if( $complexity === "normal" ) { $chars = $normal; }
		if( $complexity === "complex" ) { $chars = $complex; }
  $i = 0; $pass = '';
		srand( ( double ) microtime() * 100000000 );
		for( $i = 0; $i <= $length; $i++ ) {
			$num = rand() % 99;
			$tmp = substr( $chars, $num, 1 );
			if( !empty( $tmp ) ) {	$pass = $pass . $tmp; }
		}
		return $pass;
	}
	
 public function ValidatePasswordFields( $password_1, $password_2 )
 {
  $data = 0;
  if( ( $password_1 !== $password_2 ) || ( strcmp( $password_1, $password_2 ) ) ) {
   $data = -1;
  } else {
   if( !eregi( "^[-!#$@%&\'*+\\./0-9=?A-Z^_`a-z{|}~<>]{5,25}$", $password_1 ) ) {
    $data = -2;
   }
   if( ( $password_1 === "************" ) || ( $password_1 === "************" ) ) {
    $data = -3;
   }
  }
  return $data;
 }
 
 public function ValidateUploadedFile( $file, $uploaded, $type, $size, $allowederegi, $allowedtype, $allowedsize )
 {
  if( !empty( $file ) ) {
   if( is_file( $uploaded ) ) {
				//if( eregi( "^[0-9a-z]{1,85}\.[" . $allowederegi . "]$", $file ) ) {
     if( $allowedtype === $type ) {
      if( $allowedsize >= $size ) {
       $data = 0;
      } else {
       $data = -5;
      }
     } else {
      $data = -4;
     }
    //} else {
    // $data = -3;
    //}
   } else {
    $data = -2;
   }
  } else {
   $data = -1;
  }
  return $data;
 }

 public function ValidateURI( $uri )
 {
  $domain = "([a-z0-9][-[:alnum:]]*[[:alnum:]] )(\.[[:alpha:]][-[:alnum:]]*[[:alpha:]] )+";
  $dir = "(/[[:alpha:]][-[:alnum:]]*[[:alnum:]] )*";
  $page = "(/[[:alpha:]][-[:alnum:]]*\.[[:alpha:]]{3,5})?";
  $getstring = "(\?([[:alnum:]][-_%[:alnum:]]*=[-_%[:alnum:]]+)(&([[:alnum:]][-_%[:alnum:]]*=[-_%[:alnum:]]+) )*)?";
  $pattern = $domain . $dir . $page . $getstring;
  if( eregi( $pattern, $uri ) ) {
   $data->uri = 0;
  } else {
   $data->uri = -1;
  }
  return $data->uri;
 }

 public function ValidateSQL( $sql, $db )
 {
  $data = new InputFilter();
  $data->sql = $data->safeSQL( $sql, $db );
  return $data->sql;
 }

 public function ValidateXSS( $xss )
 {
  $data = new InputFilter();
  $data->xss = $data->process( $xss );
  return $data->xss;
 }

 function html2txt( $document ) {
  $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                  '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
                  '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
                  '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
                 );
  $text = preg_replace( $search, '', $document );
  return $text;
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

/*
 * class: InputFilter (PHP5-Strict without comments)
 * contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
 * copyright: Daniel Morris
 */
class InputFilter {
 protected $tagsArray;
 protected $attrArray;

 protected $tagsMethod;
 protected $attrMethod;

 protected $xssAuto;
 protected $tagBlacklist = array( 'applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml' );
 protected $attrBlacklist = array( 'action', 'background', 'codebase', 'dynsrc', 'lowsrc' );
  
 public function __construct( $tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1 ) {
  for( $i = 0; $i < count( $tagsArray ); $i++ ) $tagsArray[$i] = strtolower( $tagsArray[$i] );
  for( $i = 0; $i < count( $attrArray ); $i++ ) $attrArray[$i] = strtolower( $attrArray[$i] );
  $this->tagsArray = ( array ) $tagsArray;
  $this->attrArray = ( array ) $attrArray;
  $this->tagsMethod = $tagsMethod;
  $this->attrMethod = $attrMethod;
  $this->xssAuto = $xssAuto;
 }
 
 public function process( $source ) {
  if( is_array( $source ) ) {
   foreach( $source as $key => $value )
    if( is_string( $value) ) $source[$key] = $this->remove( $this->decode( $value ) );
   return $source;
  } elseif( is_string( $source ) ) {
   return $this->remove( $this->decode( $source ) );
  } else return $source;
 }

 protected function remove( $source ) {
  $loopCounter=0;
  while( $source != $this->filterTags( $source ) ) {
   $source = $this->filterTags( $source );
   $loopCounter++;
  }
  return $source;
 } 
 
 protected function filterTags( $source ) {
  $preTag = NULL;
  $postTag = $source;
  $tagOpen_start = strpos( $source, '<' );
  while( $tagOpen_start !== FALSE ) {
   $preTag .= substr( $postTag, 0, $tagOpen_start );
   $postTag = substr( $postTag, $tagOpen_start );
   $fromTagOpen = substr( $postTag, 1 );
   $tagOpen_end = strpos( $fromTagOpen, '>' );
   if( $tagOpen_end === false ) break;
   $tagOpen_nested = strpos( $fromTagOpen, '<' );
   if( ( $tagOpen_nested !== false ) && ( $tagOpen_nested < $tagOpen_end) ) {
    $preTag .= substr( $postTag, 0, ( $tagOpen_nested+1 ) );
    $postTag = substr( $postTag, ( $tagOpen_nested+1 ) );
    $tagOpen_start = strpos( $postTag, '<' );
    continue;
   } 
   $tagOpen_nested = ( strpos( $fromTagOpen, '<' ) + $tagOpen_start + 1 );
   $currentTag = substr( $fromTagOpen, 0, $tagOpen_end );
   $tagLength = strlen( $currentTag );
   if( !$tagOpen_end ) {
    $preTag .= $postTag;
    $tagOpen_start = strpos( $postTag, '<' );
   }
   $tagLeft = $currentTag;
   $attrSet = array( );
   $currentSpace = strpos( $tagLeft, ' ' );
   if(substr( $currentTag, 0, 1) == "/" ) {
    $isCloseTag = TRUE;
    list( $tagName ) = explode( ' ', $currentTag );
    $tagName = substr( $tagName, 1 );
   } else {
    $isCloseTag = FALSE;
    list( $tagName ) = explode( ' ', $currentTag );
   }  
   if( (!preg_match( "/^[a-z][a-z0-9]*$/i", $tagName ) ) || ( !$tagName ) || ( (in_array( strtolower( $tagName ), $this->tagBlacklist ) ) && ( $this->xssAuto ) ) ) {
    $postTag = substr( $postTag, ( $tagLength + 2 ) );
    $tagOpen_start = strpos( $postTag, '<' );
    continue;
   }
   while( $currentSpace !== FALSE ) {
    $fromSpace = substr( $tagLeft, ( $currentSpace + 1 ) );
    $nextSpace = strpos( $fromSpace, ' ' );
    $openQuotes = strpos( $fromSpace, '"' );
    $closeQuotes = strpos( substr( $fromSpace, ( $openQuotes + 1 ) ), '"' ) + $openQuotes + 1;
    if( strpos( $fromSpace, '=' ) !== FALSE ) {
     if( ( $openQuotes !== FALSE ) && ( strpos( substr( $fromSpace, ( $openQuotes + 1 ) ), '"' ) !== FALSE ) )
      $attr = substr( $fromSpace, 0, ( $closeQuotes+1 ) );
     else $attr = substr( $fromSpace, 0, $nextSpace );
    } else $attr = substr( $fromSpace, 0, $nextSpace );
    if( !$attr ) $attr = $fromSpace;
    $attrSet[] = $attr;
    $tagLeft = substr( $fromSpace, strlen( $attr ) );
    $currentSpace = strpos( $tagLeft, ' ' );
   }
   $tagFound = in_array( strtolower( $tagName ), $this->tagsArray );
   if( (!$tagFound && $this->tagsMethod ) || ( $tagFound && !$this->tagsMethod ) ) {
    if( !$isCloseTag ) {
     $attrSet = $this->filterAttr( $attrSet );
     $preTag .= '<' . $tagName;
     for( $i = 0; $i < count( $attrSet ); $i++ )
      $preTag .= ' ' . $attrSet[$i];
     if( strpos( $fromTagOpen, "</" . $tagName ) ) $preTag .= '>';
     else $preTag .= ' />';
       } else $preTag .= '</' . $tagName . '>';
   }
   $postTag = substr( $postTag, ( $tagLength + 2 ) );
   $tagOpen_start = strpos( $postTag, '<' );
  }
  $preTag .= $postTag;
  return $preTag;
 }

 protected function filterAttr( $attrSet ) {
  $newSet = array( );
  for( $i = 0; $i < count( $attrSet ); $i++ ) {
   if( !$attrSet[$i] ) continue;
   $attrSubSet = explode( '=', trim( $attrSet[$i] ) );
   list( $attrSubSet[0] ) = explode( ' ', $attrSubSet[0] );
   if( ( !eregi("^[a-z]*$",$attrSubSet[0] ) ) || ( ( $this->xssAuto ) && ( (in_array( strtolower( $attrSubSet[0] ), $this->attrBlacklist ) ) || ( substr( $attrSubSet[0], 0, 2 ) == 'on' ) ) ) )
    continue;
   if( $attrSubSet[1] ) {
    $attrSubSet[1] = str_replace( '&#', '', $attrSubSet[1] );
    $attrSubSet[1] = preg_replace( '/\s+/', '', $attrSubSet[1] );
    $attrSubSet[1] = str_replace( '"', '', $attrSubSet[1] );
    if( (substr( $attrSubSet[1], 0, 1 ) == "'" ) && ( substr( $attrSubSet[1], ( strlen( $attrSubSet[1] ) - 1 ), 1 ) == "'" ) )
     $attrSubSet[1] = substr( $attrSubSet[1], 1, ( strlen( $attrSubSet[1] ) - 2 ) );
    $attrSubSet[1] = stripslashes( $attrSubSet[1] );
   }
   if( ( ( strpos( strtolower( $attrSubSet[1] ), 'expression' ) !== false ) && ( strtolower( $attrSubSet[0] ) == 'style' ) ) ||
     ( strpos( strtolower( $attrSubSet[1] ), 'javascript:' ) !== false ) ||
     ( strpos( strtolower( $attrSubSet[1] ), 'behaviour:' ) !== false ) ||
     ( strpos( strtolower( $attrSubSet[1] ), 'vbscript:' ) !== false ) ||
     ( strpos( strtolower( $attrSubSet[1] ), 'mocha:' ) !== false ) ||
     ( strpos( strtolower( $attrSubSet[1] ), 'livescript:' ) !== false )
  ) continue;

   $attrFound = in_array( strtolower( $attrSubSet[0] ), $this->attrArray );
   if( (!$attrFound && $this->attrMethod ) || ( $attrFound && !$this->attrMethod) ) {
    if( $attrSubSet[1] ) $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
    elseif( $attrSubSet[1] == "0") $newSet[] = $attrSubSet[0] . '="0"';
    else $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[0] . '"';
   } 
  }
  return $newSet;
 }
 
 protected function decode( $source ) {
  $source = html_entity_decode( $source, ENT_QUOTES, "ISO-8859-1" );
  $source = preg_replace( '/&#(\d+ );/me',"chr(\\1)", $source );    // decimal notation
  $source = preg_replace( '/&#x([a-f0-9]+ );/mei',"chr(0x\\1)", $source ); // hex notation
  return $source;
 }

 public function safeSQL( $source, &$connection ) {
  if( is_array( $source ) ) {
   foreach( $source as $key => $value )
    if( is_string( $value) ) $source[$key] = $this->quoteSmart( $this->decode( $value ), $connection );
   return $source;
  } elseif( is_string( $source ) ) {
   if( is_string( $source ) ) return $this->quoteSmart( $this->decode( $source ), $connection );
  } else return $source;
 }

 protected function quoteSmart( $source, &$connection ) {
  if(get_magic_quotes_gpc() ) $source = stripslashes( $source );
  $source = $this->escapeString( $source, $connection );
  return $source;
 }
 
 protected function escapeString( $string, &$connection ) {
  if( version_compare( phpversion(),"4.3.0", "<" ) ) @mysql_escape_string( $string );
  else @mysql_real_escape_string( $string );
  return $string;
 }

}

?>