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
	
	public function __construct(Array $config = array())
	{
		if ( empty($config) )
		{
			$file = SERVERMESSAGE_ROOT.'config.php';
			
			if ( file_exists($file) )
			{
				$config = include($file);
			}
		}
		
		$self_config = get_object_vars($this);
		
		foreach ( $self_config as $key => $value )
		{
			if ( array_key_exists($key, $config) )
			{
				$this->$key = $config[$key];
			}
		}
	}
}