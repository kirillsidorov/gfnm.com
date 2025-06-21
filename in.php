<?php
echo 'Session path: ' . (WRITEPATH . 'session') . "<br>";
echo 'Writable: ' . (is_writable(WRITEPATH . 'session') ? 'YES' : 'NO');
?>
