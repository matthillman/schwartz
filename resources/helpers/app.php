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