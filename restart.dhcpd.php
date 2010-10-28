<?PHP
/*
 * phpDHCPAdmin
 * Jason Gerfen [jason.gerfen@gmail.com]
 *
 * restart.dhcpd.php - DHCPD Restart service
 */

// load our config data
if( file_exists( "scripts/inc.config.php" ) ) {
 require 'scripts/inc.config.php';

 // ensure we are being called from our configured host
 if( $defined['hostname'] === $_SERVER['SERVER_NAME'] ) {

  // Initialize classes
 	$db = new dbConn;
  $err = new GenerateErrors;
  $tpl = new Template;
  $skin = new PageSkinner;
  $val = new ValidateStrings;
  $menu = new GenerateNavMenu;
  $auth = new Authenticate;
  $level = new AccessLevels;
 	$misc = new MiscFunctions;
 	$debug = new DebugData;

  // initialize a db connection handle
  $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

  // ensure our sessions are present
  if( empty( $_SESSION['token'] ) ) {
   $sessions = new dbSession;
  }

  //define the template and cache directories
  $tpl->strTemplateDir = $defined['virpath'] . 'templates';
  $tpl->strCacheDir    = '/tmp';
  if( ( !empty( $_GET ) ) || ( !empty( $_POST ) ) ) { $flag = "TRUE"; } else { $flag = "FALSE"; }

  // setup our template style data
  if( ( $val->ValidateString( $_GET['skin'] ) === -1 ) || ( empty( $_GET['skin'] ) ) ) {
   $style = $defined['templates'] . "/black";
   if( !empty( $_GET['skin'] ) ) {
    $skin_err = $err->GenerateErrorLink( "help/help.php", "#val_xss", $defined['error_small'], $errors['val_xss'], NULL, NULL );
   }
  } else {
   $style = $skin->SelectSkin( $defined['templates'], $_GET['skin'], $_COOKIE['skin'] );
  }

  // call our header file and pass it some variables
  $tpl->assign( 'TITLE', $defined['title'], NULL, NULL );
 	$tpl->assign( 'DESCRIPTION', "Output new dhcpd.conf and restart ISC DHCPD service", NULL, NULL );
  $tpl->assign( 'STYLE', $style, NULL, NULL );
 
  // authentication template
  $FILE = "auth.tpl";
 
  // default is no error just diplay login form
  if( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -1 ) {
   // well looks like at least one login attempt has been processed, show empty field error
   if( $_SESSION['x']++ >= 1 ) {
    $ERROR = $err->GenerateErrorLink( "help/help.html", "#missing", $defined['error'], $errors['auth_e'], NULL, NULL );
   }
  // timeout with authentication token
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -2 ) {
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#timeout", $defined['error'], $errors['auth_to'], NULL, NULL );
   $misc->ExitApplication( $_SESSION['token'] );
  // error in validation of authentication data
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -3 ) {
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#alphanum", $defined['error'], $errors['val_alp'], NULL, NULL );
  // authentication data not found in database
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -4 ) {
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#user", $defined['error'], $errors['auth_n'], NULL, NULL );
  // error in database query
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === -5 ) {
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['undef_sql'], NULL, NULL );
  // valid user found
  } elseif( $auth->AuthUser( $_POST['user'], $_POST['pass'], $_SESSION['token'] ) === 0 ) {
   // perform permissions check with access level and group data
   if( ( $level->ChkLevel( $_SESSION['token'] ) === "admin" ) || ( $level->ChkLevel( $_SESSION['token'] ) === "user" ) ) {
   
 			// define some variables for the template etc.
 			$JS = NULL;
 			$FILE = "restart.dhcpd.tpl";
    $err_chk = 0;
    $list = "<ol>";

    // initialize a db connection handle
    $dbconn = $db->dbConnect( $defined['dbhost'], $defined['username'], $defined['password'], $defined['dbname'] );

				// provide count of online users
				$online = "SELECT * FROM `admin_sessions`";
				$ret = $db->dbQuery( $val->ValidateSQL( $online, $dbconn ), $dbconn );
				$usersoline = $db->dbNumRows( $ret );

    // fix unlinked pxe group host records
    $db->dbQuery( $val->ValidateSQl( "UPDATE `conf_hosts` SET `pxe-group` = '' WHERE `pxe-group` = \"---------------\"", $dbconn), $dbconn );

    // begin with global configuration options including dns and dnssec
    $sql['global'] = "SELECT * FROM `conf_global_opts`";
				$sql['gpxe'] = "SELECT * FROM `conf_pxe_opts`";
    $sql['dns'] = "SELECT * FROM `conf_dns_opts`";
				$sql['failover'] = "SELECT * FROM `conf_failover`";
    $sql['dnssec'] = "SELECT * FROM `conf_dnssec_opts`";
				$sql['classes'] = "SELECT * FROM `conf_classes`";
				$sql['pools'] = "SELECT * FROM `conf_pools`";
    $sql['shared'] = "SELECT * FROM `conf_shared_networks`";
    $sql['subnets'] = "SELECT * FROM `conf_subnets`";
    $sql['pxe'] = "SELECT * FROM `conf_pxe_groups`";

    // process our sql array and place values in assoc array
    foreach( $sql as $key => $query ) {
     if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
      $err_chk = 1;
      $list .= "<li>Could not look up data for  the '" . $key . "' configuration options</li>";
     } else {
      $results[$key] = $db->dbArrayResults( $value );
     }
    }
    
    // determine which hosts belong to pxe groups
    $classes_count = count( $results['classes'] );
    foreach( $results['classes'] as $key => $class ) {
     $sql['class'][$class['class-name']] = "SELECT * FROM `conf_classes_options` WHERE `class-name` = '" . $class['class-name'] . "'";
    }

    // build our static hosts into pxe groups first
    if( count( $sql['class'] ) !== 0 ) {
     foreach( $sql['class'] as $key => $query ) {
      if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $err_chk = 1;
       $list .= "<li>Could not look up data for the '" . $key . "' configuration options</li>";
      } else {
       $results['classes']['classes-options'][$key] = $db->dbArrayResults( $value );
      }
     }
    }
				
				// determine which hosts belong to pxe groups
    $pxe_group_count = count( $results['pxe'] );
    foreach( $results['pxe'] as $key => $pxe_group ) {
     $sql['pxe_groups'][$pxe_group['pxe-group-name']] = "SELECT * FROM `conf_hosts` WHERE `pxe-group` = '" . $pxe_group['pxe-group-name'] . "'";
    }

    // build our static hosts into pxe groups first
    if( ( count( $sql['pxe_groups'] ) !== 0 ) && ( $results['gpxe'][0]['pxe-enabled'] === "true" ) ) {
     foreach( $sql['pxe_groups'] as $key => $query ) {
      if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
       $err_chk = 1;
       $list .= "<li>Could not look up data for the '" . $key . "' configuration options</li>";
      } else {
       $results['pxe']['pxe-hosts'][$key] = $db->dbArrayResults( $value );
      }
     }
    }
    
    // now build our separate static host list
    $sql['hosts'] = "SELECT * FROM `conf_hosts` WHERE 'pxe-group' IS NULL OR `pxe-group` = ''";
    if( ( $value = $db->dbQuery( $val->ValidateSQL( $sql['hosts'], $dbconn ), $dbconn ) ) === -1 ) {
     $err_chk = 1;
     $list .= "<li>Could not look up data for the 'static host' list</li>";
    } else {
     $results['static_hosts'] = $db->dbArrayResults( $value );
    }
    
    // check for errors before processing everything
    if( $err_chk === 1 ) {
     $list .= "</ol>";
     $error = $err->GenerateErrorLink( "help/help.html", "#build_config", $defined['error'], "There was an error processing all directives necessary for our dhcpd.conf configuration file" . $list, NULL, NULL );
    } else {
     
     // since no errors first we need to process our global config data
     if( count( $results['global'][0] ) === 0 ) {
					 $configdata .= $err->GenerateErrorLink( "help/help.html", "#build_config", $defined['error'], "Global configuration data is missing.<br>", NULL, NULL );
					} else {
      $configdata = "#### Global Configuration Options ####\n";
      foreach( $results['global'][0] as $key => $value ) {
       if( ( $key !== "id" ) && ( !empty( $value ) ) ) {
        if( $key === "option domain-name" ) {
         $configdata .= "$key \"$value\";\n";
        } elseif( eregi( "authoritative", $key ) ) {
									if( $value === "true" ) {
									 $configdata .= "$key;\n";
									} else {
									 $configdata .= "not $key;\n";
									}
								} elseif( eregi( "bootp", $key ) ) {
								 if( $value === "true" ) {
									 $configdata .= "allow $key;\n";
									}
								} else {
         $configdata .= "$key $value;\n";
        }
       }
      }
     }
					
					// debugging statement
					//echo "<pre>"; print_r( $results ); echo "</pre>";
					
					// perform check on global PXE options
					if( ( count( $results['gpxe'][0] ) !== 0 ) && ( $results['gpxe'][0]['pxe-enabled'] === "true" ) ) {
      $configdata .= "\n#### Global PXE Configuration Options ####\n";
      $configdata .= "option space PXE;\n";
						$configdata .= "option PXE.mtftp-ip code 1 = " . $results['gpxe'][0]['option-space'] . ";\n";
						$configdata .= "option PXE.mtftp-cport code 2 = " . $results['gpxe'][0]['mtftp-cport'] . ";\n";
						$configdata .= "option PXE.stftp-cport code 3 = " . $results['gpxe'][0]['mtftp-cport'] . ";\n";
						$configdata .= "option PXE.mtftp-tmout code 4 = " . $results['gpxe'][0]['mtftp-tmout'] . ";\n";
						$configdata .= "option PXE.mtftp-delay code 5 = " . $results['gpxe'][0]['mtftp-delay'] . ";\n";
						$configdata .= "option PXE.discovery-control code 6 = " . $results['gpxe'][0]['discovery-control'] . ";\n";
						$configdata .= "option PXE.discovery-mcast-addr code 7 = " . $results['gpxe'][0]['discovery-mcast-addr'] . ";\n";
     }
					
     // now setup any dnssec keys
					
					if( count( $results['dnssec'] ) !== 0 ) {
      $configdata .= "\n#### DNSSEC Key Definitions ####\n";
      foreach( $results['dnssec'] as $key => $value ) {
       $configdata .= "key " . $value['key-name'] . " {\n";
       $configdata .= "\talgorithm " . $value['algorithm'] . ";\n";
       $configdata .= "\tsecret " . $value['key'] . ";\n";
       $configdata .= "}\n";
      }
     }
					//echo "<pre>"; print_r( $results['dns'] ); echo "</pre>";
     // configure the dns zones if any
					if( count( $results['dns'] ) !== 0 ) {
      $configdata .= "\n#### DNS Zone Definitions ####\n";
      foreach( $results['dns'] as $key => $value ) {
      
						 // this is a fix for domains that do not have reverse lookup zones setup
       $revaddr = @dns_get_record( $value['zone'], 'DNS_PTR' );
       if( ( empty( $revaddr ) ) || ( count( $revaddr ) === 0 ) ) {
        $revaddr = $misc->GenRevAddr( @gethostbyname( $value['zone'] ) );
       }
       $reversed = $revaddr[0]['host'];
						
       // process the forward dns zone w/ or w/o the dnssec keys
							if( $value['dnssec-enabled'] === "true" ) {
        $configdata .= "zone " . $value['zone'] . " {\n";
        $configdata .= "\tprimary " . $value['type'] . ";\n";
        $configdata .= "\tkey " . $value['dnssec-key'] . ";\n";
        $configdata .= "}\n";
       } else {
        $configdata .= "zone " . $value['zone'] . " {\n";
        $configdata .= "\tprimary " . $value['type'] . ";\n";
        $configdata .= "}\n";
							}
							
							// and for the reversed dns zone w/ or w/o the dnssec keys
							if( $value['dnssec-enabled'] === "true" ) {
        $configdata .= "zone " . $reversed . " {\n";
        $configdata .= "\tprimary " . $value['type'] . ";\n";
        $configdata .= "\tkey " . $value['dnssec-key'] . ";\n";
        $configdata .= "}\n";
							} else {
							 $configdata .= "zone " . $reversed . " {\n";
        $configdata .= "\tprimary " . $value['type'] . ";\n";
        $configdata .= "}\n";
							}
      }
     }

					// create our failover/replication data
					if( count( $results['failover'] ) !== 0 ) {
					 $configdata .= "\n#### Failover configuration ####\n";
						$configdata .= "failover peer \"" . $results['failover'][0]['peer name'] . "\" {\n";
						foreach( $results['failover'][0] as $key => $value ) {
						 if( ( !empty( $value ) ) && ( $key !== "id" ) && ( $key !== "peer name" ) ) {
 							if( $key === "type" ) {
								 $configdata .= "\t" . $value . ";\n";
								} else {
								 $configdata .= "\t" . $key . " " . $value . ";\n";
								}
							}
						}
						$configdata .= "}\n";
					}

     // create our classes first
					if( count( $results['classes'] ) != 0 ) {
						$configdata .= "\n#### Class Definitions ####\n";
						foreach( $results['classes'] as $key => $value ) {
							if( !empty( $value['class-name'] ) ) {
 							$configdata .= "\n### '" . $value['class-name'] . "' Class Definition ###\n";
								$configdata .= "class \"" . $value['class-name'] . "\" {\n";
        foreach( $results['classes']['classes-options'][$value['class-name']] as $opt_key => $opt_value ) {
									$class_array[] = $value['class-name'];
         if( $opt_value['class-option'] === "root-path" ) { $opt_value['match-substring-regex'] = "\"" . $opt_value['match-substring-regex'] . "\""; }
         if( $opt_value['class-match'] === "TRUE" ) {
										if( $opt_value['class-substring'] === "TRUE" ) {
											if( $opt_value['class-match-option'] === "pick-first-value" ) {
            $configdata .= "\tmatch " . $opt_value['class-match-option'] . " option " . $opt_value['class-option'] . ", " . $opt_value['class-substring-start'] . ", " . $opt_value['class-substring-end'] . " = \"" . $opt_value['match-substring-regex'] . "\";\n";
           } else {
            $configdata .= "\tmatch " . $opt_value['class-match-option'] . " substring ( option " . $opt_value['class-option'] . ", " . $opt_value['class-substring-start'] . ", " . $opt_value['class-substring-end'] . " ) = \"" . $opt_value['match-substring-regex'] . "\";\n";
           }
										}// else {
											//$configdata .= "\t" . $opt_value['class-match-option'] . " option " . $opt_value['class-option'] . ", " . $opt_value['match-substring-regex'] . ";\n";
										//}
									} else {
										if( $opt_value['class-option'] === "dhcp-lease-time" ) {
           //$configdata .= "\tlease time " . $opt_value['match-substring-regex'] . ";\n";
          } else {
           $configdata .= "\toption " . $opt_value['class-option'] . " " . $opt_value['match-substring-regex'] . ";\n";
          }
									}
								}
								$configdata .= "}\n";
							}
						}
					}

     // determine which subnets belong to our shared-networks list
     if( count( $results['shared'] ) !== 0 ) {
      foreach( $results['shared'] as $key => $shared_network ) {
       $sql['shared_networks_subnets'][$shared_network['shared-network-name']] = "SELECT * FROM `conf_subnets` WHERE `shared-network` = '" . $shared_network['shared-network-name'] . "'";
      }
     }

     // build our shared-networks items
     if( count( $sql['shared_networks_subnets'] ) !== 0 ) {
      foreach( $sql['shared_networks_subnets'] as $key => $query ) {
       if( ( $value = $db->dbQuery( $val->ValidateSQL( $query, $dbconn ), $dbconn ) ) === -1 ) {
        $err_chk = 1;
        $list .= "<li>Could not look up data for the '" . $key . "' configuration options</li>";
       } else {
        $results['shared_networks_subnets'][$key] = $db->dbArrayResults( $value );
       }
      }
     }

     //echo "<pre>"; print_r( $results['shared_networks_subnets'] ); echo "</pre>";
     // print out our shared-network data
     if( count( $results['shared_networks_subnets'] ) !== 0 ) {
      $configdata .= "\n#### Shared Network Definitions ####\n";
      foreach( $results['shared_networks_subnets'] as $key => $value ) {
       $configdata .= "\n### Shared Network '" . $key . "' ###";
       $configdata .= "\nshared-network " . $key . " {\n";
       foreach( $results['shared_networks_subnets'][$key] as $key => $value ) {
        $configdata .= "\n\t## '" . $value['subnet-name'] . "' Subnet Definition ##\n";
 							$configdata .= "\tsubnet " . $value['subnet'] . " ";
        $configdata .= "netmask " . $value['subnet-mask'] . " {\n";
        $configdata .= "\t\toption domain-name-servers " . $value['dns-server-1'] . ", " . $value['dns-server-2'] . ";\n";
        $configdata .= "\t\toption routers " . $value['router'] . ";\n";
        if( $value['enable-scope'] === "true" ) {
         $configdata .= "\t\trange " . $value['scope-range-1'] . " " . $value['scope-range-2'] . ";\n";
        }
								if( !empty( $value['bootp-filename']) ) {
									$configdata .= "\t\tfilename \"" . $value['bootp-filename'] . "\";\n";
								}
								if( !empty( $value['bootp-server']) ) {
									$configdata .= "\t\tnext-server " . $value['next-server'] . ";\n";
								}
 							if( !empty( $value['ip-forwarding'] ) ) {
 							 if( $value['ip-forwarding'] === "true" ) { $value['ip-forwarding'] = "on"; } else { $value['ip-forwarding'] = "off"; }
 							 $configdata .= "\t\toption ip-forwarding " . $value['ip-forwarding'] . ";\n";
 							}
 							if( !empty( $value['broadcast-address'] ) ) {
 							 $configdata .= "\t\toption broadcast-address " . $value['broadcast-address'] . ";\n";
 							}
 							if( !empty( $value['ntp-servers'] ) ) {
 							 $configdata .= "\t\toption ntp-servers " . $value['ntp-servers'] . ";\n";
 							}
 							if( !empty( $value['netbios-name-servers'] ) ) {
 							 $configdata .= "\t\toption netbios-name-servers " . $value['netbios-name-servers'] . ";\n";
 							}
 							if( !empty( $value['default-lease-time'] ) ) {
 							 $configdata .= "\t\tdefault-lease-time " . $value['default-lease-time'] . ";\n";
 							}
 							if( !empty( $value['min-lease-time'] ) ) {
 							 $configdata .= "\t\tmin-lease-time " . $value['min-lease-time'] . ";\n";
 							}
 							if( !empty( $value['max-lease-time'] ) ) {
 							 $configdata .= "\t\tmax-lease-time " . $value['max-lease-time'] . ";\n";
 							}
 							if( !empty( $value['pool'] ) ) {
 								$configdata .= "\t\t## Pool '" . $value['pool'] . "' ##\n";
 								$configdata .= "\t\tpool {\n";
 								foreach( $results['pools'] as $pool_key => $pool_value ) {
 									if( in_array( $value['pool'], $pool_value ) ) {
           $poolitem = $value['pool'];
 										if( ( !empty( $pool_value['dns-server-1'] ) ) && ( !empty( $pool_value['dns-server-2'] ) ) ) {
  										$configdata .= "\t\t\toption domain-name-servers " . $pool_value['dns-server-1'] . ", " . $pool_value['dns-server-2'] . ";\n";
 										}
 										if( !empty( $pool_value['router'] ) ) {
 											$configdata .= "\t\t\toption routers " . $pool_value['router'] . ";\n";
 										}
 										if( ( !empty( $pool_value['scope-range-1'] ) ) && ( !empty( $pool_value['scope-range-2'] ) ) ) {
 											$configdata .= "\t\t\trange " . $pool_value['scope-range-1'] . " " . $pool_value['scope-range-2'] . ";\n";
 										}
											if( !empty( $pool_value['bootp-filename']) ) {
   									$configdata .= "\t\t\tfilename \"" . $pool_value['bootp-filename'] . "\";\n";
			   					}
						   		if( !empty( $pool_value['bootp-server']) ) {
							   		$configdata .= "\t\t\tnext-server " . $pool_value['bootp-server'] . ";\n";
								   }
 										if( $pool_value['ip-forwarding'] === "true" ) {
 											$configdata .= "\t\t\toption ip-forwarding on;\n";
 										}
 										if( !empty( $pool_value['broadcast-address'] ) ) {
 											$configdata .= "\t\t\toption broadcast-address " . $pool_value['broadcast-address'] . ";\n";
 										}
 										if( !empty( $pool_value['ntp-servers'] ) ) {
 											$configdata .= "\t\t\toption ntp-servers " . $pool_value['ntp-servers'] . ";\n";
 										}
 										if( !empty( $pool_value['netbios-name-servers'] ) ) {
 											$configdata .= "\t\t\toption netbios-name-servers " . $pool_value['netbios-name-servers'] . ";\n";
 										}
 										if( !empty( $pool_value['default-lease-time'] ) ) {
 											$configdata .= "\t\t\tdefault-lease-time " . $pool_value['default-lease-time'] . ";\n";
 										}
 										if( !empty( $pool_value['min-lease-time'] ) ) {
 											$configdata .= "\t\t\tmin-lease-time " . $pool_value['min-lease-time'] . ";\n";
 										}
 										if( !empty( $pool_value['max-lease-time'] ) ) {
 											$configdata .= "\t\t\tmax-lease-time " . $pool_value['max-lease-time'] . ";\n";
 										}
           if( ( $pool_value['allow-deny'] !== "na" ) && ( !empty( $pool_value['allow-deny-options'] ) ) && ( $pool_value['allow-deny-options'] !== "---------------" ) ) {
            if( in_array( $pool_value['allow-deny-options'], $class_array ) ) {
  											$configdata .= "\t\t\t" . $pool_value['allow-deny'] . " members of \"" . $pool_value['allow-deny-options'] . "\";\n";
 											} else {
  											$configdata .= "\t\t\t" . $pool_value['allow-deny'] . " " . $pool_value['allow-deny-options'] . ";\n";
 											}
           }
 									}
 								}
         $configdata .= "\t\t}\n";
 							}
        $configdata .= "\t}\n";
       }
       $configdata .= "}\n";
      }
     }

     // create our subnet configurations
					if( count( $results['subnets'] ) !== 0 ) {
      $configdata .= "\n#### Subnet Definitions ####\n";
      foreach( $results['subnets'] as $key => $value ) {
       if( empty( $value['shared-network'] ) ) {
        $configdata .= "\n### '" . $value['subnet-name'] . "' Subnet Definition ###\n";
 							$configdata .= "subnet " . $value['subnet'] . " ";
        $configdata .= "netmask " . $value['subnet-mask'] . " {\n";
        $configdata .= "\toption domain-name-servers " . $value['dns-server-1'] . ", " . $value['dns-server-2'] . ";\n";
        $configdata .= "\toption routers " . $value['router'] . ";\n";
        if( $value['enable-scope'] === "true" ) {
         $configdata .= "\trange " . $value['scope-range-1'] . " " . $value['scope-range-2'] . ";\n";
        }
								if( !empty( $value['bootp-filename']) ) {
									$configdata .= "\tfilename \"" . $value['bootp-filename'] . "\";\n";
								}
								if( !empty( $value['bootp-server']) ) {
									$configdata .= "\tnext-server " . $value['bootp-server'] . ";\n";
								}
 							if( !empty( $value['ip-forwarding'] ) ) {
 							 if( $value['ip-forwarding'] === "true" ) { $value['ip-forwarding'] = "on"; } else { $value['ip-forwarding'] = "off"; }
 							 $configdata .= "\toption ip-forwarding " . $value['ip-forwarding'] . ";\n";
 							}
 							if( !empty( $value['broadcast-address'] ) ) {
 							 $configdata .= "\toption broadcast-address " . $value['broadcast-address'] . ";\n";
 							}
 							if( !empty( $value['ntp-servers'] ) ) {
 							 $configdata .= "\toption ntp-servers " . $value['ntp-servers'] . ";\n";
 							}
 							if( !empty( $value['netbios-name-servers'] ) ) {
 							 $configdata .= "\toption netbios-name-servers " . $value['netbios-name-servers'] . ";\n";
 							}
 							if( !empty( $value['default-lease-time'] ) ) {
 							 $configdata .= "\tdefault-lease-time " . $value['default-lease-time'] . ";\n";
 							}
 							if( !empty( $value['min-lease-time'] ) ) {
 							 $configdata .= "\tmin-lease-time " . $value['min-lease-time'] . ";\n";
 							}
 							if( !empty( $value['max-lease-time'] ) ) {
 							 $configdata .= "\tmax-lease-time " . $value['max-lease-time'] . ";\n";
 							}
 							if( !empty( $value['pool'] ) ) {
 								$configdata .= "\t## Pool '" . $value['pool'] . "' ##\n";
 								$configdata .= "\tpool {\n";
 								foreach( $results['pools'] as $pool_key => $pool_value ) {
 									if( in_array( $value['pool'], $pool_value ) ) {
           $poolitem = $value['pool'];
 										if( ( !empty( $pool_value['dns-server-1'] ) ) && ( !empty( $pool_value['dns-server-2'] ) ) ) {
  										$configdata .= "\t\toption domain-name-servers " . $pool_value['dns-server-1'] . ", " . $pool_value['dns-server-2'] . ";\n";
 										}
											if( !empty( $pool_value['bootp-filename']) ) {
									   $configdata .= "\tfilename \"" . $value['bootp-filename'] . "\";\n";
								   }
								   if( !empty( $pool_value['bootp-server']) ) {
									   $configdata .= "\tnext-server " . $value['bootp-server'] . ";\n";
								   }
 										if( !empty( $pool_value['router'] ) ) {
 											$configdata .= "\t\toption routers " . $pool_value['router'] . ";\n";
 										}
 										if( ( !empty( $pool_value['scope-range-1'] ) ) && ( !empty( $pool_value['scope-range-2'] ) ) ) {
 											$configdata .= "\t\trange " . $pool_value['scope-range-1'] . " " . $pool_value['scope-range-2'] . ";\n";
 										}
 										if( $pool_value['ip-forwarding'] === "true" ) {
 											$configdata .= "\t\toption ip-forwarding on;\n";
 										}
 										if( !empty( $pool_value['broadcast-address'] ) ) {
 											$configdata .= "\t\toption broadcast-address " . $pool_value['broadcast-address'] . ";\n";
 										}
 										if( !empty( $pool_value['ntp-servers'] ) ) {
 											$configdata .= "\t\toption ntp-servers " . $pool_value['ntp-servers'] . ";\n";
 										}
 										if( !empty( $pool_value['netbios-name-servers'] ) ) {
 											$configdata .= "\t\toption netbios-name-servers " . $pool_value['netbios-name-servers'] . ";\n";
 										}
 										if( !empty( $pool_value['default-lease-time'] ) ) {
 											$configdata .= "\t\tdefault-lease-time " . $pool_value['default-lease-time'] . ";\n";
 										}
 										if( !empty( $pool_value['min-lease-time'] ) ) {
 											$configdata .= "\t\tmin-lease-time " . $pool_value['min-lease-time'] . ";\n";
 										}
 										if( !empty( $pool_value['max-lease-time'] ) ) {
 											$configdata .= "\t\tmax-lease-time " . $pool_value['max-lease-time'] . ";\n";
 										}
           if( ( $pool_value['allow-deny'] !== "na" ) && ( !empty( $pool_value['allow-deny-options'] ) ) && ( $pool_value['allow-deny-options'] !== "---------------" ) ) {
            if( in_array( $pool_value['allow-deny-options'], $class_array ) ) {
  											$configdata .= "\t\t" . $pool_value['allow-deny'] . " members of \"" . $pool_value['allow-deny-options'] . "\";\n";
 											} else {
  											$configdata .= "\t\t" . $pool_value['allow-deny'] . " " . $pool_value['allow-deny-options'] . ";\n";
 											}
           }
 									}
 								}
         $configdata .= "\t}\n";
 							}
        $configdata .= "}\n";
       }
      }
     }

     /* process pools NOT assigned to a subnet
     if( count( $results['pools'] ) !== 0 ) {
      $configdata .= "\n#### Unassigned Pools ####\n";
      foreach( $results['pools'] as $pool_key => $pool_value ) {
 						if( !in_array( $poolitem, $pool_value ) ) {
        $configdata .= "\n### Pool '" . $pool_value['pool-name'] . "' ###\n";
   					$configdata .= "pool {\n";
 							if( ( !empty( $pool_value['dns-server-1'] ) ) && ( !empty( $pool_value['dns-server-2'] ) ) ) {
  							$configdata .= "\toption domain-name-servers " . $pool_value['dns-server-1'] . ", " . $pool_value['dns-server-2'] . ";\n";
 							}
 							if( !empty( $pool_value['router'] ) ) {
 								$configdata .= "\toption routers " . $pool_value['router'] . ";\n";
 							}
 							if( ( !empty( $pool_value['scope-range-1'] ) ) && ( !empty( $pool_value['scope-range-2'] ) ) ) {
 								$configdata .= "\trange " . $pool_value['scope-range-1'] . " " . $pool_value['scope-range-2'] . ";\n";
 							}
 							if( $pool_value['ip-forwarding'] === "true" ) {
 								$configdata .= "\toption ip-forwarding on;\n";
 							}
 							if( !empty( $pool_value['broadcast-address'] ) ) {
 								$configdata .= "\toption broadcast-address " . $pool_value['broadcast-address'] . ";\n";
 							}
 							if( !empty( $pool_value['ntp-servers'] ) ) {
 								$configdata .= "\toption ntp-servers " . $pool_value['ntp-servers'] . ";\n";
 							}
 							if( !empty( $pool_value['netbios-name-servers'] ) ) {
 								$configdata .= "\toption netbios-name-servers " . $pool_value['netbios-name-servers'] . ";\n";
 							}
 							if( !empty( $pool_value['default-lease-time'] ) ) {
 								$configdata .= "\tdefault lease time " . $pool_value['default-lease-time'] . ";\n";
 							}
 							if( !empty( $pool_value['min-lease-time'] ) ) {
 								$configdata .= "\tmin lease time " . $pool_value['min-lease-time'] . ";\n";
 							}
 							if( !empty( $pool_value['max-lease-time'] ) ) {
 								$configdata .= "\tmax lease time " . $pool_value['max-lease-time'] . ";\n";
 							}
        if( ( $pool_value['allow-deny'] !== "na" ) && ( !empty( $pool_value['allow-deny-options'] ) ) && ( $pool_value['allow-deny-options'] !== "---------------" ) ) {
         $configdata .= "\t" . $pool_value['allow-deny'] . " " . $pool_value['allow-deny-options'] . ";\n";
        }
 						}
 					}
      $configdata .= "}\n";
     }
     */

     // pxe group w/ static host configurations
					if( ( count( $results['pxe'] ) !== 0 ) && ( $results['gpxe'][0]['pxe-enabled'] === "true" ) ) {
      $end_pxe_arr = count( $results['pxe'] ) - 1;
      $configdata .= "\n#### PXE Groups w/ Static Hosts ####\n";
      for( $x = 0; $x < $pxe_group_count; $x++ ) {
       if( !empty( $results['pxe'][$x]['pxe-group-name'] ) ) {
        $configdata .= "\n### PXE Group '" . $results['pxe'][$x]['pxe-group-name'] . "' ###\n";
        $configdata .= "group {\n";
        $configdata .= "\t\tfilename \"" . $results['pxe'][$x]['bootp-filename'] . "\";\n";
        $configdata .= "\t\tnext-server " . $results['pxe'][$x]['pxe-server'] . ";\n";
       }
							$configdata .= "\n\t\t## Static Hosts Assigned to PXE Group ##\n";
       //foreach( $results['pxe'] as $key => $value ) {
       foreach( $results['pxe']['pxe-hosts'] as $key => $value ) {
        //for( $y = count( $results['pxe'] ) - $pxe_group_count; $y <= count( $value ); $y++ ) {
        for( $y = 0; $y <= count( $value ); $y++ ) {
         if( ( $value[$y]['pxe-group'] === $results['pxe'][$x]['pxe-group-name'] ) && ( !empty( $value[$y]['pxe-group'] ) ) ) {
          $configdata .= "\t\thost " . $value[$y]['hostname'] . " {\n";
          $configdata .= "\t\t\thardware ethernet " . $value[$y]['mac-address'] . ";\n";
          $configdata .= "\t\t\tfixed-address " . $value[$y]['ip-address'] . ";\n";
          $configdata .= "\t\t}\n";
         }
        }
       }
       $configdata .= "}\n";
      }
     }
					     
     // ok, now grab the stragler static hosts
     $configdata .= "\n#### Static Hosts w/o PXE Group Membership ####\n";
     foreach( $results['static_hosts'] as $key => $value ) {
      if( empty( $value['pxe-group'] ) ) {
       $configdata .= "host " . $value['hostname'] . " {\n";
       $configdata .= "\thardware ethernet " . $value['mac-address'] . ";\n";
       $configdata .= "\tfixed-address " . $value['ip-address'] . ";\n";
       $configdata .= "}\n";
      }
     }
     $configdata_html = preg_replace( '/\n/', "<br>", preg_replace( '/\t/', "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $configdata ) );
    }
    
    // perform a quick temp file write to test configuration on
    if( ( $return = $misc->SafeWriteFile( $defined['confpath'], 'dhcpd.test', $configdata ) ) == 0 ) {
     
     // create test command to ensure file validity
     $cmd = escapeshellcmd( $defined['dhcpd_tst'] );
     $handle = popen( $cmd . " 2>&1", "r" );
     while( $read = fread( $handle, 2096 ) ) {
      // look for errors in output
      if( eregi( '^error|^warning|^bad|expecting', $read ) ) {
       $list = "<li>" . $read . "</li>";
       $wtf = 1;
      }
     }
    }
    
    if( $wtf === 1 ) {
     $error = $err->GenerateErrorLink( "help/help.html", "#build_config", $defined['error'], "An error was found when testing syntax.<br><br><ol>" . $list . "</ol>", NULL, NULL );
     $disable = "disabled";
    }
    
    // look for command to gen new conf and restart
    if( !empty( $_POST['RestartDHCPD'] ) ) {
     // perform a safe file write
     if( ( $return = $misc->SafeWriteFile( $defined['confpath'], 'dhcpd.conf', $configdata ) ) === 0 ) {
      $img = $defined['good'];
      $e = "A new 'dhcpd.conf' file has been written to the '$defined[confpath]' folder. A restart of the ISC DHCPD service should take place within the next couple of minutes reflecting the changes listed below";
      if( ( $return = $misc->SafeWriteFile( $defined['confpath'], 'restart', "Restart the ISC DHCPD service please, thanks." ) ) === 0 ) {
       $img = $defined['good'];
       $e = "A new 'dhcpd.conf' file has been written to the '$defined[confpath]' folder. A restart of the ISC DHCPD service should take place within the next couple of minutes reflecting the changes listed below";
      } else {
       // oops, process return codes to find error with SafeWriteFile function
       $img = $defined['error'];
       if( $return === -5 ) { $e = "Error writting data to file"; }
       if( $return === -4 ) { $e = "Error setting lock on file"; }
       if( $return === -3 ) { $e = "Error opening/creating file"; }
       if( $return === -2 ) { $e = "Not a valid path for writing"; }
       if( $return === -1 ) { $e = "Empty data for processing"; }
      }
     } else {
      // oops, process return codes to find error with SafeWriteFile function
      $img = $defined['error'];
      if( $return === -5 ) { $e = "There was an error attempting to write the new 'dhcpd.conf' file reflecting the data show below. Please check permissions on the folder and file."; }
      if( $return === -4 ) { $e = "Error setting lock on the new 'dhcpd.conf' configuration file. Please check permissions on the folder and file."; }
      if( $return === -3 ) { $e = "Error opening/creating file. You may need to check the permissions on the folder for write access as the web server user."; }
      if( $return === -2 ) { $e = "Not a valid path for writing. Invalid directory, please check configuration options."; }
      if( $return === -1 ) { $e = "Empty data for processing. An empty data block was found when attempting to write file."; }
     }
     $error = $err->GenerateErrorLink( "help/help.html", "#build_config", $img, $e, NULL, NULL );
    }
    
    $tpl->assign( 'error', $error, NULL, NULL );
    $tpl->assign( 'disable', $disable, NULL, NULL );
    $tpl->assign( 'configdata_html', $configdata_html, NULL, NULL );
    
    // Do some cleaning before leaving
    $misc->CleanUpVars( $_POST, NULL );
    $misc->CleanUpVars( $sql, NULL );
    $misc->CleanUpVars( $results, NULL );

 			// Perform analyze, repair and optimize on used tables
    $db->dbFixTable( "conf_global_opts", $dbconn );
    $db->dbFixTable( "conf_dns_opts", $dbconn );
    $db->dbFixTable( "conf_dnssec_opts", $dbconn );
    $db->dbFixTable( "conf_subnets", $dbconn );
    $db->dbFixTable( "conf_pxe_groups", $dbconn );
    $db->dbFixTable( "conf_hosts", $dbconn );
 			
 			// Free db handle and close connection(s)
    $db->dbFreeData( $dbconn );
    $db->dbCloseConn( $dbconn );

   } else {
    // page view restricted by access level
 			$ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['level'], NULL, NULL );
   }
  } else {
 		// general authentication error
   $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['auth_n'], NULL, NULL );
  }

 } else {
  // Possible XSS attack
  $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['xss_config'], NULL, NULL );
 }

} else {
 // File is missing for configuration params
 $ERROR = $err->GenerateErrorLink( "help/help.html", "#undef", $defined['error'], $errors['config_file'], NULL, NULL );
}

// include our header file
$tpl->assign( 'JS', $JS, NULL, NULL );
$tpl->display( 'header.tpl', $flag, NULL );

// determine our menu system
$MENU = $menu->CreateNav( $_SESSION['token'], $_GET['skin'] );
$tpl->assign( 'MENU', $tpl->assign( 'SKIN', preg_replace( '/templates\//', '', $style ), $MENU, $flag ), NULL, NULL );

// assign some vars to our main template
$tpl->assign( 'URL', $_SERVER['PHP_SELF'] . "?skin=" . $_GET['skin'], NULL, NULL );
$tpl->assign( 'IP_ADDRESS', $_SERVER['REMOTE_ADDR'], NULL, NULL );
$tpl->assign( 'ERROR', $ERROR, NULL, NULL );
$tpl->assign( 'STYLE', $style, NULL, NULL );
 $tpl->assign( 'usersonline', "<b>Users:</b> " . $usersoline . " online", NULL, NULL );

// call our main body template data
$tpl->assign( 'DATA', $tpl->assign( NULL, NULL, $FILE, $flag ), NULL, NULL );
$tpl->display( 'main.tpl', $flag, NULL );

// call our footer file
$tpl->assign( 'DISCLAIMER', $defined['disclaimer'], NULL, NULL );
$tpl->assign( 'SKIN_MENU', $skin->GenSkinMenu( $_GET['skin'], $defined['templates'] ), NULL, NULL );
$tpl->assign( 'SKIN_MENU_ERR', $skin_err, NULL, NULL );
$tpl->display( 'footer.tpl', $flag, NULL );

// show some debugging if enabled
if( $defined['debug'] === "TRUE" ) { $debug->ShowDebug( $_GET, $_POST, $_REQUEST, $_SESSION ); }

?>