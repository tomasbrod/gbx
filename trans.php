<?php /* Transactions */

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
	$comment= GRCExtractXML($hashboinc,'MESSAGE',true);
	$msgtype= GRCExtractXML($hashboinc,'MT',true);
	$rainmsg= GRCExtractXML($hashboinc,'NARR',true);
	if($comment!==false):?>
	<div class='kvbox'>
		<p>Comment
		<p><?=htmlspecialchars($comment)?>
	</div><?php elseif($rainmsg!==false):?>
	<div class='kvbox'>
		<p>Rain
		<p><?=htmlspecialchars($rainmsg)?>
	</div><?php elseif($msgtype!==false):
		/*if($msgtype==='vote')
			{echo "<s>vote</s>";}
		else*/
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
						<span><?=$out['scriptPubKey']['asm']?></span>
					<?php else: ?>
						<span>other</span>
						<span><?=$out['scriptPubKey']['asm']?></span>
				<?php endif; endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody></table><?php endif; ?>
	</div>
	<?php
}

function pageTransaction($page,$rpc,$arg)
{
	$resp=$rpc->gettransaction($arg);
	FormatTreeNodeHtml($resp);
	$page->ok();
}
