import flashBug;

import net.nemein.flashplayer.events.EventDispatcher;
import net.nemein.flashplayer.utils.Delegate;

class net.nemein.flashplayer.constructors.NNFMovieClip extends MovieClip {
	
	private var _console:flashBug;
	
	public function NNFMovieClip() {
	    super();
	    
	    _console = new flashBug(true);
	    _console.info("NNFMovieClip::NNFMovieClip");
	    
	    EventDispatcher.initialize(this);
	}
	
	public function show(Void):Void {
		_visible = true;
	}
	
	public function hide(Void):Void {
		_visible = false;
	}

	public function addListener(method:String,func:Function):Void
	{
	    _console.info("NNFMovieClip::addListener method: "+method);
		this.addEventListener(method,func);
	}

	public function addEventListener() {}
	public function removeEventListener() {}
	public function dispatchEvent() {}
	public function dispatchQueue() {}

}