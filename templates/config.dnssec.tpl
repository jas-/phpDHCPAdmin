<!-- Global DNSSEC configuration template -->
<form action="{$URL}" method="post" name="configDNSSEC">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="2">
      <a href="javascript:popUp('help/help.html#config_dnssec','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Global DHCPD DNSSEC Configuration Options</b><br>
      Please define the options available for the ISC DHCPD Global DNSSEC configuration options
      <div class="copyright">** This configuration requires the ISC Bind service installed and the available use of the 'dnssec-keygen' utility</div>
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
        <td colspan="3">
         <a href="javascript:popUp('help/help.html#config_dnssec_form','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Add/Edit/Delete DNSSEC Keys</b><br>
         <div class="copyright">** Use this form to add/edit/delete DNSSEC keys</div>
        </td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Key Name:</b></td>
        <td><input type="text" name="key_name" value="{$key_name}" style="width: 100%"{$disabled}></td>
        <td class="copyright" nowrap>{$key_name_err}* Enter Unique Key Name</td>
       </tr>
       <tr>
        <td nowrap><b>Algorithm:</b></td>
        <td>{$algorithm}</td>
        <td class="copyright" nowrap>{$algorithm_err}* Select Algorithm Type</td>
       </tr>
       <tr>
        <td nowrap><b>Passphrase:</b></td>
        <td><input type="text" name="key" value="{$key}" style="width: 100%"{$disabled}>
        <td class="copyright" nowrap>{$key_err}* See dnssec-keygen util</td>
       </tr>
       <tr>
        <td>&nbsp;</td>
        <td align="center"><input type="submit" name="AddDNSSECConfOpts" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditDNSSECConfOpts" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelDNSSECConfOpts" value="Delete" onclick="return confirm( 'Are you sure you want to delete the DNSSEC key data for {$key_name}?' )"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
       </tr>
							<tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetDNSSECFields();'></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </TD>
     <td valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td>
         <a href="javascript:popUp('help/help.html#config_dnssec_list','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Select an existing key to edit/delete</b><br>
         <div class="copyright">** List of currently defined DNSSEC keys</div>
        </td>
       </tr>
       <tr>
        <td>{$dnssec_opt}</td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end Global DHCPD DNSSEC configuration template -->
