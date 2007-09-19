//import flashBug;
import net.nemein.flashplayer.Playlist;
import net.nemein.flashplayer.helpers.Common;
import net.nemein.flashplayer.utils.Delegate;

class net.nemein.flashplayer.assets.Thumbnails extends MovieClip
{
    //private var _console:flashBug;
    private var _playlist:Playlist;
    
    private var _current_thumb_index:Number = 0;

    private var _zoom_max:Number;
    private var _zoom_min:Number;
    private var _zoom_range:Number;
    private var _zoom_r_min:Number;
    private var _zoom_r_max:Number;
    private var _zoom_r_range:Number;
    private var _thumbs_padding:Number;
    private var _arena_w:Number;
    private var _first_thumb:MovieClip;
    private var _last_thumb:MovieClip;
    private var _thumbs_w_max:Number;
    private var _thumbs_w_min:Number;
    private var _thumbnails:Array;

    public var last_mouse_x:Number;
    public var last_mouse_y:Number;

    public var thumbs_showing:Boolean;
    public var num_thumbs:Number = 0;
    
    public var mouse_tracker:MovieClip;
    public var thumb0:MovieClip;
    
	public function Thumbnails()
	{
	    super();
	    //_console = new flashBug(true);
	    //_console.info("Thumbnails::Thumbnails");
        
        _playlist = Playlist.getInstance();
        
        _zoom_max = 120;
        _zoom_min = 40;
        _zoom_range = _zoom_max - _zoom_min;
        _zoom_r_min = 40;
        _zoom_r_max = 160;
        _zoom_r_range = _zoom_r_max - _zoom_r_min;
        _thumbs_padding = 8;
        _arena_w = _parent.thumbnails_mask._width - 15;
        _first_thumb = thumb0;
        _last_thumb = thumb0;
        _thumbs_w_max = _first_thumb._width * _zoom_max / 100;
        _thumbs_w_min = _first_thumb._width * _zoom_min / 100;
        _thumbnails = [];

        last_mouse_x = _xmouse;
        last_mouse_y = _ymouse;
        thumbs_showing = false;
    }

    public function initialize(items_array:Array):Void
    {
        //_console.info("Thumbnails::initialize");

        num_thumbs = items_array.length;
        //_console.debug("num_thumbs: "+num_thumbs);

        if (num_thumbs)
        {
            _first_thumb.update(items_array[0]);
        }
        
        for (var i = 1; i < num_thumbs; ++i)
        {
            var item = items_array[i];            
            var thumb = _first_thumb.duplicateMovieClip("thumb" + i, this.getNextHighestDepth(), {id: i, _xscale: _zoom_min, _yscale: _zoom_min, item_data: item});
            thumb._x = thumb._width / 2 + (thumb._width + _thumbs_padding) * i;
            _last_thumb = thumb;
        }
        
        startMouseTracking();
        updateThumbs();
        showThumbs();
        _thumbnails = items_array;
    }

    public function getThumbnail(index)
    {
        return (this["thumb" + index]);
    }
    
    public function startMouseTracking()
    {   
        if (!mouse_tracker)
        {
            mouse_tracker = this.createEmptyMovieClip("mouse_tracker", this.getNextHighestDepth());
        }
        mouse_tracker.onEnterFrame = function()
        {
            var distance = Common.calcDistance(_parent._xmouse, _parent._ymouse, _parent.mouse_last_x, _parent.mouse_last_y);
            if (distance > 1)
            {
                _parent.updateThumbs();
            }
            
            _parent.mouse_last_x = _parent._xmouse;
            _parent.mouse_last_y = _parent._ymouse;
        };
        
/*        if (!mouse_tracker)
        {
            mouse_tracker = this.attachMovie("MouseTracker", "mouse_tracker", this.getNextHighestDepth());
        }*/
        
        //_console.info("Thumbnails::startMouseTracking mouse_tracker: "+mouse_tracker);
    }
    
    public function stopMouseTracking()
    {
        //_console.info("Thumbnails::stopMouseTracking");
        removeMovieClip(mouse_tracker);
        //_console.info("Thumbnails::stopMouseTracking mouse_tracker: "+mouse_tracker);
    }
    
    public function updateThumbs()
    {
        for (var i = 0; i < num_thumbs; ++i)
        {
            var thumb = this.getThumbnail(i);
            var distance = Common.calcDistance(thumb._x, thumb._y, _xmouse, _ymouse);
            var multiplier = 1 - (distance - _zoom_r_min) / _zoom_r_range;
            if (multiplier < 0)
            {
                multiplier = 0;
            }
            if (multiplier > 1)
            {
                multiplier = 1;
            }
            thumb._xscale = thumb._yscale = Math.round(_zoom_min + _zoom_range * multiplier);
        }
        
        var thumbs_width = _thumbs_padding * (num_thumbs - 1);
        for (var i = 0; i < num_thumbs; ++i)
        {
            thumbs_width = thumbs_width + this.getThumbnail(i)._width;
        }
        
        var empty_space = thumbs_width - _arena_w;
        var thumb_center = _first_thumb._width / 2;
        if (empty_space > 0)
        {
            var mouse_x = _xmouse;
            if (mouse_x > _arena_w)
            {
                mouse_x = _arena_w;
            }
            if (mouse_x < 0)
            {
                mouse_x = 0;
            }
            _first_thumb._x = thumb_center - empty_space * (mouse_x / _arena_w);
        }
        else
        {
            _first_thumb._x = thumb_center - empty_space * 5.000000E-001;
        }
        
        for (var i = 1; i < num_thumbs; ++i)
        {
            var thumb = this.getThumbnail(i);
            var prev_thumb = this.getThumbnail(i - 1);
            thumb._x = prev_thumb._x + prev_thumb._width / 2 + _thumbs_padding + thumb._width / 2;
        }
    }
    
    public function showThumbs()
    {
        //_console.info("Thumbnails::showThumbs");
        if (!this.getThumbsShowing())
        {
            this.displayThumbs("show");
        }
    }
    
    public function hideThumbs()
    {
        //_console.info("Thumbnails::hideThumbs");
        if (this.getThumbsShowing())
        {
            this.displayThumbs("hide");
            this.updateDisplay();
        }
    }
    
    public function displayThumbs(state)
    {
        //_console.info("Thumbnails::displayThumbs state: "+state);
        var display_count = 0;
        this.onEnterFrame = Delegate.create(this, function(){
            var curr_disp_cnt = display_count;
            
            if (curr_disp_cnt >= num_thumbs)
            {
                delete this.onEnterFrame;
                return;
            }
            
            getThumbnail(curr_disp_cnt).gotoAndPlay(state);
            getThumbnail(curr_disp_cnt).border_mc._visible = false;
            ++display_count;
        });
        thumbs_showing = state == "show" ? (true) : (false);
    }
    
    public function getThumbsShowing()
    {
        //_console.info("Thumbnails::getThumbsShowing thumbs_showing: "+thumbs_showing);
        return (thumbs_showing);
    }
    
    public function updateDisplay(data)
    {
/*        //_console.info("Thumbnails::updateDisplay data: "+data);*/
        _parent.updateDisplay(data);
    }
    
    public function thumbHide()
    {
        //_console.info("Thumbnails::thumbHide");
        this.stopMouseTracking();
        this.hideThumbs();
    }
    
    public function thumbShow()
    {
        //_console.info("Thumbnails::thumbShow");
        this.startMouseTracking();
    }
    
    public function getNumItems()
    {
        //_console.info("Thumbnails::getNumItems num_thumbs: "+num_thumbs);
        return (num_thumbs);
    }
    
    public function setCurrentThumb(index)
    {
        //_console.info("Thumbnails::setCurrentThumb index: "+index);
        _current_thumb_index = index;
        _playlist.change_movie(_thumbnails[_current_thumb_index]);
    }
    
    public function getNext()
    {
        if (_current_thumb_index + 1 > _thumbnails.length - 1)
        {
            return (_thumbnails[0]);
        }
        else
        {
            return (_thumbnails[_current_thumb_index + 1]);
        }
    }
    
    public function getPrev()
    {
        if (_current_thumb_index > 0)
        {
            return (_thumbnails[_current_thumb_index - 1]);
        }
        else
        {
            return (_thumbnails[_thumbnails.length - 1]);
        }
    }
    
}