class net.nemein.flashplayer.utils.Delegate {

	public static function create(target:Object, handler:Function):Function {
		// Get any extra arguments for handler
		var extraArgs:Array = arguments.slice(2);
		
		// Create delegate function
		var delegate:Function = function() {
			// Get reference to self
            var self:Function = arguments.callee;
            
			// Augment arguments passed from broadcaster with additional args
			var fullArgs:Array = arguments.concat(self.extraArgs, [self]);
			
			// Call handler with arguments
			return self.handler.apply(self.target, fullArgs);
		};
		
		// Pass in local references
		delegate.extraArgs = extraArgs;
		delegate.handler = handler;
		delegate.target = target;
		
		// Return the delegate function.
		return delegate;
	}
}