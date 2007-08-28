//import flashBug;
import net.nemein.flashplayer.utils.Delegate;

class net.nemein.flashplayer.assets.Controller extends MovieClip
{
    private var _self:Controller;
    //private var _console:flashBug;
    
    private var _left_justified_elements:Array
    private var _right_justified_elements:Array;
    
    public var bg:MovieClip;
    public var pause_btn:MovieClip;
    public var play_btn:MovieClip;
    public var full_progress_bar:MovieClip;
    public var slider:MovieClip;
    public var fullBar:MovieClip;
    public var seekBar:MovieClip;
    public var progressBar:MovieClip;
    public var slider_down:Boolean = false;
    public var timer:MovieClip;
    public var seek_time;
    public var sound_control:net.nemein.flashplayer.assets.SoundControl;
    public var ldiv:MovieClip;
    public var rdiv:MovieClip;
            
    public var movie:net.nemein.flashplayer.assets.Movie;
    
    public static var MIN_PROGRESS_BAR_SIZE = 50;
    
    public var min, max, regular, small;
    
	public function Controller()
	{
	    super();
	    
	    //_console = new flashBug(true);
	    //_console.info("Controller::Controller");
	    
	    pause_btn._visible = false;
	    play_btn._visible = true;
        fullBar = full_progress_bar.fullBar;
        seekBar = full_progress_bar.seekBar;
        progressBar = full_progress_bar.progressBar;
        seek_time = timer.seek_time;
        
        _self = this;
        
        //_console.info("Controller::Controller play_btn:"+play_btn);
        
        play_btn.onRelease = Delegate.create(this, function(){
            //_console.info("Controller::play_btn onRelease");
            movie.playMovie();
        });
        pause_btn.onRelease = Delegate.create(this, function(){
            //_console.info("Controller::pause_btn onRelease");
            movie.pauseMovie();
        });
        
        full_progress_bar.onPress = Delegate.create(this, progressOnPress);
        slider.onPress = Delegate.create(this, progressOnPress);
        
        full_progress_bar.onRelease = full_progress_bar.onReleaseOutside = slider.onRelease = slider.onReleaseOutside = Delegate.create(this, progressOnRelease); 
        
        var self_bounds = this.getBounds(this);
        for (var i in self_bounds) {
            //_console.debug("Controller::self_bounds "+i+" --> "+self_bounds[i]);
        }
        _left_justified_elements = [play_btn, pause_btn, ldiv, full_progress_bar, bg];
        _right_justified_elements = [timer, sound_control, rdiv];
        
        for (var elem in _left_justified_elements)
        {
            _left_justified_elements[elem]._xstart = self_bounds.xMin - _left_justified_elements[elem]._x - 25;
        }
        
        for (var elem in _right_justified_elements)
        {
            _right_justified_elements[elem]._xstart = self_bounds.xMax - _right_justified_elements[elem]._x - 25;
        }
        
        full_progress_bar._xend = self_bounds.xMax - (full_progress_bar._width + full_progress_bar._x);
        
        bg._width_offset = _width - bg._width;
    }

    public function progressOnPress()
    {
        //_console.info("Controller::full_progress_bar onPress");
        slider_down = true;
        slider.highLight();
        this.onEnterFrame = function()
        {
            var mouse_x = _xmouse;
            if (mouse_x < _self.full_progress_bar._x)
            {
                mouse_x = _self.full_progress_bar._x;
            }
            else if (mouse_x > (_self.full_progress_bar._x + _self.full_progress_bar._width))
            {
                mouse_x = (_self.full_progress_bar._x + _self.full_progress_bar._width);
            }
            
            _self.slider._x = mouse_x;
            _self.movie.peekSeekRatio(_self.getScale());
        }
    }
    public function progressOnRelease()
    {
        slider.normal();
        movie.setSeekRatio(getScale());
        slider_down = false;
        delete this.onEnterFrame;
    };
    
    function registerMovie(m)
    {
        //_console.info("Controller::registerMovie movie: "+m);
        movie = m;
        sound_control.registerMovie(m);
        this.showSeek(0, 0);
        this.showProgress(0, 0);
        
        m.onPauseMovie = Delegate.create(this, function(){
            showPlay();
        });
        var old_handler = m.onPlayMovie;
        m.onPlayMovie = Delegate.create(this, function(){
            showPause();
            old_handler();
        });
        m.onSeek = Delegate.create(this, onMovieSeek);
        m.onProgress = Delegate.create(this, onMovieProgress);
    }
    
    private function onMovieSeek(ir, r)
    {
        showSeek(ir, r);
    }
    private function onMovieProgress(ir, r)
    {        
        showProgress(ir, r);
    }    
    
    function getScale()
    {
        var pb_pos = (slider._x - full_progress_bar._x) / full_progress_bar._width;
        if (pb_pos < 0)
        {
            return (0);
        }
        else
        {
            return (pb_pos);
        }
    }
    
    function format_time(t)
    {
        if (isNaN(t) || t < 0)
        {
            return ("--:--");
        }
        
        var seconds = String(Math.floor(t % 60));
        if (seconds.length == 1)
        {
            seconds = "0" + seconds;
        }
        
        var minutes = String(Math.floor(t / 60));
        if (minutes.length == 1)
        {
            minutes = "0" + minutes;
        }
        
        return (minutes + ":" + seconds);
    }

    function showPlay()
    {
        play_btn._visible = true;
        pause_btn._visible = false;
    }
    
    function showPause()
    {
        play_btn._visible = false;
        pause_btn._visible = true;
    }
    
    function showSeek(ir, r)
    {
        var cur_time = movie.getCurrentTime();
        if (isNaN(cur_time))
        {
            cur_time = 0;
        }
        
        seek_time.text = this.format_time(movie.getTotalTime() - cur_time);
        //seek_total_time.text = this.format_time(movie.getTotalTime());
        seekBar._x = ir * fullBar._width;
        seekBar._width = (r - ir) * fullBar._width;
        if (!slider_down)
        {
            slider._x = r * full_progress_bar._width + full_progress_bar._x;
        }
    }
    
    function showProgress(ir, r)
    {
        progressBar._x = ir * fullBar._width;
        progressBar._width = (r - ir) * fullBar._width;
    }
    
    function resize_width(w)
    {
        //_console.info("Controller::resize_width w: "+w);
        bg._width = w - bg._width_offset;
        
/*        _console.log("bg._width: "+bg._width);
        _console.log("bg._x: "+bg._x);
        _console.log("bg._y: "+bg._y);*/
        
        for (var elem in _left_justified_elements)
        {
            _left_justified_elements[elem]._x = (-1 * (w / 2)) - _left_justified_elements[elem]._xstart;
        }
        
        sound_control.enablePopUp();
        
        slider._x = full_progress_bar._x;
        var leftmost = full_progress_bar._x + net.nemein.flashplayer.assets.Controller.MIN_PROGRESS_BAR_SIZE;
        var r_x;

        for (var i = 0; i < _right_justified_elements.length; ++i)
        {
            _right_justified_elements[i]._x = w / 2 - _right_justified_elements[i]._xstart;
            if (_right_justified_elements[i]._x < rdiv._x)
            {
                if (_right_justified_elements[i]._x - 10 < leftmost)
                {
                    _right_justified_elements[i]._visible = false;
                }
                else
                {
                    _right_justified_elements[i]._visible = true;

                    if (isNaN(r_x))
                    {
                        r_x = _right_justified_elements[i]._x;
                    }
                }
                continue;
            }

            if (isNaN(r_x))
            {
                r_x = _right_justified_elements[i]._x;
            }
        }
        
        full_progress_bar._width = r_x - full_progress_bar._x - 12;
    }
}