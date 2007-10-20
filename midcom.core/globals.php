<?php

/**
 * The last non-critical MidCOM error message. This is slowly superseded
 * by having all errors call generate_error.
 * 
 * @global string $GLOBALS['midcom_errstr']
 */
$GLOBALS['midcom_errstr'] = '';

/**
 * The main MidCOM applicaiton class.
 * 
 * @global midcom_application $GLOBALS['midcom']
 */
$GLOBALS['midcom'] = null;

/**
 * The MidCOM Logging interface. Note, that even though this variable
 * is called "debugger", it is actually a full-blown logging solution
 * which should not be turned off any longer. Instead, step up the
 * error reporting level to something above MIDCOM_LOG_DEBUG.
 * 
 * @global midcom_helper_debug $GLOBALS['midcom_debugger']
 */
$GLOBALS['midcom_debugger'] = null;

/**
 * This is a component specific global data storage area, which should 
 * be used for stuff like default configurations etc. thus avoiding the
 * pollution of the global namespace. Each component has its own array 
 * in the global one, allowing storage of arbitrary data indexed by arbitrary
 * keys in there. The component-specific arrays are indexed by their
 * name.
 * 
 * Note, that this facility is quite a different thing to the component
 * context from midcom_application, even if it has many similar applications.
 * The component context is only available and valid for components, which 
 * are actually handling a request. This data storage area is static to the 
 * complete component and shared over all subrequests and therefore suitable
 * to hold default configurations, -schemas and the like.
 * 
 * @global Array $GLOBALS['midcom_component_data']
 */
$GLOBALS['midcom_component_data'] = Array();

?>