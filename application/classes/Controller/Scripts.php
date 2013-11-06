<?php defined('SYSPATH') or die('No direct script access.'); 
/**
 * Controller Scripts has actions to run against
 * db to fix errors
 * 
 * @version 01 - Joseph Bosire
 *
 * PHP version 5
 */	  
class Controller_Scripts extends Controller
{
	/**
	 * Function to run all script functions	
	 */
	public function action_run_all() {
		echo "Running All Script Functions";
		$this->action_clean_personnel();
		$this->action_clean_clients();
		echo "All Scripts have been run successfully";
	}

	/**
	 * Function to clean up the personnel avatar field in personnel table
	 * Makes sure whether an avatar has not been given the field is left black	
	 */
	public function action_clean_personnel() {
		$personnel = ORM::factory("Personnel")->find_all();
		foreach ($personnel as $person) {
			if($person->personnel_avatar == ""||$person->personnel_avatar == "avatar-default.jpg" || $person->personnel_avatar == "default.jpg")
				$person->personnel_avatar = NULL;
				$person->save();
		}
		echo "<br/>Personnel_Avatar field in Personnel Table has been cleaned up.";
	}

	/**
	 * Function to clean up the client avatar field in client table
	 * Makes sure whether an avatar has not been given the field is left black
	 */
	public function action_clean_clients() {
		$clients = ORM::factory("Client")->find_all();
		foreach ($clients as $client) {
			if($client->client_avatar == "avatar-default.jpg" || $client->client_avatar == "default.jpg")
				$client->client_avatar = "";
				$client->save();
		}
		echo "<br/>Client_Avatar field in Client Table has been cleaned up.";
	}
	
  
}

	
	