<!-- Global Failover configuration template -->
<form action="{$URL}" method="post" name="configFAILOVER">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="2">
      <a href="javascript:popUp('help/help.html#config_failover','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Global Failover Configuration Options</b><br>
      <div class="copyright">** You may enable one failover client for use with the DHCPD service</div>
     </td>
    </tr>
    <tr>
     <td colspan="2">
      {$error}
     </td>
    </tr>
    <tr>
     <td width="5%" nowrap><b>Failover Peer Name:</b></td>
     <td><input type="text" name="peer_name" value="{$peer_name}" style="width: 100%"></td>
     <td class="copyright" nowrap>{$peer_name_err}* domain.com</td>
    </tr>
    <tr>
     <td nowrap><b>Primary?:</b></td>
      <td><select name="primary" style="width: 100%"><option value="-----------">-----------</option><option value="primary">Primary</option><option value="secondary">Secondary</option></td>
      <td class="copyright" nowrap>{$primary_err}* Primary or Secondary?</td>
     </tr>
     <tr>
      <td nowrap><b>Address:</b></td>
      <td><input type="text" name="address" value="{$address}" style="width: 100%">
      <td class="copyright" nowrap>{$address_err}* dhcp1.domain.com</td>
     </tr>
     <tr>
      <td nowrap><b>Port:</b></td>
      <td><input type="text" name="port" value="{$port}" style="width: 100%"></td></td>
      <td class="copyright" nowrap>{$port_err}* TCP Port 520</td>
     </tr>
     <tr>
      <td nowrap><b>Peer Address:</b></td>
      <td><input type="text" name="peer_address" value="{$peer_address}" style="width: 100%"></td></td>
      <td class="copyright" nowrap>{$peer_address_err}* dhcp2.domain.com</td>
     </tr>
     <tr>
      <td nowrap><b>Peer Port:</b></td>
      <td><input type="text" name="peer_port" value="{$peer_port}" style="width: 100%"></td></td>
      <td class="copyright" nowrap>{$peer_port_err}* TCP Port 519</td>
     </tr>
     <tr>
      <td nowrap><b>Max Delay:</b></td>
      <td><input type="text" name="max_response_delay" value="{$max_response_delay}" style="width: 100%"></td></td>
      <td class="copyright" nowrap>{$max_response_delay_err}* In seconds (30)</td>
     </tr>
     <tr>
      <td nowrap><b>Max Unacked Updates:</b></td>
      <td><input type="text" name="max_unacked_updates" value="{$max_unacked_updates}" style="width: 100%"></td></td>
      <td class="copyright" nowrap>{$max_unacked_updates_err}* Max unacked updates (10)</td>
     </tr>
     <tr>
      <td nowrap><b>Max Lead Time:</b></td>
      <td><input type="text" name="mclt" value="{$mclt}" style="width: 100%"></td></td>
      <td class="copyright" nowrap>{$mclt_err}* In seconds (5)</td>
     </tr>
     <tr>
      <td nowrap><b>Split:</b></td>
      <td><input type="text" name="split" value="{$split}" style="width: 100%"></td></td>
      <td class="copyright" nowrap>{$split_err}* Use with caution (128 is best practice)</td>
     </tr>
     <tr>
      <td nowrap><b>Load Max Seconds:</b></td>
      <td><input type="text" name="load_balance_max_seconds" value="{$load_balance_max_seconds}" style="width: 100%"></td></td>
      <td class="copyright" nowrap>{$load_balance_max_seconds_err}* In secons (30)</td>
     </tr>
     <tr>
      <td>&nbsp;</td>
      <td align="center"><input type="submit" name="AddFailOverOpts" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditFailOverOpts" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelFailOverOpts" value="Delete" onclick="return confirm( 'Are you sure you want to delete the Failover Options?' )"></td>
      <td><input type="hidden" name="id" value="{$id}"></td>
     </tr>
					<tr>
      <td>&nbsp;</td>
      <td align="center"><input type="button" value="Clear Fields" onclick='ResetFailOverFields();'></td>
      <td>&nbsp;</td>
     </tr>
    </table>
   </TD>
 </tr>
</table>
</form>
<!-- end Global DHCPD DNSSEC configuration template -->
