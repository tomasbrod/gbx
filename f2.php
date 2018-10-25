<?php
require("fw.php");
require("btrpc/easybitcoin.php");

function fragTransactionDetail($blockver,$index,$tx)
{
	echo "stub";
}

function toKiSize($val)
{
	return $val;
}

function fragCoinbaseHashboinc($version,$hashboinc)
{
	?>
			<p>dissect hashboinc of coinbase ...
	<?php
}

function fragTxHashBoinc($version,$hashboinc)
{
	?>
			<p>dissect hashboinc of transactions ...
	<?php
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
					<div>coinbase</div>
				<?php endif;?>
			<?php endforeach;?>
		</div>
		<?php if(!empty($outputs)):?><div class='txouttitle'>Outputs</div><div class='txout'>
			<?php foreach($outputs as $out): ?>
				<div><span><?=number_format($out['value'],8,".","'")?></span> <span>GRC</span>
				<?php if(isset($out['scriptPubKey']) && isset($out['scriptPubKey']['type'])):
					if($out['scriptPubKey']['type']=="pubkey"):	?>
						<span>PK</span>
						<span><?=$out['scriptPubKey']['addresses'][0]?></span>
					<?php elseif($out['scriptPubKey']['type']=="pubkeyhash"):	?>
						<span>PKH</span>
						<span><?=$out['scriptPubKey']['addresses'][0]?></span>
					<?php else: ?>
						<span>??</span>
				<?php endif; endif; ?>
				</div>
			<?php endforeach; ?>
		</div><?php endif; ?>
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
		

		<h3>Block Information</h3>
		<div class='blockinfoheader'>
			<a href='<?=href('block/'.$block->previousblockhash)?>'><span class='arrowback'>&lt;=</span></a>
			<span class='hash'><?=$block->hash?></span>
			<?php if(isset($block->nextblockhash)): ?>
				<a href='<?=href('block/'.$block->nextblockhash)?>'><span class='arrowfwrd'>=&gt;</span></a>
			<?php endif;?>
		</div>
		<div class='blockinfo'>
			<div><p>Height<p><?=$block->height?></div>
			<div><p>Difficulty<p><?=number_format($block->difficulty,4,'.','')?></div>
			<div><p>Depth<p>...</div>
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
				<?php if(!empty($tx->hashboinc)) { fragTxHashBoinc($block->version,$tx->hashboinc); } ?>
				<?php fragInputsOutputs($tx->vin,$tx->vout); ?>
			</div><?php endfor;?>
		<h3>Index extra</h3>
		<a href="https://grctnexplorer.neuralminer.io/block/002d8055684709e730ac82e2457558f81c787a74d16f866dfc9df3d8421b9310">Inspiration</a>
		<div class='blockinfox'>
			<div><p>Money Supply<p><?=$block->MoneySupply?></div>
			<div><p>Mint<p><?=$block->mint?></div>
			<div><p>Trust<p><?=$block->blocktrust?></div>
			<?php if(isset($block->modifier)):?>
				<div><p>Modifier<p><?=$block->modifier?></div>
			<?php endif;?>
		</div>
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
	</main>
	<?php
}

function pageBlock($page,$rpc,$arg)
{
	$resp=$rpc->getblock($arg,true);
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
