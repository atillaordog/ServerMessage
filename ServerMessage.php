<?php

use ServerMessage\Interfaces\Storage as StorageInterface;
use ServerMessage\Config as Config;
use ServerMessage\MessageException as MessageException;
use ServerMessage\Entity\Message as MessageEntity;
use ServerMessage\Interfaces\Validation as ValidationInterface;
use ServerMessage\Validation\MessageValidation as MessageValidation;
use ServerMessage\Storage\DB as DBStorage;

class ServerMessage
{
	private $_storage;
	
	private $_config;
	
	private $_message;
	
	private $_validation;
	
	/**
	 * Constructor.
	 * @param array $config Can overwrite default config upon creation
	 * If no storage class is provided, the config default will be used
	 * @param ServerMessage\Interfaces\Storage $storage
	 * @param ServerMessage\Interfaces\Validation $validation The validation class that validates the message, can be set externally
	 */
	public function __construct(Array $config = array(), StorageInterface $storage = null, ValidationInterface $validation = null)
	{
		$this->_config = new Config($config);
		
		if ( $storage == null )
		{
			$this->_storage = new DBStorage($this->_config->db);
		}
		else
		{
			$this->_storage = $storage;
		}
		
		$this->_message = new MessageEntity();
		
		if ( $validation == null )
		{
			$this->_validation = new MessageValidation();
		}
		else
		{
			$this->_validation = $validation;
		}
	}
	
	public function set_subject($subject = '')
	{
		$this->_message->subject = (string)$subject;
	}
	
	public function set_body($body = '')
	{
		$this->_message->body = (string)$body;
	}
	
	public function set_sender($sender_id = 0, $sender_type = '')
	{
		$this->_message->sender_id = (int)$sender_id;
		$this->_message->sender_type = (string)$sender_type;
	}
	
	public function set_reciever($reciever_id = 0, $reciever_type = '')
	{
		$this->_message->reciever_id = (int)$sender_id;
		$this->_message->reciever_type = (string)$sender_type;
	}
	
	/**
	 * After the parts of the message have been set, saves to storage
	 * If validation is set to true in config, validates before sending
	 * @return boolean
	 */
	public function send()
	{
		$valid = true;
		if ( $this->_config->validate_on_send )
		{
			$valid = $this->_validation->valid($this->_message);
		}
		
		if ( $valid )
		{
			try
			{
				$this->_storage->add($this->_message);
			}
			catch(Exception $e)
			{
				throw new MessageException('Could not save message.');
			}
			
			return true;
		}
		
		return false;
	}
}