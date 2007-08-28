//import flashBug;
import net.nemein.flashplayer.helpers.MovieLoader;
import net.nemein.flashplayer.utils.Delegate;

class net.nemein.flashplayer.assets.Movie extends MovieClip
{

    private var _ox:Number;
    private var _oy:Number;
    private var _player:MovieClip;
    //private var _console:flashBug;
    
    private var display_ratio:Number;

    public var Overlay:net.nemein.flashplayer.assets.MovieOverlay;
    public var end_screen:MovieClip;
    public var movie_bg:MovieClip;
    
    public var video_display;
    public var sound_data_so;
    public var sound_data;
    public var audio;
    public var is_playing;
    
    public var ns:NetStream;
    
    public var movieTime, started, restart, stall_count, max_seek_ratio, snd, is_infringe_mute, cues, init_run, is_peeking, lastTime, stallCount;
        
    public var movieloader:MovieLoader;
    
    private var _movie_set:Boolean = false;
    
    public function onEndMovie() {};
    public function onPauseMovie() {};
    public function onPlayMovie() {}
    public function onStartMovie() {};
    public function onDisplayMovie() {};
    public function onShowMute() {};
    public function onShowVolume() {};
    public function onSeek() {};
    public function onProgress() {};
                                
	public function Movie()
	{
	    super();
        
        _player = _parent;

/*        _console = new flashBug(true);
        //_console.info("Movie::Movie");*/

        // Save original position on stage
        _ox = this._x;
        _oy = this._y;

	    video_display.smoothing = true;
        var snd = this.createEmptyMovieClip("snd", 0);
        sound_data_so = SharedObject.getLocal("soundData", "/");
        sound_data = sound_data_so.data;
        audio = new Sound(snd);
        if (sound_data.volume == undefined)
        {
            sound_data.volume = 100;
        }
        if (sound_data.mute == undefined)
        {
            sound_data.mute = false;
        }
        
        display_ratio = video_display._width / video_display._height;
        
        this.registerLoader(new MovieLoader());
    }

    function initController()
    {
        ////_console.log("Movie::initController");
        if (sound_data.mute)
        {
            audio.setVolume(0);
            this.onShowMute();
        }
        else
        {
            this.onShowVolume(sound_data.volume);
            audio.setVolume(sound_data.volume);
        }
        
        if (is_playing)
        {
            this.onPlayMovie();
        }
        else
        {
            this.onPauseMovie();
        }
    }

    function setMovie(data, options)
    {
/*        //_console.info("Movie::setMovie data.thumbnail_url: "+data.thumbnail_url);
        //_console.info("Movie::setMovie data.video_url: "+data.video_url);*/
        this.setSeek(0);
        this.pauseMovie();
        this.hideEnded();
        is_playing = false;
        started = false;
        restart = true;
        stall_count = 0;
        max_seek_ratio = 0;
        if (data.thumbnail_url)
        {
            Overlay.makePressable();
            Overlay.loadStill(data.thumbnail_url);
        }
        
        movieloader.load(data.video_url);
        this._attachLoader(movieloader);
        
        if (!movieloader.started)
        {
            Overlay.show();
        }
        else
        {
            //Overlay.hide();
        }
        
        _movie_set = true;
    }
    
    function registerLoader(loader, leave_old)
    {
        if (movieloader)
        {
            if (!leave_old)
            {
                movieloader.die();
            }
            delete this.ns;
            delete this.movieloader;
        }
        this._attachLoader(loader);
    }
    
    function _attachLoader(loader)
    {
        movieloader = loader;
        movieloader.setMovie(this);
        ns = movieloader.ns;
        video_display.attachVideo(ns);
        snd.attachAudio(ns);
    }
    function popLoader()
    {
        var old = movieloader;
        delete this.ns;
        delete this.movieloader;
        this.registerLoader(new MovieLoader());
        return (old);
    }

    function endMovie()
    {
        restart = true;
        movieloader.clearOffset();
        this.pauseMovie();

        if (!this.onEndMovie())
        {
            this.showEnded();
        }
    }
    
    function showEnded()
    {
        end_screen.show();
    }
    
    function hideEnded()
    {
        end_screen.hide();
    }
    
    function pauseMovie()
    {
        //delete this.onEnterFrame;
        ns.pause(true);
        is_playing = false;
        this.onPauseMovie();
    }
    
    function stopMovie()
    {
        restart = true;
        movieloader.clearOffset();
        this.pauseMovie();
    }
    
    function stopAll()
    {
        is_playing = false;
        movieloader.die();
        this.pauseMovie();
    }
    
    function playMovie()
    {
/*        //_console.info("Movie::playMovie");*/
        
        if (_movie_set == false)
        {
/*            //_console.info("Movie::playMovie no movie has been set yet!");*/
            return;
        }
        
        if (movieloader.started == false)
        {
/*            //_console.info("Movie::playMovie movieloader not started");*/
            movieloader.start();
            Overlay.showLoading();
            this.onStartMovie();
        }
        else
        {
/*            //_console.info("Movie::playMovie movieloader started");*/
            if (restart == true)
            {
                this.setSeek(0);
            }
            ns.pause(false);
            Overlay.hide();
        }
        this.hideEnded();
        this.onPlayMovie();
        is_playing = true;
        restart = false;
        
        //this.onEnterFrame = Delegate.create(this, onEnterFrame);
    }
    
    function isPlaying()
    {
        return (is_playing);
    }
    
    function Mute()
    {
        sound_data.mute = true;
        audio.setVolume(0);
        this.onShowMute();
        sound_data_so.flush();
    }
    
    function setInfringeMute()
    {
        is_infringe_mute = true;
    }
    
    function unMute()
    {
        if (!is_infringe_mute)
        {
            sound_data.mute = false;
            audio.setVolume(sound_data.volume);
            this.onShowVolume(sound_data.volume);
            sound_data_so.flush();
        }
    }
    
    function use_master_sound(master_movie)
    {
        sound_data = new Object();
        sound_data_so = undefined;
        var mm_sd = master_movie.sound_data;
        sound_data = {mute: mm_sd.mute, volume: mm_sd.volume};
        if (sound_data.mute)
        {
            this.Mute();
        }
        else
        {
            this.unMute();
        }
    }
    
    function toggleMute()
    {
        if (sound_data.mute)
        {
            this.unMute();
        }
        else
        {
            this.Mute();
        }    
    }
    
    function setVolume(v)
    {
        if (!is_infringe_mute)
        {
            sound_data.volume = v;
            sound_data.mute = false;
            audio.setVolume(sound_data.volume);
            this.onShowVolume(sound_data.volume);
            sound_data_so.flush();
        }
    }
    
    function setSeekRatio(ratio)
    {
        this.setSeek(ratio * movieTime);
    }
    
    function getTotalTime()
    {
        return (movieTime);
    }
    
    function getCurrentTime()
    {
        return (ns.time);
    }
    
    function findCue(t)
    {
        if (movieloader.cues)
        {
            var ml_cues = movieloader.cues;
            for (var i = 0; i < ml_cues.times.length; ++i)
            {
                var x = i + 1;
                if (ml_cues.times[i] <= t && (ml_cues.times[x] >= t || x == ml_cues.times.length))
                {
                    return ({time: ml_cues.times[i], position: ml_cues.positions[i]});
                }
            }
        }
    }
    
    function setSeek(s)
    {
        if (s != undefined)
        {
            var cue = this.findCue(s);
            if (cue)
            {
                var db = 100;
                if (cue.position > movieloader.start_pos + ns.bytesLoaded + db || cue.position < movieloader.start_pos - db)
                {
                    if (!movieloader.loadOffset(cue.time, cue.position))
                    {
                        ns.seek(s);
                    } 
                }
                else
                {
                    ns.seek(s);
                }
            }
            else
            {
                ns.seek(s);
            }
            
            this.onSeek(this.getStartRatio(), this.getSeekRatio());

            is_peeking = false;
            ns.pause(!is_playing);
        }
    }
    
    function peekSeekRatio(r)
    {
        restart = false;
        is_peeking = true;
        //this.hideEnded();
        ns.seek(r * movieTime);
        this.onSeek(this.getStartRatio(), this.getSeekRatio());
    }
    
    function getLoadRatio()
    {
        if (!movieloader.started)
        {
            return (0);
        }
        
        var cur_loaded = ns.bytesLoaded + movieloader.start_pos;
        var tot_load = ns.bytesTotal + movieloader.start_pos;
        if (tot_load == 0)
        {
            return (0);
        }
        else if (cur_loaded == tot_load && cur_loaded > 1000)
        {
            return (1);
        }
        else
        {
            return (cur_loaded / tot_load);
        }
    }
    
    function getSeekRatio()
    {   
        if (restart == true)
        {
            return (0);
        }
        else if (movieloader.delay_progress || !movieloader.started)
        {
            return (movieloader.start_time / movieTime);
        }
        else if (ns.time > movieTime)
        {
            return (1);
        }
        else
        {
            return (ns.time / movieTime);
        }
    }
    
    function getStartRatio()
    {   
        if (movieloader.start_time == undefined)
        {
            return (0);
        }
        else if (movieloader.start_time > movieTime)
        {
            return (1);
        }
        else
        {
            return (movieloader.start_time / movieTime);
        }
    }
    
    function getTime()
    {
        return (ns.time);
    }
    
    function onEnterFrame()
    {
        ////_console.log("Movie::onEnterFrame");
        var start_ratio = 0;
        var seek_ratio = 0;
        var start_ratio = getStartRatio();
        var seek_ratio = getSeekRatio();
        var cur_time = this.getTime();
        
        onSeek(start_ratio, seek_ratio);
        onProgress(start_ratio, getLoadRatio());
        max_seek_ratio = Math.max(max_seek_ratio, seek_ratio);

        if (init_run)
        {
            initController();
            init_run = false;
        }
        else if (ns.bytesLoaded == ns.bytesTotal && cur_time >= movieTime - 1 && is_playing && !is_peeking)
        {
            if (lastTime != cur_time)
            {
                lastTime = cur_time;
                stallCount = 0;
            }
            else if (stallCount < 30)
            {
                ++stallCount;
            }
            else
            {
                endMovie();
                stallCount = 0;
            }
        }
    }
    
    function resizeNormal()
    {
        video_display._height = Overlay._height;
        video_display._width = Overlay._width;
        video_display._x = video_display._width / -2;
        video_display._y = video_display._height / -2;
    }
    
    function resizeOriginal()
    {
        video_display._height = video_display.height;
        video_display._width = video_display.width;
        video_display._x = video_display._width / -2;
        video_display._y = video_display._height / -2;
    }
    
    function resize(w, h)
    {
        //_console.info("Movie::resize w: "+w);
        //_console.info("Movie::resize h: "+h);
        
/*        this.clear();
        this.beginFill(0);
        var _loc5 = -1 * w / 2;
        var _loc4 = -1 * h / 2;
        this.moveTo(_loc5, _loc4);
        this.lineTo(_loc5 + w, _loc4);
        this.lineTo(_loc5 + w, _loc4 + h);
        this.lineTo(_loc5, _loc4 + h);
        this.lineTo(_loc5, _loc4);
        this.endFill();*/
        
/*        //_console.info("Movie::resize display_ratio: "+display_ratio);*/
        
        var square = w / h;
/*        //_console.info("Movie::resize square: "+square);*/
        
        if (square > display_ratio)
        {
            w = h * display_ratio;
        }
        else
        {
            h = w / display_ratio;
        }

/*        //_console.info("Movie::resize new w: "+w);
        //_console.info("Movie::resize new h: "+h);
        
        //_console.info("Movie::resize before video_display._width: "+video_display._width);
        //_console.info("Movie::resize before video_display._height: "+video_display._height);*/

        video_display._width = w;
        video_display._height = h;
        /*video_display._x = -1 * w / 2;
                video_display._y = -1 * h / 2;*/
        
/*        //_console.info("Movie::resize video_display._width: "+video_display._width);
        //_console.info("Movie::resize video_display._height: "+video_display._height);
        //_console.info("Movie::resize video_display._x: "+video_display._x);
        //_console.info("Movie::resize video_display._y: "+video_display._y);*/
                
        movie_bg._width = w;
        movie_bg._height = h;
        Overlay.resize(w, h);
        //end_screen.resize(w, h);
    }

}