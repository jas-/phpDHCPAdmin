<!-- Host Search template -->
<form action="{$URL}" method="post" name="configLEASES">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="3">
      <a href="javascript:popUp('help/help.html#leases','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Manage Leases</b><br>
      Remove, set active state on client leases
     </td>
    </tr>
    <tr>
					<td colspan="2" align="center"><img src="templates/images/graphs/graph.leases.php"></td>
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
         <a href="javascript:popUp('help/help.html#lease_search','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Search for existing lease</b><br>
         <div class="copyright">** Use this to perform a quick search by IP/MAC/Hostname of existing lease status & configuration</div>
        </td>
       </tr>
       <tr>
							 <td>
								 <table border="0" cellpadding="0" cellspacing="2">
          <tr>
           <td width="2%" nowrap><b>Search:</b></td>
           <td nowrap><input type="text" name="search" name="SearchLeases" style="width: 95%"></td>
											<td nowrap><b>Start:</b></td>
											<td nowrap><input type="text" name="startdate" style="width: 85%" id="startdate"><a href="#" onclick="return showCalendar('startdate', '%Y/%m/%d %H:%M:%S');">[X]</a></td>
											<td nowrap><b>End:</b></td>
											<td nowrap><input type="text" name="enddate" style="width: 85%" id="enddate"><a href="#" onclick="return showCalendar('enddate', '%Y/%m/%d %H:%M:%S');">[X]</a></td>
           <td nowrap><input type="submit" name="SrchLeases" value="Search Leases"></td>
           <td nowrap>{$search_err}</td>
          </tr>
									</table>
								</td>
							</tr>
      </table>
     </td>
    </tr>
    <tr>
     <td width="60%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="3">
         <a href="javascript:popUp('help/help.html#lease_form','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Add/Edit/Delete Client Leases</b><br>
         <div class="copyright">** Here you can add/edit and delete client lease configurations</div>
        </td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>IP Address:</b></td>
        <td><input type="text" name="ip" value="{$ip}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$ip_err}* 192.168.0.21</td>
       </tr>
							<tr>
        <td nowrap><b>MAC Address:</b></td>
        <td><input type="text" name="hardware" value="{$hardware}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$hardware_err}* 00:ef:78:b0:ad:e4</td>
       </tr>
							<tr>
        <td width="5%" nowrap><b>Hostname:</b></td>
        <td><input type="text" name="hostname" value="{$hostname}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$hostname_err} my-machine</td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Current State:</b></td>
        <td>ACTIVE:<input type="radio" name="state" value="active" {$state_true}>&nbsp;&nbsp;FREE:<input type="radio" name="state" value="free" {$state_false}></td>
        <td class="copyright" nowrap>{$state_err} Set state</td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Next State:</b></td>
        <td>ACTIVE:<input type="radio" name="next_state" value="active" {$next_state_true}>&nbsp;&nbsp;FREE:<input type="radio" name="next_state" value="free" {$next_state_false}></td>
        <td class="copyright" nowrap>{$next_state_err} Set state</td>
       </tr>
       <tr>
        <td nowrap><b>Lease Start Time:</b></td>
        <td nowrap><input type="text" name="start" value="{$start}" style="width: 85%" id="start"><a href="#" onclick="return showCalendar('start', '%Y/%m/%d %H:%M:%S');">[X]</a></td>
        <td class="copyright" nowrap>{$start_err}* yyyy/mm/dd hh:mm:ss</td>
       </tr>
       <tr>
        <td nowrap><b>Lease End Time:</b></td>
        <td nowrap><input type="text" name="end" value="{$end}" style="width: 85%" id="end"><a href="#" onclick="return showCalendar('end', '%Y/%m/%d %H:%M:%S');">[X]</a></td>
        <td class="copyright" nowrap>{$end_err}* yyyy/mm/dd hh:mm:ss</td>
       </tr>
							<tr>
        <td nowrap><b>CLTT:</b></td>
        <td nowrap><input type="text" name="cltt" value="{$cltt}" style="width: 85%" id="cltt"><a href="#" onclick="return showCalendar('cltt', '%Y/%m/%d %H:%M:%S');">[X]</a></td>
        <td class="copyright" nowrap>{$cltt_err} yyyy/mm/dd hh:mm:ss</td>
       </tr>
							<tr>
							 <td colspan="3" align="center" nowrap><b>Need some extra lease options?</b>&nbsp;&nbsp;<a href="javascript:switchid( 'extras' );">[+]</a>&nbsp;&nbsp;<a href="javascript:hidediv( 'extras' );">[-]</a></td>
							</tr>
							<tr>
							 <td colspan="3">
							  <div id="extras">
							   <table>
							    <tr>
            <td nowrap><b>Abandoned?:</b></td>
            <td>TRUE:<input type="radio" name="abandoned" value="true" {$abandoned_true}>&nbsp;&nbsp;FALSE:<input type="radio" name="abandoned" value="false" {$abandoned_false}></td>
            <td class="copyright" nowrap>{$abandoned_err} true</td>
           </tr>
							    <tr>
            <td nowrap><b>Circut ID:</b></td>
            <td><input type="text" name="circut_id" value="{$circut_id}" style="width: 100%"></td>
            <td class="copyright" nowrap>{$circut_id_err} true</td>
           </tr>
							    <tr>
            <td nowrap><b>Remote ID:</b></td>
            <td><input type="text" name="remote_id" value="{$remote_id}" style="width: 100%"></td>
            <td class="copyright" nowrap>{$remote_id_err} true</td>
           </tr>
							    <tr>
            <td nowrap><b>DDNS-Text:</b></td>
            <td><input type="text" name="ddns_text" value="{$ddns_text}" style="width: 100%"></td>
            <td class="copyright" nowrap>{$ddns_text_err} true</td>
           </tr>
							    <tr>
            <td nowrap><b>DDNS-FWD-Name:</b></td>
            <td><input type="text" name="ddns_fwd_name" value="{$ddns_fwd_name}" style="width: 100%"></td>
            <td class="copyright" nowrap>{$ddns_fwd_name_err} true</td>
           </tr>
							    <tr>
            <td nowrap><b>DDNS-Client-FQDN:</b></td>
            <td><input type="text" name="ddns_client_fqdn" value="{$ddns_client_fqdn}" style="width: 100%"></td>
            <td class="copyright" nowrap>{$ddns_client_fqdn_err} true</td>
           </tr>
							    <tr>
            <td nowrap><b>DDNS-REV-Name:</b></td>
            <td><input type="text" name="ddns_rev_name" value="{$ddns_rev_name}" style="width: 100%"></td>
            <td class="copyright" nowrap>{$ddns_rev_name_err} true</td>
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
        <td colspan="2" align="right"><input type="submit" name="AddLease" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditLease" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelLease" value="Delete" onclick="return confirm( 'Are you sure you want to delete the lease data for {$ip}?' )"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
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
         <a href="javascript:popUp('help/help.html#lease_list','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Leases List</b><br>
         <div class="copyright">** List of currently lease data. Select existing lease to edit</div>
        </td>
       </tr>
       <tr>
        <td>{$lease_list}</td>
        <td valign="top" class="copyright" nowrap>{$lease_list_err}</td>
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
