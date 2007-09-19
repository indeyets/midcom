//import flashBug;
import com.macromedia.javascript.JavaScriptProxy;
import net.nemein.flashplayer.utils.Delegate;

class net.nemein.flashplayer.Player extends MovieClip
{
    private var _self:Player;
    
    private var _proxy:JavaScriptProxy;
    //private var _console:flashBug;
    
    private var _player_id:String;
    private var _show_end_menu:Boolean = false;
    
    private var _active_video:Object;
    private var _last_playlist_controller_id:String;
    
    public var Movie:MovieClip;
    public var Controller:net.nemein.flashplayer.assets.Controller;
    
    public var min_resize;
    
    static var FIX_WIDTH = 450;
    static var FIX_HEIGHT = 358;
    
	public function Player()
	{
	    super();
        //_console = new flashBug(true);      
        //_console.info("Player::Player");
	    
	    _self = this;
        
        Movie._ox = Movie._x;
        Movie._oy = Movie._y;
        
        Movie.onPlayMovie = Delegate.create(this, function(){
            onPlayMovie();
        });
        Movie.onEndMovie = Delegate.create(this, function(){
            onEndMovie();
        });
    }
    
    public function initialize(root)
    {
        //_console.info("Player::initialize");

        _player_id = _root.player_id;
        //_console.debug("_player_id: "+_player_id);

	    _proxy = new JavaScriptProxy(_player_id, this);
	        /*Delegate.create(this, function(){
	        _console.info("proxy deletgate");
	        function player_embedded(args)
            {
                _console.info("delegate embedded");
            	embedded(args);
            }
	    }));*/
        
	    Stage.scaleMode = "noScale";
        Stage.addListener(this);
        
        if (root.loop_movie)
        {
            Movie.onEndMovie = Delegate.create(this, function(){
                playMovie();
                return (true);
            });
        }

        var action_data = {};
        execute_on_client(_player_id, 'player_initialized', action_data);

        Controller.registerMovie(Movie);
        this.onResize();
    }
    
    public function onResize(w, h)
    {
        this.resize(Stage.width, Stage.height);
    }
    
    public function resize(w, h)
    {
        if (w < FIX_WIDTH || h < FIX_HEIGHT)
        {
            this.fixed_resize(FIX_WIDTH, FIX_HEIGHT);
            var width_perc = w / FIX_WIDTH * 100;
            var height_perc = h / FIX_HEIGHT * 100;
            
            /*thumbnail_container._visible = false;
                        left_paddle._visible = false;
                        right_paddle._visible = false;*/
            min_resize = true;
            if (width_perc > height_perc)
            {
                width_perc = height_perc;
            }
            else
            {
                height_perc = width_perc;
            }
            _xscale = width_perc;
            _yscale = height_perc;
        }
        else
        {
            min_resize = false;
            _xscale = 100;
            _yscale = 100;
            this.fixed_resize(w, h);
        }
    }
    
    function fixed_resize(w, h)
    {
        var controller_height = Controller.bg._height;
        Movie.resize(w, h - controller_height);
        /*this.fitPaddles();*/
        
        Controller.resize_width(Movie._width);
        Controller._y = Movie._y + Movie._height; - (Controller._height - controller_height);// / 2;
    }
    
    function setVideo(video, options)
    {
        //_console.info("Player::setVideo video: "+video);
        _active_video = video;
        Movie.setMovie(_active_video, options);
    }
    
    public function playMovie()
    {
        //_console.info("Player::playMovie");
        Movie.playMovie();
    } 

    public function onPlayMovie()
    {
        //_console.info("Player::onPlayMovie");
    }

    public function onEndMovie()
    {
        //_console.info("Player::onEndMovie");

        if (_show_end_menu)
        {
            //_console.debug("_show_end_menu == true");
            _self.drawEndMenu();
        }
        else
        {
            //_console.debug("_show_end_menu == false");
            Movie.Overlay.hidePlayButton;
        }
    }
    
    public function drawEndMenu()
    {
        //_console.info("Player::drawEndMenu");
    }
    
    public function execute_on_client(client_id, action_name, action_data)
    {
        _proxy.call('run_client_action', client_id, action_name, action_data);
    }
    
    public function get_next_movie()
    {
        var action_data = {};
        execute_on_client(_player_id, 'player_initialized', action_data);
    }

    /**
     * Functions callable from client
    **/
    
    public function embedded(args)
    {
        //_console.info("Player::embedded");
        //_console.debug("args.element_id: "+args.element_id);
    }
    
    public function set_video(args)
    {
        //_console.info("Player::set_video");
        
        _last_playlist_controller_id = args.playlist_id;
        //_console.info("Player::set_video _last_playlist_controller_id: "+_last_playlist_controller_id);
        
        setVideo(args[0], args[1]);
    }
}