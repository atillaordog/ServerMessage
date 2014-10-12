<?php
/**
 * Test file for demonstrating the workings of this system
 */
include('autoload.php');

// Instantiate the class using explicit values
$message = new ServerMessage(
	array(
        'db' => array(
            'server' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'database' => 'test',
            'table_name' => 'messages'
        )
    )
);

// Check if storage exists and create it if does not
if ( !$message->storage_exists() )
{
    $message->install_storage();
}

// Create message 
$message->set_subject('Testing message abc@bcd.com');
$message->set_body('Body of the message, also containing filterable things like http://www.filterurl.dev or abcd@efgh.dev');
$message->set_sender(1, 'user');
$message->set_reciever(1, 'support');
$message->set_meta(array('meta_key' => 'meta_value'));
$message->send();

// Now the message is also set in the inner container, thus we can change one thing and send the message again.
$message->set_reciever(2, 'support');
$message->send();

// You can also update the message
$message->set_subject('Changed');
$message->update();

// To reset inner message, you can call
$message->reset_inner_message();

// For the full list of things this class can do, see documentation
