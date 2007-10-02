function run_client_action(client_id, action_name, action_data)
{
    //console.log("run_client_action client_id: "+client_id+" action_name: "+action_name);
    
    var receiver = jQuery('[@nnf_id=' + client_id + ']');
    //console.log("receiver nnf_id: "+receiver.attr('nnf_id'));
    //console.log("receiver nnf_type: "+receiver.attr('nnf_type'));
    
    if (receiver.attr('nnf_type') == 'player')
    {
        receiver.run_playerclient_action(action_name, action_data);
    }
    else
    {
        receiver.run_playlistclient_action(action_name, action_data);        
    }
}

/*
 * jQuery("#video_holder").net_nemein_flashplayer();
 * jQuery("#playlist_holder").net_nemein_flashplaylist();
 * jQuery("#video_holder").connect_playlist( jQuery("#playlist_holder").getID() );
 */

jQuery.fn.extend({
	net_nemein_flashplayer: function(options) {
		options = jQuery.extend({}, jQuery.net_nemein_flashplayer_player.defaults, {
		}, options);
	    return new jQuery.net_nemein_flashplayer_player(this, options);
	},
	run_playerclient_action: function(action, args) {
		return this.trigger("run_client_action",[action, args]);
	},
	run_player_action: function(action, args) {
		return this.trigger("run_player_action",[action, args]);
	},
	set_video: function(item, options) {
		return this.trigger("set_video",[item, options]);
	}
});

jQuery.net_nemein_flashplayer_player = function(object, options)
{    
    //console.log("net_nemein_flashplayer_player object: "+object);
    
    var _self = this;    
    var _object = object;
    var _player_id = generate_id();
    var _proxy = new FlashProxy(_player_id, generate_static_url(options.proxy_gateway_swf_path));
    
    var _flashvars = "player_id="+_player_id;
    
    var player_content = '';
    
    if ( jQuery.browser.msie == true )
    {
        var flashplayer_tag = { movie: generate_static_url(options.flash_player_path), width: options.player_width, height: options.player_height, flashvars: _flashvars };    
        if (   _object.attr('id') == undefined
            || _object.attr('id') == '')
        {
            _object.attr('id', _player_id);
        }
        UFO.create(flashplayer_tag, _object.attr('id'));
        UFO.writeSWF(_object.attr('id'));        
    }
    else
    {
        var flashplayer_tag = new FlashTag(generate_static_url(options.flash_player_path), options.player_width, options.player_height);
        flashplayer_tag.setVersion(options.flash_version);
        flashplayer_tag.setFlashvars(_flashvars);
        player_content = flashplayer_tag.toString();
    }
        
    _object.attr("nnf_id",_player_id)
    .attr("nnf_type","player")
    .html(player_content);
    //.html(UFO.writeSWF(_object.attr('id')));
    
    _object.bind("run_client_action", function(event, action, args){
	    //console.log("player run_client_action action: "+action);
	    //console.log("player run_client_action args: "+args);
        var functionToCall = eval(action);
        functionToCall.apply(functionToCall, [args]);
	}).bind("run_player_action", function(event, action, args){
	    //console.log("run_player_action action: "+action);
	    //console.log("run_player_action args.length: "+args.length);
	    proxy_send(action, args);
	}).bind("set_video", function(event, item, options){
        // console.log("set_video item.id: "+item.id);
        // console.log("set_video item.video_url: "+item.video_url);
        // console.log("set_video options: "+options);
	    set_video(item, options);
	}).bind('embedded', function(e){
	    if (options.on_embedded) {
    	    options.on_embedded(e);
	    }
	});
	
	//proxy_send('player_embedded', {element_id: _object.attr("id")});
	_object.trigger('embedded');
	
	function set_video(item, video_options)
	{
	    //console.log("set_video item:"+item+" video_options:"+video_options);
	    
	    var args_options = jQuery.extend({
	        auto_play: options.auto_play
	    }, video_options || {});
        
        //console.log("item.video_url: "+item.video_url);
        
	    var send_args = [
	        item,
	        args_options
	    ];
	    
	    if (   options.set_video_callback
	        && typeof(options.set_video_callback) == "function")
	    {
            var functionToCall = eval(options.set_video_callback);
            functionToCall.apply(functionToCall, [send_args]);
	    }
	    
	    proxy_send('set_video',send_args);
	}
    
    function proxy_send(action, args)
    {
        _proxy.call(action, args);
    }
    
    function generate_id()
    {
        random_key = Math.floor(Math.random()*4013);
        return "net_nemein_flashplayer_player_" + (10016486 + (random_key * 22423));
    }
    
    function generate_static_url(suffix)
    {
        return options.site_root + options.static_prefix + suffix;
    }
    
    /**
     * Functions callable from flash
    **/
    
    function player_initialized(args)
    {
        //console.log("player_initialized");
    }
    
    return this;
};

jQuery.net_nemein_flashplayer_player.defaults = {
    site_root: '/',
    static_prefix: 'midcom-static/',
    proxy_gateway_swf_path: 'net.nemein.flashplayer/gw/JavaScriptFlashGateway.swf',
    flash_player_path: 'net.nemein.flashplayer/player.swf',
    flash_version: '8,0,0,0',
	set_video_callback: false,
	auto_play: false,
	player_width: 450,
	player_height: 358
};

jQuery.fn.extend({
	net_nemein_flashplaylist: function(options) {
		options = jQuery.extend({}, jQuery.net_nemein_flashplayer_playlist.defaults, {
		}, options);
		return new jQuery.net_nemein_flashplayer_playlist(this, options);
	},
	run_playlistclient_action: function(action, args) {
	    //console.log("run_playlistclient_action action: "+action);
	    //console.log("run_playlistclient_action args: "+args);
	    return this.trigger("run_client_action",[action, args]);
	},
	run_playlist_action: function(action, args) {
		return this.trigger("run_playlist_action",[action, args]);
	},
	load_playlist: function(url) {
		return this.trigger("load_playlist",[url]);
	},
	add_item: function(item_data) {
		var item = new jQuery.net_nemein_flashplayer_playlist_item(item_data);
		return this.trigger("add_item",[item]);
	},
	remove_item: function(item_id) {
		return this.trigger("remove_item",[item_id]);
	},
	connect_player: function(player_id) {
		return this.trigger("connect_player",[player_id]);
	},
	disconnect_player: function(player_id) {
		return this.trigger("disconnect_player",[player_id]);
	},
	change_movie: function(item) {
	    return this.trigger("change_movie",[item]);
	}
});

jQuery.net_nemein_flashplayer_playlist = function(object, options)
{
    //console.log("net_nemein_flashplayer_playlist object: "+object);

    var _object = object;
    var _playlist_id = generate_id();
    var _proxy = new FlashProxy(_playlist_id, generate_static_url(options.proxy_gateway_swf_path));
    var _playlist_content = [];
    
    var _playlist_initialized = false;
    var _playlist_loaded = false;
    var _playlist_sended = false;
    
    var _players = [];
    
    var _flashvars = "playlist_id="+_playlist_id;

    var player_content = '';
    
    if ( jQuery.browser.msie == true )
    {
        var flashplaylist_tag = { movie: generate_static_url(options.flash_playlist_path), width: options.width, height: options.height, flashvars: _flashvars };    
        if (   _object.attr('id') == undefined
            || _object.attr('id') == '')
        {
            _object.attr('id', _playlist_id);
        }
        UFO.create(flashplaylist_tag, _object.attr('id'));
        UFO.writeSWF(_object.attr('id'));
    }
    else
    {
        var flashplaylist_tag = new FlashTag(generate_static_url(options.flash_playlist_path), options.width, options.height);
        flashplaylist_tag.setVersion(options.flash_version);
        flashplaylist_tag.setFlashvars(_flashvars);
        
        player_content = flashplaylist_tag.toString()
    }
        
    _object.attr("nnf_id",_playlist_id)
    .attr("nnf_type","playlist")
    .html(player_content);
    //.html(UFO.writeSWF(_object.attr('id')));
    
    _object.bind("run_client_action", function(event, action, args){
	    //console.log("playlist run_client_action action: "+action);
	    //console.log("playlist run_client_action args: "+args);
        var functionToCall = eval(action);
        functionToCall.apply(functionToCall, [args]);
	})
    .bind("run_playlist_action", function(event, action, args){
	    //console.log("run_playlist_action action: "+action);
	    //console.log("run_playlist_action args.length: "+args.length);
	    proxy_send(action, args);
	})
    .bind("load_playlist", function(event, url){
	    //console.log("set_video url: "+url);
	    load_playlist(url);
	}).bind("add_item", function(event, item){
	    //console.log("add_item item: "+item);
	    add_item(item);
	}).bind("remove_item", function(event, item_id){
	    //console.log("remove_item item_id: "+item_id);
	    remove_item(item_id);
    }).bind("connect_player", function(event, player_id){
	    //console.log("connect_player player_id: "+player_id);
	    connect_player(player_id);
	}).bind("disconnect_player", function(event, player_id){
	    //console.log("disconnect_player player_id: "+player_id);
	    disconnect_player(player_id);
	}).bind("change_movie", function(event, item){
	    //console.log("change_movie item.id: "+item.id);
	    change_movie(item);
	});

	//proxy_send('playlist_embedded', {element_id: _object.attr("id")});
    
    function change_movie(item)
    {
        // var data = {
        //     item: item,
        //     options: {},
        //     playlist_id: _playlist_id
        // };
        //console.log("change_movie item.video_url: "+item.video_url);
        jQuery(_players).each(function(i,player){
            player.set_video(item);
        });
    }
    
	function _player_exists(player_id)
	{   
	    var existing = false;
        existing = jQuery.grep( _players, function(n,i){
           return n.attr("nnf_id") == player_id;
        });
        
        if (existing == false)
        {
            return false;
        }

        return true;
	}
	
    function connect_player(player_id)
    {
        //console.log("connect_player player_id: "+player_id);
        if (!_player_exists(player_id))
        {
            var player = jQuery('[@nnf_id=' + player_id + ']');
            _players.push(player);
        }
    }
    
    function disconnect_player(player_id)
    {
        //console.log("disconnect_player player_id: "+player_id);
        if (_player_exists(player_id))
        {
            _players = jQuery.grep( _players, function(n,i){
              return n != player_id;
            });
        }
    }
    
    function _item_exists(item_id)
    {
	    var existing = false;
        existing = jQuery.grep( _playlist_content, function(n,i){
           return n.id == item_id;
        });
        
        if (!existing)
        {
            return false;
        }
        
        return true;
    }
    
    function add_item(item)
    {
        if (!_item_exists(item.id))
        {
            _playlist_content.push(item);

            var args = {
                item: item
            };
            proxy_send('add_item',args);
        }
    }
    
    function remove_item(item_id)
    {
        if (_item_exists(item_id))
        {
            _playlist_content = jQuery.grep( _playlist_content, function(n,i){
              return n.id != item_id;
            });

            var args = {
                id: item_id
            };
            proxy_send('remove_item',args);
        }
    }
    
    function load_playlist(url)
    {
        //console.log("load_playlist url: "+url);
    
        _request(url, loading_success, loading_failure)
    }
    
    function loading_success(server_data)
    {        
        var results = [];
        jQuery('item',server_data).each(function(idx) {            
            var rel_this = jQuery(this);
    	    
            results[idx] = {
                index:idx,
                id:rel_this.find("id").text(),
                title:rel_this.find("title").text(),
                video_url:rel_this.find("video_url").text(),
                thumbnail_url:rel_this.find("thumbnail_url").text(),
                data_url:rel_this.find("data_url").text()
            };
            
            jQuery.each(options.item_extra_keys,function(i,key){
                //console.log("rel_this.find("+key+").text(): "+rel_this.find(key).text());
                results[idx][key] = rel_this.find(key).text();
            });
        });
    	
        _playlist_content = results;
        
        //console.log("_playlist_content.length: "+_playlist_content.length);
        _playlist_loaded = true;
        
        if (   _playlist_content.length > 0
            && _playlist_initialized
            && !_playlist_sended)
        {
            var args = {
                content: _playlist_content
            };
            //alert(args);
            proxy_send('playlist_loading_success',args);
        }
    }
    
	function loading_failure(type, expobj) {
	    //console.log("loading_failure type: "+type);
	    
	    var status = type;
	    
        var args = {
            status: status
        };
        proxy_send('playlist_loading_failure',args);
	}    

	function _request(url, success, failure) {
	    //console.log("_request url: "+url);
        
		jQuery.ajax({
		    type: "GET",
			url: url,
			dataType: 'xml',
			data: jQuery.extend({
    			limit: options.max_items
    		}, {}),
            error: function(obj, type, expobj) {
                failure(type, expobj);
            },
			success: function(data) {
				success(data);
			}
		});
	}
    
    function proxy_send(action, args)
    {
        //console.log("Call Flash Playlist with action: "+action);
        _proxy.call(action, args);
    }	
    
    function generate_id()
    {
        random_key = Math.floor(Math.random()*4013);
        return "net_nemein_flashplayer_playlist_" + (10016486 + (random_key * 22423));
    }

    function generate_static_url(suffix)
    {
        return options.site_root + options.static_prefix + suffix;
    }
    
    /**
     * Functions callable from flash
    **/
    
    function playlist_initialized(args)
    {
        //console.log("playlist_initialized");
        _playlist_initialized = true;
        
        if (   _playlist_content.length > 0
            && !_playlist_sended)
        {
            var args = {
                content: _playlist_content
            };
            if (_playlist_loaded)
            {
                proxy_send('playlist_loading_success',args);
            }
        }
    }
};

jQuery.net_nemein_flashplayer_playlist.defaults = {
    site_root: jQuery.net_nemein_flashplayer_player.defaults.site_root,
    static_prefix: jQuery.net_nemein_flashplayer_player.defaults.static_prefix,
    proxy_gateway_swf_path: jQuery.net_nemein_flashplayer_player.defaults.proxy_gateway_swf_path,
    flash_playlist_path: 'net.nemein.flashplayer/playlist.swf',
    flash_version: '8,0,0,0',
    item_extra_keys: [],
	max_items: 20,
	width: 450,
	height: 118
};

jQuery.net_nemein_flashplayer_playlist_item = function(data)
{
    //console.log("net_nemein_flashplayer_playlist_item data: "+data);

    data = jQuery.extend({}, jQuery.net_nemein_flashplayer_playlist_item.defaults, {
	}, data);    
    
    return this;
};

jQuery.net_nemein_flashplayer_playlist_item.defaults = {
    id: '',
    title: '',
    video_url: '',
    thumbnail_url: '',
    data_url: ''
};