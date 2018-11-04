<?php
if($_SERVER['PATH_INFO']=="/phpinfo") {
	return phpinfo();
}

/* no amount of shutdown handlers seem to catch parse errors ... */

require("all.php");
main();
