<?php
require("fw.php");
require("btrpc/easybitcoin.php");

function fragTransactionDetail($blockver,$index,$tx)
{
	echo "stub";
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

function GRCExtractXML($data,$key)
{
	$matches=Array();
	if(!preg_match("/\\<$key\\>(.*)\\<\\/$key\\>/",$data,$matches,PREG_UNMATCHED_AS_NULL))
		return FALSE;
	if($matches[1]===NULL)
		return FALSE;
	/*if($matches[1]==='')
		return FALSE;*/
	return $matches[1];
}

function fragCoinbaseHashboinc($version,$hashboinc)
{
	$arr=explode('<|>',$hashboinc);
	?>
		<div class='kvboxes'>
			<div><p>CPID<p><?=$arr[0]?></div>
			<div><p>Client Version<p><?=$arr[10]?></div>
			<div><p>Org/Nick<p><?=htmlspecialchars($arr[19])?></div>
			<div><p>Interest<p><?=$arr[18]?></div>
			<div><p>GRCAddress<p><?=$arr[16]?></div>
			<?php /*same as prevblockhash <div><p>Last Block<p><a href='<?=href('block/'.$arr[17])?>'><?=substr($arr[17],0,10)?></a></div>*/?>
			<div><p>NeuralHash<p><?=$arr[21]?></div>
			<div><p>MagUnit<p><?=$arr[25]?></div>
			<?php if($arr[0]!=='' && $arr[0]!='INVESTOR'):?>
				<div><p>ResRwrd<p><?=$arr[11]?></div>
				<div><p>Magnitude<p><?=$arr[15]?></div>
				<div><p>ResAge<p><?=$arr[24]?></div>
				<div><p>AvgMag<p><?=$arr[26]?></div>
				<div><p>LastPORBlk<p><a href='<?=href('block/'.$arr[27])?>'><?=substr($arr[27],0,10)?></a></div>
				<div><p>Beacon Public Key<p><?=$arr[29]?></div>
			<?php endif;?>
		</div>
	<?php
	if(!empty($arr[22])):
		$sb=$arr[22];
		$avg=GRCExtractXML($sb,'AVERAGES');
		$avg=explode(';',$avg);
		$prjcount=count($avg)-1; /* 1 is special NeuralNetwork */
		?>
		<div class='titlekvboxes'>
			<div class='title'>Superblock</div>
			<div class='kvboxes'>
				<div><p>Size<p><?=toKiSize(strlen($arr[22]))?></div>
				<div><p>Projects<p><?=$prjcount?></div>
				<?php foreach($avg as $prjavg):
					if($prjavg==='') continue;
					$prjavg=explode(',',$prjavg);
				?>
					<div><p><?=$prjavg[0]?><p><?=$prjavg[1]?><p><?=$prjavg[2]?></div>
				<?php endforeach;?>
			</div>
		</div>
	<?php endif;
}

function fragHashboincGeneric($version,$hb)
{
	$action=GRCExtractXML($hb,'MA');
	if($action=='A')
		$action2='Add Contract';
	elseif($action=='D')
		$action2='Delete Contract';
	else
		return;
	?>
	<div class='titlekvboxes'>
		<div class='title'><?=$action2?></div>
		<div class='kvboxes'>
			<div><p>Type<p><?=htmlspecialchars(GRCExtractXML($hb,'MT'))?></div>
			<div><p>Key<p><?=htmlspecialchars(GRCExtractXML($hb,'MK'))?></div>
			<?php if($action=='A'):?>
				<div><p>Value<p><?=htmlspecialchars(GRCExtractXML($hb,'MV'))?></div>
			<?php endif;?>
		</div>
	</div>
	<?php
	/* todo: public key */
}

function fragTxHashBoinc($version,$hashboinc)
{
	//echo "<s>$hashboinc</s>";
	$comment= GRCExtractXML($hashboinc,'MESSAGE');
	$msgtype= GRCExtractXML($hashboinc,'MT');
	$rainmsg= GRCExtractXML($hashboinc,'NARR');
	if($comment!==false):?>
	<div class='kvbox'>
		<p>Comment
		<p><?=htmlspecialchars($comment)?>
	</div><?php elseif($rainmsg!==false):?>
	<div class='kvbox'>
		<p>Rain
		<p><?=htmlspecialchars($rainmsg)?>
	</div><?php elseif($msgtype!==false):
		if($msgtype==='')
			{echo "<s>test</s>";}
		else
			fragHashboincGeneric($version,$hashboinc);
	endif;
}

function fragInputsOutputs($inputs,$outputs)
{
	$outputs=array_filter($outputs, function($v) {
		return !empty($v['value']);
	});
	?>
	<div class='txio'>
		<div class='txin'>
			<span>Inputs</span>
			<?php foreach($inputs as $in): ?>
				<?php if(isset($in['txid'])): ?>
					<div><a href='<?=href("tx/{$in['txid']}?i={$in['vout']}")?>'><?=substr($in['txid'],0,6)?>-<?=$in['vout']?></a></div>
				<?php elseif(isset($in['coinbase'])): ?>
					<div>coinbase <?=$in['coinbase']?></div>
				<?php endif;?>
			<?php endforeach;?>
		</div>
		<?php if(!empty($outputs)):?>
			<table class='txout'><thead>
				<tr><th colspan=1>Value (GRC)<th>Output</tr>
			<thead><tbody>
			<?php foreach($outputs as $out): ?>
				<tr><td><?=number_format($out['value'],8,".","'")?><td>
				<?php if(isset($out['scriptPubKey']) && isset($out['scriptPubKey']['type'])):
					if($out['scriptPubKey']['type']=="pubkey"):	?>
						<span>PK</span>
						<span><?=$out['scriptPubKey']['addresses'][0]?></span>
					<?php elseif($out['scriptPubKey']['type']=="pubkeyhash"):	?>
						<span>pkh</span>
						<span><?=$out['scriptPubKey']['addresses'][0]?></span>
					<?php elseif($out['scriptPubKey']['type']=="scripthash"):	?>
						<span>SH</span>
						<span><?=$out['scriptPubKey']['asm'][0]?></span>
					<?php else: ?>
						<span>other</span>
						<span><?=$out['scriptPubKey']['asm'][0]?></span>
				<?php endif; endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody></table><?php endif; ?>
	</div>
	<?php
}

function fragBlock($block)
{
	$usertxstart=2;
	$coinbase=(object)$block->tx[0];
	$coinstake=(object)$block->tx[1];
	/* sections:
	 * block data
	 * block index
	 * coinbase/stake
	 * transactions
	*/
	?>
	<main>
		<h2>Block Details</h2>
		<div class='blockinfoheader'>
			<a href='<?=href('block/'.$block->previousblockhash)?>'><span class='arrowback'>&lt;=</span></a>
			<span class='hash'><?=$block->hash?></span>
			<?php if(isset($block->nextblockhash)): ?>
				<a href='<?=href('block/'.$block->nextblockhash)?>'><span class='arrowfwrd'>=&gt;</span></a>
			<?php endif;?>
		</div>
		<div class='blockinfo kvboxes'>
			<div><p>Height<p><?=number_format($block->height,0,".","'")?></div>
			<div><p>Difficulty<p><?=number_format($block->difficulty,4,'.','')?></div>
			<div><p>Depth<p><?=$block->confirmations?></div>
			<div><p>Size<p><?=toKiSize($block->size)?></div>
			<div><p>Time<p><?=$block->time?></div>
			<div><p>Ver<p><?=$block->version?></div>
			<div><p>Flags<p>...</div>
		</div>
		<h3>Coinbase</h3>
			<?php if(!empty($coinbase->hashboinc)) { fragCoinbaseHashboinc($block->version,$coinbase->hashboinc); } ?>
			<?php if(!empty($coinstake->hashboinc)):?>
				<div><p>CoinStake HashBoinc<p><?=htmlspecialchars($coinstake->hashboinc)?></div>
			<?php endif;
				$inputs=array_merge(array_values($coinbase->vin),array_values($coinstake->vin));
				$outputs=array_merge(array_values($coinbase->vout),array_values($coinstake->vout));
				fragInputsOutputs($inputs,$outputs);
			?>
		<h3>Transactions</h3>
			<?php for($ix=$usertxstart; $ix<count($block->tx); $ix++): $tx=(object)$block->tx[$ix]; ?>
			<div class='blocktx'>
				<div class='hdr'>
					<div><span>Transaction: </span><span><kbd><?=$tx->txid?></kbd></span><a href='<?=href('tx/'.$tx->txid)?>'>^</a></div>
					<div>v<?=$tx->version?></div>
					<div><span>Delay: </span><span><?=fragTimePeriod($block->time - $tx->time)?></span></div>
					<?php if(!empty($tx->locktime)): ?>
						<div><span>Locked: </span><span><?=$tx->locktime?></span></div>
					<?php endif; ?>
				</div>
				<?php fragInputsOutputs($tx->vin,$tx->vout); ?>
				<?php if(!empty($tx->hashboinc)) { fragTxHashBoinc($block->version,$tx->hashboinc); } ?>
			</div><?php endfor;?>
		<h3>Index extra</h3>
		<a href="https://grctnexplorer.neuralminer.io/block/002d8055684709e730ac82e2457558f81c787a74d16f866dfc9df3d8421b9310">Inspiration</a>
		<div class='blockinfox kvboxes'>
			<div><p>Money Supply<p><?=$block->MoneySupply?></div>
			<div><p>Mint<p><?=$block->mint?></div>
			<div><p>Trust<p><?=$block->blocktrust?></div>
			<?php if(isset($block->modifier)):?>
				<div><p>Modifier<p><?=$block->modifier?></div>
			<?php endif;?>
			<div><p>Flags<p><?=$block->flags?></div>
			<div><p>Proof Hash<p><?=$block->proofhash?></div>
		</div>
	</main>
	<?php
}

function pageBlock($page,$rpc,$arg)
{
	$resp=$rpc->getblock($arg,true);
	/*
	switch (json_last_error()) {
        case JSON_ERROR_NONE:
            echo ' - No errors';
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }
  */
	fragBlock((object)$resp);
	$page->durStatic();
}

function pageTransaction($page,$rpc,$arg)
{
	$resp=$rpc->gettransaction($arg);
	FormatTreeNodeHtml($resp);
	$page->ok();
}

function kek($api)
{
	$resp= $api->getinfo();
	echo "node version: $resp[version]\n";
}

function pageOverview($page,$rpc,$arg)
{
	/* everything: diffplot, recent, sbactive, stats */
	/*
	 * basic info panel (block age, avgdiff)
	 * special info hidden panel
	 * recent blocks
	 * nodeinfo (version, connections)
	 */
	kek($rpc);
};

class DataGetRecentBlocks
{
	public $reqtime;
	public $lastblock;
	public $blocks;
	public function __construct($rpc,$moredetailed)
	{
		$mode = $moredetailed? 120 : 100;
		$count= 80;
		$converttonumerickeys= false;
		$resp= $rpc->getrecentblocks((string)$mode,(string)$count);
		$this->blocks=array();
		foreach($resp as $height => $block)
		{
			$this->blocks[(int)$height] = (object) $block;
		}
		$this->lastblock= (object)$rpc->getblock(reset($this->blocks)->hash);
		$this->reqtime=42;
	}
}

function fragRecentBlocksTable($data,$moredetailed)
{
	?>
	<table><thead>
		<tr><th>Height<th>Diff<th>Delay<th>F<th>F<th>CPID/Org
		<?php if($moredetailed):?>
			<th>Version
			<th>Tx
		<?php else:?>
			<th>Hash
		<?php endif;?>
		<!--<tr><th>Hash-->
	</thead><tbody>
		<?php foreach($data->blocks as $height => $block): ?>
			<tr><td><a href='<?=href("block/".$block->hash)?>'><?=number_format($height,0,'.',"'")?></a>
			<td><?=number_format($block->difficulty,3,'.','')?><td><?=$block->deltatime?>
			<td><?=$block->issuperblock? 'S' : $block->iscontract? 'C' : '-'?>
			<td><?=($block->cpid=='INVESTOR'? 'I' : ($block->research>0? 'R' : 'U'))?>
			<td><?=empty($block->organization)? $block->cpid : htmlspecialchars($block->organization) ?>
			<?php if($moredetailed):?>
				<td><?=htmlspecialchars($block->cversion)?>
				<td><?=($block->vtxsz-2)?>
			<?php else:?>
				<td><?=$block->hash?>
			<?php endif;?>
			</tr>
			<!--<tr><td><?=$block->hash?></tr>-->
		<?php endforeach; ?>
	</tbody></table>
	<?php
}

function pageRecent($page,$rpc,$arg)
{
	/* getrecentblocks: loads data that could have been cached, suprisingly fast
	 * getinfo: slow as hell
	 * getblockcount: fast, but getblock is slow. could cache, but cache is cold
	 * getblock: as slow as getrecentblocks 100 80
	 */
	$moredetailed=true;
	$data=new DataGetRecentBlocks($rpc,$moredetailed);
	?>
		<main>
			<h2>Recent blocks</h2>
			<p>Generated <?=fragRelaTime($data->reqtime);?>, last block <?=fragRelaTime($data->lastblock->time);?>.</p>
			<p><a href=''>Refresh</a> (please do not use F5)</p>
			<?php fragRecentBlocksTable($data,$moredetailed);?>
		</main>
	<?php
}

$pages=Array(
		"/"=>"pageOverview",
		"/info"=>"pageInfo",
		"/what"=>"pageWhat",
		"/block"=>"pageBlock",
		"/recent"=>"pageRecent",
		"/tx"=>"pageTransaction",
);
