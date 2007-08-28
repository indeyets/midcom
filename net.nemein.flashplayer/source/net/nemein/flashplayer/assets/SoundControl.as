//import flashBug;
import net.nemein.flashplayer.utils.Delegate;

class net.nemein.flashplayer.assets.SoundControl extends MovieClip
{
    private var _self:SoundControl;
    //private var _console:flashBug;
    
    private var _delayed_hide_id:Number;
    private var _num_bars:Number = 2;
    
    public var sound_btn:MovieClip;
    public var sound_bar:MovieClip;
    public var knob:MovieClip;
    public var bg:MovieClip;
    public var muted:Boolean = false;
    
    public var movie:net.nemein.flashplayer.assets.Movie;
    
	public function SoundControl()
	{
	    super();
	    
/*      _console = new flashBug(true);
        //_console.info("SoundControl::SoundControl");*/
        
        _self = this;
        
        sound_btn.onRelease = Delegate.create(this, function(){
            ////_console.info("sound_bar / knob onRelease");
            movie.toggleMute();
        });
        
        sound_bar.onPress = knob.onPress = Delegate.create(this, function(){
            ////_console.info("sound_bar / knob onPress");
            knob.highLight();
            _self.onEnterFrame = function ()
            {
                //_console.info("sound_bar / knob onPress onenterframe _self: "+_self);
                var pos = _ymouse - _self.sound_bar._y;
                if (pos < 0)
                {
                    pos = 0;
                }
                else if (pos > _self.sound_bar._height)
                {
                    pos = _self.sound_bar._height;
                }
                _self.hideMute();
                _self.knob._y = pos + _self.sound_bar._y;
                _self.movie.setVolume(_self.getSoundPos(_self.knob._y));
            };
        });
        
        sound_bar.onRelease = sound_bar.onReleaseOutside = knob.onRelease = knob.onReleaseOutside = Delegate.create(this, function ()
        {
            //_console.info("sound_bar / knob onRelease");
            knob.normal();
            var new_vol = getSoundPos(knob._y);
            //_console.info("new volume: new_vol: "+new_vol);
            movie.setVolume(new_vol);
            delete _self.onEnterFrame;
        });

        this.hideBar();
	}
	
    function registerMovie(m)
    {
        //_console.info("SoundControl::registerMovie movie: "+m);
        movie = m;
        m.onShowMute = Delegate.create(this, function(){
            showMute();
        });
        m.onShowVolume = Delegate.create(this, function (v){
            hideMute();
            showVolume(v);
        });
    }
    
    function getSoundPos(pos)
    {
        //_console.info("SoundControl::getSoundPos pos: "+pos);
        var vol = Math.round((sound_bar._height - (pos - sound_bar._y)) * 100 / sound_bar._height);
        vol = Math.min(vol, 100);
        return (Math.max(vol, 0));
    }
    
    function enablePopUp()
    {
        //_console.info("SoundController::enablePopUp");

        sound_btn.onRollOver = bg.onRollOver = knob.onRollOver = sound_bar.onRollOver = Delegate.create(this, function(){
            //_console.info("sound_btn onRollOver");
            showBar();
            clearInterval(_delayed_hide_id);
        });

        sound_btn.onRollOut = bg.onRollOut = Delegate.create(this, function(){
            //_console.info("sound_btn onRollOut");
            _delayed_hide_id = setInterval(Delegate.create(this, execute_hidePopup), 100);
        });
    }
    
    private function execute_hidePopup()
    {
        //_console.info("execute_hide");
        hideBar();
        clearInterval(_delayed_hide_id);
    };
    
    function disablePopUp()
    {
        sound_btn.onRollOver = undefined;
        sound_btn.onRollOut = undefined;
    }
    
    function hideBar()
    {
        //_console.info("SoundController::hideBar");
        sound_bar._visible = false;
        knob._visible = false;
        bg._visible = false;
    }
    
    function showBar()
    {
        //_console.info("SoundController::showBar");
        sound_bar._visible = true;
        knob._visible = true;
        bg._visible = true;
    }
    
    function showMute()
    {
        this.showVolume(0);
    }
    
    function hideMute()
    {
        this.showBars(100);
    }
    
    function showVolume(v)
    {
        var pos = sound_bar._height - v * sound_bar._height / 100 + sound_bar._y;
        knob._y = pos;
        this.showBars(v);
    }
    
    function showBars(v)
    {
        var visible_bars = Math.round(v * _num_bars / 100);
        for (var i = 1; i <= _num_bars; ++i)
        {
            if (i <= visible_bars)
            {
                this["v" + i]._visible = true;
                continue;
            }
            
            this["v" + i]._visible = false;
        }
        
        if (visible_bars == 0)
        {
            sound_btn._alpha = 50;
        }
        else
        {
            sound_btn._alpha = 100;
        }
    }
}