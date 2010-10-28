<!-- manage application users -->
<form name="editUsers" method="POST" action="{$URL}">
<table width="100%" cellspacing="0" border="0" cellpadding="0">
 <tr>
  <td align="left">
   <table border="0" width="100%">
    <tr>
     <td><a href="javascript:popUp('help/help.html#admin_users','800','800')"><img src="templates/{$SKIN}/images/help02.jpg" border="0"></a>&nbsp;&nbsp;<b>Manage Users:</b><br>Here you can add, edit or delete new users. <div class="copyright">*Please note that you will need to have users created before trying to assign users to a new user.</div></td>
    </tr>
    <tr>
     <td>{$message}</td>
    </tr>
    <tr>
     <td>
      <table border="0" width="100%">
       <tr>
        <td width="50%" valign="top">
         <table border="0" width="100%">
          <tr>
           <td colspan="3" nowrap><a href="javascript:popUp('help/help.html#admin_users','800','800')"><img src="templates/{$SKIN}/images/help02.jpg" border="0"></a>&nbsp;&nbsp;<b>User data:</b><div class="copyright">*Use this form to add/edit/delete users.</div></td>
          </tr>
          <tr align="left">
           <td width="10%" nowrap><b>Username:</b></td>
           <td><input type="text" name="user_username" value="{$user_username}" style="width: 100%"></td>
           <td nowrap>{$user_username_err} Username</td>
          </tr>
          <tr>
           <td nowrap><b>First Name:</b></td>
           <td><input type="text" name="user_fname" value="{$user_fname}" style="width: 100%"></td>
           <td nowrap>{$user_fname_err} Users first name</td>
          </tr>
          <tr>
           <td nowrap><b>Last Name:</b></td>
           <td><input type="text" name="user_lname" value="{$user_lname}" style="width: 100%"></td>
           <td nowrap>{$user_lname_err} Users last name</td>
          </tr>
          <tr>
           <td nowrap><b>Access Level:</b></td>
           <td>{$user_access_level}</td>
           <td nowrap>{$user_access_level_err} Access level?</td>
          </tr>
          <tr>
           <td nowrap><b>Group:</b></td>
           <td>{$user_group}</td>
           <td nowrap>{$user_group_err}* User group?</td>
          </tr>
          <tr>
           <td nowrap><b>Department:</b></td>
           <td><input type="text" name="user_department" value="{$user_department}" style="width: 100%"></td>
           <td nowrap>{$user_department_err} Enter department</td>
          </tr>
          <tr>
           <td nowrap><b>Phone:</b></td>
           <td><input type="text" name="user_phone" value="{$user_phone}" style="width: 100%"></td>
           <td nowrap>{$user_phone_err} 123-123-1234</td>
          </tr>
          <tr>
           <td nowrap><b>Email:</b></td>
           <td><input type="text" name="user_email" value="{$user_email}" style="width: 100%"></td>
           <td nowrap>{$user_email_err} user@email.com</td>
          </tr>
          <tr>
										 <td>&nbsp;</td>
           <td align="center" nowrap><input name="AddUser" type="submit" value="Add New User" rel="lightboxform">&nbsp;<input type="submit" name="EditUser" value="Edit User" rel="lightboxform">&nbsp;<input type="submit" name="DelUser" value="Delete User" onclick="return confirm( 'Are you sure you want to delete the user data for '{$user_username}'?' )"></td>
											<td>&nbsp;</td>
          </tr>
										<tr>
           <td>&nbsp;</td>
           <td align="center"><input type="button" value="Clear Fields" onclick='ResetUserFields();'></td>
           <td>&nbsp;</td>
          </tr>
         </table>
        </td>
        <td valign="top">
         <table border="0" width="100%">
          <tr>
           <td><a href="javascript:popUp('help/help.html#admin_user_list','800','800')"><img src="templates/{$SKIN}/images/help02.jpg" border="0"></a>&nbsp;&nbsp;<b>Select user:</b><div class="copyright">*Use this form to select a user to edit or delete.</div></td>
          </tr>
          <tr>
           <td>{$user_list}</td>
          </tr>
          <tr>
           <td><input type="hidden" name="user_id" value="{$user_id}"></td>
          </tr>
										<tr>
           <td>
												<table border="0" width="100%">
             <tr>
              <td colspan="3"><a href="javascript:popUp('help/help.html#admin_changepw','800','800')"><img src="templates/{$SKIN}/images/help02.jpg" border="0"></a>&nbsp;&nbsp;<b>Change Password:</b><div class="copyright">*Use this form to select reset users passwords.</div></td>
             </tr>
             <tr>
              <td colspan="2">{$user_pw_list}</td>
              <td>{$user_pw_list_err}</td>
             </tr>
             <tr>
														<td colspan="3" nowrap>Generate random password?&nbsp<input type="checkbox" name="GenRandomPw" value="GenRandomPw"></td>
             </tr>
             <tr>
              <td nowrap><b>Password:</b></td>
              <td><input type="password" name="user_pw_1" value="************" style="width: 100%" onclick="clickclear( this, '************' )" onblur="clickclear( this, '************' )" onkeyup="clickclear( this, '************' )"></td>
              <td nowrap>{$user_pw_1_err}</td>
             </tr>
             <tr>
              <td nowrap><b>Confirm:</b></td>
              <td><input type="password" name="user_pw_2" value="************" style="width: 100%" onclick="clickclear( this, '************' )" onblur="clickclear( this, '************' )" onkeyup="clickclear( this, '************' )"></td>
              <td nowrap>{$user_pw_2_err}</td>
             </tr>
													<tr>
   										 <td>&nbsp;</td>
              <td align="center" nowrap><input name="ResetPassword" type="submit" value="Reset Password" rel="lightboxform"></td>
	   										<td>&nbsp;</td>
             </tr>
             <tr>
              <td colspan="3"><input type="hidden" name="user_id" value="{$user_id}"></td>
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
  </td>
 </tr>
</table>
</form>
<!-- end manage application users -->