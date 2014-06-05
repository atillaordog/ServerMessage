<?php

namespace ServerMessage\Entity;

use ServerMessage\Entity\Base as BaseEntity;

/**
 * Simple data class holding everything a message has to have
 */
class Message extends BaseEntity
{
	public $id = null;
	public $created_on = '';
	public $updated_on = '';
	public $subject = '';
	public $body = '';
	public $sender_id = null;
	public $sender_type = '';
	public $reciever_id = null;
	public $reciever_type = '';
	public $status = 0;
	public $meta = '';
}