CREATE DATABASE `phpDHCPAdmin`;
USE `phpDHCPAdmin`;

CREATE TABLE IF NOT EXISTS `admin_backup_conf` (
  `id` int(255) NOT NULL auto_increment,
  `date` varchar(25) NOT NULL default '',
  `data` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `admin_config_algorithm` (
  `id` int(255) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

INSERT INTO `admin_config_algorithm` (`id`, `name`) VALUES
(1, 'HMAC-MD5'),
(2, 'RSAMD5'),
(4, 'DSA'),
(5, 'DH'),
(6, 'RSASHA1'),
(7, 'HMAC-SHA1'),
(8, 'HMAC-SHA224'),
(9, 'HMAC-SHA256'),
(10, 'HMAC-SHA384'),
(11, 'HMAC-SHA512');

CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` int(255) NOT NULL auto_increment,
  `date` varchar(255) NOT NULL default '',
  `time` varchar(255) NOT NULL default '',
  `ip` varchar(255) NOT NULL default '',
  `port` int(255) NOT NULL default '0',
  `username` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `page` longtext NOT NULL,
  `pagecount` varchar(255) NOT NULL default '',
  `message` longtext NOT NULL,
  `session` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `session_id` varchar(32) NOT NULL default '',
  `http_user_agent` varchar(32) NOT NULL default '',
  `session_data` blob NOT NULL,
  `session_expire` int(11) NOT NULL default '0',
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `auth_groups` (
  `description` varchar(255) NOT NULL,
  `manager` varchar(80) NOT NULL,
  `contact` varchar(80) NOT NULL,
  `id` int(255) NOT NULL auto_increment,
  `group` varchar(20) NOT NULL,
  `owner` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `group` (`group`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

INSERT INTO `auth_groups` (`group`, `owner`) VALUES ('admin', 'admin');

CREATE TABLE IF NOT EXISTS `auth_levels` (
  `id` int(255) NOT NULL auto_increment,
  `level` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

INSERT INTO `auth_levels` (`id`, `level`) VALUES
(1, 'admin'),
(2, 'user'),
(3, 'view');

CREATE TABLE IF NOT EXISTS `auth_users` (
  `id` int(255) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` varchar(8) NOT NULL,
  `group` varchar(40) NOT NULL,
  `dept` varchar(40) NOT NULL,
  `first` varchar(255) NOT NULL,
  `last` varchar(255) NOT NULL,
  `phone` varchar(12) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `create_date` varchar(255) NOT NULL,
  `create_time` varchar(255) NOT NULL,
  `access_date` varchar(255) NOT NULL,
  `access_time` varchar(255) NOT NULL,
  `session` varchar(255) NOT NULL,
  `reset` varchar(8) NOT NULL,
  `owner` varchar(45) NOT NULL,
  UNIQUE KEY `username` (`username`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

INSERT INTO `auth_users` (`username`, `password`, `level`, `group`, `reset`, `owner`) VALUES ('admin', sha1('phpDHCPAdmin'), 'admin', 'admin', 'TRUE', 'admin');

CREATE TABLE IF NOT EXISTS `conf_adapters` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(45) NOT NULL,
  `encap` varchar(45) NOT NULL,
  `hwaddr` varchar(60) NOT NULL,
  `ipv4` varchar(40) NOT NULL,
  `broadcast` varchar(45) NOT NULL,
  `mask` varchar(45) NOT NULL,
  `ipv6` varchar(80) NOT NULL,
  `flags` varchar(100) NOT NULL,
  `rx_packets` bigint(20) NOT NULL,
  `rx_errors` bigint(20) NOT NULL,
  `rx_dropped` bigint(20) NOT NULL,
  `rx_overruns` bigint(20) NOT NULL,
  `rx_frame` bigint(20) NOT NULL,
  `tx_packets` bigint(20) NOT NULL,
  `tx_errors` bigint(20) NOT NULL,
  `tx_dropped` bigint(20) NOT NULL,
  `tx_overruns` bigint(20) NOT NULL,
  `tx_carrier` bigint(20) NOT NULL,
  `rx_bytes` bigint(20) NOT NULL,
  `tx_bytes` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_classes` (
  `id` int(255) NOT NULL auto_increment,
  `class-name` varchar(85) NOT NULL,
  `group` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `class-name` (`class-name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_classes_options` (
  `id` int(255) NOT NULL auto_increment,
  `class-name` varchar(85) NOT NULL,
  `class-option` varchar(85) NOT NULL,
  `class-match` varchar(5) NOT NULL,
  `class-match-option` varchar(40) NOT NULL,
  `class-substring` varchar(5) NOT NULL,
  `class-substring-start` int(2) NOT NULL,
  `class-substring-end` int(2) NOT NULL,
  `match-substring-regex` varchar(48) NOT NULL,
  `group` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_classes_opts` (
  `id` int(255) NOT NULL auto_increment,
  `all-subnets-local` tinyint(1) NOT NULL,
  `arp-cache-timeout` int(32) NOT NULL,
  `bootfile-name` text NOT NULL,
  `boot-size` int(16) NOT NULL,
  `broadcast-address` varchar(45) NOT NULL,
  `cookie-servers` varchar(255) NOT NULL,
  `default-ip-ttl` int(8) NOT NULL,
  `default-tcp-ttl` int(8) NOT NULL,
  `dhcp-client-identifier` varchar(255) NOT NULL,
  `dhcp-lease-time` int(32) NOT NULL,
  `dhcp-max-message-size` int(16) NOT NULL,
  `dhcp-message` text NOT NULL,
  `dhcp-message-type` int(8) NOT NULL,
  `dhcp-options-overload` int(8) NOT NULL,
  `dhcp-parameter-request-list` int(16) NOT NULL,
  `dhcp-rebinding-time` int(32) NOT NULL,
  `dhcp-renewal-time` int(32) NOT NULL,
  `dhcp-requested-address` varchar(45) NOT NULL,
  `domain-name` text NOT NULL,
  `domain-name-servers` varchar(255) NOT NULL,
  `extensions-path` text NOT NULL,
  `finger-server` varchar(255) NOT NULL,
  `font-servers` varchar(255) NOT NULL,
  `host-name` varchar(48) NOT NULL,
  `ieee802-3-encapsulation` tinyint(1) NOT NULL,
  `ien116-name-servers` varchar(255) NOT NULL,
  `impress-servers` varchar(255) NOT NULL,
  `interface-mtu` int(16) NOT NULL,
  `ip-forwarding` tinyint(1) NOT NULL,
  `irc-server` varchar(255) NOT NULL,
  `log-servers` varchar(255) NOT NULL,
  `lpr-servers` varchar(255) NOT NULL,
  `mask-supplier` tinyint(1) NOT NULL,
  `max-dgram-reassembly` int(16) NOT NULL,
  `merit-dump` text NOT NULL,
  `mobile-ip-home-agent` varchar(255) NOT NULL,
  `nds-servers` varchar(255) NOT NULL,
  `netbios-dd-server` varchar(255) NOT NULL,
  `netbios-name-servers` varchar(255) NOT NULL,
  `netbios-node-type` int(8) NOT NULL,
  `netbios-scope` varchar(255) NOT NULL,
  `nis-domain` varchar(255) NOT NULL,
  `nis-servers` varchar(255) NOT NULL,
  `nisplus-domain` varchar(255) NOT NULL,
  `nisplus-servers` varchar(255) NOT NULL,
  `nntp-server` varchar(255) NOT NULL,
  `non-local-source-routing` tinyint(1) NOT NULL,
  `ntp-servers` varchar(255) NOT NULL,
  `nwip-domain` varchar(255) NOT NULL,
  `nwip-suboptions` varchar(255) NOT NULL,
  `path-mtu-aging-timeout` int(32) NOT NULL,
  `path-mtu-plateau-table` int(16) NOT NULL,
  `perform-mask-discovery` tinyint(1) NOT NULL,
  `pop-server` varchar(255) NOT NULL,
  `resource-location-servers` varchar(255) NOT NULL,
  `root-path` varchar(255) NOT NULL,
  `router-discovery` tinyint(1) NOT NULL,
  `router-solicitation-address` varchar(255) NOT NULL,
  `routers` varchar(255) NOT NULL,
  `slp-directory-agent` varchar(255) NOT NULL,
  `slp-service-scope` varchar(255) NOT NULL,
  `smtp-server` varchar(255) NOT NULL,
  `streettalk-server` varchar(255) NOT NULL,
  `subnet-mask` varchar(255) NOT NULL,
  `subnet-selection` varchar(255) NOT NULL,
  `swap-server` varchar(255) NOT NULL,
  `tcp-keepalive-garbage` varchar(255) NOT NULL,
  `tcp-keepalive-interval` int(32) NOT NULL,
  `tftp-server-name` varchar(255) NOT NULL,
  `time-offset` int(32) NOT NULL,
  `time-servers` varchar(255) NOT NULL,
  `trailer-encapsulation` tinyint(1) NOT NULL,
  `uap-servers` varchar(255) NOT NULL,
  `user-class` varchar(255) NOT NULL,
  `vendor-class-identifier` varchar(255) NOT NULL,
  `www-server` varchar(255) NOT NULL,
  `x-display-manager` varchar(255) NOT NULL,
  `agent.circuit-id` varchar(255) NOT NULL,
  `agent.remote-id` varchar(255) NOT NULL,
  `agent.DOCSIS-device-class` int(32) NOT NULL,
  `fqdn.no-client-update` tinyint(1) NOT NULL,
  `fqdn.server-update` tinyint(1) NOT NULL,
  `fqdn.encoded` tinyint(1) NOT NULL,
  `fqdn.rcode1` tinyint(1) NOT NULL,
  `fqdn.rcode2` tinyint(1) NOT NULL,
  `fqdn.fqdn` varchar(255) NOT NULL,
  `nwip.nsq-broadcast` varchar(255) NOT NULL,
  `nwip.preferred-dss` varchar(255) NOT NULL,
  `nwip.nearest-nwip-server` varchar(255) NOT NULL,
  `nwip.autoretries` int(8) NOT NULL,
  `nwip.autoretry-secs` int(8) NOT NULL,
  `nwip.nwip-1-1` varchar(255) NOT NULL,
  `nwip.primary-dss` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_dnssec_opts` (
  `id` int(255) NOT NULL auto_increment,
  `key-name` varchar(128) NOT NULL,
  `algorithm` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key-name` (`key-name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_dns_opts` (
  `id` int(255) NOT NULL auto_increment,
  `zone` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `file-name` varchar(255) NOT NULL,
  `dnssec-enabled` varchar(8) NOT NULL,
  `dnssec-key` varchar(255) NOT NULL,
  `allow-update` varchar(255) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `zone` (`zone`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_failover` (
  `id` int(255) NOT NULL auto_increment,
  `peer name` varchar(85) NOT NULL,
  `type` varchar(45) NOT NULL,
  `address` varchar(255) NOT NULL,
  `port` int(30) NOT NULL,
  `peer address` varchar(255) NOT NULL,
  `peer port` int(30) NOT NULL,
  `max-response-delay` int(30) NOT NULL,
  `max-unacked-updates` int(30) NOT NULL,
  `mclt` int(30) NOT NULL,
  `split` int(30) NOT NULL,
  `load balance max seconds` int(30) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_global_opts` (
  `id` int(11) NOT NULL auto_increment,
  `option domain-name` varchar(100) NOT NULL,
  `option subnet-mask` varchar(255) NOT NULL,
  `default-lease-time` int(6) NOT NULL default '0',
  `max-lease-time` int(6) NOT NULL default '0',
  `option time-offset` varchar(40) NOT NULL,
  `option routers` varchar(255) NOT NULL,
  `option domain-name-servers` varchar(255) NOT NULL,
  `option lpr-servers` varchar(255) NOT NULL,
  `option-broadcast-addr` varchar(255) NOT NULL,
  `server-identifier` varchar(255) NOT NULL,
  `option time-serv` varchar(255) NOT NULL,
  `ddns-update-style` varchar(255) NOT NULL,
  `authoritative` varchar(8) NOT NULL,
  `bootp` varchar(8) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `option-domain-name` (`option domain-name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_hosts` (
  `id` int(11) NOT NULL auto_increment,
  `hostname` varchar(100) NOT NULL,
  `mac-address` varchar(100) NOT NULL,
  `ip-address` varchar(100) NOT NULL,
  `subnet-name` varchar(100) NOT NULL,
  `pxe-group` varchar(255) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hostname` (`hostname`),
  UNIQUE KEY `mac-address` (`mac-address`),
  UNIQUE KEY `ip-address` (`ip-address`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_leases` (
  `id` int(255) NOT NULL auto_increment,
  `ip` varchar(85) NOT NULL,
  `start` varchar(60) NOT NULL,
  `end` varchar(60) NOT NULL,
  `cltt` varchar(60) NOT NULL,
  `current-state` varchar(10) NOT NULL,
  `next-state` varchar(10) NOT NULL,
  `hardware` varchar(45) NOT NULL,
  `hostname` varchar(45) NOT NULL,
  `abandoned` varchar(5) NOT NULL,
  `circut-id` varchar(80) NOT NULL,
  `remote-id` varchar(80) NOT NULL,
  `ddns-text` varchar(80) NOT NULL,
  `ddns-fwd-name` varchar(80) NOT NULL,
  `ddns-client-fqdn` varchar(80) NOT NULL,
  `ddns-rev-name` varchar(80) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_leases_properties` (
  `id` int(255) NOT NULL auto_increment,
  `date` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `recreate` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_pools` (
  `id` int(11) NOT NULL auto_increment,
  `pool-name` varchar(80) NOT NULL,
  `dns-server-1` varchar(100) NOT NULL,
  `dns-server-2` varchar(100) NOT NULL,
  `router` varchar(100) NOT NULL,
  `scope-range-1` varchar(100) NOT NULL,
  `scope-range-2` varchar(100) NOT NULL,
  `bootp-filename` varchar(80) NOT NULL,
  `bootp-server` varchar(80) NOT NULL,
  `allow-deny` varchar(8) NOT NULL,
  `allow-deny-options` varchar(85) NOT NULL,
  `ip-forwarding` varchar(8) NOT NULL,
  `broadcast-address` varchar(80) NOT NULL,
  `ntp-servers` varchar(80) NOT NULL,
  `netbios-name-servers` varchar(80) NOT NULL,
  `default-lease-time` int(5) NOT NULL,
  `min-lease-time` int(4) NOT NULL,
  `max-lease-time` int(8) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pool-name` (`pool-name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_pxe_groups` (
  `id` int(255) NOT NULL auto_increment,
  `pxe-group-name` varchar(124) NOT NULL,
  `pxe-server` varchar(255) NOT NULL,
  `bootp-filename` varchar(255) NOT NULL,
  `assigned-subnet` varchar(255) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `pxe-group-name` (`pxe-group-name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_pxe_opts` (
  `id` int(255) NOT NULL auto_increment,
  `option-space` varchar(24) NOT NULL,
  `mtftp-ip` varchar(120) NOT NULL,
  `mtftp-cport` varchar(120) NOT NULL,
  `mtftp-sport` varchar(120) NOT NULL,
  `mtftp-tmout` varchar(120) NOT NULL,
  `mtftp-delay` varchar(120) NOT NULL,
  `discovery-control` varchar(128) NOT NULL,
  `discovery-mcast-addr` varchar(128) NOT NULL,
  `pxe-enabled` varchar(8) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

INSERT INTO `conf_pxe_opts` (`option-space`, `mtftp-ip`, `mtftp-cport`, `mtftp-sport`, `mtftp-tmout`, `mtftp-delay`, `discovery-control`, `discovery-mcast-addr`, `pxe-enabled`) VALUES
('ip-address', 'unsigned integer 16', 'unsigned integer 16', 'unsigned integer 16', 'unsigned integer 8', 'unsigned integer 8', 'unsigned integer 8', 'ip-address', 'true');

CREATE TABLE IF NOT EXISTS `conf_shared_networks` (
  `id` int(255) NOT NULL auto_increment,
  `shared-network-name` varchar(45) NOT NULL,
  `ip-forwarding` tinyint(1) NOT NULL,
  `bootp-filename` varchar(80) NOT NULL,
  `bootp-server` varchar(80) NOT NULL,
  `broadcast-address` varchar(80) NOT NULL,
  `ntp-server` varchar(80) NOT NULL,
  `netbios-server` varchar(80) NOT NULL,
  `default-lease` int(8) NOT NULL,
  `min-lease` int(8) NOT NULL,
  `max-lease` int(8) NOT NULL,
  `group` varchar(35) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `shared-network-name` (`shared-network-name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_subnets` (
  `id` int(11) NOT NULL auto_increment,
  `subnet` varchar(100) NOT NULL,
  `subnet-mask` varchar(100) NOT NULL,
  `dns-server-1` varchar(100) NOT NULL,
  `dns-server-2` varchar(100) NOT NULL,
  `router` varchar(100) NOT NULL,
  `subnet-name` varchar(255) NOT NULL,
  `shared-network` varchar(45) NOT NULL,
  `pool` varchar(85) NOT NULL,
  `enable-scope` char(8) NOT NULL,
  `scope-range-1` varchar(100) NOT NULL,
  `scope-range-2` varchar(100) NOT NULL,
  `bootp-filename` varchar(80) NOT NULL,
  `bootp-server` varchar(80) NOT NULL,
  `ip-forwarding` varchar(8) NOT NULL,
  `broadcast-address` varchar(80) NOT NULL,
  `ntp-servers` varchar(80) NOT NULL,
  `netbios-name-servers` varchar(80) NOT NULL,
  `default-lease-time` int(5) NOT NULL,
  `min-lease-time` int(4) NOT NULL,
  `max-lease-time` int(8) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `subnet` (`subnet`),
  UNIQUE KEY `subnet-name` (`subnet-name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `conf_traffic` (
  `id` int(255) NOT NULL auto_increment,
  `interface` varchar(45) NOT NULL,
  `bytes` bigint(20) NOT NULL,
  `time` varchar(45) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
