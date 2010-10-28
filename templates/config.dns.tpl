<!-- Global DNS configuration template -->
<form action="{$URL}" method="post" name="configDNS">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="2">
      <a href="javascript:popUp('help/help.html#config_dns','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Global DHCPD DNS Configuration Options</b><br>
      Please define the options available for the ISC DHCPD Global DNS configuration options
     </td>
    </tr>
    <tr>
     <td colspan="2">
      {$error}
     </td>
    </tr>
    <tr>
     <td width="60%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#config_dns_form','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Add/Edit/Delete DNS zones</b><br>
         <div class="copyright">** Make modifications and add new DNS zones</div>
        </td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Zone:</b></td>
        <td><input type="text" name="zone" value="{$zone}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$zone_err}* domain.com</td>
       </tr>
       <tr>
        <td nowrap><b>Primary:</b></td>
        <td><input type="text" name="primary" value="{$primary}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$primary_err}* server.domain.com</td>
       </tr>
       <tr>
       <tr>
        <td nowrap><b>Use DNSSEC?:</b></td>
        <td>TRUE:<input type="radio" name="dnssec_enabled" value="true" {$dnssec_enabled_true}>&nbsp;&nbsp;FALSE:<input type="radio" name="dnssec_enabled" value="false" {$dnssec_enabled_false}></td>
        <td class="copyright" nowrap>{$dnssec_enabled_err}* Enable DNSSEC?</td>
       </tr>
       <tr>
        <td nowrap><b>DNSSEC Key:</b></td>
        <td>{$dnssec_key}</td>
        <td class="copyright" nowrap>{$dnssec_key_err}* Select key</td>
       </tr>
        <td>&nbsp;</td>
        <td align="center"><input type="submit" name="AddDNSConfOpts" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditDNSConfOpts" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelDNSConfOpts" value="Delete" onclick="return confirm( 'Are you sure you want to delete the DNS zone {$zone}?' )"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
       </tr>
							<tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetDNSFields();'></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </td>
     <td valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td>
         <a href="javascript:popUp('help/help.html#config_dns_list','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Select an existing DNS zone to edit/delete</b><br>
         <div class="copyright">** List of currently defined DNS zones</div>
        </td>
       </tr>
       <tr>
        <td>{$dns_opt}</td>
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
