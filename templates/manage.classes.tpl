<!-- Manage Classes configuration template -->
<form action="{$URL}" method="post" name="configClasses">
<table width="100%" cellspacing="0" border="0" cellpadding="0" summary="main">
 <tr>
  <td>
   <table width="100%" cellspacing="0" border="0" cellpadding="0" summary="global">
    <tr>
     <td colspan="2">
      <a href="javascript:popUp('help/help.html#classes','800','800')">
       <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
      </a>
      &nbsp;&nbsp;<b>Manage Client Classes</b><br>
      Use this to manage various class types
     </td>
    </tr>
    <tr>
     <td colspan="2">
      {$error}
     </td>
    </tr>
    <tr>
     <td width="60%" valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="3">
         <a href="javascript:popUp('help/help.html#classes_form','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Add/Edit/Delete Classes</b><br>
         <div class="copyright">** Here you can add/edit and delete classes configuration options</div>
        </td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Class Name:</b></td>
        <td><input type="text" name="class_name" value="{$class_name}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$class_name_err}* Unique identifier</td>
       </tr>
       <tr>
        <td colspan="3"><a href="#" onclick="javascript:addOptions( 'options', 'optionsrow', '{$count}', document.getElementById( 'num' ).innerHTML )"><b>[+] Add class option</b></a>&nbsp;&nbsp;<div id="remove"></div><div id="num"></div>
         <table id="options" width="100%">
          <tr id="optionsrow">
           <script language="javascript">
            document.getElementById( 'num' ).style.visibility = 'hidden';
            var ClassOptList = {$encoded};
           </script>
											{$form}
          </tr>
         </table>
        </td>
       </tr>
       <!--<tr>
        <td nowrap><b>Match?</b></td>
        <td>True:&nbsp;<input type="radio" name="class_match" value="enable" {$class_match_enabled}>&nbsp;False:&nbsp;<input type="radio" name="class_match" value="disable" {$class_match_disabled}></td>
        <td class="copyright" nowrap>{$class_match_err}* Use matching?</td>
       </tr>
       <tr>
        <td nowrap><b>Substring?</b></td>
        <td>True:&nbsp;<input type="radio" name="class_substring" value="enable" {$class_substring_enabled}>&nbsp;False:&nbsp;<input type="radio" name="class_substring" value="disable" {$class_substring_disabled}></td>
        <td class="copyright" nowrap>{$class_substring_err}* Use substring?</td>
       </tr>
       <tr>
        <td width="5%" nowrap><b>Option:</b></td>
        <td>{class_option}</td>
        <td class="copyright" nowrap>{$class_option_err}* Select option</td>
       </tr>
       <tr>
        <td nowrap><b>Start & End?</b></td>
        <td>Start:&nbsp;<input type="text" name="class_val_start" value="{$class_val_start}" style="width: 5%">&nbsp;End:&nbsp;<input type="text" name="class_val_end" value="{$class_val_end}" style="width: 5%"></td>
        <td class="copyright" nowrap>{$class_val__err}* Specify start & end?</td>
       </tr>
       <tr>
        <td nowrap><b>Value:</b></td>
        <td><input type="text" name="class_value" value="{$class_value}" style="width: 100%"></td>
        <td class="copyright" nowrap>{$class_value_err}* Value of option selected</td>
       </tr>-->
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
        <td align="center"><input type="submit" name="AddClass" value="Add" rel="lightboxform">&nbsp;<input type="submit" name="EditClass" value="Edit" rel="lightboxform">&nbsp;<input type="submit" name="DelClass" value="Delete" onclick="return confirm( 'Are you sure you want to delete the Class {$class_name}?' )"></td>
        <td><input type="hidden" name="id" value="{$id}"></td>
       </tr>
							<tr>
        <td>&nbsp;</td>
        <td align="center"><input type="button" value="Clear Fields" onclick='ResetClassFields();'></td>
        <td>&nbsp;</td>
       </tr>
      </table>
     </td>
     <td valign="top">
      <table width="100%" cellspacing="5" border="0" cellpadding="0" summary="globalForm">
       <tr>
        <td colspan="2">
         <a href="javascript:popUp('help/help.html#config_class_list','800','800')">
          <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
         </a>
         &nbsp;&nbsp;<b>Existing Classes</b><br>
         <div class="copyright">** Select existing class to edit</div>
        </td>
       </tr>
       <tr>
        <td>{$classes}</td>
        <td valign="top" class="copyright" nowrap>{$classes_err}</td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>
<!-- end Manage Classes configuration template -->
