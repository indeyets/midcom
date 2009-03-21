com_rohea_facebook
==================

Makes it possible to midgard-service login using a facebook ID.

Component connects a facebook ID to a midgard_person guid, and authenticates the user through the facebook API. The athentication through facebook API is successful, user is logged in as matching midgard_person using trusted authentication (no midgard-password required). *notice security issues*

It is possible to join the facebook ID to existing midgard ID or create a new midgard id and in the process join the facebook id to it.

Facebook API is stored in folder: `/facebook-client/`
API Wiki: <http://wiki.developers.facebook.com/index.php/Main_Page>
API Functions <reference: http://wiki.developers.facebook.com/index.php/API>

What do you need to set it up?
------------------------------

-Add following line to your <html tags> to add support for the facebook notations:

    xmlns:fb="http://www.facebook.com/2008/fbml"

    Example:

    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" xml:lang="fi" lang="fi">
    
-Copy `xd_receiver.htm  to a folder (/facebook-javascript) that visible to outside (ex: /static/js/). Enables async. communication with facebook server on clientside.

-Create a facebook app and take note of the api_key and secret_key
    Needed in:
        config.yml 
        (also update the correct location of xd_receiver.html)

-Create an instance of com_rohea_facebook

-Dyn. load "login"-route to your login page to add the link for facebook login