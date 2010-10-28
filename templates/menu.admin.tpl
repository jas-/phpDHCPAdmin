<!-- administrative menu system -->
<tr>
 <td>
  <dl id="menu">
   <dt onclick="javascript:montre();"><a href=index.php?skin={$SKIN} rel=lightboxform><b>&spades;&nbsp;Main Page&nbsp;&spades;</b></a></dt>
  </dl>
  <dl id="menu">
  <dt onclick="javascript:montre('smenu1');"><a href="#"><b>&spades;&nbsp;DHCPD Options&nbsp;&spades;</b></a></dt>
   <dd id="smenu1">
    <ul>
     <li><a href=config.global.php?skin={$SKIN} rel=lightboxform>Global Options</a></li>
     <li><a href=config.pxe.php?skin={$SKIN} rel=lightboxform>PXE Options</a></li>
					<li><a href=config.dnssec.php?skin={$SKIN} rel=lightboxform>DNSSEC Keys</a></li>
     <li><a href=config.dns.php?skin={$SKIN} rel=lightboxform>DNS Zones</a></li>
     <li><a href=config.replication.php?skin={$SKIN} rel=lightboxform>Replication Options</a></li>
    </ul>
   </dd>
  </dl>
  <dl id="menu">
  <dt onclick="javascript:montre('smenu2');"><a href="#"><b>&spades;&nbsp;Subnet Options&nbsp;&spades;</b></a></dt>
   <dd id="smenu2">
    <ul>
     <li><a href=manage.classes.php?skin={$SKIN} rel=lightboxform>Manage Classes</a></li>
     <li><a href=manage.pools.php?skin={$SKIN} rel=lightboxform><b>Manage Pools</b></a></li>
     <li><a href=manage.pxe.php?skin={$SKIN} rel=lightboxform><b>Manage Groups</b></a></li>
					<li><a href=manage.shared-networks.php?skin={$SKIN} rel=lightboxform><b>Shared Networks</b></a></li>
     <li><a href=manage.subnets.php?skin={$SKIN} rel=lightboxform><b>Manage Subnets</b></a></li>
    </ul>
   </dd>
  </dl>
  <dl id="menu">
  <dt onclick="javascript:montre();"><a href="manage.hosts.php?skin={$SKIN}" rel=lightboxform><b>&spades;&nbsp;Manage Clients&nbsp;&spades;</b></a></dt>
  <dt onclick="javascript:montre();"><a href="manage.leases.php?skin={$SKIN}" rel=lightboxform><b>&spades;&nbsp;Manage Leases&nbsp;&spades;</b></a></dt>
  </dl>
  <dl id="menu">
  <dt onclick="javascript:montre('smenu3');"><a href="#"><b>&spades;&nbsp;Admin Utilities&nbsp;&spades;</b></a></dt>
   <dd id="smenu3">
    <ul>
     <li><a href=admin.import.hosts.php?skin={$SKIN} rel=lightboxform><b>Import Static Hosts</b></a></li>
     <li><a href=admin.import.conf.php?skin={$SKIN} rel=lightboxform><b>Import Config</b></a></li>
     <li><a href=admin.manage.backups.php?skin={$SKIN} rel=lightboxform><b>Manage Backups</b></a></li>
     <li><a href=admin.manage.groups.php?skin={$SKIN} rel=lightboxform><b>Manage Groups</b></a></li>
     <li><a href=admin.manage.users.php?skin={$SKIN} rel=lightboxform><b>Manage Users</b></a></li>
    </ul>
   </dd>
  </dl>
  <dl id="menu">
   <dt onclick="javascript:montre();"><a href=restart.dhcpd.php?skin={$SKIN} rel=lightboxform><b>&spades;&nbsp;Restart DHCPD&nbsp;&spades;</b></a></dt>
  </dl>
  <dl id="menu">
   <dt onclick="javascript:montre();"><a href=exit.php?skin={$SKIN} rel=lightboxform><b>&spades;&nbsp;Log Out&nbsp;&spades;</b></a></dt>
  </dl>
 </td>
</tr>
<!-- end administrative menu system -->
