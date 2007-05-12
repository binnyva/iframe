function $(id){return document.getElementById(id);}
function addEvent(elm,evType,fn,capture){if(!capture)var capture=true;if(elm.addEventListener){elm.addEventListener(evType, fn, capture);return true;}else if (elm.attachEvent) {var r = elm.attachEvent('on' + evType, fn);return r;}else {elm['on' + evType] = fn;}}
function stopEvent(e){e.cancelBubble=true;e.returnValue=false;if(e.stopPropagation){e.stopPropagation();e.preventDefault();}}
function findTarget(e) {var element;if(!e)var e=window.event;if(e.target)element=e.target;else if(e.srcElement)element=e.srcElement;if(element.nodeType==3)element=element.parentNode;return element;}
function getAll(e){return e.all?e.all:e.getElementsByTagName('*');}
function getElementsByCSS(selector){if(!document.getElementsByTagName) return new Array();var tks=selector.split(' '),cc=new Array(document);for(var i=0;i<tks.length;i++){tk=tks[i].replace(/^\s+/,'').replace(/\s+$/,'');if(tk.indexOf('#')>-1){var bits=tk.split('#'),tn=bits[0],id=bits[1],el=document.getElementById(id);if(tn && el.nodeName.toLowerCase()!=tn)return new Array();cc=new Array(el);continue;}if(tk.indexOf('.')>-1){var bits=tk.split('.'),tn=bits[0],className=bits[1],fnd=new Array,fc=0;if(!tn) tn='*';for(var h=0;h<cc.length;h++){var es;if(tn=='*')es=getAll(cc[h]);else es=cc[h].getElementsByTagName(tn);for(var j=0;j<es.length;j++){fnd[fc++]=es[j];}}cc=new Array;var ccIndex=0;for(var k=0;k<fnd.length;k++){if(fnd[k].className && fnd[k].className.match(new RegExp('(\\s|^)'+className+'(\\s|$)')))cc[ccIndex++]=fnd[k];}continue;}if(tk.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/)) {var tn=RegExp.$1,an=RegExp.$2,attrOperator=RegExp.$3,av=RegExp.$4,fnd=new Array,fc=0;if(!tn) tn='*';for(var h=0;h<cc.length;h++) {var es;if(tn=='*')es=getAll(cc[h]);else es=cc[h].getElementsByTagName(tn);for(var j=0;j<es.length;j++) {fnd[fc++]=es[j];}}cc=new Array;var ccIndex=0,cf;switch(attrOperator){case '=':cf=function(e){return(e.getAttribute(an)==av);};break;case '~':cf=function(e){return(e.getAttribute(an).match(new RegExp('\\b'+av+'\\b')));};break;case '|':cf=function(e){return(e.getAttribute(an).match(new RegExp('^'+av+'-?')));};break;case '^':cf=function(e){return(e.getAttribute(an).indexOf(av)==0);};break;case '$':cf=function(e){return(e.getAttribute(an).lastIndexOf(av)==e.getAttribute(an).length-av.length);};break;case '*':cf=function(e){return(e.getAttribute(an).indexOf(av)>-1);};break;default:cf=function(e){return e.getAttribute(an);};}cc=new Array;var ccIndex=0;for(var k=0;k<fnd.length;k++)if(cf(fnd[k]))cc[ccIndex++]=fnd[k];continue;}tn=tk;var fnd=new Array,fc=0;for(var h=0;h<cc.length;h++){var es=cc[h].getElementsByTagName(tn);for(var j=0;j<es.length;j++) fnd[fc++]=es[j];}cc=fnd;}return cc;}
function getElementsByClassName(classname,tag){if(!tag)var tag="";return getElementsByCSS(tag+"."+classname);}
function toggle(item,state){if(state)item.style.display=state;else item.style.display = (item.style.display == "block") ? "none" : "block";}

//Framework Specific
function showMessage(data) {
	if(data.success) $("success-message").innerHTML = stripSlashes(data.success);
	if(data.error) $("error-message").innerHTML = stripSlashes(data.error);
}
function stripSlashes(text) {
	if(!text) return "";
	return text.replace(/\\([\'\"])/,"$1");
}
function siteInit() {
	
}
window.onload=siteInit;