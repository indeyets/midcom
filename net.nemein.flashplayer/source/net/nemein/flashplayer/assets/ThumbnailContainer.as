//import flashBug;

class net.nemein.flashplayer.assets.ThumbnailContainer extends MovieClip
{
    //private var _console:flashBug;
    
    public var thumbnail_title:TextField;
    public var Thumbnails:MovieClip;
    
	public function ThumbnailContainer()
	{
	    super();	    
	    //_console = new flashBug(true);
	    //_console.info("ThumbnailContainer::ThumbnailContainer");
    }

    function updateDisplay(data)
    {
        //_console.log("ThumbnailContainer::updateDisplay");
        
        thumbnail_title.autoSize = true;
        
        if (data.title)
        {            
            thumbnail_title.text = data.title;
        }
        else
        {
            if (data.name)
            {
                thumbnail_title.text = data.name;
            }
            else
            {
                thumbnail_title.text = "";
            }
        }
    }

}