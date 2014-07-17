<?php

use ServerMessage\Interfaces\Storage as StorageInterface;
use ServerMessage\Config as Config;
use ServerMessage\MessageException as MessageException;
use ServerMessage\Entity\Message as MessageEntity;
use ServerMessage\Interfaces\Validation as ValidationInterface;
use ServerMessage\Validation\MessageValidation as MessageValidation;
use ServerMessage\Storage\DB as DBStorage;
use ServerMessage\Filters\Email as EmailFilter;
use ServerMessage\Filters\Url as UrlFilter;

class ServerMessage
{
	private $_storage;
	
	private $_config;
	
	private $_message;
	
	private $_validation;
	
	private $_predef_filters = array();
	
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
		
		$this->_predef_filters['email'] = new EmailFilter();
		$this->_predef_filters['url'] = new UrlFilter();
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
		$this->_message->reciever_id = (int)$reciever_id;
		$this->_message->reciever_type = (string)$reciever_type;
	}
	
	public function set_meta($meta)
	{
		// We encode anything into a string that comes in meta
		$this->_message->meta = base64_encode( serialize($meta) );
	}
	
	/**
	 * Set the status of one or more messages
	 * @param array|int $id
	 * @param int $status Must be one from the predefined statuses
	 * @return boolean
	 */
	public function change_status($id, $status)
	{
		if ( !array_key_exists($status, $this->_config->statuses) )
		{
			throw new MessageException('Status has to be from the predefined values.');
		}
		
		if ( !is_array($id) )
		{
			$id = array($id);
		}
		
		$this->_message->id = $id;
		$this->_message->status = $status;
		
		return $this->_storage->update($this->_message, array('status'), array('id'));
	}
	
	/**
	 * Set one or more messages to read or unread
	 * @param array|int $id
	 * @param boolean $read True or false
	 * @return boolean
	 */
	public function set_read($id, $read)
	{	
		if ( !is_array($id) )
		{
			$id = array($id);
		}
		
		$this->_message->id = $id;
		$this->_message->read = ((boolean)$read)? 1 : 0;
		
		return $this->_storage->update($this->_message, array('read'), array('id'));
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
			$this->_message->created_on = date('Y-m-d H:i:s');
			
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
	
	/**
	 * Updates the loaded message in the storage
	 * It will validate the message if set in config
	 * @return boolean
	 */
	public function update()
	{
		$valid = true;
		if ( $this->_config->validate_on_send )
		{
			$valid = $this->_validation->valid($this->_message);
		}
		
		if ( $valid )
		{
			$this->_message->updated_on = date('Y-m-d H:i:s');
			
			try
			{
				$this->_storage->update($this->_message, array('subject', 'body', 'meta'), array('id'));
			}
			catch(Exception $e)
			{
				throw new MessageException('Could not save message.');
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * If sending was not successful, we get the validation errors
	 * @return array
	 */
	public function get_validation_errors()
	{
		return $this->_validation->get_errors();
	}
	
	/**
	 * Gets the inbox of a given object
	 * @param int $obj_id The id of the reciever
	 * @param string $obj_type The type of the reciever
	 * @param int $status Optional, taken in consideration if not null
	 * @param int $limit
	 * @param int $offset
	 * @return array Returns an array with message objects
	 */
	public function get_inbox($obj_id, $obj_type, $status = null, $limit = null, $offset = null)
	{
		$data = array(
			'reciever_id' => (int)$obj_id, 
			'reciever_type' => (string)$obj_type
		);
		
		if ( $status !== null )
		{
			$data['status'] = (int)$status;
		}
		
		$result = $this->_storage->get($data, $limit, $offset);
		
		for ( $i = 0, $m = count($result); $i < $m; $i++ )
		{
			$result[$i]->meta = unserialize( base64_decode($result[$i]->meta) );
		}
		
		return $result;
	}
	
	/**
	 * Gets the number of messages in inbox
	 * @param int $obj_id The id of the reciever
	 * @param string $obj_type The type of the reciever
	 * @param int $status (Optional) Status filter
	 * @param int $read (Optional) Read filter
	 * @return int
	 */
	public function get_total_inbox($obj_id, $obj_type, $status = null, $read = null)
	{
		$data = array(
			'reciever_id' => (int)$obj_id, 
			'reciever_type' => (string)$obj_type
		);
		
		if ( $read !== null )
		{
			$data['read'] = (int)$read;
		}
		
		if ( $status !== null )
		{
			$data['status'] = (int)$status;
		}
		
		return $this->_storage->get_total($data);
	}
	
	/**
	 * Gets the outbox of a given object
	 * @param int $obj_id The id of the sender
	 * @param string $obj_type The type of the sender
	 * @param int $status Optional, taken in consideration if not null
	 * @param int $limit
	 * @param int $offset
	 * @return array Returns an array with message objects
	 */
	public function get_outbox($obj_id, $obj_type, $status = null, $limit = null, $offset = null)
	{
		$data = array(
			'sender_id' => (int)$obj_id, 
			'sender_type' => (string)$obj_type
		);
		
		if ( $status !== null )
		{
			$data['status'] = (int)$status;
		}
		
		$result = $this->_storage->get($data, $limit, $offset);
		
		for ( $i = 0, $m = count($result); $i < $m; $i++ )
		{
			$result[$i]->meta = unserialize( base64_decode($result[$i]->meta) );
		}
		
		return $result;
	}
	
	/**
	 * Gets the number of unapproved messages
	 * @param int $obj_id The id of the reciever
	 * @param string $obj_type The type of the reciever
	 * @param int $status (Optional) Status filter
	 * @return int
	 */
	public function get_total_outbox($obj_id, $obj_type, $status = null)
	{
		$data = array(
			'sender_id' => (int)$obj_id, 
			'sender_type' => (string)$obj_type
		);
		
		if ( $status !== null )
		{
			$data['status'] = (int)$status;
		}
		
		return $this->_storage->get_total($data);
	}
	
	/**
	 * Get all the messages, pagination dependent
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function get_all($limit = null, $offset = null)
	{
		$result = $this->_storage->get(array(), $limit, $offset, array('created_on' => 'DESC'));
		
		for ( $i = 0, $m = count($result); $i < $m; $i++ )
		{
			$result[$i]->meta = unserialize( base64_decode($result[$i]->meta) );
		}
		
		return $result;
	}
	
	/**
	 * Get the total numebr of messages
	 */
	public function get_total_messages()
	{
		return $this->_storage->get_total(array());
	}
	
	/**
	 * Get a single message by ID or the current message set
	 * @param int $message_id If null, returns current message
	 * @return ServerMessage\Entity\Message
	 */
	public function get_single($message_id = null)
	{
		if ( $message_id != null )
		{
			$result = $this->_storage->get(array('id' => (int)$message_id), 1);
		
			if ( !empty($result) )
			{
				$this->_message = $result[0];
				$this->_message->meta = unserialize( base64_decode($this->_message->meta) );
			}
		}
		
		return $this->_message;
	}
	
	/**
	 * Deletes one or more messages
	 * @param array|int $message_id
	 * @return boolean
	 */
	public function delete_message($message_id)
	{
		if ( !is_array($message_id) )
		{
			$message_id = array($message_id);
		}
		
		$this->_message->id = $message_id;
		
		return $this->_storage->delete($this->_message, $message_id);
	}
	
	/**
	 * Checks if storage exists and can be used
	 * @return boolean
	 */
	public function storage_exists()
	{
		return $this->_storage->exists();
	}
	
	/**
	 * Creates storage if that does not exist
	 */
	public function install_storage()
	{
		if ( !$this->_storage->exists() )
		{
			$this->_storage->create_storage();
		}
	}
	
	/**
	 * Clear out the storage created for messages
	 */
	public function remove_storage()
	{
		$this->_storage->destroy_storage();
	}
	
	/**
	 * Filters the given message
	 * @param ServerMessage\Entity\Message $message Optional, if not set, the inner message will be used
	 * @param array $filters Optional, can be used to add filters (Ex. array('facebook' => new FacebookFilter()))
	 * The filters need to extend the filter interface
	 * @param boolean $subject_only Filter only the subject
	 * @param boolean $delete_found Remove the found matches from the message
	 * @param boolean $save If true, saves the message back to storage (used when deleting threads)
	 * @return array Returns an associative array with the filtered message and the found matches
	 */
	public function filter_message(MessageEntity $message = null, Array $filters = array(), $subject_only = false, $delete_found = false, $save = false)
	{
		if ( $message == null )
		{
			$message = $this->_message;
		}
		
		$total_threats = 0;
		$found_matches = array();
		foreach ( $this->_predef_filters as $key => $filter )
		{
			$message = $filter->filter($message, (boolean)$subject_only, (boolean)$delete_found);
			$found_matches[$key] = $filter->get_found_matches();
			$total_threats += $filter->total_matches();
		}
		
		foreach ( $filters as $key => $filter )
		{
			if ( is_a($filter, 'ServerMessage\Interfaces\Filter') )
			{
				$message = $filter->filter($message, (boolean)$subject_only, (boolean)$delete_found);
				$found_matches[$key] = $filter->get_found_matches();
				$total_threats += $filter->total_matches();
			}
		}
		
		if ( (boolean)$save )
		{
			$this->_storage->update($message, array('subject', 'body'), array('id'));
		}
		
		return array(
			'message' => $message,
			'found_matches' => $found_matches,
			'total_threats' => $total_threats
		);
	}
	
	public function get_statuses()
	{
		return $this->_config->statuses;
	}
	
	/**
	 * We need to reset our message entity sometimes, so we do that with this function
	 */
	public function reset_inner_message()
	{
		$this->_message = new MessageEntity();
	}
}