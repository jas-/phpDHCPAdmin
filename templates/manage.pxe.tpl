<!-- Add PXE Group configuration template -->
<form action="{$URL}" method="post" name="configPXE">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="2">
      <a href="javascript:popUp('help/help.html#config_pxegroup','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Manage PXE Groups</b><br>
      Use this to manage multiple PXE Groups configurations
     </td>
    </tr>
    <tr>
     <td colspan="2">
      {$error}
     </td>
    </tr>
    <tr>
     <td colspan="2" align="center">
      <img src="templates/images/graphs/graph.pxe.php">
     </td>
    </tr>
    <tr>
     <td width="60%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#config_pxegroup_form','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Add/Edit/Delete PXE Groups</b><br>
         <div class="copyright">** Here you can add/edit and delete PXE Group configuration options</div>
        </td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>PXE Group Name:</b></td>
        <td><input type="text" name="pxe_group_name" value="{$pxe_group_name}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$pxe_group_name_err}* Unique identifier</td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>PXE Server:</b></td>
        <td><input type="text" name="pxe_server" value="{$pxe_server}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$pxe_server_err}* 192.168.0.5</td>
       </tr>
       <tr>
        <td nowrap><b>BOOTP File Name:</b></td>
        <td><input type="text" name="bootp_filename" value="{$bootp_filename}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$bootp_filename_err}* tftpd bootp filename<br>ex. /tftproot/pxelinux.0</td>
       </tr>
       <tr>
        <td nowrap><b>Assign to Subnet:</b></td>
        <td>{$assign_subnet}</td>
        <td class="copyright" nowrap>{$assign_subnet_err}* Assign to subnet?</td>
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
        <td align="center"><input type="submit" name="AddPXEGroup" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditPXEGroup" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelPXEGroup" value="Delete" onclick="return confirm( 'Are you sure you want to delete the PXE Group {$pxe_group_name}? WARNING!! This will make modifications to any static host assigned to the PXE Group {$pxe_group_name}!!!' )"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
       </tr>
							<tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetPXEGroupFields();'></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </td>
     <td valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#config_pxegroup_list','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Select an existing PXE Group to edit/delete</b><br>
         <div class="copyright">** List of currently defined PXE Group configurations. Select existing PXE Group to edit</div>
        </td>
       </tr>
       <tr>
        <td>{$pxe_groups}</td>
        <td valign="top" class="copyright" nowrap>{$pxe_groups_err}</td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end PXE Groups configuration template -->
