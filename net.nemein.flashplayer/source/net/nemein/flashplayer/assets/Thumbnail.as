import net.nemein.flashplayer.helpers.Loader;

class net.nemein.flashplayer.assets.Thumbnail extends MovieClip
{
    private var _loader:Loader;
    private var _thumbnails:MovieClip;

    public var border_mc:MovieClip;
    public var main_mc:MovieClip
    public var reflection_mc:MovieClip
    
    public var img_bounds;
    public var item_data:Object;
        
	public function Thumbnail()
	{
	    super();
        border_mc._visible = false;
        img_bounds = main_mc.getBounds(main_mc);
        
        _loader = new Loader();
        _thumbnails = _parent;
        
        if (item_data)
        {
            this.render();
        }
    }

    public function update(d)
    {
        item_data = d;
        this.render();
    }
    
    function render()
    {
        var thumb_image = main_mc.createEmptyMovieClip("imgHolder", 1);
        var thumb_reflection = reflection_mc.createEmptyMovieClip("imgHolder", 1);
        _loader.loadImageToClip(thumb_image, item_data.thumbnail_url.toString(), this, img_bounds);
        _loader.loadImageToClip(thumb_reflection, item_data.thumbnail_url.toString(), this, img_bounds);
    }
    
    function onRollOver()
    {
        border_mc._visible = true;
        _thumbnails.updateDisplay(item_data);
    }
    
    function onRollOut()
    {
        border_mc._visible = false;
    }
    
    function onRelease()
    {
        _thumbnails.setCurrentThumb(item_data.index);
    }    
}