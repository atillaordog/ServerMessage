<?php

namespace ServerMessage\Storage;

use ServerMessage\Interfaces\Storage as StorageInterface;
use ServerMessage\MessageException as MessageException;
use ServerMessage\Entity\Message as MessageEntity;
use mysqli;

/**
 * The DB storage extension of the server message, uses mysqli PHP extension
*/ 
class DB implements StorageInterface
{
	private $_config = array(
		'server' => '',
		'user' => '',
		'pass' => '',
		'database' => '',
		'table_name' => 'server_messages'
	);
	
	private $_db = null;
	
	public function __construct(Array $config = array())
	{
		foreach ( $this->_config as $key => $value )
		{
			if ( array_key_exists($key, $config) )
			{
				$this->_config[$key] = $config[$key];
			}
			
			if ( !class_exists('mysqli') )
			{
				throw new MessageException('Mysqli module not installed.');
			}
			
			$this->_db = new mysqli($this->_config['server'], $this->_config['user'], $this->_config['pass'], $this->_config['database']);
			
			if ($this->_db->connect_errno) {
				throw new MessageException("Failed to connect to MySQL: (" . $this->_db->connect_errno . ") " . $this->_db->connect_error);
			}
		}
	}
	
	public function create_storage()
	{
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$this->_config['table_name'].'` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`created_on` DATETIME NULL,
			`updated_on` DATETIME NULL,
			`subject` VARCHAR(255) NULL,
			`body` TEXT NULL,
			`sender_id` INT UNSIGNED NULL,
			`sender_type` VARCHAR(255) NULL,
			`reciever_id` INT UNSIGNED NULL,
			`reciever_type` VARCHAR(255) NULL,
			`status` TINYINT UNSIGNED NULL,
			`meta` TEXT NULL,
			PRIMARY KEY (`id`)
		)
		COLLATE=\'utf8_unicode_ci\'
		ENGINE=InnoDB;
		';
		
		if ( $this->_db->query($sql) )
		{
			return true;
		}
		
		return false;
	}
	
	public function add(MessageEntity $message)
	{
		
	}
	
	public function update(MessageEntity $message, Array $fields, Array $by_fields)
	{
		
	}
	
	public function delete(MessageEntity $message, Array $by_fields)
	{
		
	}
	
	public function get(Array $by_params)
	{
		
	}
}