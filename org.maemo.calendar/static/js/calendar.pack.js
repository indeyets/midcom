if(console==undefined){var console={};{return;}}
function finishCalendarLoad(id){jQuery('#'+id).fadeIn("",function(){on_view_update();var aa=calendar_config["types_classes"][calendar_config["type"]];if(aa=='week'||aa=='day'){jQuery('div.calendar-timeline-holder')[0].scrollTop=calendar_config["start_hour_x"];}});}
function run_scripts(e){if(e.nodeType!=1)return;if(e.tagName.toLowerCase()=='script'){eval(e.text);}
else{var n=e.firstChild;while(n){if(n.nodeType==1)run_scripts(n);n=n.nextSibling;}}}
function zoom_view(ba,ca){if(ba){if(calendar_config["type"]==4){return false;}
calendar_config["type"]+=1;}
else{if(calendar_config["type"]==1){return false;}
calendar_config["type"]-=1;}
var da=jQuery('#date-selection-form');var ea=APPLICATION_PREFIX+ca+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({url:ea,timeout:12000,error:function(fa,ga,ha){alert("Failed to zoom request.statusText: "+fa.statusText);jQuery('#calendar-holder').show();},success:function(r){var ia=unescape(r);jQuery('#calendar-holder').html(ia);var ja=calendar_config["types_classes"][calendar_config["type"]];jQuery('body').attr('class',ja);setTimeout("finishCalendarLoad('calendar-holder')",400);}});return false;}
function update_date_selection(ka,la,ma){var na=[];var oa=jQuery('#date-selection-form');jQuery(':input',oa).each(function(){if(this.name=="month-select"){this.value=la;}
if(this.name=="day-select"){this.value=ka;}
if(this.name=="year-select"){this.value=ma;}
na.push(this.name+'='+escape(this.value));});return na;}
function goto_prev(){var pa=new Date();var qa=pa.getDate();var ra=pa.getMonth();var sa=pa.getFullYear();var ta=new Date(qa,ra,sa);var ua=jQuery('#date-selection-form');jQuery(':input',ua).each(function(){if(this.name=="month-select"){ra=this.value-1;}
if(this.name=="day-select"){qa=this.value;}
if(this.name=="year-select"){sa=this.value;}});ta.setDate(qa);ta.setMonth(ra);ta.setYear(sa);if(calendar_config["type"]==1){ta.setYear(ta.getFullYear()-1);}
if(calendar_config["type"]==2){ta.setMonth((ta.getMonth()-1));}
if(calendar_config["type"]==3){ta.setDate((ta.getDate()-7));}
if(calendar_config["type"]==4){ta.setDate((ta.getDate()-1));}
var va=String((ta.getMonth()+1));if(va.length<2){va="0"+va;}
var wa=String((ta.getDate()));if(wa.length<2){wa="0"+wa;}
var xa=update_date_selection(wa,va,ta.getFullYear());timestamp=ta.getTime()/1000.0;calendar_config["timestamp"]=timestamp;var ya=APPLICATION_PREFIX+'ajax/change/date/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:xa.join('&'),url:ya,timeout:12000,error:function(za,Aa,Ba){alert("Failed to change date");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});}
function goto_next(){var Ca=new Date();var Da=Ca.getDate();var Ea=Ca.getMonth();var Fa=Ca.getFullYear();var Ga=new Date(Da,Ea,Fa);var Ha=jQuery('#date-selection-form');jQuery(':input',Ha).each(function(){if(this.name=="day-select"){Da=this.value;}
if(this.name=="month-select"){Ea=this.value-1;}
if(this.name=="year-select"){Fa=this.value;}});Ga.setDate(Da);Ga.setMonth(Ea);Ga.setYear(Fa);if(calendar_config["type"]==1){Ga.setYear(Ga.getFullYear()+1);}
if(calendar_config["type"]==2){Ga.setMonth((Ga.getMonth()+1));}
if(calendar_config["type"]==3){Ga.setDate((Ga.getDate()+7));}
if(calendar_config["type"]==4){Ga.setDate((Ga.getDate()+1));}
var Ia=String((Ga.getMonth()+1));if(Ia.length<2){Ia="0"+Ia;}
var Ja=String((Ga.getDate()));if(Ja.length<2){Ja="0"+Ja;}
var Ka=update_date_selection(Ja,Ia,Ga.getFullYear());timestamp=Ga.getTime()/1000.0;calendar_config["timestamp"]=timestamp;var La=APPLICATION_PREFIX+'ajax/change/date/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:Ka.join('&'),url:La,timeout:12000,error:function(Ma,Na,Oa){alert("Failed to change date");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});}
function goto_today(){var Pa=jQuery('#date-selection-form');var Qa=new Date();var Ra=Qa.getDate();var Sa=Qa.getMonth();var Ta=Qa.getFullYear();var Ua=String((Sa+1));if(Ua.length<2){Ua="0"+Ua;}
var Va=update_date_selection(Ra,Ua,Ta);var Wa=new Date(Ta,Sa,Ra);timestamp=Wa.getTime()/1000.0;calendar_config["timestamp"]=timestamp;var Xa=APPLICATION_PREFIX+'ajax/change/date/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:Va.join('&'),url:Xa,timeout:12000,error:function(Ya,Za,$a){alert("Failed to change date");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});return false;}
function change_date(){var ab=jQuery('#date-selection-form');var bb=new Date();var cb=[];var db=bb.getDate();var eb=bb.getMonth();var fb=bb.getFullYear();jQuery(':input',ab).each(function(){if(this.name=="month-select"){eb=this.value-1;}
if(this.name=="day-select"){db=this.value;}
if(this.name=="year-select"){fb=this.value;}
cb.push(this.name+'='+escape(this.value));});var gb=new Date(fb,eb,db);timestamp=gb.getTime()/1000.0;calendar_config["timestamp"]=timestamp;var hb=APPLICATION_PREFIX+'ajax/change/date/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:cb.join('&'),url:hb,timeout:12000,error:function(ib,jb,kb){alert("Failed to change date");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});return false;};function change_timezone(){var lb=jQuery('#timezone-selection-form');var mb=null;var nb=[];jQuery(':input',lb).each(function(){if(this.name=="timezone"){mb=this.value-1;}
nb.push(this.name+'='+escape(this.value));});var ob=APPLICATION_PREFIX+'ajax/change/timezone/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:nb.join('&'),url:ob,timeout:12000,error:function(pb,qb,rb){alert("Failed to change timezone");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});return false;};function create_event(sb){var tb="ajax/event/create/"+sb;if(active_shelf_item){tb="ajax/event/move/"+active_shelf_item+"/"+sb;}
load_modal_window(tb);}
function close_create_event(){close_modal_window();return;}
function load_modal_window(ub){var vb=jQuery("div.calendar-modal-window");if(ub.substr(0,7)!='midcom-'){ub=APPLICATION_PREFIX+ub;}
else{ub=HOST_PREFIX+ub;}
jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:ub,timeout:12000,error:function(wb,xb,yb){if(wb.statusText=="Forbidden"){window.location=HOST_PREFIX+'midcom-login-';}},success:function(r){vb.html(unescape(r));jQuery("div.calendar-modal-window").show();}});}
function close_modal_window(){jQuery("div.calendar-modal-window").hide();return;}
function set_modal_window_contents(zb){jQuery("div.calendar-modal-window").html(unescape(zb));}
function load_shelf_contents(){var Ab='midcom-exec-org.maemo.calendar/shelf.php?action=load';jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:HOST_PREFIX+Ab,timeout:12000,dataType:'json',error:function(Bb,Cb,Db){},success:function(r){shelf_contents=r;hide_shelf_events_from_view();}});}
function save_shelf_contents(){var Eb='midcom-exec-org.maemo.calendar/shelf.php?action=save';jQuery.ajaxSetup({global:false});jQuery.ajax({type:"POST",url:HOST_PREFIX+Eb,data:{data:protoToolkit.toJSON(shelf_contents)},timeout:12000,error:function(Fb,Gb,Hb){alert("Failed to save shelf content! exception type: "+Gb);},success:function(Ib){update_shelf_panel_leaf();hide_shelf_events_from_view();}});}
function update_shelf_panel_leaf(){var Jb='midcom-exec-org.maemo.calendar/shelf.php?action=update_list';jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:HOST_PREFIX+Jb,timeout:12000,dataType:'script',error:function(Kb,Lb,Mb){alert("Failed to update shelf list");},success:function(r){}});}
function hide_shelf_events_from_view(){jQuery.each(shelf_contents,function(i,n){jQuery('#event-'+n.guid).hide().attr({in_shelf:'true'});jQuery('div.event-toolbar-holder div.event-toolbar:visible').hide();});}
function unhide_shelf_events_from_view(){jQuery.each(shelf_contents,function(i,n){jQuery('#event-'+n.guid).show().attr({in_shelf:'false'});});}
function move_event_to_shelf(Nb,Ob){var Pb=jQuery.grep(shelf_contents,function(n,i){return n.guid==Nb;});if(Pb.length==0){var Qb=shelf_contents.push({guid:Nb,data:Ob});save_shelf_contents();}
else{}}
function activate_shelf_item(Rb){var Sb=jQuery.grep(shelf_contents,function(n,i){return n.guid==Rb;});if(Sb.length>0){if(active_shelf_item&&jQuery('#shelf-item-list li.active')[0].id=='shelf-list-item-'+Rb){active_shelf_item=false;jQuery('#shelf-item-list li.active').attr('class','');}
else{jQuery('#shelf-item-list li.active').attr('class','');active_shelf_item=Rb;jQuery('#shelf-list-item-'+Rb).attr('class','active');}}
else{}}
function attach_active_shelf_item(Tb,Ub){}
function empty_shelf(){var Vb='midcom-exec-org.maemo.calendar/shelf.php?action=empty';jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:HOST_PREFIX+Vb,timeout:12000,error:function(Wb,Xb,Yb){alert("Failed to empty shelf list");},success:function(r){unhide_shelf_events_from_view();shelf_contents=Array();jQuery('#shelf-item-list').html('');}});}
function show_event_delete_form(Zb){var $b='ajax/event/delete/'+Zb;load_modal_window($b);}
function enable_event_delete_form(ac){var bc='ajax/event/delete/'+ac;jQuery.ajaxSetup({global:false});var cc={success:show_results,url:APPLICATION_PREFIX+bc,type:'post',timeout:12000};jQuery('#event-delete-form').ajaxForm(cc);}
function on_event_deleted(dc){close_modal_window();jQuery('#event-'+dc).remove();jQuery('#event-'+dc+"-toolbox").remove();}
function show_results(ec,fc){if(fc=="success"){set_modal_window_contents(ec);}
if(fc=="Forbidden"){window.location=HOST_PREFIX+'midcom-login-';}}
function check_dm2_form_submit(gc,hc,ic){var jc=true;jQuery.each(gc,function(i,n){if(n.name=='midcom_helper_datamanager2_cancel'){if(typeof(ic.oncancel)=='function'){jc=ic.oncancel();}
close_modal_window();jc=false;}});return jc;}
function takeover_dm2_form(kc){var lc=jQuery("#org_maemo_calendar");var mc=lc[0].action;if(mc.substr(0,1)=='/'){mc=APPLICATION_PREFIX+mc.substr(1);}
jQuery.ajaxSetup({global:false});kc=jQuery.extend({beforeSubmit:check_dm2_form_submit,success:show_results,url:mc,type:lc[0].method,timeout:120000,oncancel:null},kc);jQuery('#org_maemo_calendar').ajaxForm(kc);}
function enable_buddylist_search(){var nc='midcom-exec-org.maemo.calendar/buddylist.php?action=search';jQuery.ajaxSetup({global:false});var oc={beforeSubmit:show_searching,success:render_buddylist_search_results,url:HOST_PREFIX+nc,type:'post',dataType:'json',timeout:12000};jQuery('#buddylist-search-form').ajaxForm(oc);}
function show_searching(){jQuery('#search-indicator').show();jQuery('#buddylist-search-result-count span').html(0);jQuery('#buddylist-search-result-count').hide();jQuery('#buddylist-search-results').html('');}
function render_buddylist_search_results(pc){var qc=jQuery('#buddylist-search-results');var rc=function(){return['table',{width:"100%",border:0,cellspacing:0,cellpadding:0},['thead',{},['tr',{},this.header_items],'tbody',{},this.result_items]];};var sc=function(){return['div',{class:"search-message"},this.message];};if(pc.count>0){var tc=jQuery.extend({header_items:[],result_items:[]},pc);jQuery('#buddylist-search-result-count span').html(pc.count);jQuery('#buddylist-search-result-count').show();jQuery(qc).tplAppend(tc,rc);}
else{var tc=jQuery.extend({message:''},pc);jQuery(qc).tplAppend(tc,sc);}
jQuery('#search-indicator').hide();}
function remove_item_from_results(uc){var vc=jQuery('#buddylist-search-result-count span')[0].innerHTML;var wc=vc-1;jQuery('#buddylist-search-result-count span').html(' '+wc);jQuery('#result-item-'+uc).fadeOut("slow",function(){jQuery('#result-item-'+uc).remove();});}
function add_person_as_buddy(xc){var yc='ajax/buddylist/add/'+xc;jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:APPLICATION_PREFIX+yc,timeout:12000,error:function(zc,Ac,Bc){alert("Failed to add person as buddy! exception type: "+Ac);},success:function(Cc){remove_item_from_results(xc);refresh_buddylist();}});}
function remove_person_from_buddylist(Dc){var Ec='ajax/buddylist/remove/'+Dc;jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:APPLICATION_PREFIX+Ec,timeout:12000,error:function(Fc,Gc,Hc){},success:function(Ic){jQuery('#buddylist-item-'+Dc).fadeOut("slow",function(){jQuery('#buddylist-item-'+Dc).remove();});jQuery('#buddylist-item-list').Highlight(800,'#4c4c4c');clean_up_person(Dc);}});}
function refresh_buddylist(Jc){var Kc='midcom-exec-org.maemo.calendar/buddylist.php?action=refresh_list';var Lc=jQuery("#buddylist-item-list");jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:HOST_PREFIX+Kc,timeout:12000,error:function(Mc,Nc,Oc){},success:function(r){Lc.html(unescape(r));jQuery('#buddylist-item-list').Highlight(800,'#4c4c4c');if(Jc){var Pc=confirm("You have approved new buddy. We should refresh to get that persons events. Refresh now?");if(Pc){window.location.reload(true);}}}});}
function approve_buddy_request(Qc){var Rc='ajax/buddylist/action/approve/'+Qc;jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:APPLICATION_PREFIX+Rc,timeout:12000,error:function(Sc,Tc,Uc){},success:function(Vc){jQuery('#pending-list-item-'+Qc).fadeOut("slow",function(){jQuery('#pending-list-item-'+Qc).remove();});var Wc=false;if(Vc=='added_new'){Wc=true;}
refresh_buddylist(Wc);}});}
function deny_buddy_request(Xc){var Yc='ajax/buddylist/action/deny/'+Xc;jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:APPLICATION_PREFIX+Yc,timeout:12000,error:function(Zc,$c,ad){},success:function(bd){jQuery('#pending-list-item-'+Xc).fadeOut("slow",function(){jQuery('#pending-list-item-'+Xc).remove();});}});}
function clean_up_person(cd){var dd="#calendar-list-item-"+cd;jQuery(dd).remove();var dd="#calendar-list-item-"+cd+"-tags";jQuery(dd).remove();var dd="#calendar-layer-"+cd;jQuery(dd).fadeOut("slow",function(){jQuery(dd).remove();});}
function edit_calendar_layer_properties(ed){var fd='midcom-exec-org.maemo.calendar/layers.php?action=show_update_layer&layer_id='+ed;load_modal_window(fd);}
function edit_calendar_layer_tag_properties(gd,hd){var jd='midcom-exec-org.maemo.calendar/layers.php?action=show_update_tag&layer_id='+gd+'&tag_id='+hd;load_modal_window(jd);}
function enable_layer_update_form(kd,ld){var md='layer';var nd='midcom-exec-org.maemo.calendar/layers.php?action=update_layer&layer_id='+kd;if(ld!=undefined){md='layer_tag';nd='midcom-exec-org.maemo.calendar/layers.php?action=update_tag&layer_id='+kd+'&tag_id='+ld;}
jQuery.ajaxSetup({global:false});var od={beforeSubmit:show_processing,success:processing_successfull,url:HOST_PREFIX+nd,type:'post',timeout:12000};var pd='#update-'+md+'-form';jQuery(pd).ajaxForm(od);}
function enable_tag_create_form(qd){url='midcom-exec-org.maemo.calendar/layers.php?action=create_tag&layer_id='+qd;jQuery.ajaxSetup({global:false});var rd={beforeSubmit:show_processing,success:processing_successfull,url:HOST_PREFIX+url,type:'post',timeout:12000};var sd='#create-new_tag-form';jQuery(sd).ajaxForm(rd);}
function show_processing(td,ud,vd){}
function processing_successfull(wd,xd){close_modal_window();if(wd>0){window.location=window.location;}}
function on_view_update(){hide_shelf_events_from_view();}
function show_layout(){jQuery('#calendar-loading').hide();jQuery('#calendar-holder').show();jQuery('div.application div.header').show();jQuery('#main-panel').show();}
function modify_foreground_color(yd){jQuery.each(jQuery(yd),function(i,n){execute_modify_foreground_color(n);});}
function execute_modify_foreground_color(zd){function bg_to_bits(Ad){Ad=String(Ad);Ad=Ad.replace(/ /g,'');Ad=Ad.toLowerCase();var Bd=[];var Cd=[{re:/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,process:function(Dd){return[parseInt(Dd[1]),parseInt(Dd[2]),parseInt(Dd[3])];}},{re:/^(\w{2})(\w{2})(\w{2})$/,process:function(Ed){return[parseInt(Ed[1],16),parseInt(Ed[2],16),parseInt(Ed[3],16)];}}];for(var i=0;i<Cd.length;i++){var re=Cd[i].re;var Fd=Cd[i].process;var Gd=re.exec(Ad);if(Gd){Bd=Fd(Gd);}}
Bd[0]=(Bd[0]<0||isNaN(Bd[0]))?0:((Bd[0]>255)?255:Bd[0]);Bd[1]=(Bd[1]<0||isNaN(Bd[1]))?0:((Bd[1]>255)?255:Bd[1]);Bd[2]=(Bd[2]<0||isNaN(Bd[2]))?0:((Bd[2]>255)?255:Bd[2]);return Bd;}
function RGBToHSL(Hd){var Id,max,delta,h,s,l;var r=Hd[0],g=Hd[1],b=Hd[2];Id=Math.min(r,Math.min(g,b));max=Math.max(r,Math.max(g,b));delta=max-Id;l=(Id+max)/2;s=0;if(l>0&&l<1){s=delta/(l<0.5?(2*l):(2-2*l));}
h=0;if(delta>0){if(max==r&&max!=g)h+=(g-b)/delta;if(max==g&&max!=b)h+=(2+(b-r)/delta);if(max==b&&max!=r)h+=(4+(r-g)/delta);h/=6;}
return[h,s,l];}
bg=jQuery(zd).css('background');if(bg==undefined||bg==""){bg=jQuery(zd).css('background-color');}
if(bg==undefined||bg==""){bg=jQuery(zd).attr('bgcolor');}
if(bg==undefined||bg==""){jQuery(zd).css({color:"#3c3c3c"});return false;}
if(bg.charAt(0)=='#'){bg=bg.substr(1,6);}
var Jd=RGBToHSL(bg_to_bits(bg));var Kd='#ffffff';if(Jd[0]<0.5){Kd='#3c3c3c';}
jQuery(zd).css({color:Kd});}
jQuery(document).ready(function(){jQuery.extend(jQuery.blockUI.defaults.overlayCSS,{backgroundColor:'#b39169'});jQuery('#calendar-loading').ajaxStart(function(){jQuery('#calendar-holder').hide();var Ld=MIDCOM_STATIC_URL+"/org.maemo.calendar/images/indicator.gif";jQuery.blockUI('<img src="'+Ld+'" alt="Loading..." /> Please wait');}).ajaxStop(function(){jQuery.unblockUI();});});