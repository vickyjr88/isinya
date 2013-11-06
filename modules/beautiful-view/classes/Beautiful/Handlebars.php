<?php defined('SYSPATH') or die('No direct script access.');
require_once Kohana::find_file('vendor', 'Handlebars/Autoloader');
Handlebars_Autoloader ::register();
class Beautiful_Handlebars extends Handlebars_Engine {}