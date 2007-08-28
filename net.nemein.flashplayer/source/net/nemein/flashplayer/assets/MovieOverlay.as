//import flashBug;
import net.nemein.flashplayer.utils.Delegate;

class net.nemein.flashplayer.assets.MovieOverlay extends MovieClip
{
    private var _movie:MovieClip;
    //private var _console:flashBug;
    
    public var play_btn:MovieClip;
    public var spinner_mc:MovieClip;
    public var background_mc:MovieClip;
    public var img:MovieClip;
    
	public function MovieOverlay()
	{
	    super();
	    
	    _movie = _parent;
	    /*_console = new flashBug(true);
	           //_console.info("MovieOverlay::MovieOverlay");*/
	    
	    play_btn._visible = false;
    }

    public function showLoading()
    {
        //_console.info("MovieOverlay::showLoading");
        spinner_mc = this.attachMovie("Spinner", "spinner_mc", this.getNextHighestDepth());
        spinner_mc._xscale = 200;
        spinner_mc._yscale = 200;
        _setSpinnerPosition();
        play_btn._visible = false;
    }
    
    public function hide()
    {
        //_console.info("MovieOverlay::hide");
        spinner_mc.removeMovieClip();
        _alpha = 0;
    }
    
    public function show()
    {
        //_console.info("MovieOverlay::show");
        _alpha = 100;
    }
    
    public function makePressable()
    {
        //_console.info("MovieOverlay::makePressable");
        play_btn._visible = true;
        this.onRelease = Delegate.create(this, function(){
            play_btn._visible = false;
            _movie.playMovie();
            delete this.onRelease;
        });
    }

    public function resize(w, h)
    {
        //_console.info("MovieOverlay::resize");
        background_mc._width = w;
        background_mc._height = h;
        this.resize_image();
    }

    private function _setSpinnerPosition()
    {
        //_console.info("MovieOverlay::_setSpinnerPosition"); 
        spinner_mc._x = ((_width / 2) - (spinner_mc._width / 2));
        spinner_mc._y = ((_height / 2) - (spinner_mc._height / 2));
    }
    
    function resize_image()
    {
        //_console.info("MovieOverlay::resize_image img: "+img)
        //_console.info("MovieOverlay::resize_image img.depth: "+img.getDepth())
        if (img._loaded)
        {
            img._width = background_mc._width;
            img._height = background_mc._height;
/*            img._x = -1 * background_mc._width / 2;
            img._y = -1 * background_mc._height / 2;*/
            img._x = background_mc._x;
            img._y = background_mc._y;
        }
    }
    
    function loadStill(still_url)
    {
        //_console.info("MovieOverlay::loadStill still_url: "+still_url);
        var load_listener = new Object();
        if (!still_url)
        {
            return;
        }
        
        load_listener.onLoadInit = Delegate.create(this, function(target_mc){
            target_mc._loaded = true;
            resize_image();
        });
        var still_loader = new MovieClipLoader();
        still_loader.addListener(load_listener);
        this.createEmptyMovieClip("img", play_btn.getDepth() - 1);//background_mc.getDepth() - 1);
        still_loader.loadClip(still_url, img);
    }

}