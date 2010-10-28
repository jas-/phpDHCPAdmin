<!-- Global DHCPD PXE/BOOTP configuration template -->
<form action="{$URL}" method="post" name="configPXE">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td>
      <a href="javascript:popUp('help/help.html#config_pxe','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Global DHCPD PXE/BOOTP Configuration Options</b><br>
      Please define the options available for the ISC DHCPD Global PXE/BOOTP configuration options
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
        <td nowrap><b>Enable Global PXE/BOOTP Options?</b></td>
        <td>TRUE: <input type="radio" name="pxe_enabled" value="true" {$pxe_enabled_true}>&nbsp;&nbsp;&nbsp;FALSE: <input type="radio" name="pxe_enabled" value="false" {$pxe_enabled_false}></td>
        <td class="copyright" nowrap>{$pxe_enabled_err}* Enable Options?</td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Option space:</b></td>
        <td><input type="text" name="option_space" value="{$option_space}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$option_space_err}* Option space PXE</td>
       </tr>
       <tr>
        <td nowrap><b>mtftp-ip code 1:</b></td>
        <td><input type="text" name="mtftp_ip" value="{$mtftp_ip}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$mtftp_ip_err}* PXE IP Code</td>
       </tr>
       <tr>
        <td nowrap><b>mtftp-cport code 2:</b></td>
        <td><input type="text" name="mtftp_cport" value="{$mtftp_cport}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$mtftp_cport_err}* PXE CPort Code</td>
       </tr>
       <tr>
        <td nowrap><b>mtftp-sport code 3:</b></td>
        <td><input type="text" name="mtftp_sport" value="{$mtftp_sport}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$mtftp_sport_err}* PXE SPort Code</td>
       </tr>
       <tr>
        <td nowrap><b>mtftp-tmout code 4:</b></td>
        <td><input type="text" name="mtftp_tmout" value="{$mtftp_tmout}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$mtftp_tmout_err}* PXE Timeout</td>
       </tr>
       <tr>
        <td nowrap><b>mtftp-delay code 5:</b></td>
        <td><input type="text" name="mtftp_delay" value="{$mtftp_delay}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$mtftp_delay_err}* PXE Delay Code</td>
       </tr>
       <tr>
        <td nowrap><b>discovery-control code 6:</b></td>
        <td><input type="text" name="discovery_control" value="{$discovery_control}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$discovery_control_err}* Discovery Control param</td>
       </tr>
       <tr>
        <td nowrap><b>discovery-mcast-addr code 7:</b></td>
        <td><input type="text" name="discovery_mcast_addr" value="{$discovery_mcast_addr}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$discovery_mcast_addr_err}* Discovery Multi-Cast param</td>
       </tr>
       <tr>
							 <td>&nbsp;</td>
        <td align="center"><input type="submit" name="AddPXEConfOpts" value="Add Options" rel="lightboxform">&nbsp;<input type="submit" name="EditPXEConfOpts" value="Edit Options" rel="lightboxform">&nbsp;<input type="submit" name="DelPXEConfOpts" value="Delete Options" onclick="return confirm( 'Are you sure you want to delete global PXE configuration?' )"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
       </tr>
							<tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetPXEFields();'></td>
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
<!-- end Global DHCPD PXE/BOOTP configuration template -->
