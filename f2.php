<?php
require("fw.php");
require("btrpc/easybitcoin.php");

function fragTransactionDetail($blockver,$index,$tx)
{
	echo "stub";
}

function fragTransactionInOut($tx)
{
	?>
	<div><div>Inputs</div><?php foreach($tx->vin as $in): $in=(object)$in; ?>
		<a href='<?=href("tx/{$in->txid}?i={$in->vout}")?>'><?=substr($in->txid,0,6)?>-<?=$in->vout?></a>
	<?php endforeach; ?></div>
	<table>
		<caption>Outputs</caption>
		<thead>
		<tr><th>Value<th>Type<th>Address</tr>
	</thead><tbody>
		<?php foreach($tx->vout as $out): $out=(object)$out; $out->type=$out->scriptPubKey['type']; $out->addresses=$out->scriptPubKey['addresses'];?>
			<?php /*FormatTreeNodeHtml($out);*/ ?>
			<tr><td><?=number_format($out->value,8,".","'")?>
			<td><?=$out->type?>
			<td><?php if(!empty($out->addresses)) { echo $out->addresses[0]; } else { echo "?";} ?>
			</tr>
		<?php endforeach; ?>
	</tbody></table>		
	<?php
}

function fragBlock($block)
{
	$usertxstart=2;
	?>
	<main>
		<h2>Block Details</h2>
		<table><tbody>
			<tr><th>Hash<td><?=$block->hash?>
			<tr><th>Height<td><?=$block->height?>
			<tr><th>Size<td><?=$block->size?>
			<tr><th>Version<td><?=$block->version?>
			<tr><th>Mint<td><?=$block->mint?>
			<tr><th>MoneySupply<td><?=$block->MoneySupply?>
			<tr><th>Time<td><?=$block->time?>
			<tr><th>Difficulty<td><?=$block->difficulty?>
			<tr><th>Block Trust<td><?=$block->blocktrust?>
			<tr><th>Previous<td><a href='<?=href('block/'.$block->previousblockhash)?>'><?=$block->previousblockhash?></a>
			<tr><th>Next<td><a href='<?=href('block/'.$block->nextblockhash)?>'><?=$block->nextblockhash?></a>
			<tr><th>Confirmations<td><?=$block->confirmations?>
			<tr><th>Flags<td><?=$block->flags?>
			<tr><th>Proof Hash<td><?=$block->proofhash?>
			<tr><th>Modifier<td><?=$block->modifier?>
			<tr><th>CPID<td><?=$block->CPID?>
			<tr><th>Magnitude<td><?=$block->Magnitude?>
			<tr><th>Research<td><?=$block->ResearchSubsidy?>
			<tr><th>LastPaymentTime<td><?=$block->LastPaymentTime?>
			<tr><th>ResearchAge<td><?=$block->ResearchAge?>
			<tr><th>ResearchMagnitudeUnit<td><?=$block->ResearchMagnitudeUnit?>
			<tr><th>ResearchAverageMagnitude<td><?=$block->ResearchAverageMagnitude?>
			<tr><th>LastPORBlockHash<td><?=$block->LastPORBlockHash?>
			<tr><th>Interest<td><?=$block->Interest?>
			<tr><th>Client Version<td><?=$block->ClientVersion?>
			<tr><th>NeuralHash<td><?=$block->NeuralHash?>
			<tr><th>IsSuperBlock<td><?=$block->IsSuperBlock?>
			<tr><th>IsContract<td><?=$block->IsContract?>
			</tbody>
		</table>
		<div>
			<p>coinbase, coinstake <s>...</s>
		</div>
		<?php for($ix=$usertxstart; $ix<count($block->tx); $ix++): $tx=(object)$block->tx[$ix]; ?><div>
			<div>Transaction: <kbd><?=$tx->txid?></kbd></div>
			<div>Delay: <?=fragTimePeriod($block->time - $tx->time)?>, (v<?=$tx->version?>)</div>
			<?php if($tx->locktime): ?><div>
				Locked <?=$tx->locktime?>
			</div><?php endif; if(strlen($tx->hashboinc)):?><div>
				Has message: <?=$tx->hashboinc?>
			</div><?php endif;?>				
			<?php fragTransactionInOut($tx); ?>
			<br/>
		</div><?php endfor;?>
	</main>
	<?php
	/*
			<tr><th><td><?=$block->?>
	*/
}

function pageBlock($page,$rpc,$arg)
{
	$resp=$rpc->getblock($arg,true);
	fragBlock((object)$resp);
	$page->durStatic();
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
);
