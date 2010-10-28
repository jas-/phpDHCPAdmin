<!-- manage application groups -->
<form name="editGroups" method="POST" action="{$URL}">
<table width="100%" cellspacing="0" border="0" cellpadding="0">
 <tr>
  <td align="left">
   <table border="0" width="100%">
    <tr>
     <td><a href="javascript:popUp('help/help.html#admin_groups','800','800')"><img src="templates/{$SKIN}/images/help02.jpg" border="0"></a>&nbsp;&nbsp;<b>Manage Groups:</b><br>Here you can add, edit or delete new groups. <div class="copyright">*Please note that you will need to have groups created before trying to assign users to a new group.</div></td>
    </tr>
    <tr>
     <td>{$message}</td>
    </tr>
    <tr>
     <td>
      <table border="0" width="100%">
       <tr>
        <td width="70%" valign="top">
         <table border="0" width="100%">
          <tr>
           <td colspan="3" nowrap><b>Group data:</b><div class="copyright">*Use this form to add/edit/delete groups.</div></td>
          </tr>
          <tr align="left">
           <td width="10%" nowrap><b>Name:</b></td>
           <td><input type="text" name="group_name" value="{$group_name}" style="width: 100%"></td>
           <td nowrap>{$group_name_err}* Unique identifier</td>
          </tr>
          <tr>
           <td nowrap><b>Manager:</b></td>
           <td><input type="text" name="group_manager" value="{$group_manager}" style="width: 100%"></td>
           <td nowrap>{$group_manager_err}* Manager name</td>
          </tr>
          <tr>
           <td nowrap><b>Phone:</b></td>
           <td><input type="text" name="group_contact" value="{$group_contact}" style="width: 100%"></td>
           <td nowrap>{$group_contact_err}* 123-123-1234</td>
          </tr>
          <tr>
           <td nowrap><b>Description:</b></td>
           <td><input type="text" name="group_description" value="{$group_description}" style="width: 100%"></td>
           <td nowrap>{$group_description_err}* A user group</td>
          </tr>
          <tr>
           <td><input type="hidden" name="group_id" value="{$group_id}"></td>
           <td align="center" nowrap><input name="AddGroup" type="submit" value="Add New Group" rel="lightboxform">&nbsp;<input type="submit" name="EditGroup" value="Edit Group" rel="lightboxform">&nbsp;<input type="submit" name="DelGroup" value="Delete Group" onclick="return confirm( 'Are you sure you want to delete the group data for {$group_name}?' )"></td>
          </tr>
										<tr>
           <td>&nbsp;</td>
           <td align="center"><input type="button" value="Clear Fields" onclick='ResetGroupFields();'></td>
           <td>&nbsp;</td>
          </tr>
         </table>
        </td>
        <td width="30%" valign="top">
         <table border="0" width="100%">
          <tr>
           <td><b>Select group:</b><div class="copyright">*Use this form to select a group to edit or delete.</div></td>
          </tr>
          <tr>
           <td>{$group_list}</td>
          </tr>
         </table>
        </td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end manage application groups -->