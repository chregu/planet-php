/*Javascript for Bubble Tooltips by Alessandro Fulciniti
http://pro.html.it - http://web-graphics.com */

function enableTooltips(id){
	var links,i,h;
	if(!document.getElementById || !document.getElementsByTagName) return;
	h=document.createElement("span");
	h.id="btc";
	h.setAttribute("id","btc");
	h.style.position="absolute";
	document.getElementsByTagName("body")[0].appendChild(h);
	if(id==null) {
		links=document.getElementsByTagName("span");
	} else {
		links=document.getElementById(id).getElementsByTagName("span");
	}
	for(i=0;i<links.length;i++){
		if (links[i].className== 'pic') {
			
			Prepare(links[i]);
		}
	}
}

function Prepare(el){
	var tooltip,t,b,s,l;
	t=el.getAttribute("title");
	
	if (t==null || t.length==0) {
		return;
		t="link:";
	}
	el.removeAttribute("title");
	
	var src = "http://"+ t.replace(/.*src: (.*)$/,"$1");
	
	tooltip=CreateEl("span","tooltip");
	
	s=CreateEl("span","top");
	
	s.appendChild(document.createTextNode(src));
	s.appendChild(document.createElement("br"));
	
	
	img = document.createElement("img");
	
	img.setAttribute("alt",src);
	s.appendChild(img);
	
	
	tooltip.appendChild(s);
	
	b=CreateEl("b","bottom");
	
	/*l=el.getAttribute("href");
	
	if(l.length>28) l=l.substr(0,25)+"...";
	
	b.appendChild(document.createTextNode(l));
	*/
	tooltip.appendChild(b);
	
	setOpacity(tooltip);
	
	el.tooltip=tooltip;
	el.img = img;
	
	el.onmouseover=showTooltip;
	
	el.onmouseout=hideTooltip;
	
	el.onmousemove=Locate;
}

function showTooltip(e){
	this.img.setAttribute("src",this.img.getAttribute("alt"));
	document.getElementById("btc").appendChild(this.tooltip);
	Locate(e);
}

function hideTooltip(e){
	var d=document.getElementById("btc");
	if(d.childNodes.length>0) d.removeChild(d.firstChild);
}

function setOpacity(el){
	el.style.filter="alpha(opacity:95)";
	el.style.KHTMLOpacity="0.95";
	el.style.MozOpacity="0.95";
	el.style.opacity="0.95";
}

function CreateEl(t,c){
	var x=document.createElement(t);
	x.className=c;
	x.style.display="block";
	return(x);
}



function Locate(e){
	var posx=0,posy=0;
	if(e==null) e=window.event;
	if(e.pageX || e.pageY){
		posx=e.pageX; posy=e.pageY;
	}
	else if(e.clientX || e.clientY){
		if(document.documentElement.scrollTop){
			posx=e.clientX+document.documentElement.scrollLeft;
			posy=e.clientY+document.documentElement.scrollTop;
		}
		else{
			posx=e.clientX+document.body.scrollLeft;
			posy=e.clientY+document.body.scrollTop;
		}
	}
	document.getElementById("btc").style.top=(posy+10)+"px";
	document.getElementById("btc").style.left=(posx-20)+"px";
}
