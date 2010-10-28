<!-- Global DHCPD configuration template -->
<form action="{$URL}" method="post" name="configGlobal">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td>
      <a href="javascript:popUp('help/help.html#config_global','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Global DHCPD Configuration Options</b><br>
      Below are the options available for the ISC DHCPD Global configuration options
     </td>
    </tr>
    <tr>
     <td>
      {$error}
     </td>
    </tr>
    <tr>
     <td width="60%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td width="5%" nowrap><b>Domain Name:</b></td>
        <td><input type="text" name="domain_name" value="{$domain_name}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$domain_name_err}* server.com</td>
       </tr>
       <tr>
        <td nowrap><b>DNS List:</b></td>
        <td><input type="text" name="dns_server_list" value="{$dns_server_list}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$dns_server_list_err}* dc1.server.com dc2.servrer.com</td>
       </tr>
       <tr>
        <td nowrap><b>Default Lease Time:</b></td>
        <td><input type="text" name="default_lease_time" value="{$default_lease_time}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$default_lease_time_err}* 1800 seconds = 5 minutes</td>
       </tr>
       <tr>
        <td nowrap><b>Maximum Lease Time:</b></td>
        <td><input type="text" name="max_lease_time" value="{$max_lease_time}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$max_lease_time_err}* 1800 seconds = 5 minutes</td>
       </tr>
       <tr>
        <td nowrap><b>Option Time Offset:</b></td>
        <td><input type="text" name="time_offset" value="{$time_offset}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$time_offset_err}* Max Clockskew</td>
       </tr>
       <tr>
        <td nowrap><b>Option Routers:</b></td>
        <td><input type="text" name="routers" value="{$routers}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$routers_err}* 192.168.1</td>
       </tr>
       <tr>
        <td nowrap><b>LPR Server List:</b></td>
        <td><input type="text" name="lpr_server_list" value="{$lpr_server_list}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$lpr_server_list_err}* List of LPR Servers</td>
       </tr>
       <tr>
        <td nowrap><b>Broadcast Address:</b></td>
        <td><input type="text" name="broadcast_addr" value="{$broadcast_addr}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$broadcast_addr_err}* 192.168.0.0</td>
       </tr>
       <tr>
        <td nowrap><b>Subnet Mask:</b></td>
        <td><input type="text" name="subnet_mask_addr" value="{$subnet_mask_addr}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$subnet_mask_addr_err}* 255.255.255.0</td>
       </tr>
       <tr>
        <td nowrap><b>Server Identification:</b></td>
        <td><input type="text" name="server_ident" value="{$server_ident}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$server_ident_err}* Use with caution</td>
       </tr>
       <tr>
        <td nowrap><b>DDNS Update Style:</b></td>
        <td><select name="ddns_update_style" style="width: 100%">{$ddns_update_style}<option value="---------">---------</option><option value="ad-hoc">ad-hoc</option><option value="interim">interim</option><option value="none">none</option></select></td>
        <td class="copyright" nowrap>{$ddns_update_style_err}* Select from list</td>
       </tr>
       <tr>
        <td nowrap><b>Authoritative:</b></td>
        <td><select name="authoritative" style="width: 100%">{$authoritative}<option value="---------">---------</option><option value="true">true</option><option value="false">false</option></select></td>
        <td class="copyright" nowrap>{$authoritative_err}* Authoritive type (boolean)</td>
       </tr>
       <tr>
        <td nowrap><b>BOOTP Option:</b></td>
        <td><select name="bootp" style="width: 100%">{$bootp}<option value="---------">---------</option><option value="true">true</option><option value="false">false</option></select></td>
        <td class="copyright" nowrap>{$bootp_err}* BOOTP Enabled (boolean)</td>
       </tr>
       <tr>
        <td><input type="hidden" name="id" value="{$id}"></td>
        <td><input type="submit" name="AddGlobalConfOpts" value="Add Options" rel="lightboxform">&nbsp;<input type="submit" name="EditGlobalConfOpts" value="Edit Options" rel="lightboxform">&nbsp;<input type="submit" name="DelGlobalConfOpts" value="Delete Options" onclick="return confirm( 'Are you sure you want to delete the global options?' )"></td>
        <td>&nbsp;</td>
       </tr>
							<tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetGlobalFields();'></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end Global DHCPD configuration template -->
