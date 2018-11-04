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
		$this->finished= true;
		echo "<!DOCTYPE html>\n";
		?>
			<html><head>
				<title>Gridcoin Network</title>
				<link rel='stylesheet' href='<?=hrefs('1.css')?>'/>
			</head><body>
				<h1>Gridcoin Test Network</h1>
				<p><strike>--header--</strike></p>
				<?=$body?>
				<p><strike>--footer--</strike></p>
				<script type='text/javascript' src='<?=hrefs('1.js')?>'></script>
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
	return number_format($time,0,".","'") . "s";
	//stub
}
function toKiSize($val)
{
	return number_format($val,0,'.',"'");
	/* Note: it does not look good with the size prefixes */
	$tab=Array(
		1073741824=>'Gi',
		1048576=>'Mi',
		1024=>'Ki',
	);
	foreach($tab as $base=>$prefix)
	{
		if($val>($base*8))
		{
			$a = floor($val / $base);
			return $a.$prefix.'B';
		}
	}
	return $val.'B';
}

function GRCExtractXML($data,$key,$begin=FALSE)
{
	$tagL="<$key>";
	$tagR="</$key>";
	if($begin)
	{
		$posL=0;
		if(substr($data,$posL,strlen($tagL))!=$tagL)
			return FALSE;
	} else {
		$posL=strpos($data,$tagL);
	}
	$posR=strrpos($data,$tagR);
	if($posL===FALSE || $posR===FALSE)
		return FALSE;
	$posL+=strlen($tagL);
	return substr($data,$posL,$posR-$posL);
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
	return $_SERVER['SCRIPT_NAME'].'/'.$rel;
}

function hrefs($rel)
{
	/* The t parameter is there to trigger reload of the resource if it changes */
	$time= filemtime ( "static/$rel" );
	return $_SERVER['SCRIPT_NAME'].'/static/'.$rel.'?t=' . $time;
}

function pageStatic($name)
{
	/* You should realy configure your server to serve static files. */
	$allowed=Array(
		'1.css'=>'text/css',
		'1.js'=>'text/javascript',
	);
	if(isset($allowed[$name]))
	{
		header("Content-type: ".$allowed[$name]);
		header("Expires: ". gmstrftime("%a, %d %b %Y %H:%M:%S GMT", time() + 365*86440) );
		readfile("static/$name");
	} else {
		header("HTTP/1.1 404 Not Found");
		echo "resource not found";
	}
}

function main()
{
	global $pages;
	$page=new PageRender();
	try {
		$path=splitPath1($_SERVER['PATH_INFO']);
		if($path[0] == "/static")
		{
			$page->abort();
			return pageStatic($path[1]);
		}
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
