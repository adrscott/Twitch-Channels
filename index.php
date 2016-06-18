<?php
/**
 * @author adrscott (twitter.com/adrscott, adrscott.com)
 */

// Current directory path
define('CUR_PATH', basename(__DIR__));

// The main class.
include 'Application.php';

// Init the app
$app = new Application;

// Get array of live channels
$dataArray = $app->getChannelsCache();

// Get array of team channels
$teamChannels = json_decode($app->getTeamCache(), true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Twitch API</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<body>	
	<div class="container" style="margin-top: 100px;">
		<div class="row">
			<div class="col-md-2">
				<b>Members:</b>
				<ul class="list-group">
					<?php foreach($teamChannels as $key): ?>
					<li class="list-group-item"><a href='https://www.twitch.tv/<?= $key; ?>'><?= $key; ?></a></li>
					<?php endforeach; ?>
				</ul>				
			</div>
			<div class="col-md-10">
				<h3>Live Members</h3>
				<div class="row">
					<?php foreach($dataArray['streams'] as $streamer): ?>
					<?php if($streamer['_id'] != null): ?>
					<div class="col-md-4">
						 <img src="<?= $streamer['preview']['medium']; ?>">
						 <div style="margin: 10px 0;"><b><?= substr( $streamer['channel']['status'], 0, 35 ); ?></b></div>
						 <div><?= number_format($streamer['viewers']); ?> viewers on <?= $streamer['channel']['display_name']; ?></div>
						 <div style="margin-bottom: 30px;">Game: <?= $streamer['channel']['game']; ?></div>
					</div>
					<?php endif; ?>			
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</body>
</html>