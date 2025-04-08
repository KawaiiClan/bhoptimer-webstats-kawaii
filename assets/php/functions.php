<?php
//added second arg, so that i can not display seconds on playtime on user page
function formattoseconds($time, $user=NULL)
{
	$iTemp = floor($time);
	$iHours = 0;

	if($iTemp > 3600)
	{
		$iHours = floor($iTemp / 3600.0);
		$iTemp %= 3600;
	}

	$sHours = $iHours;
	$iMinutes = 0;

	if($iTemp >= 60)
	{
		$iMinutes = floor($iTemp / 60.0);
		$iTemp %= 60;
	}

	$sMinutes = '';
	$sMinutes = $iMinutes;
	$fSeconds = (($iTemp) + $time - floor($time));
	$sSeconds = number_format($fSeconds, 2);
	
	if($iHours > 0)
	{
		if($sSeconds < 10)
		{
			$sSeconds = '0'.$sSeconds;
		}
		if($sMinutes < 10)
		{
			$sMinutes = '0'.$sMinutes;
		}
		$newtime = (isset($user)) ? $sHours.'h '.$sMinutes.'m' : $sHours.'h '.$sMinutes.'m '.$sSeconds.'s';
	}
	elseif($iMinutes > 0)
	{
		$newtime = $sMinutes.'m '.$sSeconds.'s';
	}
	else{
		$newtime = number_format($fSeconds, 3).'s';
	}

	return $newtime;
}
?>