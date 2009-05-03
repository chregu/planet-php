	/*
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Bitflux GmbH                                      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// +----------------------------------------------------------------------+

*/
var moreInfoReq = false;
var t = null;
var moreInfoLast = "";
var isIE = false;

if (!(navigator.userAgent.indexOf("Safari") > 0 && navigator.product == "Gecko")) {
	isIE = true;
}
// on !IE we only have to initialize it once
if (window.XMLHttpRequest) {
	moreInfoReq = new XMLHttpRequest();
}



function moreInfoHideDelayed() {
	window.setTimeout("moreInfoHide()",400);
}
	
function moreInfoHide() {
	document.getElementById("LSResult").style.display = "none";
	var highlight = document.getElementById("LSHighlight");
	if (highlight) {
		highlight.removeAttribute("id");
	}
}

function moreInfoKeyPress(event) {
	
	if (event.keyCode == 40 )
	//KEY DOWN
	{
		highlight = document.getElementById("LSHighlight");
		if (!highlight) {
			highlight = document.getElementById("LSShadow").firstChild.firstChild;
		} else {
			highlight.removeAttribute("id");
			highlight = highlight.nextSibling;
		}
		if (highlight) {
			highlight.setAttribute("id","LSHighlight");
		} 
		if (!isIE) { event.preventDefault(); }
	} 
	//KEY UP
	else if (event.keyCode == 38 ) {
		highlight = document.getElementById("LSHighlight");
		if (!highlight) {
			highlight = document.getElementById("LSResult").firstChild.firstChild.lastChild;
		} 
		else {
			highlight.removeAttribute("id");
			highlight = highlight.previousSibling;
		}
		if (highlight) {
				highlight.setAttribute("id","LSHighlight");
		}
		if (!isIE) { event.preventDefault(); }
	} 
	//ESC
	else if (event.keyCode == 27) {
		highlight = document.getElementById("LSHighlight");
		if (highlight) {
			highlight.removeAttribute("id");
		}
		document.getElementById("LSResult").style.display = "none";
	} 
}

function moreInfoStart(blog_id,id) {
	if (id) {
		var moretext = false;
		var className = "moreDiv";
	} else {
		id = blog_id;
		var moretext = true;
		var className = "moreText";
	}
	sh = document.getElementById('morediv'+id);
	
	if (sh.style.display == 'block' && sh.className == className) {
		sh.style.display = 'none';
	} else {
		sh.className = className;
		if (moretext) {
			moreInfoRoot = webroot + "moretext/"+blog_id;
		} else {
			moreInfoRoot = webroot + "moreinfo/"+blog_id+"/"+id;
		}
	
		if (window.XMLHttpRequest) {
			// branch for IE/Windows ActiveX version
		} else if (window.ActiveXObject) {
			moreInfoReq = new ActiveXObject("Microsoft.XMLHTTP");
		}
		moreInfoReq.onreadystatechange= moreInfoProcessReqChange;
		moreInfoReq.open("GET", moreInfoRoot );
		//moreInfoReq.entry_id = id;
		moreInfoLast = 'morediv' + id;
		
		moreInfoReq.send(null);
	}
	//}
}

function moreInfoProcessReqChange() {
	
	if (moreInfoReq.readyState == 4) {
		var  sh = document.getElementById(moreInfoLast);
		
		sh.style.display = 'block';
		sh.innerHTML = moreInfoReq.responseText;
		if(sh.className == 'moreText') {
		 enableTooltips();
		}
	}
}

function moreInfoSubmit() {
	var highlight = document.getElementById("LSHighlight");
	if (highlight && highlight.firstChild) {
		window.location = moreInfoRoot + moreInfoRootSubDir + highlight.firstChild.nextSibling.getAttribute("href");
		return false;
	} 
	else {
		return true;
	}
}

function openMore(id,open) {
	
	var sh = document.getElementById("more" +id);
	if (typeof open == "undefined") { 
		if (sh.style.display == 'block' ) {
			sh.style.display = 'none';
		} else {
			sh.style.display = 'block';
		}
	} else if (open) {
		sh.style.display = 'block';
	} else {
		sh.style.display = 'none';
	}
	return false;
}


