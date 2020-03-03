<?php
if (!function_exists('guide')) {
	function guide($name) {
		return route('guide', ['name' => $name]);
	}
}
if (!function_exists('handbook')) {
	function handbook($name) {
		return route('handbook', ['name' => $name]);
	}
}
if (!function_exists('google_sheets')) {
	function google_sheets() {
		return app('google_sheets');
	}
}
if (!function_exists('shitty_bot')) {
	function shitty_bot() {
		return app('shitty_bot');
	}
}
if (!function_exists('format_stat')) {
	function format_stat($value, $stat) {
		if (!is_numeric($value)) {
			return $value;
		}
		static $percent_stats = [
			'UNITSTATCRITICALDAMAGE',
			'UNITSTATATTACKCRITICALRATING',
			'UNITSTATABILITYCRITICALRATING',
			'UNITSTATRESISTANCE',
			'UNITSTATACCURACY',
			'UNITSTATCRITICALCHANCEPERCENTADDITIVE',
			'UNITSTATHEALTHSTEAL',
			'UNITSTATSHIELDPENETRATION',
			'UNITSTATDODGENEGATERATING',
			'UNITSTATARMOR',
			'UNITSTATDODGERATING',
			'UNITSTATATTACKCRITICALNEGATERATING',
			'UNITSTATDEFLECTIONNEGATERATING',
			'UNITSTATSUPPRESSION',
			'UNITSTATDEFLECTIONRATING',
			'UNITSTATABILITYCRITICALNEGATERATING',
		];

		if (ctype_digit(strval($stat))) {
			$stat = new \SwgohHelp\Enums\UnitStat($stat);
		}

		if ($stat instanceof \SwgohHelp\Enums\UnitStat) {
			$stat = $stat->getKey();
		}

		return in_array($stat, $percent_stats) ? sprintf("%.1f%%", $value) : number_format($value);
	}
}

// if (!function_exists('guzzle')) {
//     /**
//      * Return a guzzle client
//      *
//      * @param  string  $value
//      * @param  array  $options
//      * @return GuzzleHttp\Client
//      */
//     function guzzle() {
//         return app('guzzle');
//     }
// }

// if (!function_exists('goutte')) {
//     /**
//      * Return a goutte client
//      *
//      * @param  string  $value
//      * @param  array  $options
//      * @return Goutte\Client
//      */
//     function goutte() {
//         return app('goutte');
//     }
// }