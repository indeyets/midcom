// TODO: add functions for hovering!
<script type="text/javascript"><!--//--><![CDATA[//><!--

sfFocus = function() {
    var sfEls = document.getElementsByTagName("A");
    for (var i=0; i<sfEls.length; i++) {
        sfEls[i].onfocus=function() {
            this.className+=" sffocus";
        }
        sfEls[i].onblur=function() {
            this.className=this.className.replace(new RegExp(" sffocus\\b"), "");
        }
    }
}
if (window.attachEvent) window.attachEvent("onload", sfFocus);

//--><!]]></script>
<!-- The above script came from "Son of Suckerfish", and enhances tabbing on the expanded nav when that occurs. -->
