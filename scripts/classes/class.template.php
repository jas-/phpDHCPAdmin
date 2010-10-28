<?php
class Template {

 public $strTemplateDir = '';
 public $strCacheDir    = '/tmp';
 public $strBeginTag    = '{';
 public $strEndTag      = '}';
 public $arrVars        = array();
 public $arrValues      = array();
 public $boolCache      = true;
 public $strBuffer      = null;

 private function __contruct( ) {}

 public function __destruct( ) {
  $this->clear( );
 }

 public function assign( $strVar, $strValue, $strFile, $strFlag ) {
  $this->arrVars[]   = $this->strBeginTag . '$' . $strVar  . $this->strEndTag;
  $this->arrValues[] = $strValue;
  if( !empty( $strFile ) ) { return $this->display( $strFile, $strFlag, "VAR" ); }
 }

 public function clear( ) {
  unset( $this->arrVars, $this->arrValues );
 }

 public function display( $strFile, $strFlag, $strCmd ) {
  if( $this->boolCache === true ) {
   if( ( file_exists( $this->strCacheDir . '/' . md5( $strFile . $_SERVER['REMOTE_ADDR'] ) . '.tpl' ) && filemtime( $this->strCacheDir . '/' . md5( $strFile . $_SERVER['REMOTE_ADDR'] ) . '.tpl' ) >= time() - 300 ) && ( $strFlag === FALSE ) ) {
    $resFile = fopen( $this->strCacheDir . '/' . md5( $strFile . $_SERVER['REMOTE_ADDR'] ) . '.tpl', 'r' );
    $this->strBuff = fread( $resFile, filesize( $this->strCacheDir . '/' . md5( $strFile . $_SERVER['REMOTE_ADDR'] ) . '.tpl' ) );
    if( $strCmd === "VAR" ) {
     return $this->strBuff;
    } else {
     echo $this->strBuff;
    }
    fclose( $resFile );
   } else {
    $resFile = fopen( $this->strTemplateDir . '/' . $strFile, 'r' );
    $strBuff = fread( $resFile, filesize( $this->strTemplateDir . '/' . $strFile ) );
    $this->strBuff = str_replace( $this->arrVars, $this->arrValues, $strBuff );
    fclose( $resFile );
    if( $strCmd === "VAR" ) {
     return $this->strBuff;
    } else {
     echo $this->strBuff;
    }
    $resFileCache = fopen( $this->strCacheDir . '/' . md5( $strFile . $_SERVER['REMOTE_ADDR'] ) . '.tpl', 'w' );
    fwrite( $resFileCache, $this->strBuff );
   }
  } else {
   $resFile = fopen( $this->strTemplateDir . '/' . $strFile, 'r' );
   $strBuff = fread( $resFile, filesize( $this->strTemplateDir . '/' . $strFile ) );
   $this->strBuff = str_replace( $this->arrVars, $this->arrValues, $strBuff );
   fclose( $resFile );
   if( $strCmd === "VAR" ) {
    return $this->strBuff;
   } else {
    echo $this->strBuff;
   }
  }
 }
}
?>