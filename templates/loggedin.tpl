<!-- login splash data -->
<table width="100%" border="0" cellpadding="5">
 <tr>
  <td width="100%" valign="top">{$LIBERROR}{$message}{$FORM}</td>
 <tr>
  <tr>
  <td height="20">&nbsp;</td>
 </tr>
 <tr>
  <td><b>>> SERVICE STATUS:</b> {$dhcpd_status}</td>
 </tr>
 <tr>
  <td height="20">&nbsp;</td>
 </tr>
 <tr>
  <td width="100%" valign="top"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Welcome to the phpDHCPAmin application. I was designed to assist in you management of the ISC DHCPD service.</b></td>
 </tr>
 <tr>
  <td height="5">&nbsp;</td>
 </tr>
 <!--<tr>
  <td class="btmTableBdr_2" height="5">
   <b>&nbsp;&nbsp;&spades;&nbsp;View graphical data</b>&nbsp;&nbsp;<a href="javascript:showdiv( 'graphs' );">[+]</a>&nbsp;&nbsp;<a href="javascript:hidediv( 'graphs' );">[-]</a>
 </td>
 </tr>
	<tr>
		<td width="100%" valign="top" align="center">
   <div id="graphs">
   <div style="width: 100%; height: 210px;">
    <div class="graphs">
     <img src="templates/images/graphs/graph.traffic.php" />
    </div>
    <div class="graphs">
     <img src="templates/images/graphs/graph.leases.php" />
    </div>
    <div class="graphs">
     <img src="templates/images/graphs/graph.subnets.php" />
    </div>
    <div class="graphs">
     <img src="templates/images/graphs/graph.pxe.php" />
    </div>
   </div>
   <div id="graphsmenu" class="paginationstyle" style="width: 100%">
    <a href="#" rel="previous"><</a> <span class="flatview"></span> <a href="#" rel="next">></a>
   </div>
   <script type="text/javascript">
    var gallery=new virtualpaginate("graphs", 1)
    gallery.buildpagination("graphsmenu", ["Traffic Per Interface", "Leases Per Scope", "Hosts Per Subnet", "Hosts Per PXE Group" ])
   </script>
   </div>
  </td>
	</tr>
 <tr>
  <td height="5">&nbsp;</td>
 </tr>-->
	<tr>
	 <td class="btmTableBdr_2" height="5">
   <b>&nbsp;&nbsp;&spades;&nbsp;View subnet data</b>&nbsp;&nbsp;<a href="javascript:showdiv( 'subnets' );">[+]</a>&nbsp;&nbsp;<a href="javascript:hidediv( 'subnets' );">[-]</a>
		</td>
 </tr>
 <tr>
  <td>
   <div id="subnets">
   <table width="100%" cellspacing="5" border="0" cellpadding="0">
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
   </div>
 	</td>
	</tr>
 <tr>
  <td height="5">&nbsp;</td>
 </tr>
	<tr>
	 <td class="btmTableBdr_2" height="5">
   <b>&nbsp;&nbsp;&spades;&nbsp;View available interfaces</b>&nbsp;&nbsp;<a href="javascript:showdiv( 'adapters' );">[+]</a>&nbsp;&nbsp;<a href="javascript:hidediv( 'adapters' );">[-]</a>
		</td>
 </tr>
 <tr>
  <td>
   <div id="adapters">
				<table width="100%" cellspacing="5" border="0" cellpadding="0">
     <tr>
      <td>
       <a href="javascript:popUp('help/help.html#config_subnet_adapters','800','800')">
        <img src="templates/{$SKIN}/images/help02.jpg" border="0" alt="">
       </a>
       &nbsp;&nbsp;<b>Adapters and their Broadcast address</b><br>
       <div class="copyright">** List of currently defined interfaces and the broadcast address they are using</div>
      </td>
     </tr>
     <tr>
      <td>{$adapters}</td>
     </tr>
    </table>
			</div>
   </td>
		</tr>
</table>
<!-- end login splash data -->
