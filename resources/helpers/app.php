<?php
if (!function_exists('guide')) {
	function guide($name) {
		return route('guide', ['page' => $name]);
	}
}