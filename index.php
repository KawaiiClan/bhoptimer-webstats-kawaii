<?php
//using _GET requests so that you can link specific pages/filters to others by copying URL
//eww sanitizing on define.. won't cause problems in this application though.. probably..
$sv = (isset($_REQUEST['sv']) && ($_REQUEST['sv'] == 'bhop' || $_REQUEST['sv'] == 'climb')) ? $_REQUEST['sv'] : 'bhop';
$s = isset($_REQUEST['s']) ? (int)$_REQUEST['s'] : -1;
$t = isset($_REQUEST['t']) ? (int)$_REQUEST['t'] : -1;
$u = isset($_REQUEST['u']) ? htmlspecialchars($_REQUEST['u']) : NULL;
$m = isset($_REQUEST['m']) ? htmlspecialchars($_REQUEST['m']) : NULL;
$f = isset($_REQUEST['f']) ? (int)$_REQUEST['f'] : 0;
$rr = isset($_REQUEST['rr']);
$rt = isset($_REQUEST['rt']);

//includes c:
require 'assets/php/config.php';
require 'assets/php/functions.php';
require 'assets/php/steamid.php';

//db init
$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_SCHEMA);
$connection->set_charset('utf8');

//you did put an api key.. right? c;
if(API_KEY != false){SteamID::SetSteamAPIKey(API_KEY);}
?>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<link href="assets/img/favicon.png" rel="icon" type="image/x-icon" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<?php echo $m ? '<title>'.$m.'</title>' : (isset($u) ? '<title>Player stats '.$u.'</title>' : '<title>'.HOMEPAGE_TITLE.'</title>'); ?>
	</head>
	<body>

<?php
		include('assets/php/nav.php');
?>
		<div class="main">
		<center>
<?php
		if($rr || $rt)
		{
			include('assets/php/pages/r.php');
		}
		elseif($u)
		{
			include('assets/php/pages/u.php');
		}
		elseif($m)
		{
			include('assets/php/pages/m.php');
		}
		else
		{
			include('assets/php/pages/t.php');
		}
?>
		</center>
		</div>
	</body>
</html>
