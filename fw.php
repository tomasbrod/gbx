<?php

class PageRender
{
	public $finished;
	public function __construct()
	{
		ob_start();
		$finished= FALSE;
	}

	public function durStatic()
	{
		/* no-op stub */
	}

	public function ok()
	{
		$body= ob_get_clean();
		$this->filished= true;
		echo "<!DOCTYPE html>\n";
		?>
			<html><head>
				<title>Gridcoin Network</title>
			</head><body>
				<h1>Gridcoin Test Network</h1>
				<p><strike>--header--</strike></p>
				<?=$body?>
				<p><strike>--footer--</strike></p>
			</body>
		<?php
	}
	public function abort()
	{
		ob_end_clean();
		$this->filished= true;
	}
}

function FormatTreeNodeHtml($node)
{
	echo "<ul>";
	foreach($node as $k=>$v) {
		if(is_array($v)) {
			echo "<li class='node'><span class='name'>$k</span><span class='opening'>: {</span>";
			FormatTreeNodeHtml($v);
			echo "</li>";
		} else {
			echo "<li class='leaf'><span class='name'>$k</span><span class='is'>: </span><span class='val'>";
			echo htmlspecialchars($v);
			echo "</span></li>";
		}
	}
	echo "</ul>";
}

function FormatTreeHtml($tree)
{
	echo "<div class='tree'>";
	FormatTreeNodeHtml($tree);
	echo "</div>";		
}

function fragRelaTime($unix)
{
	return "<s>RelaTime(".(string)$unix.")</s>";
	//stub
}

function fragTimePeriod($time)
{
	//stub
	return number_format($time,0,".","'") . "s";
}

function myExceptionHandler($page,$e)
{
	$page->abort();
	echo "<p>exception handler stub";
	echo "<p><kbd>".get_class($e)."</kbd>";
	echo "<pre>".$e."</pre>";
}

function splitPath1($path)
{
	$pos=strpos($path,'/',1);
	if($pos!==FALSE) {
		return Array(substr($path,0,$pos),substr($path,$pos+1));
	} else {
		return Array($path,'');
	}
}

function pageUnknown($page,$path,$path0)
{
	$page->abort();
	header("HTTP/1.0 404 Not Found");
	echo "<p>Unknown Page Requested";
}

function pageWhat($page,$rpc,$arg)
{
	?>
		<main>
			<h2>What's This?</h2>
			<div>
				<p>@brod</p>
				<p>page What --<i>stub</i>--
			</div>
		</main>
	<?php
	$page->durStatic();
}

function href($rel)
{
	echo $_SERVER['SCRIPT_NAME'].'/'.$rel;
}

function main()
{
	global $pages;
	$page=new PageRender();
	try {
		$path=splitPath1($_SERVER['PATH_INFO']);
		$api= new Bitcoin("gridcoinrpc","CmSFqmq5gXDJhHv68JxbC7BPSMqpdHzah8mXQUxvs8FQ","127.0.0.1",25715);
		$pageHandler=@$pages[$path[0]];
		if(is_callable($pageHandler))	{
			$pageHandler($page,$api,$path[1]);
		} else {
			pageUnknown($page,$path[1],$path[0]);
		}
		if(!$page->finished)
			$page->ok();
	}
	catch (Throwable $e) {
		myExceptionHandler($page,$e);
	}
}
