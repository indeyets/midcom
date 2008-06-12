<?php
$_MIDCOM->auth->require_admin_user();

/**
 * TODO: Write treereflector based system for approving trees of any
 * loaded mgdschema objects
 */
?>
<h1>Grand unified approver</h1>
<p>
    The reflection based system is not done yet, meanwhile look at the following
scripts
    <ul>
        <li><a href="approve_topic_tree_reflector.php">approve_topic_tree_reflector.php</a>, forced approval of everything that is child of topic</li>
        <li><a href="approve_topic_tree_classic.php">approve_topic_tree_classic.php</a>, the classic topic/article approval if not yet approved</li>
        <li><a href="approve_style_tree.php">approve_style_tree.php</a>, forced approval of styles and style_elements</li>
        <li><a href="approve_snippet_tree.php">approve_snippet_tree.php</a>, forced approval of snippetdirs and snippets</li>
    </ul>
</p>
