<?php

namespace ServerMessage;

/**
 * Config class that loads configuration from file and overwrites defaults as necessary
 */
class Config
{	
	public $storage = 'db';
	public $validate_on_send = true;
	public $db = array(
		'server' => '',
		'user' => '',
		'pass' => '',
		'database' => '',
		'table_name' => 'server_messages'
	);
	public $statuses = array(
		0 => 'Unapproved',
		1 => 'Approved'
	);
	
	public function __construct(Array $config = array())
	{
		$file = SERVERMESSAGE_ROOT.'config.php';
		if ( file_exists($file) )
		{
			$file_config = include($file);
		}
		
		$self_config = get_object_vars($this);
		
		foreach ( $self_config as $key => $value )
		{
			if ( array_key_exists($key, $file_config) )
			{
				$this->$key = $file_config[$key];
			}
			
			if ( array_key_exists($key, $config) )
			{
				$this->$key = $config[$key];
			}
		}
	}
}