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