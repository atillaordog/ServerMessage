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
			
			if (strpos($this->_config['server'], ':') !== false)
			{
				list($server, $port) = explode(':', $this->_config['server']);
				$this->_db = @new mysqli($server, $this->_config['user'], $this->_config['pass'], $this->_config['database'], $port);
			}
			else
			{
				$this->_db = @new mysqli($this->_config['server'], $this->_config['user'], $this->_config['pass'], $this->_config['database']);
			}
			
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
			`read` TINYINT UNSIGNED NULL,
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
		$data = (array)$message;
		
		unset($data['id']);
		
		$sql = 'INSERT INTO '.$this->_config['table_name'].'(`'.implode('`,`', array_keys($data)).'`) VALUES(';
		
		foreach( $data as $key => $value )
		{
			if ( is_numeric($value) )
			{
				$sql .= $value.',';
			}
			else
			{
				$sql .= '"'.$value.'",';
			}
		}
		
		$sql = rtrim($sql, ',');
		
		$sql .= ')';
		
		if ( $this->_db->query($sql) )
		{
			return $this->_db->insert_id;
		}
		
		return 0;
	}
	
	public function update(MessageEntity $message, Array $fields, Array $by_fields)
	{
		$sql = 'UPDATE '.$this->_config['table_name'].' SET ';
		
		$where = ' WHERE (1 = 1) ';
		$data = (array)$message;
		
		foreach( $data as $key => $value )
		{
			if ( in_array($key, $fields) )
			{
				$sql .= '`'.$key.'` = '.((is_numeric($value))? $value : '"'.$value.'"').',';
			}
			
			if ( in_array($key, $by_fields) )
			{
				if ( !is_array($value) )
				{
					$where .= ' AND `'.$key.'` = '.((is_numeric($value))? $value : '"'.$value.'"').',';
				}
				else
				{
					$are_numbers = array_filter($value, 'is_numeric');
					if ( count($are_numbers) == count($value) )
					{
						$in = '('.implode(',', $value).')';
					}
					else
					{
						$in = '("'.implode('", "', $value).'")';
					}
					
					$where .= ' AND `'.$key.'` IN '.$in.',';
				}
			}
		}
		
		$sql = rtrim($sql, ',');
		$where = rtrim($where, ',');
		
		if ( $this->_db->query($sql.$where) )
		{
			return true;
		}
		
		return false;
	}
	
	public function delete(MessageEntity $message, Array $by_fields)
	{
		$sql = 'DELETE FROM '.$this->_config['table_name'].' WHERE (1 = 1) ';
		
		$data = (array)$message;
		
		foreach( $data as $key => $value )
		{
			if ( in_array($key, $by_fields) )
			{
				if ( !is_array($value) )
				{
					$sql .= ' AND `'.$key.'` = '.((is_numeric($value))? $value : '"'.$value.'"').',';
				}
				else
				{
					$are_numbers = array_filter($value, 'is_numeric');
					if ( count($are_numbers) == count($value) )
					{
						$in = '('.implode(',', $value).')';
					}
					else
					{
						$in = '("'.implode('", "', $value).'")';
					}
					
					$where .= ' AND `'.$key.'` IN '.$in.',';
				}
			}
		}
		
		$sql = rtrim($sql, ',');
		
		if ( $this->_db->query($sql) )
		{
			return true;
		}
		
		return false;
	}
	
	public function get(Array $by_params, $limit = null, $offset = null)
	{
		$sql = 'SELECT * FROM '.$this->_config['table_name'].' WHERE (1 = 1) ';
		
		foreach( $by_params as $key => $value )
		{
			$sql .= ' AND `'.$key.'` = '.((is_numeric($value))? $value : '"'.$value.'"');
		}
		
		if ( $limit != null )
		{
			$sql .= ' LIMIT '.(int)$limit;
			if ( $offset != null )
			{
				$sql .= ' OFFSET '.(int)$offset;
			}
		}
		
		$res = $this->_db->query($sql);
		
		$tmp = array();
		
		$res->data_seek(0);
		while ( $row = $res->fetch_assoc() ) 
		{
			$message = new MessageEntity();
			$message->inject_data($row);
			
			$tmp[] = $message;
		}
		
		return $tmp;
	}
	
	public function get_total(Array $by_params)
	{
		$sql = 'SELECT COUNT(id) as nr FROM '.$this->_config['table_name'].' WHERE (1 = 1) ';
		
		foreach( $by_params as $key => $value )
		{
			$sql .= ' AND `'.$key.'` = '.((is_numeric($value))? $value : '"'.$value.'"');
		}
		
		$sql .= ' LIMIT 1';
		
		$res = $this->_db->query($sql);
		
		$row = $res->fetch_row();
		
		return (int)$row['nr'];
	}
	
	public function exists()
	{
		$res = $this->_db->query('SHOW TABLES LIKE '.$this->_config['table_name']);
		
		return ($res->num_rows == 1);
	}
	
	public function destroy_storage()
	{
		$this->_db->query('DROP TABLE IF EXISTS '.$this->_config['table_name']);
	}
}