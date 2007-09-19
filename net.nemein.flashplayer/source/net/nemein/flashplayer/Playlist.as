//import flashBug;
import com.macromedia.javascript.JavaScriptProxy;

class net.nemein.flashplayer.Playlist extends MovieClip
{
    private var _proxy:JavaScriptProxy;
    //private var _console:flashBug;

    private var _playlist_id:String;
    private static var _self:Playlist;

    public var thumbnail_container:MovieClip;
    private var _thumbnails;
    
    public var background_mc:MovieClip;
    
	public function Playlist()
	{
	    super();
	    //_console = new flashBug(true);
	    //_console.info("Playlist::Playlist");
	    
	    _self = this;
    }
    
    static function getInstance(Void):Playlist
    {
        return _self;
    }

    public function initialize(root)
    {
        //_console.info("Playlist::initialize");

        _playlist_id = _root.playlist_id;
        //_console.debug("_playlist_id: "+_playlist_id);

	    /*_proxy = new JavaScriptProxy(_playlist_id, Delegate.create(this, function(){
	               _console.info("proxy deletgate");
	               function playlist_loading_success(args)
	                {
	                    _console.info("delegate loading_success");
	                   embedded(args);
	                }
	           }));*/
	    _proxy = new JavaScriptProxy(_playlist_id, this);
        
	    Stage.scaleMode = "noScale";
        Stage.addListener(this);
        
        _thumbnails = thumbnail_container.Thumbnails;
        //_console.info("_thumbnails: "+_thumbnails);
        
        var action_data = {};
        execute_on_client(_playlist_id, 'playlist_initialized', action_data);
    }

    function renderThumbnails()
    {
        if (!_thumbnails.getNumItems())
        {
            thumbnail_container._visible = false;
        }
        else
        {
            thumbnail_container._visible = true;
/*            var bg_mc = background_mc.getBounds(this);
            thumbnail_container._xscale = bg_mc._xscale;
            thumbnail_container._yscale = thumbnail_container._xscale;
            thumbnail_container._x = bg_mc.xMin;
            thumbnail_container._y = bg_mc.yMax;*/
        }
/*        _console.debug("thumbnail_container._visible: "+thumbnail_container._visible);
        _console.debug("thumbnail_container._x: "+thumbnail_container._x);
        _console.debug("thumbnail_container._y: "+thumbnail_container._y);*/
    }
    
    public function change_movie(item)
    {
        var action_data = {
            item: item
        };
        execute_on_client(_playlist_id, 'change_movie', item);
    }
    
    public function execute_on_client(client_id, action_name, action_data)
    {
        _proxy.call('run_client_action', client_id, action_name, action_data);
    }

    /**
     * Functions callable from client
    **/

    public function embedded(args)
    {
        //_console.info("Playlist::embedded");
        //_console.debug("args.element_id: "+args.element_id);
    }
    
    public function playlist_loading_success(args)
    {
        //_console.info("Playlist::playlist_loading_success");
        
        var playlist_content = args.content;
        //_console.info("Playlist::playlist_loading_success playlist_content.length: "+playlist_content.length);
                
        _thumbnails.initialize(playlist_content);
        _thumbnails.setCurrentThumb(0);
        this.renderThumbnails();
    }
}