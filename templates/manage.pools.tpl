<!-- Pools configuration template -->
<form action="{$URL}" method="post" name="configSUBS">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="2">
      <a href="javascript:popUp('help/help.html#config_pools','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Manage Pools</b><br>
      Use this to manage multiple pool configurations
     </td>
    </tr>
    <tr>
     <td colspan="2">
      {$error}
     </td>
    </tr>
    <tr>
     <td colspan="2" align="center">
      <img src="templates/images/graphs/graph.pools.php">
     </td>
    </tr>
    <tr>
     <td width="65%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#config_pools_form','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Add/Edit/Delete Pools</b><br>
         <div class="copyright">** Here you can add/edit and delete pool configuration options</div>
        </td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Pool Name:</b></td>
        <td><input type="text" name="pool_name" value="{$pool_name}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$pool_name_err}* Unique identifier</td>
       </tr>
       <tr>
        <td nowrap><b>Scope Range Start:</b></td>
        <td><input type="text" name="scope_range_1" value="{$scope_range_1}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$scope_range_1_err}* 192.168.0.10</td>
       </tr>
       <tr>
        <td nowrap><b>Scope Range End:</b></td>
        <td><input type="text" name="scope_range_2" value="{$scope_range_2}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$scope_range_2_err}* 192.168.0.240</td>
       </tr>
       <tr>
        <td nowrap><b>DNS Server 1:</b></td>
        <td><input type="text" name="dns_server_1" value="{$dns_server_1}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$dns_server_1_err}* dns1.server.com</td>
       </tr>
       <tr>
        <td nowrap><b>DNS Server 2:</b></td>
        <td><input type="text" name="dns_server_2" value="{$dns_server_2}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$dns_server_2_err}* dns2.server.com</td>
       </tr>
							<tr>
        <td nowrap><b>Allow/Deny:</b></td>
        <td>Allow:<input type="radio" name="allow_deny" value="allow" {$allow_deny_allow}>&nbsp;&nbsp;Deny:<input type="radio" name="allow_deny" value="deny" {$allow_deny_deny}>&nbsp;&nbsp;N/A:<input type="radio" name="allow_deny" value="na" {$allow_deny_na}></td>
        <td class="copyright" nowrap>{$allow_deny_err}* Allow or deny?</td>
       </tr>
							<tr>
        <td nowrap><b>Allow/Deny Option List:</b></td>
        <td>{$list}</td>
        <td class="copyright" nowrap>{$list_err}* Allow/Deny list</td>
       </tr>
							<tr>
							 <td colspan="3" align="center" nowrap><b>Need some extra pool options?</b>&nbsp;&nbsp;<a href="javascript:switchid( 'extras' );">[+]</a>&nbsp;&nbsp;<a href="javascript:hidediv( 'extras' );">[-]</a></td>
							</tr>
       <tr>
							 <td colspan="3">
								 <div id="extras">
								 <table width="100%" cellspacing="5" cellpadding="0" border="0">
									 <tr>
           <td nowrap><b>IP Forwarding?:</b></td>
           <td>TRUE:<input type="radio" name="enable_forwarding" value="true" {$enable_forwarding_true}>&nbsp;&nbsp;FALSE:<input type="radio" name="enable_forwarding" value="false" {$enable_forwarding_false}></td>
           <td class="copyright" nowrap>{$enable_forwarding_err}* Forwarding?</td>
          </tr>
							   <tr>
           <td nowrap><b>Broadcast Address:</b></td>
           <td><input type="text" name="broadcast_address" value="{$broadcast_address}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$broadcast_address_err}* 192.168.0/24</td>
          </tr>
          <tr>
           <td nowrap><b>Router:</b></td>
           <td><input type="text" name="router" value="{$router}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$router_err}* 192.168.0.1</td>
          </tr>
          <tr>
           <td nowrap><b>BOOTP Filename:</b></td>
           <td><input type="text" name="bootp_filename" value="{$bootp_filename}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$bootp_filename_err}ex. /boot/pxelinux.0</td>
          </tr>
          <tr>
           <td nowrap><b>BOOTP Server:</b></td>
           <td><input type="text" name="bootp_server" value="{$bootp_server}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$bootp_server_err}ex. 192.168.0.240</td>
          </tr>
							   <tr>
           <td nowrap><b>NTP Servers:</b></td>
           <td><input type="text" name="ntp_servers" value="{$ntp_servers}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$ntp_servers_err}* 192.168.0.2</td>
          </tr>
							   <tr>
           <td nowrap><b>NETBIOS Servers:</b></td>
           <td><input type="text" name="netbios_servers" value="{$netbios_servers}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$netbios_servers_err}* 192.168.0.10</td>
          </tr>
							   <tr>
           <td nowrap><b>Default Lease:</b></td>
           <td><input type="text" name="default_lease" value="{$default_lease}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$default_lease_err}* 1800</td>
          </tr>
							   <tr>
           <td nowrap><b>Min. Lease:</b></td>
           <td><input type="text" name="min_lease" value="{$min_lease}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$min_lease_err}* 30</td>
          </tr>
							   <tr>
           <td nowrap><b>Max Lease:</b></td>
           <td><input type="text" name="max_lease" value="{$max_lease}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$max_lease_err}* 3200</td>
          </tr>
									</table>
									</div>
								</td>
							</tr>
							<tr>
							 <td colspan="3" align="center" nowrap>
									<b>Set additional group permissions?</b>&nbsp;&nbsp;<a href="javascript:showdiv( 'perms' );">[+]</a>&nbsp;&nbsp;<a href="javascript:hidediv( 'perms' );">[-]</a></td>
							</tr>
       <tr>
							 <td colspan="3">
								 <div id="perms">
								 <table width="100%" cellspacing="5" cellpadding="0" border="0">
									 <tr>
           <td>{$select_groups}<input type="hidden" name="ex_group" value="{$ex_group}"></td>
           <td class="copyright" nowrap>{$select_groups_err}* Select groups</td>
          </tr>
									</table>
									</div>
								</td>
							</tr>
       <tr>
        <td>&nbsp;</td>
        <td align="center"><input type="submit" name="AddPool" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditPool" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelPool" value="Delete" onclick="return confirm( 'Are you sure you want to delete the Pool {$pool_name}?' )"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
       </tr>
							<tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetPoolFields();'></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </td>
     <td valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#config_pools_list','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Select an existing Pool to edit/delete</b><br>
         <div class="copyright">** List of currently defined Pool configurations. Select existing pool to edit</div>
        </td>
       </tr>
       <tr>
        <td>{$pools}</td>
        <td valign="top" class="copyright" nowrap>{$pools_err}</td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end Pool configuration template -->
