<?php
/**
 * @package midcom_core
 *
 */
?>
<h1>About MidCOM and Midgard</h1>

<(midcom-show-vendor)>

<aside>
    <h2>Installed software</h2>
    <table>
        <thead>
            <tr>
                <th>MidCOM</th>
                <td tal:content="midcom_core/versions/midcom">3</td>
            </tr>
            <tr>
                <th>Midgard</th>
                <td tal:content="midcom_core/versions/midgard">2</td>
            </tr>
            <tr>
                <th>PHP</th>
                <td tal:content="midcom_core/versions/php">5</td>
            </tr>
        </thead>
        <tbody tal:repeat="component midcom_core/components">
            <tr>
                <th tal:content="component/name">Some component</th>
                <td tal:content="component/version">1.0</td>
            </tr>
        </tbody>
    </table>
</aside>

<p>
    MidCOM is an <a href="http://en.wikipedia.org/wiki/Model-view-controller">MVC</a> framework for
    the PHP programming language. It runs on top of Midgard, a Free Software 
    <a href="http://en.wikipedia.org/wiki/Persistent_storage">persistent storage</a> plaftorm for 
    interactive web application development.
</p>

<p>
    The first version of the Midgard platform was released in 1999, and it has been developed and maintained 
    by an international community since then. First usable versions of 3rd generation MidCOM framework
    surfaced in 2008.
</p>

<p>
    <a href="http://www.midgard-project.org/" rel="group">www.midgard-project.org</a>
</p>

<h2>Credits</h2>

<ul class="developers" tal:repeat="author midcom_core/authors">
    <li class="vcard">
        <span class="fn">
            <a href="http://example.net" rel="member" class="url" tal:attributes="href author/url" tal:content="author/name">
                Alice
            </a>
        </span>
    </li>
</ul>

<div class="logos">
    <a href="http://www.gnu.org/licenses/lgpl.html" rel="license">
        <img src="/midcom-static/midcom_core/midgard/lgplv3.png" alt="LGPLv3" />
    </a>
</div>