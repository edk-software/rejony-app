<?php
if (!isset($_GET['language'])) {
	die('');
}
switch($_GET['language']) {
	case 'pl':
		die('2016-03-11 12:11:43');
	case 'en':
		die('2016-03-11 12:11:44');
	default:
		die('');
}