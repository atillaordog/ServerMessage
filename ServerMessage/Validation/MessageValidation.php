<?php

namespace ServerMessage\Validation;

use ServerMessage\Interfaces\Validation as ValidationInterface;
use ServerMessage\Entity\Message as MessageEntity;

class MessageValidation implements ValidationInterface
{
	private $_errors = array();
	
	public function valid(MessageEntity $message)
	{
		$is_good = true;
		
		// We check the fields to be valid
		if ( $message->subject == '' )
		{
			$this->_errors['subject'] = 'Subject cannot be empty.';
			$is_good = false;
		}
		
		if ( $message->body == '' )
		{
			$this->_errors['body'] = 'Body cannot be empty.';
			$is_good = false;
		}
		
		if ( $message->sender_id == null )
		{
			$this->_errors['sender_id'] = 'Sender ID has to be set.';
			$is_good = false;
		}
		
		if ( $message->sender_type == '' )
		{
			$this->_errors['sender_type'] = 'Sender type has to be set.';
			$is_good = false;
		}
		
		if ( $message->reciever_id == null )
		{
			$this->_errors['reciever_id'] = 'Reciever ID has to be set.';
			$is_good = false;
		}
		
		if ( $message->reciever_type == '' )
		{
			$this->_errors['reciever_type'] = 'reciever type has to be set.';
			$is_good = false;
		}
		
		return $is_good;
	}
	
	public function get_errors()
	{
		return $this->_errors;
	}
}