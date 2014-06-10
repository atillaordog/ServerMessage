<?php

namespace ServerMessage\Filters;

use ServerMessage\Interfaces\Filter as FilterInterface;

class Url extends FilterInterface
{
	private $matches = array(
		'subject' => array(),
		'body' => array()
	);
	
	public function filter(ServerMessage $message, $subject_only = false, $delete_found = false)
	{
		$subject_matches = array();
		preg_match_all(
			'/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i',
			$message->subject,
			$subject_matches
		);
		
		$this->matches['subject'] = $subject_matches[0];
		
		$body_matches = array(array());
		if ( !$subject_only )
		{
			preg_match_all(
				'/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i',
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