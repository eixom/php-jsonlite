--TEST--
Check for jsonlite presence
--FILE--
<?php
echo extension_loaded("jsonlite");
?>
--EXPECT--
1
