<?php

class User extends DataObject {
	static $db = array(
		'Name' => 'Varchar(256)',
	);

	static $has_many = array(
		'Auctions' => 'Auction'
	);

	static $belongs_many_many = array(
		'MonitoredAuctions' => 'Auction'
	);
}

class User_Controller extends Controller {

}
