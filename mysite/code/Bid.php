<?php

class Bid extends DataObject {
	static $db = array(
		'Price' => 'Float'
	);

	static $has_one = array(
		'Auction' => 'Auction'
	);
}

class Bid_Controller extends Controller {

}
