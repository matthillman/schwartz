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
if (!function_exists('mod_bonus')) {
	function mod_bonus($stat, $stats) {
        if (is_string($stat)) {
            $stat = \SwgohHelp\Enums\UnitStat::$stat();
        }
        return array_get($stats, 'mods.'.$stat->getValue(), null);
	}
}
if (!function_exists('mod_image_for_stat')) {
	function mod_image_for_stat($stat) {
		if (is_int($stat)) {
			$stat = new \SwgohHelp\Enums\UnitStat($stat);
		} else if (is_string($stat)) {
            $stat = \SwgohHelp\Enums\UnitStat::$stat();
        }
        return $stat->displayString();
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

		if (in_array($stat, [ 'UNITSTATATTACKCRITICALRATING', 'UNITSTATABILITYCRITICALRATING'])) {
			$value = $value / 100;
		}

		return in_array($stat, $percent_stats) ? sprintf("%.1f%%", $value) : number_format($value);
	}
}

if (!function_exists('previous_route_name')) {
    /**
     * The route name of the previous url
	 *
     * @return String
     */
    function previous_route_name() {
        return app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();
    }
}

if (!function_exists('solve')) {
    /**
     * Solves a linear equation and returns the solution for the single variable
	 *
     * @return Number
     */
	function solve($equation) {
		list($lhs, $rhs) = explode('=', $equation);
		$reduced = [];

		foreach ([$lhs, $rhs] as $side) {
			if (preg_match('/[a-zA-Z]/', $side)) {
				$reduced[] = $side;
			} else {
				$reduced[] = \RR\Shunt\Parser::parse($side);
			}
		}

		return +max( last((new \Solver\Solve(implode('=', $reduced), false))->solution() ) );
	}
}
if (!function_exists('request_is_bot')) {
    /**
     * Is the request from the bot?
	 *
     * @return Boolean
     */
	function request_is_bot() {
		return stripos(request()->header('schwartz'), 'bot') !== false;
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