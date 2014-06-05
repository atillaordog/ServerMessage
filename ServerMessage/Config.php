<?php

namespace ServerMessage;

/**
 * Config class that loads configuration from file and overwrites defaults as necessary
 */
class Config
{	
	public $storage_class = null;
	public $validate_on_send = true;
	
	public function __construct()
	{
		$file = SERVERMESSAGE_ROOT.'config.php';
		
		$config = array();
		if ( file_exists($file) )
		{
			$config = include($file);
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