<?php
echo "<h1>" . sprintf($data['l10n']->get("expenses in week %s"), strftime("%V", $data['requested_time'])) . "</h1>\n";
?>