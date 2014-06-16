<?php

namespace ServerMessage\Filters;

use ServerMessage\Interfaces\Filter as FilterInterface;
use ServerMessage\Entity\Message as MessageEntity;

class Email implements FilterInterface
{
	private $matches = array(
		'subject' => array(),
		'body' => array()
	);
	
	public function filter(MessageEntity $message, $subject_only = false, $delete_found = false)
	{
		$subject_matches = array();
		preg_match_all(
			"/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i",
			$message->subject,
			$subject_matches
		);
		
		$this->matches['subject'] = $subject_matches[0];
		
		$body_matches = array(array());
		if ( !$subject_only )
		{
			preg_match_all(
				"/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i",
				$message->body,
				$body_matches
			);
			
			$this->matches['body'] = $body_matches[0];
		}
		
		if ( $delete_found )
		{
			$message->subject = str_replace($subject_matches[0], '', $message->subject);
			$message->body = str_replace($body_matches[0], '', $message->body);
		}
		else
		{
			foreach( $subject_matches[0] as $match )
			{
				$message->subject = str_replace($match, '<span style="color:red;">'.$match.'</span>', $message->subject);
			}
			
			foreach( $body_matches[0] as $match )
			{
				$message->body = str_replace($match, '<span style="color:red;">'.$match.'</span>', $message->body);
			}
		}
		
		return $message;
	}
	
	public function get_found_matches()
	{
		return $this->matches;
	}
}