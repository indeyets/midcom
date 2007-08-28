import flashBug;
import net.nemein.flashplayer.helpers.Common;

class net.nemein.flashplayer.assets.MouseTracker extends MovieClip
{
    private var _console:flashBug;
    
	public function MouseTracker()
	{
	    super();
	    _console = new flashBug(true);
	    _console.info("MouseTracker::MouseTracker");
	    
	    this.onEnterFrame = function()
        {
            var distance = Common.calcDistance(_parent._xmouse, _parent._ymouse, _parent.mouse_last_x, _parent.mouse_last_y);
            if (distance > 1)
            {
                _parent.updateThumbs();
            }
            
            _parent.mouse_last_x = _parent._xmouse;
            _parent.mouse_last_y = _parent._ymouse;
        };
    }
    
}