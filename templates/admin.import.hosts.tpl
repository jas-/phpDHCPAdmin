<!-- Administrative import static hosts template -->
<form action="{$URL}" method="post" name="ImportHosts" enctype="multipart/form-data">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="import">
    <tr>
     <td colspan="2">
      <a href="javascript:popUp('help/help.html#import_hosts','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Import Static Hosts</b><div class="copyright">This utility can assist you with importing large amounts of static host records</td>
    </tr>
    <tr>
     <td colspan="2">
      {$error}{$total}{$imported}{$errors}{$duplicates}
     </td>
    </tr>
    <tr>
     <td>
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="importForm">
       <tr>
        <td width="5%" NOWRAP><b>Select File to Import:</b></td>
        <td><input type="file" name="file_name" value="{$file_name}" style="width: 100%"></td>
        <td class="copyright" NOWRAP>{$file_name_err}* View help system for info</td>
       </tr>
       <tr>
        <td width="5%" NOWRAP>&nbsp;</td>
        <td><input type="checkbox" name="overwrite" value="overwrite"></td>
        <td class="copyright" NOWRAP>Overwrite duplicates?</td>
       </tr>
       <tr>
        <td>&nbsp;</td>
        <td><input type="submit" name="ImportHost" value="Import Records" rel="lightboxform"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
       </tr>
      </table>
     </td>
     </TD>
    </TR>
   </table>
  </td>
 </tr>
</table>
</form>
<form action="{$URL}" method="post" name="importHost">
{$error_template}
{$error_template_resub}
</form>
<!-- end Import hosts template -->
