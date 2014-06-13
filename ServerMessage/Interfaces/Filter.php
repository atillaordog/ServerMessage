<?php

namespace ServerMessage\Interfaces;

use ServerMessage\Entity\Message as MessageEntity;

interface Filter
{
	/**
	 * Filters the passed message and returns it
	 * @param ServerMessage\Entity\Message $message
	 * @param boolean $subject_only Filters only the subject
	 * @param boolean $delete_found If false, just highlights the found strings, otherwise removes them
	 * @return ServerMessage\Entity\Message
	 */
	public function filter(MessageEntity $message, $subject_only, $delete_found);
	
	/**
	 * After the filtering has been done, returns the found matches
	 */
	public function get_found_matches();
}