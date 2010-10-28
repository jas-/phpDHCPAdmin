<!-- authentication form -->
<table width="100%" border="0" cellpadding="5">
 <tr>
  <td valign="top">
   <table width="100%" border="0" cellspacing="5" cellpadding="0">
    <form action="{$page}" method="post" name="login">
     <tr>
      <td colspan="3"><b>{$ERROR}</b><br><b>You will need to reset the temporary password to a more permanant one.</td>
     </tr>
     <tr>
      <td width="50" nowrap><b>Password:</b></td>
      <td width="150"><input type="password" name="user_pw_1" size="15" maxlength="20" style="width: 100%"></td>
      <td>*{$user_pw_1_err}</td>
     </tr>
     <tr>
      <td nowrap><b>Confirm Password:</b></td>
      <td><input type="password" name="user_pw_2" size="15" maxlength="20" style="width: 100%"></td>
      <td>*{$user_pw_2_err}</td>
     </tr>
     <tr>
      <td valign="top"><input type="hidden" name="user_pw_list" value="{$username}"></td>
      <td><input type="submit" name="ResetPassword" value="Reset Password" rel="lightboxform"></td>
     </tr>
    </form>
   </table>
  </td>
 </tr>
</table>
<!-- end authentication form -->
