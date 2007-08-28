class net.nemein.flashplayer.helpers.Loader
{

	public function Loader()
	{
    }

    public function loadImageToClip(clip, img_url, notifier, _bounds)
    {
        var loader_listener = new Object();
        
        var bounds = _bounds;
        if (!bounds)
        {
            bounds = clip.getBounds(clip._parent);
        }
        
        var n = notifier;
        var on_loaded = function (target_mc, error)
        {
            target_mc._x = bounds.xMin;
            target_mc._y = bounds.yMin;
            target_mc._width = bounds.xMax - bounds.xMin;
            target_mc._height = bounds.yMax - bounds.yMin;
            if (n)
            {
                n.onClipLoaded(target_mc, error);
            }
        };
        loader_listener.onLoadInit = on_loaded;
        loader_listener.onLoadError = on_loaded;
        var image_loader = new MovieClipLoader();
        image_loader.addListener(loader_listener);
        var load_status = image_loader.loadClip(img_url, clip);
    }
    
}