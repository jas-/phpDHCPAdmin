<?PHP

/*
 * phpMyOrdering
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * class.encryption.php - Handle encryption functionality
 */

class Encryption
{

 function ReadFolder( $folder )
 {
  if( empty( $folder ) ) {
   $value = -1;
  } else {
   $rand_image = array();
   if( $handle = opendir( $folder ) ) {
    while( false !== ( $file = readdir( $handle ) ) ) {
     if( ( $file != "." ) && ( $file != ".." ) && ( $file != "index.html" ) && ( !is_dir( $file ) ) ) {
      $value[] = $file;
     }
    }
    closedir( $handle );
   }
  }
  return $value;
 }

 function MakeSuperRandom()
 {
  return srand( ( double ) microtime( time() ) * 100000 );
 }

 function PickRandomImages( $array )
 {
  $auth = new Encryption();
  $num1 = count( $array );
  $num1 = $num1 - 1;
  $auth->MakeSuperRandom();

  $img_num = rand( 3, $num1 );
  $image[] = $array[$img_num];

  $num2 = count( $array );
  $num2 = $num2 - 1;
  $auth->MakeSuperRandom();

  $img_num = rand( 3, $num2 );
  $image[] = $array[$img_num];

  $num3 = count( $array );
  $num3 = $num3 - 1;
  $auth->MakeSuperRandom();

  $img_num = rand( 3, $num3 );
  $image[] = $array[$img_num];
  return $image;
 }

 function GeneratePrivKey( $array )
 {
  if( empty( $array ) ) {
   $data = -1;
  } else {
   for( $x = 0; $x < count( $array ); $x++ ) {
    if( function_exists( mhash ) ) {
     $keys[] = mhash( MHASH_SHA1, sha1( $array[$x] ) );
    } elseif( function_exists( sha1 ) ) {
     $keys[] = sha1( sha1( $array[$x] ) );
    } else {
     $keys[] = md5( md5( $array[$x] ) );
    }
   }
   for( $y = 0; $y < count( $keys ); $y++ ) {
    if( count( $keys ) == $keys[$y] ) {
     $data .= $keys[$y];
    } else {
     $data .= $keys[$y] . ":";
    }
   }
  }
  return $data;
 }

 function GeneratePublicKey( $data )
 {
  return md5( $data );
 }

 function GeneratePrivateKey( $a )
 {
  $auth = new Encryption();
  $x = $auth->ReadFolder( $a );
  $y = $auth->PickRandomImages( $x );
  $z = $auth->GeneratePrivKey( $y );
  return $z;
 }

 function EncodePrivToHex( $key )
 {
  return bin2hex( $key );
 }

 function DecodePrivToBin( $key )
 {
  $hexLenght = strlen( $key );
  if( $hexLenght % 2 != 0 || preg_match( "/[^\da-fA-F]/", $key ) ) { $binString = -1; }
  unset( $binString );
  for( $x = 1; $x <= $hexLenght/2; $x++ ) {
   $binString .= chr( hexdec( substr( $key, 2 * $x - 2, 2 ) ) );
  }
  return $binString;
 }

 function EncodeAuthToken( $user, $pass, $level, $group, $time, $token )
 {
  if( ( empty( $user ) ) || ( empty( $pass ) ) || ( empty( $level ) ) || ( empty( $group ) ) || ( empty( $time ) ) || ( empty( $token ) ) ) {
   $Authenticate->value = -1;
  } else {
   $user = base64_encode( $user );
   $pass = base64_encode( $pass );
   $level = base64_encode( $level );
   $group = base64_encode( $group );
   $Authenticate->value = $user . ":" . $pass . ":" . $level . ":"  . $group . ":" . $time . ":" . $token;
  }
  return $Authenticate->value;
 }

 function DecodeAuthToken( $token )
 {
  if( empty( $token ) ) {
   $Authenticate->value = -1;
  } else {
   $Authenticate->value = preg_split( "/:/", $token );
  }
  return $Authenticate->value;
 }
}

/**
* Cipher
*
* Simple mcrypt interface.
*
* Cipher is a simple class for working with mcrypt.
*
* @package     Cipher
* @author      Nathan Lucas <nathan@gimpstraw.com>
* @link        http://www.gimpstraw.com/
* @copyright   Copyright (c) 2008, Nathan Lucas
* @version     2.0.0
*
* Added $iv to both encrypt() and decrypt() allowing you to use preset IVs
* while encrypting/decrypting data.
*
* Also added getIV(), which returns the instance's current IV in base64
* allowing you to store this IV for use on other instances of Cipher.
*/
class Cipher {

    /**
     * Algorithm to use.
     *
     * @access  private
     * @var     string
     */
    private $algo;

    /**
     * Encryption mode.
     *
     * @access  private
     * @var     string
     */
    private $mode;
    
    /**
     * Randomization source.
     *
     * @access  private
     * @var     integer
     */
    private $source;

    /**
     * Initialization vector.
     *
     * @access  private
     * @var     string
     */
    private $iv = null;

    /**
     * Encryption key.
     *
     * @access  private
     * @var     string
     */
    private $key = null;


    /**
     * Cipher($algo, $mode, $source)
     *
     * Cipher constructor. Sets the algorithm being used, the encryption
     * mode, and the IV.
     *
     * @param   string $algo
     * @param   string $mode
     * @param   integer $source (randomization source)
     * @access  public
     * @return  void
     */
    public function __construct($algo = MCRYPT_3DES, $mode = MCRYPT_MODE_CBC, $source = MCRYPT_RAND) {
        $this->algo = $algo;
        $this->mode = $mode;
        $this->source = $source;
        
        if (is_null($this->algo) || (strlen($this->algo) == 0)) {
            $this->algo = MCRYPT_3DES;
        }
        if (is_null($this->mode) || (strlen($this->mode) == 0)) {
            $this->mode = MCRYPT_MODE_CBC;
        }
    }

    /**
     * encrypt($data, $key, $iv)
     *
     * Returns encrpyted $data, base64 encoded. $key must be specified at
     * least once, it can be changed at any point.
     *
     * @param   string $data
     * @param   mixed $key
     * @param   string $iv
     * @access  public
     * @return  string
     */
    public function encrypt($data, $key = null, $iv = null) {
        $key = (strlen($key) == 0) ? $key = null : $key;
        
        $this->setKey($key);
        $this->setIV($iv);
        
        $out = mcrypt_encrypt($this->algo, $this->key, $data, $this->mode, $this->iv);
        return base64_encode($out);
    }

    /**
     * decrypt($data, $key, $iv)
     *
     * Returns decrypted $data. $key must be specified at least once, it can
     * be changed at any point.
     *
     * @param   mixed $data
     * @param   mixed $key
     * @param   string $iv
     * @access  public
     * @return  string
     */
    public function decrypt($data, $key = null, $iv = null) {
        $key = (strlen($key) == 0) ? $key = null : $key;
        
        $this->setKey($key);
        $this->setIV($iv);
        
        $data = base64_decode($data);
        $out = mcrypt_decrypt($this->algo, $this->key, $data, $this->mode, $this->iv);
        return trim($out);
    }
    
    /**
     * getIV()
     *
     * Returns the IV used for encryption so you can use it again in another
     * Cipher instance to decrypt data.
     *
     * @access  public
     * @return  string
     */
    public function getIV() {
        return base64_encode($this->iv);
    }
    
    /**
     * setIV($iv)
     *
     * Sets IV. If $iv is specified, the instance IV will be set to this. If not,
     * the instance will generate an IV.
     *
     * @param   string $iv
     * @access  private
     * @return  void
     */
    private function setIV($iv) {
        if (!is_null($iv)) {
            $this->iv = base64_decode($iv);
        }
        if (is_null($this->iv)) {
            $iv_size = mcrypt_get_iv_size($this->algo, $this->mode);
            $this->iv = mcrypt_create_iv($iv_size, $this->source);
        }
    }

    /**
     * setKey($data, $key)
     *
     * Sets Cipher::key. This will be the key used for the encrypt and decrypt
     * methods until another $key is specified. This will trigger an error if
     * no initial key is set.
     *
     * @param   mixed $key
     * @access  private
     * @return  void
     */
    private function setKey($key) {
        if (!is_null($key)) {
            $key_size = mcrypt_get_key_size($this->algo, $this->mode);
            $this->key = hash("sha256", $key, true);
            $this->key = substr($this->key, 0, $key_size);
        }
        if (is_null($this->key)) {
            trigger_error("You must specify a key at least once in either Cipher::encrpyt() or Cipher::decrypt().", E_USER_ERROR);
        }
    }
} 

?>