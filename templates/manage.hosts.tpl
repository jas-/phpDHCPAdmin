<!-- Host Search template -->
<form action="{$URL}" method="post" name="configHOST" id="configHOST">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="3">
      <a href="javascript:popUp('help/help.html#host','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Manage Hosts</b><br>
      You can manage statically assigned hosts here
     </td>
    </tr>
    <tr>
					<td colspan="2" align="center"><img src="templates/images/graphs/graph.hosts.php"></td>
    </tr>
				<tr>
					<td valign="top" colspan="5">
						<table width="100%" cellspacing="5" border="0" cellpadding="0" summary="informational">
							<tr>
        <td>
         <a href="javascript:popUp('help/help.html#host_availability','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Available IPv4 addresses per subnet</b><br>
         <div class="copyright">** List of currently available IPv4 addresses per subnet</div>
        </td>
       </tr>
							<tr>
								<td>
									<br>
									<div class="menu" align="center">
										<ul>
										 {$available}
										</ul>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
    <tr>
     <td colspan="3">
      {$error}
     </td>
    </tr>
    <tr>
     <td colspan="3">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="3">
         <a href="javascript:popUp('help/help.html#host_search','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Search for existing host to edit</b><br>
         <div class="copyright">** Use this to perform a quick search by IP/MAC/Hostname of existing client configuration</div>
        </td>
       </tr>
       <tr>
        <td width="2%" nowrap><b>Search:</b></td>
        <td><input type="text" name="search" name="SearchHosts" style="width: 82%">&nbsp;&nbsp;<input type="submit" name="srch" value="Search Hosts" rel="lightboxform"></td>
        <td nowrap>{$search_err}</td>
       </tr>
      </table>
     </td>
    </tr>
    <tr>
     <td width="60%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="3">
         <a href="javascript:popUp('help/help.html#host_form','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Add/Edit/Delete Hosts</b><br>
         <div class="copyright">** Here you can add/edit and delete static host configurations</div>
        </td>
       </tr>
       <tr>
        <td colspan="3">{$duplicate}</td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Hostname:</b></td>
        <td><input type="text" name="hostname" value="{$hostname}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$hostname_err}* my-machine</td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>IP Address:</b></td>
        <td><input type="text" name="ip_address" value="{$ip_address}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$ip_address_err}* 192.168.0.21</td>
       </tr>
       <tr>
        <td nowrap><b>MAC Address:</b></td>
        <td><input type="text" name="mac_address" value="{$mac_address}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$mac_address_err}* 00:ef:78:b0:ad:e4</td>
       </tr>
       <tr>
        <td nowrap><b>Assign Subnet:</b></td>
        <td>{$subnet_name}</td>
        <td class="copyright" nowrap>{$subnet_name_err}* Assign Subnet?</td>
       </tr>
       <tr>
        <td nowrap><b>Assign PXE Group:</b></td>
        <td>{$pxe_group}</td>
        <td class="copyright" nowrap>{$pxe_group_err}* Assign to PXE Group?</td>
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
        <td align="center"><input type="submit" name="AddHosts" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditHosts" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelHosts" value="Delete" onclick="return confirm( 'Are you sure you want to delete the host data for {$hostname}?' )"></td>
        <td><input type="hidden" name="id" value="{$id}"><input type="hidden" name="allow" value="{$allow}"></td>
       </tr>
       <tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetHostFields();'></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </td>
     <td valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#host_list','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Select an existing host to edit/delete</b><br>
         <div class="copyright">** List of currently defined static host configurations. Select existing host to edit. There are currently <b>'{$host_count}'</b> available.</div>
        </td>
       </tr>
       <tr>
        <td>{$hosts_list}</td>
        <td valign="top" class="copyright" nowrap>{$hosts_list_err}</td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end Search Hosts configuration template -->
