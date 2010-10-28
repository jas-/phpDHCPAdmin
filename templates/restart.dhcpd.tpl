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
      &nbsp;&nbsp;<b>Restart the ISC DHCPD service</b><br>
      This will assist you in restarting the ISC DHCPD service with updated configuration options based on changes you have made within the database.
      <div class="copyright">** Please review the new configuation data before proceeding</div>
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
        <td align="center"><input type="submit" name="RestartDHCPD" value="Restart with new configuration data" rel="lightboxform" {$disable}></td>
       </tr>
       <tr>
        <td>{$configdata_html}</td>
       </tr>
       <tr>
        <td align="center"><input type="submit" name="RestartDHCPD" value="Restart with new configuration data" rel="lightboxform" {$disable}></td>
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
