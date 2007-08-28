//import flashBug;
import net.nemein.flashplayer.utils.Delegate;

class net.nemein.flashplayer.helpers.MovieLoader
{
    private var _self:MovieLoader;
    
    public var delay_progress:Boolean;
    public var started:Boolean;
    public var movie:net.nemein.flashplayer.assets.Movie;
    
    public var interval_id:Number;
    public var wait_time:Number;
    public var movieTime;
    public var start_time:Number;
    public var start_pos:Number;
    public var can_srub_to_offset;
    public var cues;

    private var _load_start_time;    
    
    private var _console:flashBug;
    private var _retry_timeout:Number;
    
    public var ns:NetStream;

    private var _nc:NetConnection;
    private var _current_url:String;
    
	public function MovieLoader()
	{
/*        _console = new flashBug(true);
        //_console.info("MovieLoader::MovieLoader");*/
        
	    delay_progress = false;
        started = false;
	    
        _self = this;
    }

    function initNetStream()
    {
        delete this._nc;
        delete this.ns;
        
        _nc = new NetConnection();
        _nc.connect(null);
        
        ns = new NetStream(_nc);        
        this.enableScrubToOffset();
        ns.setBufferTime(2);
        
        ns.onMetaData = Delegate.create(this, function(obj){
            /*for (var propName:String in obj) {
                                //_console.debug("MovieLoader::ns.onMetaData " + propName + " = " + obj[propName]);
                        }*/
            var dur = obj.duration;
            if (obj.totalduration != undefined)
            {
                dur = obj.totalduration;
            }
            
            if (dur != undefined)
            {
                movieTime = dur;
                movie.movieTime = movieTime;
                if (obj.keyframes)
                {
                    cues = {times: obj.keyframes.times, positions: obj.keyframes.filepositions};
                    resetScrubToOffset();
                }
            }
            else
            {
                movieTime == undefined;
                cues = undefined;
            }
        });
        ns.onStatus = Delegate.create(this, function(object){
            if (object.code == "NetStream.Play.Stop")
            {
                if (movie.is_playing)
                {
                    movie.endMovie();
                }
            }
            else if (object.code == "NetStream.Play.Start")
            {
                started = true;
                if (movie.movieloader == this)
                {
                    movie.Overlay.hide();
                    movie.onDisplayMovie();
                }
            }
            else if (object.code == "NetStream.Buffer.Full")
            {
                delay_progress = false;
                if (movie.loader == this)
                {
                    movie.onDisplayMovie();
                    if (movieTime && movie.movieTime == undefined)
                    {
                        movie.movieTime = movieTime;
                    }
                    else if (movie.movieTime == undefined)
                    {
                        movie.getMovieInfo();
                    }
                }
            }
            else if (object.code == "NetStream.Play.StreamNotFound")
            {
                if (!_retry_timeout || _retry_timeout < getTimer() - _load_start_time)
                {
                    movie.getMovieInfo();
                    loadLater();
                }
                else
                {
                    movie.onLoadError();
                }
            }
            else if (object.code == "NetStream.Buffer.Empty")
            {
                movie.bufferEmptyCount = movie.bufferEmptyCount + 1;
            }
        });
    }

    function loadLater(waited)
    {
        if (interval_id)
        {
            clearInterval(interval_id);
            interval_id = undefined;
        }
        
        if (waited)
        {
            this._loadMovie();
            ns.pause(!(movie.movieloader == this && movie.is_playing));
        }
        else
        {
            interval_id = setInterval(this, Delegate.create(this, loadLater), wait_time * 1000, true);
            wait_time = wait_time * 5;
        }
    }
    
    function load(video_url)
    {
        if (interval_id)
        {
            clearInterval(interval_id);
            interval_id = undefined;
        }
        
        if (video_url != _current_url)
        {
            //_console.info("MovieLoader::load video_url != previous_url");
            _current_url = video_url;
            movieTime = undefined;
            wait_time = 5;
            started = false;
            start_pos = 0;
            start_time = 0;
            this.initNetStream();
        }
        else
        {
            //_console.info("MovieLoader::load video_url == previous_url");
            movie.Overlay.makePressable();
            movie.Overlay.show();
        }
    }

    function setMovie(m)
    {
        movie = m;
    }
    
    function die()
    {
        started = false;
        ns.close();
        if (interval_id)
        {
            clearInterval(interval_id);
        }
    }
    
    function preLoad()
    {
        if (!started)
        {
            start_pos = 0;
            start_time = 0;
            ns.play(_current_url);
            ns.pause(true);
            _load_start_time = getTimer();
        }
    }
    
    function start()
    {
        if (!started)
        {
            this.clearOffset();
            ns.play(_current_url);
            _load_start_time = getTimer();
        }
    }
    
    function clearOffset()
    {
        if (start_pos)
        {
            start_pos = 0;
            start_time = 0;
            started = false;
            delay_progress = true;
        }
    }
    
    function loadOffset(_start_time, _pos)
    {
        if (start_pos != _pos && can_srub_to_offset)
        {
            start_pos = _pos;
            start_time = _start_time;
            started = false;
            delay_progress = true;
            this._loadMovie();
            return (true);
        }
        return (false);
    }
    
    function _loadMovie()
    {
        ns.play(_current_url + (start_pos ? ("&start=" + start_pos) : ("")));
    }
    
    function isBuffered()
    {
        return (ns.bufferLength > ns.bufferTime);
    }
    
    function enableScrubToOffset()
    {
        if (!_root.soff)
        {
            can_srub_to_offset = true;
        }
    }
    
    function resetScrubToOffset()
    {
        if (can_srub_to_offset && start_pos)
        {
            can_srub_to_offset = false;
            start_pos = 0;
            start_time = 0;
        }
    }

}