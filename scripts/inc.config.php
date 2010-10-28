<?PHP

/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * inc.config.php - Pre-defined global variables
 */

// database configuration
$defined['hostname']    = "";
$defined['dbhost']      = "localhost";
$defined['username']    = "";
$defined['password']    = "";
$defined['dbname']      = "phpDHCPAdmin";

// support & log notification email addresses
$defined['mail']        = "";

// application path information
$defined['virpath']     = "/var/www/html/phpDHCPAdmin-0.9.5-beta/";

// this folder needs write permissions
// also used for temporary file writes and dhcpd.conf
$defined['confpath']    = $defined['virpath'] . "conf/";

// path to the dhcpd.leases file this allows
// lease management if permissions allow (write access)
$defined['leases']      = $defined['confpath'] . "dhcpd.leases";

// title and copyright information
$defined['title']       = "phpDHCPAdmin-0.9.5-beta";
$defined['description'] = "Manage the ISC DHCPD service";

// if this is removed the GPL license is out of compliance
// please refer to the LICENSE file regarding GPL licensing
$defined['disclaimer']  = "All rights reserved 2008 &reg; Jason Gerfen";

// default error and success images used for messages
$defined['error']       = "templates/images/error.gif";
$defined['good']        = "templates/images/good.jpg";
$defined['error_small'] = "templates/images/error-small.gif";

// path for application templates
$defined['templates']   = "templates";

// enable debugging support?
$defined['debug']       = "FALSE";

// where is the dhcpd service service restart inet/inetd script?
$defined['dhcpd_cmd']   = "/usr/sbin/dhcpd -cf " . $defined['confpath'] . "dhcpd.conf -lf " . $defined['leases'];
$defined['dhcpd_tst']   = "/usr/sbin/dhcpd -t -cf " . $defined['confpath'] . "dhcpd.test -lf " . $defined['leases'] . " >> log";

// a few network and log parsing commands
// (these are used to gather statistical info for graphing and process status)
$defined['netstat']     = "/bin/netstat";
$defined['ifconfig']    = "/sbin/ifconfig";
$defined['tail']        = "/bin/tail";
$defined['ps']          = "/bin/ps";

// authentication timeout 1800 seconds = 30 minutes
$defined['timeout']     = "1800";

// data used with session authentication token (do NOT modify)
$defined['enckeygen']   = $defined['virpath'] . "templates/images/shared/";

// include our class files
require 'inc.libraries.php';

// include our error codes
require 'inc.errors.php';

?>
