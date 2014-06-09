<?php
// Config file that returns a simple array with the desired configuration parameters

return array(
	'validate_on_send' => true,
	'db' => array(
		'server' => '',
		'user' => '',
		'pass' => '',
		'database' => '',
		'table_name' => 'server_messages'
	),
	'statuses' => array(
		0 => 'Unapproved',
		1 => 'Unread',
		2 => 'Read'
	)
);