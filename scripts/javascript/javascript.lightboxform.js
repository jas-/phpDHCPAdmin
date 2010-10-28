/*
	Lightbox Form
	@ Creative Ruin: www.creativeruin.com
	
	Derived from:
	Lightbox JS: Fullsize Image Overlays 
	by Lokesh Dhakar - http://www.huddletogether.com

	For more information on this script, visit:
	http://huddletogether.com/projects/lightbox/

	Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
	(basically, do anything you want, just leave my name and link)

*/

//
// Configuration
//

var loadingImage = 'templates/images/loading.gif'; // alt loading image
var loadingSwf = 'templates/images/loading.swf'; // loading swf and it's settings below:
var loadingSwfWidth = '126'; // the width
var loadingSwfHeight = '22'; // the height
var loadingSwfVersion = '6'; // flash version of the loader
var loadingSwfBgColor = '#333333'; // background color of the SWF


//
// getPageScroll_lf()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
//
function getPageScroll_lf(){

	var yScroll;

	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('',yScroll) 
	return arrayPageScroll;
}



//
// getPageSize_lf()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
function getPageSize_lf(){
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}


	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
	return arrayPageSize;
}


//
// pause_lf(numberMillis)
// pause_lfs code execution for specified time. Uses busy code, not good.
// Code from http://www.faqts.com/knowledge_base/view.phtml/aid/1602
//
function pause_lf(numberMillis) {
	var now = new Date();
	var exitTime = now.getTime() + numberMillis;
	while (true) {
		now = new Date();
		if (now.getTime() > exitTime)
			return;
	}
}


//
// listenKey_lf()
//
function listenKey_lf () {	document.onkeypress = getKey; }
	

//
// showLightbox_lf()
// Preloads images. Pleaces new image in lightbox then centers and displays.
//
function showLightbox_lf(objLink)
{
	// prep objects
	var objOverlay = document.getElementById('overlayfrm');
	var objLoadingImage = document.getElementById('loadingImageFrm');

	var arrayPageSize = getPageSize_lf();
	var arrayPageScroll = getPageScroll_lf();

	// center loadingImageFrm if it exists
	if (objLoadingImage) {
		objLoadingImage.style.top = (arrayPageScroll[1] + ((arrayPageSize[3] - 35 - loadingSwfHeight) / 2) + 'px');
		objLoadingImage.style.left = (((arrayPageSize[0] - 20 - loadingSwfWidth) / 2) + 'px');
		objLoadingImage.style.display = 'block';
	}

	// set height of overlayfrm to take up whole page and show
	objOverlay.style.height = (arrayPageSize[1] + 'px');
	objOverlay.style.display = 'block';
	
	// Hide select boxes as they will 'peek' through the image in IE
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
			selects[i].style.visibility = "hidden";
	}
}



//
// hideLightbox_lf()
//
function hideLightbox_lf()
{
	// get objects
	objOverlay = document.getElementById('overlayfrm');

	// hide lightbox and overlayfrm
	objOverlay.style.display = 'none';

	// make select boxes visible
	selects = document.getElementsByTagName("select");
 for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "visible";
	}
}




//
// initLightbox_lf()
// Function runs on window load, going through link tags looking for rel="lightbox".
// These links receive onclick events that enable the lightbox display for their targets.
// The function also inserts html markup at the top of the page which will be used as a
// container for the overlay pattern and the inline image.
//
function initLightbox_lf()
{

	// loop through all input tags
	if (!document.getElementsByTagName){ return; }
	var myData = new Array();
	var inputs = document.getElementsByTagName("input");
	var selects = document.getElementsByTagName("select");
	var hrefs = document.getElementsByTagName("a");
	
	for( var x = 0; x < inputs.length; x++ ) {
		myData.push( inputs[x] );
	}
	
	for( var y = 0; y < selects.length; y++ ) {
		myData.push( selects[y] );
	}
	
	for( var z = 0; z < hrefs.length; z++ ) {
		myData.push( hrefs[z] );
	}
	
	for( var i = 0; i < myData.length; i++ ) {
		var ndata = myData[i];
		if ((ndata.getAttribute("rel") == "lightboxform")){
			ndata.onclick = function () {showLightbox_lf(this); return true;}
		}
	}

	// the rest of this code inserts html at the top of the page that looks like this:
	//
	// <div id="overlayfrm">
	//		<a href="#" onclick="hideLightbox_lf(); return false;"><img id="loadingImageFrm" /></a>
	//	</div>
	
	var objBody = document.getElementsByTagName("body").item(0);
	
	// create overlayfrm div and hardcode some functional styles (aesthetic styles are in CSS file)
	var objOverlay = document.createElement("div");
	objOverlay.setAttribute('id','overlayfrm');
	objOverlay.onclick = function () {return false;}
	objOverlay.style.display = 'none';
	objOverlay.style.position = 'absolute';
	objOverlay.style.top = '0';
	objOverlay.style.left = '0';
	objOverlay.style.zIndex = '90';
 	objOverlay.style.width = '100%';
	objBody.insertBefore(objOverlay, objBody.firstChild);
	
	var arrayPageSize = getPageSize_lf();
	var arrayPageScroll = getPageScroll_lf();

	// preload and create loader image
	var imgPreloader = new Image();
	
	// if loader image found, create link to hide lightbox and create loadingimage
	imgPreloader.onload=function(){

		var objLoadingImageLink = document.createElement("a");
		objLoadingImageLink.setAttribute('href','#');
		objLoadingImageLink.setAttribute('id','loadingLinkFrm');
		//objLoadingImageLink.onclick = function () {hideLightbox_lf(); return false;}
		objOverlay.appendChild(objLoadingImageLink);
		
		
		var so = new SWFObject(loadingSwf,"loadingImageFrm",loadingSwfWidth,loadingSwfHeight,loadingSwfVersion,loadingSwfBgColor);
		if (navigator.appVersion.indexOf("MSIE")!=-1) so.addParam("wmode", "transparent");
		so.write("loadingLinkFrm");
		
		var objLoadingImage = document.getElementById('loadingImageFrm');
		objLoadingImage.style.display = 'none';
		objLoadingImage.style.position = 'absolute';
		objLoadingImage.style.zIndex = '150';

		imgPreloader.onload=function(){};	//	clear onLoad, as IE will flip out w/animated gifs

		return false;
	}

	imgPreloader.src = loadingImage;
}




//
// addLoadEvent_lf()
// Adds event to window.onload without overwriting currently assigned onload functions.
// Function found at Simon Willison's weblog - http://simon.incutio.com/
//
function addLoadEvent_lf(func)
{
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
  window.onload = func;
	} else {
		window.onload = function() {
		 oldonload();
		 func();
		}
	}

}
function addUnLoadEvent_lf(func)
{	
	var oldunload = window.onunload;
	if (typeof window.onunload != 'function'){
    	window.onunload = func;
	} else {
		window.onunload = function(){
		oldunload();
		func();
		}
	}
}



//addLoadEvent_lf(initLightbox_lf);	// run initLightbox_lf onLoad
//addUnLoadEvent_lf(hideLightbox_lf); // hide Lightbox onUnLoad