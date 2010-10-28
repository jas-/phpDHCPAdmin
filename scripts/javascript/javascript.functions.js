<!--

// calculate page load times
var began_loading = ( new Date() ).getTime();
function LoadTime() {
	document.getElementById( 'loadTimer' ).innerHTML = '<b>Load Time:</b> ' + ( ( ( new Date() ).getTime() - began_loading ) / 1000 ) + 'ms';
}

// menu expand contract
//window.onload = montre;
function montre( id ) {
var d = document.getElementById( id );
	for( var i = 1; i <= 10; i++ ) {
		if( document.getElementById( 'smenu' + i ) ) { document.getElementById( 'smenu' + i ).style.display = 'none'; }
	}
 if( d ) { d.style.display = 'block'; }
}

// password field clear
function clickclear( thisfield, defaulttext ) {
 if( thisfield.value == defaulttext ) {
  thisfield.value = "";
 }
}

var ids = new Array( 'extras' );

function switchid( id ){
	hideallids();
	showdiv( id );
}

function hideallids(){
	//loop through the array and hide each element by id
	for( var i = 0; i < ids.length; i++ ) {
		hidediv( ids[i] );
	}		  
}

function hidediv( id ) {
	//safe function to hide an element with a specified id
	if( document.getElementById ) { // DOM3 = IE5, NS6
		document.getElementById( id ).style.display = 'none';
	} else {
		if( document.layers ) { // Netscape 4
			document.id.display = 'none';
		}	else { // IE 4
			document.all.id.style.display = 'none';
		}
	}
}

function showdiv( id ) {
	//safe function to show an element with a specified id
		  
	if( document.getElementById ) { // DOM3 = IE5, NS6
		document.getElementById( id ).style.display = 'block';
	}	else {
		if( document.layers ) { // Netscape 4
			document.id.display = 'block';
		}	else { // IE 4
			document.all.id.style.display = 'block';
		}
	}
}

// reset global fields
function ResetGlobalFields() {
 document.configGlobal.domain_name.value = '';
 document.configGlobal.dns_server_list.value = '';
 document.configGlobal.default_lease_time.value = '';
 document.configGlobal.max_lease_time.value = '';
 document.configGlobal.time_offset.value = '';
 document.configGlobal.routers.value = '';
 document.configGlobal.lpr_server_list.value = '';
 document.configGlobal.broadcast_addr.value = '';
 document.configGlobal.subnet_mask_addr.value = '';
 document.configGlobal.server_ident.value = '';
 document.configGlobal.ddns_update_style.value = '---------';
 document.configGlobal.authoritative.value = '---------';
 document.configGlobal.bootp.value = '---------';
}

// reset pxe config fields
function ResetPXEFields() {
 document.configPXE.option_space.value = '';
 document.configPXE.mtftp_ip.value = '';
 document.configPXE.mtftp_cport.value = '';
 document.configPXE.mtftp_sport.value = '';
 document.configPXE.mtftp_tmout.value = '';
 document.configPXE.mtftp_delay.value = '';
 document.configPXE.discovery_control.value = '';
 document.configPXE.discovery_mcast_addr.value = '';
}

// reset dns zone config fields
function ResetDNSFields() {
 document.configDNS.zone.value = '';
 document.configDNS.type.value = '';
 document.configDNS.file_name.value = '';
 document.configDNS.dnssec_key.value = '---------------';
 document.configDNS.allow_update.value = '';
}

// reset dnssec config fields
function ResetDNSSECFields() {
 document.configDNSSEC.key_name.value = '';
 document.configDNSSEC.algorithm.value = '---------------';
 document.configDNSSEC.key.value = '';
 document.configDNSSEC.key_bit.value = '';
}

// reset the failover options
function ResetFailOverFields() {
 document.configFAILOVER.peer_name.value = '';
 document.configFAILOVER.primary.value = '-----------';
 document.configFAILOVER.address.value = '';
 document.configFAILOVER.port.value = '';
 document.configFAILOVER.peer_address.value = '';
 document.configFAILOVER.peer_port.value = '';
 document.configFAILOVER.max_response_delay.value = '';
 document.configFAILOVER.max_unacked_updates.value = '';
 document.configFAILOVER.mclt.value = '';
 document.configFAILOVER.split.value = '';
 document.configFAILOVER.load_balance_max_seconds.value = '';
}

// reset subnets fields
function ResetSubnetFields() {
 document.configSUBS.subnet_name.value = '';
 document.configSUBS.subnet.value = '';
 document.configSUBS.subnet_mask.value = '';
 document.configSUBS.dns_server_1.value = '';
	document.configSUBS.dns_server_2.value = '';
	document.configSUBS.router.value = '';
	document.configSUBS.scope_range_1.value = '';
	document.configSUBS.scope_range_2.value = '';
}

// reset pxe groups fields
function ResetPXEGroupFields() {
 document.configPXE.pxe_group_name.value = '';
 document.configPXE.pxe_server.value = '';
 document.configPXE.bootp_filename.value = '';
 document.configPXE.assign_subnet.value = '---------------';
}

// reset host fields
function ResetHostFields() {
 document.configHOST.hostname.value = '';
 document.configHOST.ip_address.value = '';
 document.configHOST.mac_address.value = '';
 document.configHOST.subnet_name.value = '---------------';
 document.configHOST.pxe_group.value = '---------------';
}

// reset group fields
function ResetGroupFields() {
 document.editGroups.group_name.value = '';
 document.editGroups.group_manager.value = '';
 document.editGroups.group_contact.value = '';
 document.editGroups.group_description.value = '';
}

// reset user fields
function ResetUserFields() {
 document.editUsers.user_username.value = '';
 document.editUsers.user_password_1.value = '************';
	document.editUsers.user_password_2.value = '************';
 document.editUsers.user_fname.value = '';
 document.editUsers.user_lname.value = '';
	document.editUsers.user_access_level.value = '---------------';
	document.editUsers.user_group.value = '---------------';
	document.editUsers.user_address.value = '';
	document.editUsers.user_phone.value = '';
	document.editUsers.user_email.value = '';
}

// jump menu
function jumpMenu( targ, selObj, restore ){
 eval( targ + ".location = '" + selObj.options[ selObj.selectedIndex ].value + "'" );
 if( restore ) selObj.selectedIndex = 0;
}

// new window popup
function popUp( URL, WIDTH, HEIGHT ) {
 day = new Date();
 id = day.getTime();
 eval( "page" + id + " = window.open( URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=0,width=" + WIDTH + ",height=" + HEIGHT + "');" );
}

// preloader for images
function preLoader( array ) {
 imageObj = new Image();
 for( var i = 0; i < array.length; i++ ) {
  imageObj.src = array[i];
 }
}

// generic reload page
function reloadPage( init ) {
 if( init==true ) with ( navigator ) {
  if ( ( appName == "Netscape" ) && ( parseInt( appVersion ) == 4 ) ) {
   document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage;
  }
 } else if( innerWidth != document.MM_pgW || innerHeight!=document.MM_pgH ) location.reload();
}
reloadPage( true );

// setup new item elements row
var row_no = 1;
function addOptions( tbl, row, num, val ) {
 
 if( num != '' ) { num++; row_no = num; }
	if( val != '' ) { row_no = val; }
 
	// index and table locations
 var tbl = document.getElementById( tbl );
 var rowIndex = document.getElementById( row ).value;
 
 // create our row header with removal
 var headerRow = tbl.insertRow( 0 );
 
 // create our selectbox object
 var optionsRow = tbl.insertRow( 1 );
 var selectOpts = document.createElement( 'select' );

 // match radio buttson
 var matchRow = tbl.insertRow( 2 );
 var match1 = document.createElement('input');
 var match2 = document.createElement('input');

 // match select element
 var matchRow2 = tbl.insertRow( 3 );
 var selectMatch = document.createElement('select');
 
 // create option elements
 var optionsList = new Array("if","option","pick-first-value");

 // substr radio buttons
 var substringRow = tbl.insertRow( 4 );
 var substr1 = document.createElement('input');
 var substr2 = document.createElement('input');

 // substr value elements
 var substringRow2 = tbl.insertRow( 5 );
 var substr3 = document.createElement('input');
 var substr4 = document.createElement('input');

 // substring regex string element
 var substringRow3 = tbl.insertRow( 6 );
 var substr5 = document.createElement('input');
 substr5.setAttribute( 'name', 'options[' + row_no + '][substr_regex]' );
 substr5.setAttribute('style', 'width: 100%' );

 if( row_no <= 20 ) {
  
  var remove = '<a href="#" onclick="removeRow(\''+ tbl +'\',\'' + row_no + '\')"/>[X] Remove option</a>';
  
  // process options from the ClassOptList array and assign to select object
  selectOpts.setAttribute( 'name', 'options[' + row_no + '][option]' );
  for( var key in ClassOptList ) {
   var optionOpts = document.createElement( 'option' );
   optionOpts.appendChild( document.createTextNode( key + ' => ' + ClassOptList[key] ) );
   optionOpts.value = key;
   selectOpts.appendChild( optionOpts );
  }

  selectMatch.setAttribute( 'name', 'options[' + row_no + '][match_opt]' );
  selectMatch.setAttribute( 'style', 'width: 100%' );
  for( var i = 0; i < optionsList.length; i++ ) {
   var option = document.createElement('option');
   option.appendChild( document.createTextNode( optionsList[i] ) );
   option.value = optionsList[i];
   selectMatch.appendChild( option );
  }
  
  // match form elements (radio buttons)
  match1.setAttribute('type', 'radio');
  match1.setAttribute('name', 'options[' + row_no + '][match]');
  match1.setAttribute('value', 'TRUE');
  match2.setAttribute('type', 'radio');
  match2.setAttribute('name', 'options[' + row_no + '][match]');
  match2.setAttribute('value', 'FALSE');
  match2.setAttribute('checked', 'TRUE');
  
  // substring form elements (radio buttons)
  substr1.setAttribute('type', 'radio');
  substr1.setAttribute('name', 'options[' + row_no + '][substring]');
  substr1.setAttribute('value', 'TRUE');
  substr2.setAttribute('type', 'radio');
  substr2.setAttribute('name', 'options[' + row_no + '][substring]');
  substr2.setAttribute('value', 'FALSE');
  substr2.setAttribute('checked', 'TRUE');
  substr3.setAttribute('name', 'options[' + row_no + '][substring_start]');
  substr3.setAttribute('size', 10 );
  substr4.setAttribute('name', 'options[' + row_no + '][substring_end]');
  substr4.setAttribute('size', 10 );

  try {
   // insert our header with remove option
   var newCell = headerRow.insertCell( 0 );
   newCell.setAttribute( "colSpan", 6 );
   newCell.innerHTML = '<hr><div align=center><b>Option #' + row_no + '</b></div>';// - ' + remove;
   
   // create table with select list
   var newCell = optionsRow.insertCell( -1 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   optionsRow.innerHTML = "<b>Select Option:</b>";
   var newCell = optionsRow.insertCell( 0 );
   newCell.setAttribute( "colSpan", 4 );
   newCell.appendChild( selectOpts );
   var newCell = optionsRow.insertCell( 1 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<div class=copyright>* Select option?</div>";
   
   // begin with the row for match opts
   var newCell = matchRow.insertCell( 0 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<b>Match?</b>";
   var newCell = matchRow.insertCell( 1 );
   newCell.innerHTML = "True:";
   var newCell = matchRow.insertCell( 2 );
   newCell.appendChild( match1 );
   var newCell = matchRow.insertCell( 3 );
   newCell.innerHTML = "False:";
   var newCell = matchRow.insertCell( 4 );
   newCell.appendChild( match2 );
   var newCell = matchRow.insertCell( 5 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<div class=copyright>* REGEX?</div>";

   // create table with select list
   var newCell = matchRow2.insertCell( -1 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   matchRow2.innerHTML = "<b>Match Option:</b>";
   var newCell = matchRow2.insertCell( 0 );
   newCell.setAttribute( "colSpan", 4 );
   newCell.appendChild( selectMatch );
   var newCell = matchRow2.insertCell( 1 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<div class=copyright>* Match option?</div>";

   // now generate substring row
   var newCell = substringRow.insertCell( 0 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<b>Substring?</b>";
   var newCell = substringRow.insertCell( 1 );
   newCell.innerHTML = "True:";
   var newCell = substringRow.insertCell( 2 );
   newCell.appendChild( substr1 );
   var newCell = substringRow.insertCell( 3 );
   newCell.innerHTML = "False:";
   var newCell = substringRow.insertCell( 4 );
   newCell.appendChild( substr2 );
   var newCell = substringRow.insertCell( 5 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<div class=copyright>* Substring?</div>";

   // now generate substring row
   var newCell = substringRow2.insertCell( 0 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<b>Substring values?</b>";
   var newCell = substringRow2.insertCell( 1 );
   newCell.innerHTML = "Start:";
   var newCell = substringRow2.insertCell( 2 );
   newCell.appendChild( substr3 );
   var newCell = substringRow2.insertCell( 3 );
   newCell.innerHTML = "End:";
   var newCell = substringRow2.insertCell( 4 );
   newCell.appendChild( substr4 );
   var newCell = substringRow2.insertCell( 5 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<div class=copyright>* Start/End</div>";

   // substring regex row
   var newCell = substringRow3.insertCell( 0 );
   newCell.setAttribute( 'nowrap', 'TRUE' );
   newCell.innerHTML = "<b>REGEX value:</b>";
   var newCell = substringRow3.insertCell( 1 );
   newCell.setAttribute( 'colSpan', 4 );
   newCell.appendChild( substr5 );
   var newCell = substringRow3.insertCell( 2 );
   newCell.innerHTML = "<div class=copyright>* REGEX String</div>";

   row_no++;
   document.getElementById( 'num' ).innerHTML = row_no;
  } catch ( ex ) {
   //alert( ex );
  }
 }
}

// remove item elements row
function removeOption( tbl, num )
{
 //var table = document.getElementById( tbl );
 try {
  row_no--;
  tbl.deleteRow( num );
 } catch(ex) {
  //alert( ex );
 }
}

// setup new item elements row
row_no=1;
function addRow( tbl, row, nme, num ) {
 var textbox_quantity = document.createElement('input');
 var textbox_description = document.createElement('textarea');
 var textbox_price = document.createElement('input');
 var textbox_part = document.createElement('input');
 var textbox_source = document.createElement('textarea');
 if( num != 'NULL' ) { row_no = num; }
 if( row_no <= 45 ) {
  textbox_quantity.setAttribute('type', 'text');
  textbox_quantity.setAttribute('name', nme + '_order_items[' + row_no + '][quantity]');
  textbox_quantity.setAttribute('size', '4');
  
  textbox_description.setAttribute('type', 'textarea');
  textbox_description.setAttribute('cols', '20');
  textbox_description.setAttribute('rows', '2');
  textbox_description.setAttribute('name', nme + '_order_items[' + row_no + '][description]');
  
  textbox_price.setAttribute('type', 'text');
  textbox_price.setAttribute('name', nme + '_order_items[' + row_no + '][price]');
  textbox_price.setAttribute('size', '15');
  textbox_price.setAttribute( 'onchange', 'Total( this )' );
  textbox_price.setAttribute( 'onblur', 'Total( this )' );
  
  textbox_part.setAttribute('type', 'text');
  textbox_part.setAttribute('name', nme + '_order_items[' + row_no + '][partnum]');
  textbox_part.setAttribute('size', '15');

  textbox_source.setAttribute('type', 'textarea');
  textbox_source.setAttribute('cols', '20');
  textbox_source.setAttribute('rows', '2');
  textbox_source.setAttribute('name', nme + '_order_items[' + row_no + '][source]');
  var remove = '<a href="#" onclick="removeRow(\''+ tbl +'\',\'' + row_no + '\')"/>[X]</a>';
  var msg = '*';
  var tbl = document.getElementById( tbl );
  var rowIndex = document.getElementById( row ).value;
  try {
   var newRow = tbl.insertRow( row_no );
   var newCell = newRow.insertCell( 0 );
   newCell.innerHTML = remove;
   var newCell = newRow.insertCell( 1 );
   newCell.appendChild( textbox_quantity );
   var newCell = newRow.insertCell( 2 );
   newCell.appendChild( textbox_description );
   var newCell = newRow.insertCell( 3 );
   newCell.appendChild( textbox_price );
   var newCell = newRow.insertCell( 4 );
   newCell.appendChild( textbox_part );
   var newCell = newRow.insertCell( 5 );
   newCell.appendChild( textbox_source );
   var newCell = newRow.insertCell( 6 );
   newCell.innerHTML = msg;
   row_no++;
  } catch ( ex ) {
   alert( ex );
  }
 }
 if( row_no > 45 )
 {
  document.getElementById( nme ).style.display="none";
 }
}

// remove item elements row
function removeRow( tbl, num, Type )
{
 var table = document.getElementById( tbl );
 try {
  row_no--;
  table.deleteRow( num );
 } catch(ex) {
  alert( ex );
 }
 if( row_no <= 15 )
 {
  document.getElementById( Type ).style.display="block";
 }   
}


//-->
