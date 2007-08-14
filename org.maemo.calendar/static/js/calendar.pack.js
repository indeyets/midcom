if(console==undefined){var console={};console.log=function(aa){return;}}
function finishCalendarLoad(id){jQuery('#'+id).fadeIn("",function(){on_view_update();var ba=calendar_config["types_classes"][calendar_config["type"]];if(ba=='week'||ba=='day'){jQuery('div.calendar-timeline-holder')[0].scrollTop=calendar_config["start_hour_x"];}});}
function run_scripts(e){if(e.nodeType!=1)return;if(e.tagName.toLowerCase()=='script'){eval(e.text);}
else{var n=e.firstChild;while(n){if(n.nodeType==1)run_scripts(n);n=n.nextSibling;}}}
function zoom_view(ca,da){if(ca){if(calendar_config["type"]==4){return false;}
calendar_config["type"]+=1;}
else{if(calendar_config["type"]==1){return false;}
calendar_config["type"]-=1;}
var ea=jQuery('#date-selection-form');var fa=APPLICATION_PREFIX+da+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({url:fa,timeout:12000,error:function(ga,ha,ia){alert("Failed to zoom request.statusText: "+ga.statusText);jQuery('#calendar-holder').show();},success:function(r){var ja=unescape(r);jQuery('#calendar-holder').html(ja);var ka=calendar_config["types_classes"][calendar_config["type"]];jQuery('body').attr('class',ka);setTimeout("finishCalendarLoad('calendar-holder')",400);}});return false;}
function update_date_selection(la,ma,na){var oa=[];var pa=jQuery('#date-selection-form');jQuery(':input',pa).each(function(){if(this.name=="month-select"){this.value=ma;}
if(this.name=="day-select"){this.value=la;}
if(this.name=="year-select"){this.value=na;}
oa.push(this.name+'='+escape(this.value));});return oa;}
function goto_prev(){var qa=new Date();var ra=qa.getDate();var sa=qa.getMonth();var ta=qa.getFullYear();var ua=new Date(ra,sa,ta);var va=jQuery('#date-selection-form');jQuery(':input',va).each(function(){if(this.name=="month-select"){sa=this.value-1;}
if(this.name=="day-select"){ra=this.value;}
if(this.name=="year-select"){ta=this.value;}});ua.setDate(ra);ua.setMonth(sa);ua.setYear(ta);if(calendar_config["type"]==1){ua.setYear(ua.getFullYear()-1);}
if(calendar_config["type"]==2){ua.setMonth((ua.getMonth()-1));}
if(calendar_config["type"]==3){ua.setDate((ua.getDate()-7));}
if(calendar_config["type"]==4){ua.setDate((ua.getDate()-1));}
var wa=String((ua.getMonth()+1));if(wa.length<2){wa="0"+wa;}
var xa=String((ua.getDate()));if(xa.length<2){xa="0"+xa;}
var ya=update_date_selection(xa,wa,ua.getFullYear());timestamp=ua.getTime()/1000.0;calendar_config["timestamp"]=timestamp;var za=APPLICATION_PREFIX+'ajax/change/date/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:ya.join('&'),url:za,timeout:12000,error:function(Aa,Ba,Ca){alert("Failed to change date");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});}
function goto_next(){var Da=new Date();var Ea=Da.getDate();var Fa=Da.getMonth();var Ga=Da.getFullYear();var Ha=new Date(Ea,Fa,Ga);var Ia=jQuery('#date-selection-form');jQuery(':input',Ia).each(function(){if(this.name=="day-select"){Ea=this.value;}
if(this.name=="month-select"){Fa=this.value-1;}
if(this.name=="year-select"){Ga=this.value;}});Ha.setDate(Ea);Ha.setMonth(Fa);Ha.setYear(Ga);if(calendar_config["type"]==1){Ha.setYear(Ha.getFullYear()+1);}
if(calendar_config["type"]==2){Ha.setMonth((Ha.getMonth()+1));}
if(calendar_config["type"]==3){Ha.setDate((Ha.getDate()+7));}
if(calendar_config["type"]==4){Ha.setDate((Ha.getDate()+1));}
var Ja=String((Ha.getMonth()+1));if(Ja.length<2){Ja="0"+Ja;}
var Ka=String((Ha.getDate()));if(Ka.length<2){Ka="0"+Ka;}
var La=update_date_selection(Ka,Ja,Ha.getFullYear());timestamp=Ha.getTime()/1000.0;calendar_config["timestamp"]=timestamp;var Ma=APPLICATION_PREFIX+'ajax/change/date/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:La.join('&'),url:Ma,timeout:12000,error:function(Na,Oa,Pa){alert("Failed to change date");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});}
function goto_today(){var Qa=jQuery('#date-selection-form');var Ra=new Date();var Sa=Ra.getDate();var Ta=Ra.getMonth();var Ua=Ra.getFullYear();var Va=String((Ta+1));if(Va.length<2){Va="0"+Va;}
var Wa=update_date_selection(Sa,Va,Ua);var Xa=new Date(Ua,Ta,Sa);timestamp=Xa.getTime()/1000.0;calendar_config["timestamp"]=timestamp;var Ya=APPLICATION_PREFIX+'ajax/change/date/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:Wa.join('&'),url:Ya,timeout:12000,error:function(Za,$a,ab){alert("Failed to change date");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});return false;}
function change_date(){var bb=jQuery('#date-selection-form');var cb=new Date();var db=[];var eb=cb.getDate();var fb=cb.getMonth();var gb=cb.getFullYear();jQuery(':input',bb).each(function(){if(this.name=="month-select"){fb=this.value-1;}
if(this.name=="day-select"){eb=this.value;}
if(this.name=="year-select"){gb=this.value;}
db.push(this.name+'='+escape(this.value));});var hb=new Date(gb,fb,eb);timestamp=hb.getTime()/1000.0;calendar_config["timestamp"]=timestamp;var ib=APPLICATION_PREFIX+'ajax/change/date/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:db.join('&'),url:ib,timeout:12000,error:function(jb,kb,lb){alert("Failed to change date");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});return false;};function change_timezone(){var mb=jQuery('#timezone-selection-form');var nb=null;var ob=[];jQuery(':input',mb).each(function(){if(this.name=="timezone"){nb=this.value-1;}
ob.push(this.name+'='+escape(this.value));});var pb=APPLICATION_PREFIX+'ajax/change/timezone/'+calendar_config["timestamp"]+'/'+calendar_config["type"];jQuery.ajaxSetup({global:true});jQuery.ajax({data:ob.join('&'),url:pb,timeout:12000,error:function(qb,rb,sb){alert("Failed to change timezone");jQuery('#calendar-holder').show();},success:function(r){jQuery('#calendar-holder').html(unescape(r));setTimeout("finishCalendarLoad('calendar-holder')",400);}});return false;};function create_event(tb){var ub="ajax/event/create/"+tb;if(active_shelf_item){ub="ajax/event/move/"+active_shelf_item+"/"+tb;}
load_modal_window(ub);}
function close_create_event(){close_modal_window();return;}
function load_modal_window(vb){var wb=jQuery("div.calendar-modal-window");if(vb.substr(0,7)!='midcom-'){vb=APPLICATION_PREFIX+vb;}
else{vb=HOST_PREFIX+vb;}
jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:vb,timeout:12000,error:function(xb,yb,zb){if(xb.statusText=="Forbidden"){window.location=HOST_PREFIX+'midcom-login-';}},success:function(r){wb.html(unescape(r));jQuery("div.calendar-modal-window").show();}});}
function close_modal_window(){jQuery("div.calendar-modal-window").hide();return;}
function set_modal_window_contents(Ab){jQuery("div.calendar-modal-window").html(unescape(Ab));}
function load_shelf_contents(){var Bb='midcom-exec-org.maemo.calendar/shelf.php?action=load';jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:HOST_PREFIX+Bb,timeout:12000,dataType:'json',error:function(Cb,Db,Eb){},success:function(r){shelf_contents=r;hide_shelf_events_from_view();}});}
function save_shelf_contents(){var Fb='midcom-exec-org.maemo.calendar/shelf.php?action=save';jQuery.ajaxSetup({global:false});jQuery.ajax({type:"POST",url:HOST_PREFIX+Fb,data:{data:protoToolkit.toJSON(shelf_contents)},timeout:12000,error:function(Gb,Hb,Ib){alert("Failed to save shelf content! exception type: "+Hb);},success:function(Jb){update_shelf_panel_leaf();hide_shelf_events_from_view();}});}
function update_shelf_panel_leaf(){var Kb='midcom-exec-org.maemo.calendar/shelf.php?action=update_list';jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:HOST_PREFIX+Kb,timeout:12000,dataType:'script',error:function(Lb,Mb,Nb){alert("Failed to update shelf list");},success:function(r){}});}
function hide_shelf_events_from_view(){jQuery.each(shelf_contents,function(i,n){jQuery('#event-'+n.guid).hide().attr({in_shelf:'true'});jQuery('div.event-toolbar-holder div.event-toolbar:visible').hide();});}
function unhide_shelf_events_from_view(){jQuery.each(shelf_contents,function(i,n){jQuery('#event-'+n.guid).show().attr({in_shelf:'false'});});}
function move_event_to_shelf(Ob,Pb){var Qb=jQuery.grep(shelf_contents,function(n,i){return n.guid==Ob;});if(Qb.length==0){var Rb=shelf_contents.push({guid:Ob,data:Pb});save_shelf_contents();}
else{}}
function activate_shelf_item(Sb){var Tb=jQuery.grep(shelf_contents,function(n,i){return n.guid==Sb;});if(Tb.length>0){if(active_shelf_item&&jQuery('#shelf-item-list li.active')[0].id=='shelf-list-item-'+Sb){active_shelf_item=false;jQuery('#shelf-item-list li.active').attr('class','');}
else{jQuery('#shelf-item-list li.active').attr('class','');active_shelf_item=Sb;jQuery('#shelf-list-item-'+Sb).attr('class','active');}}
else{}}
function attach_active_shelf_item(Ub,Vb){}
function empty_shelf(){var Wb='midcom-exec-org.maemo.calendar/shelf.php?action=empty';jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:HOST_PREFIX+Wb,timeout:12000,error:function(Xb,Yb,Zb){alert("Failed to empty shelf list");},success:function(r){unhide_shelf_events_from_view();shelf_contents=Array();jQuery('#shelf-item-list').html('');}});}
function show_event_delete_form($b){var ac='ajax/event/delete/'+$b;load_modal_window(ac);}
function enable_event_delete_form(bc){var cc='ajax/event/delete/'+bc;jQuery.ajaxSetup({global:false});var dc={success:show_results,url:APPLICATION_PREFIX+cc,type:'post',timeout:12000};jQuery('#event-delete-form').ajaxForm(dc);}
function on_event_deleted(ec){close_modal_window();jQuery('#event-'+ec).remove();jQuery('#event-'+ec+"-toolbox").remove();}
function show_results(fc,gc){if(gc=="success"){set_modal_window_contents(fc);}
if(gc=="Forbidden"){window.location=HOST_PREFIX+'midcom-login-';}}
function check_dm2_form_submit(hc,ic,jc){var kc=true;jQuery.each(hc,function(i,n){if(n.name=='midcom_helper_datamanager2_cancel'){if(typeof(jc.oncancel)=='function'){kc=jc.oncancel();}
close_modal_window();kc=false;}});return kc;}
function takeover_dm2_form(lc){var mc=jQuery("#org_maemo_calendar");var nc=mc[0].action;if(lc.url){nc=lc.url;}
if(nc.substr(0,1)=='/'){nc=nc.substr(1);}
if(nc.substr(0,7)!='http://'||nc.substr(0,4)!='www.'){final_url=APPLICATION_PREFIX+nc;}
else{final_url=nc;}
jQuery.ajaxSetup({global:false});lc=jQuery.extend({beforeSubmit:check_dm2_form_submit,success:show_results,url:final_url,type:mc[0].method,timeout:120000,oncancel:null},lc);jQuery('#org_maemo_calendar').ajaxForm(lc);}
function enable_buddylist_search(){var oc='midcom-exec-org.maemo.calendar/buddylist.php?action=search';jQuery.ajaxSetup({global:false});var pc={beforeSubmit:show_searching,success:render_buddylist_search_results,url:HOST_PREFIX+oc,type:'post',dataType:'json',timeout:12000};jQuery('#buddylist-search-form').ajaxForm(pc);}
function show_searching(){jQuery('#search-indicator').show();jQuery('#buddylist-search-result-count span').html(0);jQuery('#buddylist-search-result-count').hide();jQuery('#buddylist-search-results').html('');}
function render_buddylist_search_results(qc){var rc=jQuery('#buddylist-search-results');var sc=function(){return['table',{width:"100%",border:0,cellspacing:0,cellpadding:0},['thead',{},['tr',{},this.header_items],'tbody',{},this.result_items]];};var tc=function(){return['div',{class:"search-message"},this.message];};if(qc.count>0){var uc=jQuery.extend({header_items:[],result_items:[]},qc);jQuery('#buddylist-search-result-count span').html(qc.count);jQuery('#buddylist-search-result-count').show();jQuery(rc).tplAppend(uc,sc);}
else{var uc=jQuery.extend({message:''},qc);jQuery(rc).tplAppend(uc,tc);}
jQuery('#search-indicator').hide();}
function remove_item_from_results(vc){var wc=jQuery('#buddylist-search-result-count span')[0].innerHTML;var xc=wc-1;jQuery('#buddylist-search-result-count span').html(' '+xc);jQuery('#result-item-'+vc).fadeOut("slow",function(){jQuery('#result-item-'+vc).remove();});}
function add_person_as_buddy(yc){var zc='ajax/buddylist/add/'+yc;jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:APPLICATION_PREFIX+zc,timeout:12000,error:function(Ac,Bc,Cc){alert("Failed to add person as buddy! exception type: "+Bc);},success:function(Dc){remove_item_from_results(yc);refresh_buddylist();}});}
function remove_person_from_buddylist(Ec){var Fc='ajax/buddylist/remove/'+Ec;jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:APPLICATION_PREFIX+Fc,timeout:12000,error:function(Gc,Hc,Ic){},success:function(Jc){jQuery('#buddylist-item-'+Ec).fadeOut("slow",function(){jQuery('#buddylist-item-'+Ec).remove();});jQuery('#buddylist-item-list').Highlight(800,'#4c4c4c');clean_up_person(Ec);}});}
function refresh_buddylist(Kc){var Lc='midcom-exec-org.maemo.calendar/buddylist.php?action=refresh_list';var Mc=jQuery("#buddylist-item-list");jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:HOST_PREFIX+Lc,timeout:12000,error:function(Nc,Oc,Pc){},success:function(r){Mc.html(unescape(r));jQuery('#buddylist-item-list').Highlight(800,'#4c4c4c');if(Kc){var Qc=confirm("You have approved new buddy. We should refresh to get that persons events. Refresh now?");if(Qc){window.location.reload(true);}}}});}
function approve_buddy_request(Rc){var Sc='ajax/buddylist/action/approve/'+Rc;jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:APPLICATION_PREFIX+Sc,timeout:12000,error:function(Tc,Uc,Vc){},success:function(Wc){jQuery('#pending-list-item-'+Rc).fadeOut("slow",function(){jQuery('#pending-list-item-'+Rc).remove();});var Xc=false;if(Wc=='added_new'){Xc=true;}
refresh_buddylist(Xc);}});}
function deny_buddy_request(Yc){var Zc='ajax/buddylist/action/deny/'+Yc;jQuery.ajaxSetup({global:false});jQuery.ajax({type:"GET",url:APPLICATION_PREFIX+Zc,timeout:12000,error:function($c,ad,bd){},success:function(cd){jQuery('#pending-list-item-'+Yc).fadeOut("slow",function(){jQuery('#pending-list-item-'+Yc).remove();});}});}
function clean_up_person(dd){var ed="#calendar-list-item-"+dd;jQuery(ed).remove();var ed="#calendar-list-item-"+dd+"-tags";jQuery(ed).remove();var ed="#calendar-layer-"+dd;jQuery(ed).fadeOut("slow",function(){jQuery(ed).remove();});}
function edit_calendar_layer_properties(fd){var gd='midcom-exec-org.maemo.calendar/layers.php?action=show_update_layer&layer_id='+fd;load_modal_window(gd);}
function edit_calendar_layer_tag_properties(hd,jd){var kd='midcom-exec-org.maemo.calendar/layers.php?action=show_update_tag&layer_id='+hd+'&tag_id='+jd;load_modal_window(kd);}
function enable_layer_update_form(ld,md){var nd='layer';var od='midcom-exec-org.maemo.calendar/layers.php?action=update_layer&layer_id='+ld;if(md!=undefined){nd='layer_tag';od='midcom-exec-org.maemo.calendar/layers.php?action=update_tag&layer_id='+ld+'&tag_id='+md;}
jQuery.ajaxSetup({global:false});var pd={beforeSubmit:show_processing,success:processing_successfull,url:HOST_PREFIX+od,type:'post',timeout:12000};var qd='#update-'+nd+'-form';jQuery(qd).ajaxForm(pd);}
function enable_tag_create_form(rd){url='midcom-exec-org.maemo.calendar/layers.php?action=create_tag&layer_id='+rd;jQuery.ajaxSetup({global:false});var sd={beforeSubmit:show_processing,success:processing_successfull,url:HOST_PREFIX+url,type:'post',timeout:12000};var td='#create-new_tag-form';jQuery(td).ajaxForm(sd);}
function show_processing(ud,vd,wd){}
function processing_successfull(xd,yd){close_modal_window();if(xd>0){window.location=window.location;}}
function on_view_update(){hide_shelf_events_from_view();}
function show_layout(){jQuery('#calendar-loading').hide();jQuery('#calendar-holder').show();jQuery('div.application div.header').show();jQuery('#main-panel').show();}
function modify_foreground_color(zd){jQuery.each(jQuery(zd),function(i,n){execute_modify_foreground_color(n);});}
function execute_modify_foreground_color(Ad){function bg_to_bits(Bd){Bd=String(Bd);Bd=Bd.replace(/ /g,'');Bd=Bd.toLowerCase();var Cd=[];var Dd=[{re:/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,process:function(Ed){return[parseInt(Ed[1]),parseInt(Ed[2]),parseInt(Ed[3])];}},{re:/^(\w{2})(\w{2})(\w{2})$/,process:function(Fd){return[parseInt(Fd[1],16),parseInt(Fd[2],16),parseInt(Fd[3],16)];}}];for(var i=0;i<Dd.length;i++){var re=Dd[i].re;var Gd=Dd[i].process;var Hd=re.exec(Bd);if(Hd){Cd=Gd(Hd);}}
Cd[0]=(Cd[0]<0||isNaN(Cd[0]))?0:((Cd[0]>255)?255:Cd[0]);Cd[1]=(Cd[1]<0||isNaN(Cd[1]))?0:((Cd[1]>255)?255:Cd[1]);Cd[2]=(Cd[2]<0||isNaN(Cd[2]))?0:((Cd[2]>255)?255:Cd[2]);return Cd;}
function RGBToHSL(Id){var Jd,max,delta,h,s,l;var r=Id[0],g=Id[1],b=Id[2];Jd=Math.min(r,Math.min(g,b));max=Math.max(r,Math.max(g,b));delta=max-Jd;l=(Jd+max)/2;s=0;if(l>0&&l<1){s=delta/(l<0.5?(2*l):(2-2*l));}
h=0;if(delta>0){if(max==r&&max!=g)h+=(g-b)/delta;if(max==g&&max!=b)h+=(2+(b-r)/delta);if(max==b&&max!=r)h+=(4+(r-g)/delta);h/=6;}
return[h,s,l];}
bg=jQuery(Ad).css('background');if(bg==undefined||bg==""){bg=jQuery(Ad).css('background-color');}
if(bg==undefined||bg==""){bg=jQuery(Ad).attr('bgcolor');}
if(bg==undefined||bg==""){jQuery(Ad).css({color:"#3c3c3c"});return false;}
if(bg.charAt(0)=='#'){bg=bg.substr(1,6);}
var Kd=RGBToHSL(bg_to_bits(bg));var Ld='#ffffff';if(Kd[0]<0.5){Ld='#3c3c3c';}
jQuery(Ad).css({color:Ld});}
jQuery(document).ready(function(){jQuery.extend(jQuery.blockUI.defaults.overlayCSS,{backgroundColor:'#b39169'});jQuery('#calendar-loading').ajaxStart(function(){jQuery('#calendar-holder').hide();var Md=MIDCOM_STATIC_URL+"/org.maemo.calendar/images/indicator.gif";jQuery.blockUI('<img src="'+Md+'" alt="Loading..." /> Please wait');}).ajaxStop(function(){jQuery.unblockUI();});});