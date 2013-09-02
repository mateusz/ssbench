<?php

class DatabaseGenerator extends BuildTask {

	public function truncate() {
		DB::query('DROP TABLE "SiteTree";');
		DB::query('DROP TABLE "ErrorPage";');
		DB::query('DROP TABLE "User";');
		DB::query('DROP TABLE "CarAuction";');
		DB::query('DROP TABLE "VehicleAuction";');
		DB::query('DROP TABLE "Auction";');
		DB::query('DROP TABLE "Bid";');
		DB::query('DROP TABLE "Comment";');
		DB::query('DROP TABLE "Auction_MonitoringUsers";');
		$admin = new DatabaseAdmin();
		$admin->build();
	}

	public function generateUsers($scale) {
		$faker = Faker\Factory::create();

		$iterations = pow(2, $scale)/4;
		for ($i = 0; $i<$iterations; $i++) {
			$user = new User();
			$user->Name = $faker->name;
			$user->write();

			echo '@';
			flush();
		}
	}

	public function generateAuctions($scale) {
		$faker = Faker\Factory::create();

		// Assume the IDs of users are continuous.
		$minUser = User::get()->sort('"ID"')->First()->ID;
		$maxUser = User::get()->sort('"ID" DESC')->First()->ID;

		// Produce 1/4th CarAuctions.
		$iterations = pow(2, $scale)/4;
		for ($i = 0; $i<$iterations; $i++) {
			$obj = new CarAuction();
			$obj->Title = $faker->sentence(3);
			$obj->Content = $faker->text(300);
			// Should this select from a pool?
			$obj->Brand = $faker->company;
			$obj->NumberOfDoors = rand(1,5);
			$obj->UserID = rand($minUser, $maxUser);

			$obj->write();
			$obj->doPublish();

			echo 'c';
			flush();
		}

		// Produce 1/4th VehicleAuctions.
		$iterations = pow(2, $scale)/4;
		for ($i = 0; $i<$iterations; $i++) {
			$obj = new VehicleAuction();
			$obj->Title = $faker->sentence(3);
			$obj->Content = $faker->text(300);
			// Should this select from a pool?
			$obj->Brand = $faker->company;
			$obj->UserID = rand($minUser, $maxUser);

			$obj->write();
			$obj->doPublish();

			echo 'v';
			flush();
		}

		// Produce 1/2th just Auctions.
		$iterations = pow(2, $scale)/2;
		for ($i = 0; $i<$iterations; $i++) {
			$obj = new Auction();
			$obj->Title = $faker->sentence(3);
			$obj->Content = $faker->text(300 + rand(-200,200));
			$obj->UserID = rand($minUser, $maxUser);

			$obj->write();
			$obj->doPublish();

			echo 'a';
			flush();
		}
	}

	public function generateBids($scale) {
		$faker = Faker\Factory::create();

		// Assume the IDs are continuous.
		$minAuction = Auction::get()->sort('"ID"')->First()->ID;
		$maxAuction = Auction::get()->sort('"ID" DESC')->First()->ID;
		$auctions = Auction::get();
		$countAuction = (int)$auctions->Count();

		for ($k = $minAuction; $k<=$maxAuction; $k++) {

			if ($k%8<1) {
				// 1/8ght - large auctions
				$iterationsBids = $scale*4;
				$iterationsComments = $scale*2;
			} else if ($k%8<7) {
				// 6/8ght - regular auctions
				$iterationsBids = $scale / 2;
				$iterationsComments = $scale / 4;
			} else {
				// 1/8ght - empty auctions.
				$iterationsBids = 0;
				$iterationsComments = 0;
			}

			for ($i = 0; $i<$iterationsBids; $i++) {
				$obj = new Bid();
				$obj->Price = rand(100,100000)/100;
				$obj->AuctionID = $k;

				$obj->write();

				echo 'b';
				flush();
			}

			for ($i = 0; $i<$iterationsComments; $i++) {
				$obj = new Comment();
				$obj->Message = $faker->paragraph(3);
				$obj->AuctionID = $k;

				$obj->write();

				echo '!';
				flush();
			}

		}
	}

	public function generateMonitors($scale) {
		// TODO add monitors
	}

	public function run($request) {

		if ($request->getVar('truncate')) {
			$this->truncate();
			exit;
		}

		increase_time_limit_to(600);
		increase_memory_limit_to('512M');

		$scale = (int)$request->getVar('scale');
		if (!$scale) $scale = 1;
		if ($scale>20) $scale = 20;

		$this->generateUsers($scale);
		$this->generateAuctions($scale);
		$this->generateBids($scale);
		$this->generateMonitors($scale);
	}

}

