<?php defined('SYSPATH') or die('No direct script access.');
require_once Kohana::find_file('vendor', 'Mustache/Autoloader');
Mustache_Autoloader ::register();
class Beautiful_Mustache extends Mustache_Engine {}