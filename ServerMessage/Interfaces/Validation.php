<?php

namespace ServerMessage\Interfaces;

use ServerMessage\Entity\Message as MessageEntity;

interface Validation
{
	/**
	 * Checks if the message is valid
	 * @param ServerMessage\Entity\Message $message
	 * @return boolean
	 */
	public function valid(MessageInterface $message);
	
	/**
	 * Returns the errors that got set upon validation
	 * @return array
	 */
	public function get_errors();
}