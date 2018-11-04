<?php /* Block */

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
