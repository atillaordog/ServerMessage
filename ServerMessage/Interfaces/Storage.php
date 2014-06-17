<?php

namespace ServerMessage\Interfaces;

use ServerMessage\Entity\Message as MessageEntity;

/**
 * We define an interface with the functionalities we need for storage
 */
interface Storage
{
	/**
	 * Generates the storage requirements for messages
	 * @return boolean
	 */
	public function create_storage();
	
	/**
	 * Add a message to the storage
	 * @param ServerMessage\Entity\Message $message
	 * @return int Unique id of the newly added message
	 */
	public function add(MessageEntity $message);
	
	/**
	 * Updates message(s) by provided field(s)
	 * @param ServerMessage\Entity\Message $message
	 * @param Array $fields The fields to update, all if left empty, mapping is get from the entity
	 * ex. array('body', 'subject', 'sender_type')
	 * @param Array $by_fields The name(s) of the field(s) we want to update by. Allows the update of multiple items at once.
	 * @return boolean
	 */
	public function update(MessageEntity $message, Array $fields, Array $by_fields);
	
	/**
	 * Deletes message(s) by given field(s)
	 * @param ServerMessage\Entity\Message $message
	 * @param Array $by_fields The name(s) of the field(s) we want to delete by. Allows the deletion of multiple items at once.
	 * @return boolean
	 */
	public function delete(MessageEntity $message, Array $by_fields);
	
	/**
	 * Gets message(s) by given field-value pairs
	 * @param Array $by_params An associative array with field-value pairs. Ex. array('sender_id' => 23, 'sender_type' => 'support')
	 * @param int $limit Limit for pagination purposes
	 * @param int $offset Offset for pagination purposes
	 * @return Array returns an array with the found results, every element being of type ServerMessage\Entity\Message
	 */
	public function get(Array $by_params, $limit, $offset);
	
	/**
	 * Gets the total number of message by given params
	 * @param Array $by_params An associative array with field-value pairs. Ex. array('sender_id' => 23, 'sender_type' => 'support')
	 */
	public function get_total(Array $by_params);
	
	/**
	 * Checks if the storage exists and can be used
	 * @return boolean
	 */
	public function exists();
	
	/**
	 * Destroys the created storage if needed
	 */
	public function destroy_storage();
}