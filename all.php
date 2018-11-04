<?php

require("fwk.php");
require("btrpc/easybitcoin.php");
require("trans.php");
require("block.php");
require("f3.php");

$pages=Array(
		"/"=>"pageOverview",
		"/info"=>"pageInfo",
		"/what"=>"pageWhat",
		"/block"=>"pageBlock",
		"/recent"=>"pageRecent",
		"/tx"=>"pageTransaction",
);
