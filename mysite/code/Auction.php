<?php

class Auction extends SiteTree {
	static $db = array(
	);

	static $has_one = array(
		'User' => 'User'
	);

	static $has_many = array(
		'Comments' => 'Comment',
		'Bids' => 'Bid'
	);

	static $many_many = array(
		'MonitoringUsers' => 'User'
	);

}

class Auction_Controller extends ContentController {

}
