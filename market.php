<?php require_once 'engine/init.php'; include 'layout/overall/header.php';

$server = $config['shop']['imageServer'];
$imageType = $config['shop']['imageType'];

$compare = getValue($_GET['compare']);

// If you are not comparing any items, present the list.
if (!$compare) {
	$cache = new Cache('engine/cache/market');
	$cache->setExpiration(6);
	if ($cache->hasExpired()) {
		$offers = array(
			'wts' => mysql_select_multi("SELECT `id`, `itemtype` AS `item_id`, `amount`, `price`, `created`, `anonymous`, (SELECT `name` FROM `players` WHERE `id` = `player_id`) AS `player_name` FROM `market_offers` WHERE `sale` = 1 ORDER BY `created` DESC;"),
			'wtb' => mysql_select_multi("SELECT `id`, `itemtype` AS `item_id`, `amount`, `price`, `created`, `anonymous`, (SELECT `name` FROM `players` WHERE `id` = `player_id`) AS `player_name` FROM `market_offers` WHERE `sale` = 0 ORDER BY `created` DESC;")
		);
		$cache->setContent($offers);
		$cache->save();
	} else {
		$offers = $cache->load();
	}
	?>
	<h1>Marketplace</h1>
	<p>You can buy and sell items by clicking on the <a target="_BLANK" href="http://4.ii.gl/CAPcBp.png">market in depot.</a> <br>To sell an item: Place item inside your depot, click on market, search for your item and sell it.</p>
	<h2>WTS: Want to sell</h2>
	<table class="table tbl-hover">
		<tr class="yellow">
			<td>Item</td>
			<td>Count</td>
			<td>Price</td>
			<td>Added</td>
			<td>By</td>
			<td>Compare</td>
		</tr>
		<?php
		foreach ($offers['wts'] as $o) {
		?>
		<tr>
			<td><img src="<?php echo "http://".$server."/".$o['item_id'].".".$imageType; ?>" alt="Item Image"></td>
			<td><?php echo $o['amount']; ?></td>
			<td><?php echo number_format($o['price'], 0, "", " "); ?></td>
			<td><?php echo getClock($o['created'], true, true); ?></td>
			<td><?php echo ($o['anonymous'] == 1) ? 'Anonymous' : "<a target='_BLANK' href='characterprofile.php?name=".$o['player_name']."'>".$o['player_name']."</a>"; ?></td>
			<td><a href="?compare=<?php echo $o['item_id']; ?>"><button>Compare</button></a></td>
		</tr>
		<?php
		}
		?>
	</table>
	<h2>WTB: Want to buy</h2>
	<table class="table tbl-hover">
		<tr class="yellow">
			<td>Item</td>
			<td>Count</td>
			<td>Price</td>
			<td>Added</td>
			<td>By</td>
			<td>Compare</td>
		</tr>
		<?php
		foreach ($offers['wtb'] as $o) {
		?>
		<tr>
			<td><img src="<?php echo "http://".$server."/".$o['item_id'].".".$imageType; ?>" alt="Item Image"></td>
			<td><?php echo $o['amount']; ?></td>
			<td><?php echo number_format($o['price'], 0, "", " "); ?></td>
			<td><?php echo getClock($o['created'], true, true); ?></td>
			<td><?php echo ($o['anonymous'] == 1) ? 'Anonymous' : "<a target='_BLANK' href='characterprofile.php?name=".$o['player_name']."'>".$o['player_name']."</a>"; ?></td>
			<td><a href="?compare=<?php echo $o['item_id']; ?>"><button>Compare</button></a></td>
		</tr>
		<?php
		}
		?>
	</table>
	<?php
} else {
	// Else You want to compare price
	$compare = (int)$compare;

	// First list active bids
	$offers = mysql_select_multi("SELECT `id`, `sale`, `itemtype` AS `item_id`, `amount`, `price`, `created`, `anonymous`, (SELECT `name` FROM `players` WHERE `id` = `player_id`) AS `player_name` FROM `market_offers` WHERE `itemtype`='$compare' ORDER BY `price` ASC;");
	$historyOffers = mysql_select_multi("SELECT `id`, `itemtype` AS `item_id`, `amount`, `price`, `inserted`, `expires_at`, (SELECT `name` FROM `players` WHERE `id` = `player_id`) AS `player_name` FROM `market_history` WHERE `itemtype`='$compare' AND `state` IN (3, 255) ORDER BY `price` ASC;");
	$buylist = false;
	// Markup
	?>
	<h1>Comparing item</h1>
	<a href="market.php"><button>Go back</button></a>
	<h2>Active offers</h2>
	<table class="table tbl-hover">
		<tr class="yellow">
			<td>Item</td>
			<td>Count</td>
			<td>Price</td>
			<td>Added</td>
			<td>By</td>
		</tr>
		<?php
		foreach ($offers as $o) {
			$wtb = false;
			if ($o['sale'] == 0) {
				$wtb = true;
				if ($buylist === false) $buylist = array();
				$buylist[] = $o;
			} else {
				?>
				<tr>
					<td><img src="<?php echo "http://".$server."/".$o['item_id'].".".$imageType; ?>" alt="Item Image"></td>
					<td><?php echo $o['amount']; ?></td>
					<td><?php echo number_format($o['price'], 0, "", " "); ?></td>
					<td><?php echo getClock($o['created'], true, true); ?></td>
					<td><?php echo ($o['anonymous'] == 1) ? 'Anonymous' : "<a target='_BLANK' href='characterprofile.php?name=".$o['player_name']."'>".$o['player_name']."</a>"; ?></td>
				</tr>
				<?php
			}
		}
		?>
	</table>
	<?php
	if ($buylist !== false) {
		?>
		<h2>Want to buy:</h2>
		<table class="table tbl-hover">
			<tr class="yellow">
				<td>Item</td>
				<td>Count</td>
				<td>Price</td>
				<td>Added</td>
				<td>By</td>
			</tr>
			<?php
			foreach ($buylist as $o) {
				?>
				<tr>
					<td><img src="<?php echo "http://".$server."/".$o['item_id'].".".$imageType; ?>" alt="Item Image"></td>
					<td><?php echo $o['amount']; ?></td>
					<td><?php echo number_format($o['price'], 0, "", " "); ?></td>
					<td><?php echo getClock($o['created'], true, true); ?></td>
					<td><?php echo ($o['anonymous'] == 1) ? 'Anonymous' : "<a target='_BLANK' href='characterprofile.php?name=".$o['player_name']."'>".$o['player_name']."</a>"; ?></td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
	}
	?>
	<h2>Old purchased offers</h2>
	<table class="table tbl-hover">
		<tr class="yellow">
			<td>Item</td>
			<td>Count</td>
			<td>Price</td>
			<td>Offer added</td>
			<td>Offer expired</td>
			<td>By</td>
		</tr>
		<?php
		foreach ($historyOffers as $o) {
		?>
		<tr>
			<td><img src="<?php echo "http://".$server."/".$o['item_id'].".".$imageType; ?>" alt="Item Image"></td>
			<td><?php echo $o['amount']; ?></td>
			<td><?php echo number_format($o['price'], 0, "", " "); ?></td>
			<td><?php echo getClock($o['inserted'], true, true); ?></td>
			<td><?php echo getClock($o['expires_at'], true, true); ?></td>
			<td><?php echo ($o['anonymous'] == 1) ? 'Anonymous' : "<a target='_BLANK' href='characterprofile.php?name=".$o['player_name']."'>".$o['player_name']."</a>"; ?></td>
		</tr>
		<?php
		}
		?>
	</table>
	<?php
}
include 'layout/overall/footer.php'; ?>