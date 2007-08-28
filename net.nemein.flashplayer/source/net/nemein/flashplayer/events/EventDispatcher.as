
class net.nemein.flashplayer.events.EventDispatcher {

	private static var __instance:EventDispatcher;
	private var __listeners:Object;
	private var __objects:Object;

	public static function initialize(refObj:Object):Void
	{
		if(__instance == undefined) {
			__instance = new EventDispatcher;
		}
		refObj.dispatchEvent = __instance.dispatchEvent;
		refObj.eventListenerExists = __instance.eventListenerExists;
		refObj.addEventListener = __instance.addEventListener;
		refObj.removeEventListener = __instance.removeEventListener;
		refObj.removeAllEventListeners = __instance.removeAllEventListeners;
	}

	private static function getListenerIndex(listArr:Array,evtObj:Object,evtFunc:String):Number
	{
		var len:Number = listArr.length;
		var i:Number = -1;
		while (++i < len) {
			var obj:Object = listArr[i];
			if (obj.o == evtObj && obj.f == evtFunc) {
				return i;
			}
		}
		return -1;
	}
	
	private static function _dispatchEvent(dispatchObj:Object,listArr:Array,evtObj:Object)
	{
		var i:String;
		for (i in listArr) {
			var o:Object = listArr[i].o;
			var oType:String = typeof(o);
			var f:String = listArr[i].f;
			if(oType == "object" || oType == "movieclip")
			{
				if(o.handleEvent != undefined && f == undefined) {
					o.handleEvent(evtObj);
				}
				if (f == undefined) {
					f = evtObj.type;
				}
				o[f](evtObj);
			} else {
				o.apply(dispatchObj,[evtObj]);
			}
		}
	}
	
	private function removeAllEventListeners():Void
	{
		__listeners = [];
	} 
	
	private function dispatchEvent(evtObj:Object):Void
	{
		if (evtObj.type == "ALL")
		{
			// TODO: Send command to all listeners
			return;
		}
		if(evtObj.target == undefined) {
			evtObj.target = this;
		}
		this[evtObj.type + "Handler"](evtObj);
		var listArr:Array = __listeners[evtObj.type];
		if (listArr != undefined) {
			_dispatchEvent(this,listArr,evtObj);
		}
		listArr = __listeners["ALL"];
		if (listArr != undefined) {
			_dispatchEvent(this,listArr,evtObj);
		}
	}
	
	private function eventListenerExists(method:String,evtObj:Object,evtFunc:String):Boolean
	{
		return (getListenerIndex(__listeners[method],evtObj,evtFunc) != -1);
	}
	
	private function addEventListener(evtStr:String,evtObj:Object,evtFunc:String):Void
	{
		if (__listeners == undefined)
		{
			__listeners = {};
			_global.ASSetPropFlags(this,__listeners,1);
		}
		var listArr:Array = __listeners[evtStr];
		if(listArr == undefined) {
			__listeners[evtStr] = listArr = [];
		}
		if(getListenerIndex(listArr,evtObj,evtFunc) == -1) {
			__listeners[evtStr].push({o:evtObj,f:evtFunc})
		}
	}
	
	private function removeEventListener(evtStr:String,evtObj:Object,evtFunc:String):Void
	{
		if (__listeners[evtStr] == undefined) {
			return;
		}
		var index:Number = getListenerIndex(__listeners[evtStr],evtObj,evtFunc);
		if(index != -1) {
			__listeners[evtStr].splice(index,1);
			if(__listeners[evtStr].length <= 0) {
				delete __listeners[evtStr];
			}
			
		}
	}		

}