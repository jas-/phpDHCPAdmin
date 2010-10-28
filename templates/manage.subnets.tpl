<!-- Global DNS configuration template -->
<form action="{$URL}" method="post" name="configSUBS">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="2">
      <a href="javascript:popUp('help/help.html#config_subnet','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Manage Subnets</b><br>
      Use this to manage multiple subnet configurations
     </td>
    </tr>
    <tr>
     <td colspan="2">
      {$error}
     </td>
    </tr>
    <tr>
     <td colspan="2" align="center">
      <img src="templates/images/graphs/graph.subnets.php">
     </td>
    </tr>
    <tr>
     <td width="60%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#config_subnet_form','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Add/Edit/Delete Subnets</b><br>
         <div class="copyright">** Here you can add/edit and delete subnet configuration options</div>
        </td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Subnet Name:</b></td>
        <td><input type="text" name="subnet_name" value="{$subnet_name}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$subnet_name_err}* Unique identifier</td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Subnet:</b></td>
        <td><input type="text" name="subnet" value="{$subnet}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$subnet_err}* 192.168.0.0</td>
       </tr>
       <tr>
        <td nowrap><b>Subnet Mask:</b></td>
        <td><input type="text" name="subnet_mask" value="{$subnet_mask}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$subnet_mask_err}* 255.255.255.0</td>
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
        <td nowrap><b>Router:</b></td>
        <td><input type="text" name="router" value="{$router}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$router_err}* 192.168.0.1</td>
       </tr>
							<tr>
        <td nowrap><b>Assign pool?:</b></td>
        <td>{$pool}</td>
        <td class="copyright" nowrap>{$pool_err}* Select pool</td>
       </tr>
       <tr>
        <td nowrap><b>or Enable Scope?:</b></td>
        <td>TRUE:<input type="radio" name="enable_scope" value="true" {$enable_scope_true}>&nbsp;&nbsp;FALSE:<input type="radio" name="enable_scope" value="false" {$enable_scope_false}></td>
        <td class="copyright" nowrap>{$enable_scope_err}* Enable Scope?</td>
       </tr>
       <tr>
        <td nowrap><b>Scope Range Start:</b></td>
        <td><input type="text" name="scope_range_1" value="{$scope_range_1}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$scope_range_1_err}ex. 192.168.0.10</td>
       </tr>
       <tr>
        <td nowrap><b>Scope Range End:</b></td>
        <td><input type="text" name="scope_range_2" value="{$scope_range_2}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$scope_range_2_err}ex. 192.168.0.240</td>
       </tr>
       <tr>
							 <td colspan="3" align="center" nowrap>
									<b>Need some extra subnet options?</b>&nbsp;&nbsp;<a href="javascript:showdiv( 'extras' );">[+]</a>&nbsp;&nbsp;<a href="javascript:hidediv( 'extras' );">[-]</a></td>
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
           <td nowrap><b>BOOTP Filename:</b></td>
           <td><input type="text" name="bootp_filename" value="{$bootp_filename}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$bootp_filename_err}ex. /boot/pxelinux.0</td>
          </tr>
          <tr>
           <td nowrap><b>BOOTP Server:</b></td>
           <td><input type="text" name="bootp_server" value="{$bootp_server}" style="width: 100%"></td>
           <td class="copyright" nowrap>{$bootp_server_err}ex. 192.168.0.240</td>
          </tr>
w							   <tr>
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
        <td align="center"><input type="submit" name="AddSubnet" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditSubnet" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelSubnet" value="Delete" onclick="return confirm( 'Are you sure you want to delete the Subnet {$subnet_name}? WARNING!! This will make modifications to any static host assigned to the Subnet {$subnet_name}!!!' )"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
       </tr>
							<tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetSubnetFields();'></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </td>
     <td valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#config_subnet_list','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Select an existing Subnet to edit/delete</b><br>
         <div class="copyright">** List of currently defined Subnet configurations. Select existing subnet to edit</div>
        </td>
       </tr>
       <tr>
        <td>{$subnets}</td>
        <td valign="top" class="copyright" nowrap>{$subnets_err}</td>
       </tr>
      </table>
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td>
         <a href="javascript:popUp('help/help.html#config_subnet_adapters','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Adapters and their Broadcast address</b><br>
         <div class="copyright">** List of currently defined interfaces and the broadcast address they are using</div>
        </td>
       </tr>
       <tr>
        <td>{$adapters}</td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end Global DHCPD DNS configuration template -->
