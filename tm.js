function $cls(v, c){ var o = document.getElementById(v); o.setAttribute("class",c); o.setAttribute("className",c); }

/* Copyright 2007 Codevendor.com */
var Namespace={Register:function(c){var o=window;var x=false;for(var a=c.split(".");a.length>0;){var s=a.shift();if(a.length==0){if(o[s]){x=true;}}if(!o[s]){o[s]={};}o=o[s];}if(x){return 1;}}};Namespace.Register("System.Net.Ajax");System.Net.Ajax.RequestMethod={Get:"GET",Post:"POST"};System.Net.Ajax.PageRequests=function(){return{Requests:null,GetType:function(){return "System.Net.Ajax.PageRequests";},Init:function(){this.Requests=new Array();if(arguments[0].length==1){this.Requests.push(arguments[0][0]);}return this;},AddRequest:function(){if(arguments.length==0||arguments[0].GetType()!="System.Net.Ajax.Request"){return;}this.Requests.push(arguments[0]);}}.Init(arguments);};System.Net.Ajax.Request=function(){return{Method:null,URL:null,Params:null,Callback:null,Async:false,UserObject:null,GetType:function(){return "System.Net.Ajax.Request";},Init:function(){switch(arguments[0].length){case 1:this.Method=arguments[0][0];break;case 2:this.Method=arguments[0][0];this.URL=arguments[0][1];break;case 3:this.Method=arguments[0][0];this.URL=arguments[0][1];this.Callback=arguments[0][2];break;case 4:this.Method=arguments[0][0];this.URL=arguments[0][1];this.Callback=arguments[0][2];this.Async=arguments[0][3];break;case 5:this.Method=arguments[0][0];this.URL=arguments[0][1];this.Callback=arguments[0][2];this.Async=arguments[0][3];this.UserObject=arguments[0][4];break;}this.Params=new Array();return this;},AddParam:function(){switch(arguments.length){case 1:this.Params.push(arguments[0]);break;case 2:this.Params.push(new System.Net.Ajax.Parameter(arguments[0],arguments[1]));break;}}}.Init(arguments);};System.Net.Ajax.Parameter=function(){return{Name:null,Value:null,GetType:function(){return "System.Net.Ajax.Parameter";},Init:function(){if(arguments[0].length==2){this.Name=arguments[0][0];this.Value=arguments[0][1];}return this;}}.Init(arguments);};System.Net.Ajax.ActiveObject=0;System.Net.Ajax.Connection=function(){return{ActiveXObject:null,PageRequests:null,Current:null,GetType:function(){return "System.Net.Ajax.Connection";},Init:function(){if(arguments[0].length==1){this.PageRequests=arguments[0][0];}return this;},Create:function(){switch(System.Net.Ajax.ActiveObject){case 0:if(window.ActiveXObject){try{System.Net.Ajax.ActiveObject=2;return new ActiveXObject("Msxml2.XMLHTTP");}catch(e){System.Net.Ajax.ActiveObject=3;return new ActiveXObject("Microsoft.XMLHTTP");}}else{if(window.XMLHttpRequest){System.Net.Ajax.ActiveObject=1;return new XMLHttpRequest();}}case 1:return new XMLHttpRequest();case 2:return new ActiveXObject("Msxml2.XMLHTTP");case 3:return new ActiveXObject("Microsoft.XMLHTTP");default:break;}System.Net.Ajax.ActiveObject= -1;throw "Missing a required ajax object.";return false;},Open:function(){if(this.PageRequests==null){return;}var obj=this;var Data="";this.ActiveXObject=this.Create();this.Current=this.PageRequests.Requests.shift();this.ActiveXObject.open(this.Current.Method,this.Current.URL,this.Current.Async);this.ActiveXObject.onreadystatechange=function(){obj.OnReadyStateChange();};if(this.Current.Method=="POST"){this.ActiveXObject.setRequestHeader("Content-type","application/x-www-form-urlencoded");if(this.Current.Params!=null&&this.Current.Params.length!=0){for(var Param in this.Current.Params){Data+=(Data=="")?this.Current.Params[Param].Name+"="+this.Current.Params[Param].Value:"&"+this.Current.Params[Param].Name+"="+this.Current.Params[Param].Value;}}this.ActiveXObject.send(encodeURI(Data));}else{this.ActiveXObject.send(null);}},OnReadyStateChange:function(){var r={};r.ReadyState=this.ActiveXObject.readyState;r.ResponseText=(this.ActiveXObject.readyState==4)?this.ActiveXObject.responseText:null;r.Status=(this.ActiveXObject.readyState==4)?this.ActiveXObject.status:null;r.URL=this.Current.URL;r.UserObject=this.Current.UserObject;r.Complete=(this.ActiveXObject.readyState==4&&this.PageRequests.Requests.length==0)?true:false;if(this.Current.Callback!=null){this.Current.Callback(r);}if(this.ActiveXObject.readyState==4){if(r.Complete){this.PageRequests=null;this.ActiveXObject.abort();this.Current=null;}else{this.Open();}}}}.Init(arguments);}

function loadpopup(id, tn)
{
  // Clear fields
  document.getElementById('pagesnotadded').innerHTML = '';
  document.getElementById('pagesadded').innerHTML = '';
  document.getElementById('pagespopupsearch').value = '';
  document.getElementById('attachedpages').value = '';
  document.getElementById('pagespopuptokinname').innerHTML = tn; 
  document.getElementById('pagespopup').style.display = 'block'; 
  document.getElementById('tid').value = id;
  attachedpages(id);
}

function pageadd()
{
  var o = document.getElementById("pagesnotadded");
  var pid = o.options[o.selectedIndex].value;
  var tid = document.getElementById("tid").value;

  if(pid)
  {
    document.getElementById("loader").style.display = "";
    var a = new System.Net.Ajax.Request("POST","admin.php?page=tokenmanagerjson", prcb, true);
    a.AddParam("json","attachedpagesadd");
    a.AddParam("tokenid",tid);
    a.AddParam("pageid",pid);
    var b = new System.Net.Ajax.PageRequests(a);
    var c = new System.Net.Ajax.Connection(b);
    c.Open();
  }
}

function pageremove()
{
  var o = document.getElementById("pagesadded");
  var pid = o.options[o.selectedIndex].value;
  var tid = document.getElementById("tid").value;

  if(pid)
  {
    document.getElementById("loader").style.display = "";
    var a = new System.Net.Ajax.Request("POST","admin.php?page=tokenmanagerjson", prcb, true);
    a.AddParam("json","attachedpagesremove");
    a.AddParam("tokenid",tid);
    a.AddParam("pageid",pid);
    var b = new System.Net.Ajax.PageRequests(a);
    var c = new System.Net.Ajax.Connection(b);
    c.Open();
  }
}

function prcb(src)
{
  if(src.ReadyState==4 && src.Status==200)
  {
    // Reload pages
    var tid = document.getElementById("tid").value;
    attachedpages(tid);
  }
}

function attachedpages(id)
{
  document.getElementById("loader").style.display = "";
  var a = new System.Net.Ajax.Request("POST","admin.php?page=tokenmanagerjson", apcb, true);
  a.AddParam("json","attachedpages");
  a.AddParam("tokenid",id);
  var b = new System.Net.Ajax.PageRequests(a);
  var c = new System.Net.Ajax.Connection(b);
  c.Open();
}

function apcb(src)
{
  if(src.ReadyState==4 && src.Status==200)
  {
    var results = '';
    var results2 = '';
    var o = document.getElementById('pagesadded');
    var o2 = document.getElementById('attachedpages');
    var s = eval('(' + src.ResponseText + ')');
    for(var i = 0; i<s.length; i++)
    {
      results2 += s[i].pid + ",";
      results += '<option value="' + s[i].pid + '">' + s[i].ptitle + '</option>';
    }
    o.innerHTML = results;

    o2.value = results2;
    
    document.getElementById("loader").style.display = "none";

    search_results();
  }
}

function search_results()
{
  if(document.getElementById("pagespopupsearch").value!="")
  {
    document.getElementById("loader").style.display = "";
    var a = new System.Net.Ajax.Request("POST","admin.php?page=tokenmanagerjson", srcb, true);
    a.AddParam("json","searchpages");
    a.AddParam("keyword",document.getElementById("pagespopupsearch").value);
    a.AddParam("attachedpages",document.getElementById("attachedpages").value);
    var b = new System.Net.Ajax.PageRequests(a);
    var c = new System.Net.Ajax.Connection(b);
    c.Open();
  }
}

function srcb(src)
{
  if(src.ReadyState==4 && src.Status==200)
  {
    var results = '';
    var o = document.getElementById('pagesnotadded');
    var s = eval('(' + src.ResponseText + ')');
    for(var i = 0; i<s.length; i++)
    {
      results += '<option value="' + s[i].ID + '">' + s[i].post_title + '</option>';
    }
    o.innerHTML = results;

    document.getElementById("loader").style.display = "none";
  }
}

function confirmdelete(id, tn)
{
  var answer = confirm("Are you sure you would like to remove token " + tn + "?")
  if (answer)
  {
    document.getElementById('tokenid').value=id; 
    document.getElementById('myfrom').submit();
  }
}

function confirmdelete2(id, tn)
{
  var answer = confirm("Are you sure you would like to remove token type (" + tn + ")?")
  if (answer)
  {
    document.getElementById('typeid').value=id; 
    document.getElementById('myfrom').submit();
  }
}

function moveup(tid)
{
  if(tid)
  {
    var a = new System.Net.Ajax.Request("POST","admin.php?page=tokenmanagerjson", pocb, true);
    a.AddParam("json","moveup");
    a.AddParam("tid",tid);
    var b = new System.Net.Ajax.PageRequests(a);
    var c = new System.Net.Ajax.Connection(b);
    c.Open();
  }
}

function movedown(tid)
{
  if(tid)
  {
    var a = new System.Net.Ajax.Request("POST","admin.php?page=tokenmanagerjson", pocb, true);
    a.AddParam("json","movedown");
    a.AddParam("tid",tid);
    var b = new System.Net.Ajax.PageRequests(a);
    var c = new System.Net.Ajax.Connection(b);
    c.Open();
  }
}

function pocb(src)
{
  if(src.ReadyState==4 && src.Status==200)
  {
    window.location.href=window.location.href;
  }
}

function hidetabs()
{
  $cls('tab1', 'tm_taboff');
  $cls('tab2', 'tm_taboff');
  $cls('tab3', 'tm_taboff');
  $cls('tab4', 'tm_taboff');

  $cls('tabdiv1', 'tm_tabdivoff');
  $cls('tabdiv2', 'tm_tabdivoff');
  $cls('tabdiv3', 'tm_tabdivoff');
  $cls('tabdiv4', 'tm_tabdivoff');
}

function showtab(t)
{
  hidetabs();
  $cls('tab' + t, 'tm_tabon');
  $cls('tabdiv' + t, 'tm_tabdivon');
}


