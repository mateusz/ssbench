<?php

class Comment extends DataObject {
	static $db = array(
		'Message' => 'Text'
	);

	static $has_one = array(
		'Auction' => 'Auction'
	);
}

class Comment_Controller extends Controller {

}
