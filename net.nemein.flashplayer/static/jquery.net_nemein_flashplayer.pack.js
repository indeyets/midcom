function run_client_action(na,oa,pa){var qa=jQuery('[@nnf_id='+na+']');if(qa.attr('nnf_type')=='player'){qa.run_playerclient_action(oa,pa);}
else{qa.run_playlistclient_action(oa,pa);}}
jQuery.fn.extend({net_nemein_flashplayer:function(ra){ra=jQuery.extend({},jQuery.net_nemein_flashplayer_player.defaults,{},ra);return new jQuery.net_nemein_flashplayer_player(this,ra);},run_playerclient_action:function(sa,ta){return this.trigger("run_client_action",[sa,ta]);},run_player_action:function(ua,va){return this.trigger("run_player_action",[ua,va]);},set_video:function(wa,xa){return this.trigger("set_video",[wa,xa]);}});jQuery.net_nemein_flashplayer_player=function(ya,za){var Aa=ya;var Ba=generate_id();var Ca=new FlashProxy(Ba,generate_static_url(za.proxy_gateway_swf_path));var Da="player_id="+Ba;var Ea=new FlashTag(generate_static_url(za.flash_player_path),za.player_width,za.player_height);Ea.setVersion(za.flash_version);Ea.setFlashvars(Da);Aa.attr("nnf_id",Ba).attr("nnf_type","player").html(Ea.toString()).bind("run_client_action",function(Fa,Ga,Ha){var Ia=eval(Ga);Ia.apply(Ia,[Ha]);}).bind("run_player_action",function(Ja,Ka,La){proxy_send(Ka,La);}).bind("set_video",function(Ma,Na,za){set_video(Na,za);});proxy_send('player_embedded',{element_id:Aa.attr("id")});function set_video(Oa,Pa){var Qa=jQuery.extend({auto_play:za.auto_play},Pa||{});var Ra=[Oa,Qa];if(za.set_video_callback&&typeof(za.set_video_callback)=="function"){var Sa=eval(za.set_video_callback);Sa.apply(Sa,[Ra]);}
proxy_send('set_video',Ra);}
function proxy_send(Ta,Ua){Ca.call(Ta,Ua);}
function generate_id(){random_key=Math.floor(Math.random()*4013);return "net_nemein_flashplayer_player_"+(10016486+(random_key*22423));}
function generate_static_url(Va){return za.site_root+za.static_prefix+Va;}
function player_initialized(Wa){}}
jQuery.net_nemein_flashplayer_player.defaults={site_root:'/',static_prefix:'midcom-static/',proxy_gateway_swf_path:'net.nemein.flashplayer/gw/JavaScriptFlashGateway.swf',flash_player_path:'net.nemein.flashplayer/player.swf',flash_version:'8,0,0,0',set_video_callback:false,auto_play:false,player_width:450,player_height:358}
jQuery.fn.extend({net_nemein_flashplaylist:function(Xa){Xa=jQuery.extend({},jQuery.net_nemein_flashplayer_playlist.defaults,{},Xa);return new jQuery.net_nemein_flashplayer_playlist(this,Xa);},run_playlistclient_action:function(Ya,Za){return this.trigger("run_client_action",[Ya,Za]);},run_playlist_action:function($a,ab){return this.trigger("run_playlist_action",[$a,ab]);},load_playlist:function(bb){return this.trigger("load_playlist",[bb]);},add_item:function(cb){var db=new jQuery.net_nemein_flashplayer_playlist_item(cb);return this.trigger("add_item",[db]);},remove_item:function(eb){return this.trigger("remove_item",[eb]);},connect_player:function(fb){return this.trigger("connect_player",[fb]);},disconnect_player:function(gb){return this.trigger("disconnect_player",[gb]);},change_movie:function(hb){return this.trigger("change_movie",[hb]);}});jQuery.net_nemein_flashplayer_playlist=function(ib,jb){var kb=ib;var lb=generate_id();var mb=new FlashProxy(lb,generate_static_url(jb.proxy_gateway_swf_path));var nb=[];var ob=false;var pb=false;var qb=false;var rb=[];var sb="playlist_id="+lb;var tb=new FlashTag(generate_static_url(jb.flash_playlist_path),jb.width,jb.height);tb.setVersion(jb.flash_version);tb.setFlashvars(sb);kb.attr("nnf_id",lb).attr("nnf_type","playlist").html(tb.toString()).bind("run_client_action",function(ub,vb,wb){var xb=eval(vb);xb.apply(xb,[wb]);}).bind("run_playlist_action",function(yb,zb,Ab){proxy_send(zb,Ab);}).bind("load_playlist",function(Bb,Cb){load_playlist(Cb);}).bind("add_item",function(Db,Eb){add_item(Eb);}).bind("remove_item",function(Fb,Gb){remove_item(Gb);}).bind("connect_player",function(Hb,Ib){connect_player(Ib);}).bind("disconnect_player",function(Jb,Kb){disconnect_player(Kb);}).bind("change_movie",function(Lb,Mb){change_movie(Mb);});proxy_send('playlist_embedded',{element_id:kb.attr("id")});function change_movie(Nb){var Ob={item:Nb,options:{},playlist_id:lb};jQuery(rb).each(function(i,Pb){Pb.set_video(Nb);});}
function _ka(Qb){var Rb=false;Rb=jQuery.grep(rb,function(n,i){return n.attr("nnf_id")==Qb;});if(Rb==false){return false;}
return true;}
function connect_player(Sb){if(!_ka(Sb)){var Tb=jQuery('[@nnf_id='+Sb+']');rb.push(Tb);}}
function disconnect_player(Ub){if(_ka(Ub)){rb=jQuery.grep(rb,function(n,i){return n!=Ub;});}}
function _la(Vb){var Wb=false;Wb=jQuery.grep(nb,function(n,i){return n.id==Vb;});if(!Wb){return false;}
return true;}
function add_item(Xb){if(!_la(Xb.id)){nb.push(Xb);var Yb={item:Xb};proxy_send('add_item',Yb);}}
function remove_item(Zb){if(_la(Zb)){nb=jQuery.grep(nb,function(n,i){return n.id!=Zb;});var $b={id:Zb};proxy_send('remove_item',$b);}}
function load_playlist(ac){_ma(ac,loading_success,loading_failure)}
function loading_success(bc){var cc=[];jQuery('item',bc).each(function(dc){var ec=jQuery(this);cc[dc]={index:dc,id:ec.find("id").text(),title:ec.find("title").text(),video_url:ec.find("video_url").text(),thumbnail_url:ec.find("thumbnail_url").text(),data_url:ec.find("data_url").text()};jQuery.each(jb.item_extra_keys,function(i,fc){cc[dc][fc]=ec.find(fc).text();});});nb=cc;pb=true;if(nb.length>0&&ob&&!qb){var gc={content:nb};proxy_send('playlist_loading_success',gc);}}
function loading_failure(hc,ic){var jc=hc;var kc={status:jc};proxy_send('playlist_loading_failure',kc);}
function _ma(lc,mc,nc){jQuery.ajax({type:"GET",url:lc,dataType:'xml',data:jQuery.extend({limit:jb.max_items},{}),error:function(oc,pc,qc){nc(pc,qc);},success:function(rc){mc(rc);}});}
function proxy_send(sc,tc){mb.call(sc,tc);}
function generate_id(){random_key=Math.floor(Math.random()*4013);return "net_nemein_flashplayer_playlist_"+(10016486+(random_key*22423));}
function generate_static_url(uc){return jb.site_root+jb.static_prefix+uc;}
function playlist_initialized(vc){ob=true;if(nb.length>0&&!qb){var vc={content:nb};if(pb){proxy_send('playlist_loading_success',vc);}}}}
jQuery.net_nemein_flashplayer_playlist.defaults={site_root:jQuery.net_nemein_flashplayer_player.defaults.site_root,static_prefix:jQuery.net_nemein_flashplayer_player.defaults.static_prefix,proxy_gateway_swf_path:jQuery.net_nemein_flashplayer_player.defaults.proxy_gateway_swf_path,flash_playlist_path:'net.nemein.flashplayer/playlist.swf',flash_version:'8,0,0,0',item_extra_keys:[],max_items:20,width:450,height:118}
jQuery.net_nemein_flashplayer_playlist_item=function(wc){wc=jQuery.extend({},jQuery.net_nemein_flashplayer_playlist_item.defaults,{},wc);return this;}
jQuery.net_nemein_flashplayer_playlist_item.defaults={id:'',title:'',video_url:'',thumbnail_url:'',data_url:''}