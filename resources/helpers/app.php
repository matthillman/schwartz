<?php
if (!function_exists('guide')) {
	function guide($name) {
		return route('page', ['page' => $name]);
	}
}