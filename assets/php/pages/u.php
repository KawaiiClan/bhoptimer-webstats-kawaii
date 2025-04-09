<?php
$results = false;
$stmt = false;
if(preg_match('/^\[U:1:([0-9]+)\]$/', $u, $matches))
{
	$sid = substr($u, 5, -1);
	$stmt = $connection->prepare('SELECT auth, name, lastlogin, points, playtime, race_win, race_loss FROM '.MYSQL_PREFIX.'users WHERE auth = ?;');
	$stmt->bind_param('s', $sid);
	$stmt->execute();
	$stmt->store_result();
	$results = ($rows = $stmt->num_rows) > 0;
	$stmt->bind_result($auth, $name, $lastlogin, $points, $playtime, $race_win, $race_loss);
	if($rows > 0)
	{
		while($row = $stmt->fetch())
		{
			echo '<script type="text/javascript">document.title = "Player Stats: '.$name.'";</script>';
			$authtemp = SteamID::Parse($u, SteamID::FORMAT_STEAMID3);
			$authtemp = SteamID::Parse($u, SteamID::FORMAT_AUTO, true);
			$steamid64 = $authtemp->Format(SteamID::FORMAT_STEAMID64);
			$response = SteamID::Curl("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" .  API_KEY . "&steamids=" . $steamid64);
			$result = json_decode($response);
			$img = ($result != NULL)?$result->response->players[0]->avatarfull:'assets/img/u.png';
			$personaname = ($result != NULL)?$result->response->players[0]->personaname:'Unknown';
			$stmt2 = $connection->prepare('SELECT lastlogin, points FROM '.MYSQL_PREFIX.'users WHERE auth = ' . $auth);
			$stmt2->execute();
			$stmt2->store_result();
			$stmt2->bind_result($lastlogin2, $points2);
			
			$connection->query('SET @rank:=0');
			$stmt3 = $connection->prepare('SELECT rank FROM (SELECT @rank:=@rank+1 AS rank, auth FROM '.MYSQL_PREFIX.'users ORDER BY points DESC) A WHERE auth = ' . $auth);
			$stmt3->execute();
			$stmt3->store_result();
			$stmt3->bind_result($rank);
			
			$stmt4 = $connection->prepare('SELECT COUNT(*) FROM '.MYSQL_PREFIX.'playertimes WHERE auth = ' . $auth);
			$stmt4->execute();
			$stmt4->store_result();
			$stmt4->bind_result($times);
			
			switch($f)
			{
				case 0:
					$filter = ' ORDER BY points DESC ';
					break;
				
				case 1:
					$filter = ' ORDER BY points ASC ';
					break;
				
				case 2:
					$filter = ' ORDER BY date DESC ';
					break;
				
				case 3:
					$filter = ' ORDER BY date ASC ';
					break;
					
				case 4:
					$filter = ' ORDER BY time ASC ';
					break;
				
				case 5:
					$filter = ' ORDER BY time DESC ';
					break;
				
				default:
					$filter = '';
					break;
			}
			
			if($s == -1 && $t == -1)
			{
				$stmt5 = $connection->prepare('SELECT map, time, jumps, strafes, sync, date, points, style, track FROM '.MYSQL_PREFIX.'playertimes WHERE auth = '.$auth.$filter.'LIMIT '.PLAYER_PAGE_LIMIT.';');
			}
			elseif($t == -1)
			{
				$stmt5 = $connection->prepare('SELECT map, time, jumps, strafes, sync, date, points, style, track FROM '.MYSQL_PREFIX.'playertimes WHERE auth = '.$auth.' AND style = '.$s.$filter.'LIMIT '.PLAYER_PAGE_LIMIT.';');
			}
			elseif($s == -1)
			{
				$stmt5 = $connection->prepare('SELECT map, time, jumps, strafes, sync, date, points, style, track FROM '.MYSQL_PREFIX.'playertimes WHERE auth = '.$auth.' AND track = '.$t.$filter.'LIMIT '.PLAYER_PAGE_LIMIT.';');
			}
			else
			{
				$stmt5 = $connection->prepare('SELECT map, time, jumps, strafes, sync, date, points, style, track FROM '.MYSQL_PREFIX.'playertimes WHERE auth = '.$auth.' AND style = '.$s.' AND track = '.$t.$filter.'LIMIT '.PLAYER_PAGE_LIMIT.';');
			}
			$stmt5->execute();
			$stmt5->store_result();
			$stmt5->bind_result($tmap, $ttime, $tjumps, $tstrafes, $tsync, $tdate, $tpoints, $tstyle, $ttrack);
?>
			<div>
				<div>
					<h1><?=($name == $personaname)?$name:$name.' ('.$personaname.')'?></h1>
					<?php $steamid = SteamID::Parse($auth, SteamID::FORMAT_S32);?>
					<h2>[U:1:<?=$auth?>]&nbsp<a href="https://steamcommunity.com/profiles/<?=$steamid->Format(SteamID::FORMAT_STEAMID64)?>/" target="_blank"><img src="assets/img/steam-icon.png" style="max-height:15px;"></img></a></h2>
					<img src="<?=$img?>"/>
					<div style="text-align:left; max-width:200px;">
						Rank: <?php while ($row = $stmt3->fetch()) {echo $rank;} ?>
						<br><?php while ($row = $stmt2->fetch()) {echo floor($points2);} ?>&nbsppoints
						with&nbsp<?php while ($row = $stmt4->fetch()) {echo $times;} ?>&nbsptimes
						<br>Time Played: <?=formattoseconds($playtime, 1)?>
						<br>Last Seen: <?=($lastlogin2>0)?date('j M Y', $lastlogin2):'Unknown'?>
						<br>Races Won: <?=$race_win?>&nbsp&nbspLost: <?=$race_loss?>
					</div>
				</div>
				<br>
				<div>
					<?php include('assets/php/ts-bar.php'); ?>
					<br><br>
<?php
					$rows = $stmt5->num_rows;
					if($rows > 0)
					{
						include('assets/php/filter-bar.php');
?>
							<table>
								<thead>
									<th>Map</th>
									<?=($t==-1)?'<th>Track</th>':''?>
									<?=($s==-1)?'<th>Style</th>':''?>
									<th>Time</th>
									<th>Jumps</th>
									<th>Strafes</th>
									<th>Sync</th>
									<th>Points</th>
									<th>Date</th>
								</thead>
<?php
									while($row = $stmt5->fetch())
									{
?>
										<tr>
											<td><a href="index.php?m=<?=$tmap?>&t=<?=$ttrack?>&s=<?=$tstyle?>"><?=$tmap?></a></td>
											<?=($t==-1)?'<td>'.$tracks[$ttrack].'</td>':''?>
											<?=($s==-1)?'<td>'.$styles[$tstyle].'</td>':''?>
											<td><?=formattoseconds($ttime)?></td>
											<td><?=$tjumps?></td>
											<td><?=$tstrafes?></td>
											<td><?=number_format($tsync, 2)?>%</td>
											<td><?=$tpoints?></td>
											<td><?=date('j M Y', $tdate); ?></td>
										</tr>
<?php
									}
?>
							</table>
<?php
					}
					else
					{
						echo '<h1>No times</h1>';
					}
?>
				</div>
			</div>
<?php 
			$stmt2->close();
			$stmt3->close();
			$stmt4->close();
			$stmt5->close();
		}
	}
	else
	{
		echo 'Player not found on server';
	}
}
else
{
	$stmt = $connection->prepare('SELECT UNIQUE auth, name, points, lastlogin FROM '.MYSQL_PREFIX.'users WHERE name LIKE ? ORDER BY points DESC;');
	$param = '%'.$u.'%';
	$stmt->bind_param('s', $param);
	$stmt->execute();
	$stmt->store_result();
	$results = ($rows = $stmt->num_rows) > 0;

	$stmt->bind_result($auth, $name, $points, $lastlogin);

	if($rows > 0)
	{
		echo '<p><h1>Players matching "'.$u.'"</h1></p>';
		$first = true;
		$rank = 1;
		while($row = $stmt->fetch()) 
		{
			if($first)
			{
				$first = false;
?>
				<table>
					<thead>
						<th></th>
						<th>Name</th>
						<th>Points</th>
						<th>Last Login</th>
					</thead>
<?php
			}
?>
					<tr>
						<td><center>
							<?php $steamid = SteamID::Parse($auth, SteamID::FORMAT_S32);
							echo '<a href="https://steamcommunity.com/profiles/'.$steamid->Format(SteamID::FORMAT_STEAMID64).'/" target="_blank"><img src="assets/img/steam-icon.png"></img></a>'; ?>
						</center></td>
						<td><a href="index.php?u=<?='[U:1:'.$auth.']'?>"><?=$name?></a></td>
						<td><?=number_format($points, 2)?></td>
						<td><?=($lastlogin>0)?date('j M Y', $lastlogin):'Unknown'?></td>
					</tr>
<?php

			if(++$rank > RECORD_LIMIT)
			{
				break;
			}
		}
?>
				</table>
<?php
	}
	else
	{
		echo 'No players found matching "'.$u.'"';
	}
}
if($stmt != false)
{
	$stmt->close();
}
$connection->close();
?>