<?php
/**
 * @package midcom_core
 */
?>
<?php
echo '<?' . 'xml version="1.0"' . '?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title tal:content="page/title">Midgardian</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            BODY {
              font-family: "Arial", "Helvetica", "Geneva", sans-serif;
              font-size: 1em;
              color: #333333;
              background-color: #ffffff;
              margin: 0px;
              padding: 0px;
            }
            #container 
            {
              width: 100%;
              min-height: 100%;
              background-color: #ffffff;
              color: #333333;
              padding: 0px;
              margin: 0px;
              background-image: url("http://www.midgard-project.org/midcom-static/stock-icons/logos/midgard-fade.png");
              background-position: top;
              background-repeat: repeat-x;
            }
            #branding
            {
                height: 130px;
            }
            #branding div.grouplogo img 
            {
              border: 0px;
              float: left;
              margin-right: 20px;
              padding: 4px;
            }
            #branding h1
            {
                margin: 0px;
                padding-top: 20px;
            }
            #content .main {
              padding: 10px;
            }
            #content a {
              color: #314E6C;
            }
            #content div.main 
            {
              margin: 10px;
              border: 2px solid #826647;
              -moz-border-radius: 7px;
              padding: 4px;
            }
            #content div.main a {
              font-weight: bold;
              text-decoration: none;
            }
            #content form label span
            {
                display: block;
            }
            #content p {
              margin-top: 0px;
            }
            #siteinfo {
              background-color: #663822;
              color: #C1665A;
              font-size: 0.8em;
              padding: 7px;
              padding-left: 4px;
            }
            #siteinfo a {
              color: #C1665A;
              background-color: transparent;
            }
        </style>
    </head>
    <body>
        <div id="container">
            <div id="branding">
                <div class="grouplogo">
                    <a href=""><img 
                        src="http://www.midgard-project.org/midcom-static/stock-icons/logos/midgard-project.gif"
                        alt="The Midgard Project" title="The Midgard Project" width="120" height="120" /></a>
                </div>
                <h1 tal:content="page/title">Midgardian</h1>
            </div>

            <div id="content">
                <div class="main">
                    <(content)>
                </div>
            </div>
            
            <div id="siteinfo">
                <div class="credits">
                    Copyright &copy;1999-2008 <a href="http://www.midgard-project.org/">The Midgard Project</a>.
                </div>
            </div>

        </div>
        <span tal:condition="show_toolbar" tal:replace="php: MIDCOM.toolbar.render()" />
        <span tal:condition="uimessages" tal:replace="structure uimessages" />
    </body>
</html>