<?php /* F3 */

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
		$count= 120;
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
			<td><?=(($block->issuperblock)?( 'S' ):( ($block->iscontract)? ('C') : ('-')))?>
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
