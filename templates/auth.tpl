<!-- authentication form -->
<table width="100%" border="0" cellpadding="5">
 <tr>
  <td colspan="2">{$LIBERROR}{$CONFIGERRORS}</td>
 </tr>
 <tr>
  <td width="38%" valign="top">
   <table width="100%" border="0" cellspacing="5" cellpadding="0">
    <form action="{$URL}" method="post" name="login">
     <tr>
      <td width="35%" nowrap><strong>User Name:</strong></td>
      <td><input type="text" name="user" size="15" maxlength="20" style="width: 100%"></td>
     </tr>
     <tr>
      <td nowrap><strong>Password:</strong></td>
      <td><input type="password" name="pass" size="15" maxlength="20" style="width: 100%"></td>
     </tr>
     <tr>
      <td valign="top">&nbsp;</td>
      <td><input type="submit" name="Login" value="Login" rel="lightboxform">&nbsp;&nbsp;<input type="reset" name="Reset" value="Reset"></td>
     </tr>
    </form>
   </table>
  </td>
  <td width="62%" valign="top"><b>{$ERROR}</b><br><b>This application is for authorized personel only!<br>Your IP address has been logged: {$IP_ADDRESS}</b><br><br>Your computer information has been logged to assist in the prevention of abuse to this system.</td>
 </tr>
</table>
<!-- end authentication form -->
