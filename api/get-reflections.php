<?php
if (!isset($_GET['language'])) {
	die('');
}
header('Content-type: application/json;charset=utf-8');
switch ($_GET['language']) {
	case 'pl':
		die(file_get_contents('./reflections/pl.json'));
	case 'en':
		die(file_get_contents('./reflections/en.json'));
}