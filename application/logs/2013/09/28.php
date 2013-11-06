<?php defined('SYSPATH') OR die('No direct script access.'); ?>

2013-09-28 05:39:48 --- EMERGENCY: Kohana_Exception [ 0 ]: The peronnel_avatar property does not exist in the Model_Personnel class ~ MODPATH/orm/classes/Kohana/ORM.php [ 757 ] in /var/www/epro/application/classes/Model/Base.php:18
2013-09-28 05:39:48 --- DEBUG: #0 /var/www/epro/application/classes/Model/Base.php(18): Kohana_ORM->set('peronnel_avatar', '')
#1 /var/www/epro/modules/orm/classes/Kohana/ORM.php(699): Model_Base->set('peronnel_avatar', '')
#2 /var/www/epro/application/classes/Controller/Scripts.php(21): Kohana_ORM->__set('peronnel_avatar', '')
#3 /var/www/epro/system/classes/Kohana/Controller.php(84): Controller_Scripts->action_clean_personnel()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Scripts))
#6 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/epro/index.php(118): Kohana_Request->execute()
#9 {main} in /var/www/epro/application/classes/Model/Base.php:18
2013-09-28 05:40:23 --- DEBUG: Required permission does not exist: PERSONNEL_VIEW in /var/www/epro/application/classes/Controller/App.php:1006
2013-09-28 05:51:00 --- EMERGENCY: ErrorException [ 4 ]: syntax error, unexpected '$this' (T_VARIABLE), expecting ',' or ';' ~ APPPATH/classes/Controller/Scripts.php [ 17 ] in :
2013-09-28 05:51:00 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-09-28 05:51:18 --- EMERGENCY: ErrorException [ 4 ]: syntax error, unexpected 'this' (T_STRING), expecting ',' or ';' ~ APPPATH/classes/Controller/Scripts.php [ 17 ] in :
2013-09-28 05:51:18 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-09-28 05:52:20 --- EMERGENCY: Kohana_Exception [ 0 ]: The client_avatar property does not exist in the Model_Client class ~ MODPATH/orm/classes/Kohana/ORM.php [ 684 ] in /var/www/epro/application/classes/Model/Base.php:10
2013-09-28 05:52:20 --- DEBUG: #0 /var/www/epro/application/classes/Model/Base.php(10): Kohana_ORM->get('client_avatar')
#1 /var/www/epro/application/classes/Controller/Scripts.php(43): Model_Base->__get('client_avatar')
#2 /var/www/epro/application/classes/Controller/Scripts.php(18): Controller_Scripts->action_clean_clients()
#3 /var/www/epro/system/classes/Kohana/Controller.php(84): Controller_Scripts->action_run_all()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Scripts))
#6 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/epro/index.php(118): Kohana_Request->execute()
#9 {main} in /var/www/epro/application/classes/Model/Base.php:10
2013-09-28 05:52:22 --- EMERGENCY: Kohana_Exception [ 0 ]: The client_avatar property does not exist in the Model_Client class ~ MODPATH/orm/classes/Kohana/ORM.php [ 684 ] in /var/www/epro/application/classes/Model/Base.php:10
2013-09-28 05:52:22 --- DEBUG: #0 /var/www/epro/application/classes/Model/Base.php(10): Kohana_ORM->get('client_avatar')
#1 /var/www/epro/application/classes/Controller/Scripts.php(43): Model_Base->__get('client_avatar')
#2 /var/www/epro/application/classes/Controller/Scripts.php(18): Controller_Scripts->action_clean_clients()
#3 /var/www/epro/system/classes/Kohana/Controller.php(84): Controller_Scripts->action_run_all()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Scripts))
#6 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/epro/index.php(118): Kohana_Request->execute()
#9 {main} in /var/www/epro/application/classes/Model/Base.php:10
2013-09-28 06:03:24 --- DEBUG: Required permission does not exist: PERSONNEL_VIEW in /var/www/epro/application/classes/Controller/App.php:1006
2013-09-28 06:24:32 --- EMERGENCY: LogicException [ 0 ]: Unexpected closing tag: /if ~ MODPATH/beautiful-view/vendor/Handlebars/Parser.php [ 72 ] in /var/www/epro/modules/beautiful-view/vendor/Handlebars/Parser.php:42
2013-09-28 06:24:32 --- DEBUG: #0 /var/www/epro/modules/beautiful-view/vendor/Handlebars/Parser.php(42): Handlebars_Parser->_buildTree(Object(ArrayIterator))
#1 /var/www/epro/modules/beautiful-view/vendor/Handlebars/Engine.php(484): Handlebars_Parser->parse(Array)
#2 /var/www/epro/modules/beautiful-view/vendor/Handlebars/Engine.php(410): Handlebars_Engine->_tokenize(Object(Handlebars_String))
#3 /var/www/epro/modules/beautiful-view/classes/Template/Handlebars.php(78): Handlebars_Engine->loadTemplate('<div class="mod...')
#4 /var/www/epro/modules/beautiful-view/classes/Beautiful/View.php(291): Template_Handlebars->render(Object(View_SiteLayout))
#5 /var/www/epro/modules/beautiful-view/classes/Beautiful/View.php(207): Beautiful_View->render()
#6 /var/www/epro/system/classes/Kohana/Response.php(160): Beautiful_View->__toString()
#7 /var/www/epro/application/classes/Controller/App.php(1311): Kohana_Response->body(Object(View))
#8 /var/www/epro/application/classes/Controller/Site.php(135): Controller_App->after()
#9 /var/www/epro/system/classes/Kohana/Controller.php(87): Controller_Site->after()
#10 [internal function]: Kohana_Controller->execute()
#11 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Personnel))
#12 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#13 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#14 /var/www/epro/index.php(118): Kohana_Request->execute()
#15 {main} in /var/www/epro/modules/beautiful-view/vendor/Handlebars/Parser.php:42
2013-09-28 06:29:10 --- EMERGENCY: ORM_Validation_Exception [ 0 ]: Failed to validate array ~ MODPATH/orm/classes/Kohana/ORM.php [ 1272 ] in /var/www/epro/modules/orm/classes/Kohana/ORM.php:1299
2013-09-28 06:29:10 --- DEBUG: #0 /var/www/epro/modules/orm/classes/Kohana/ORM.php(1299): Kohana_ORM->check(NULL)
#1 /var/www/epro/modules/orm/classes/Kohana/ORM.php(1418): Kohana_ORM->create(NULL)
#2 /var/www/epro/application/classes/Controller/Personnel.php(86): Kohana_ORM->save()
#3 /var/www/epro/system/classes/Kohana/Controller.php(84): Controller_Personnel->action_edit()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Personnel))
#6 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/epro/index.php(118): Kohana_Request->execute()
#9 {main} in /var/www/epro/modules/orm/classes/Kohana/ORM.php:1299
2013-09-28 07:16:59 --- EMERGENCY: LogicException [ 0 ]: Unexpected closing tag: /if ~ MODPATH/beautiful-view/vendor/Handlebars/Parser.php [ 72 ] in /var/www/epro/modules/beautiful-view/vendor/Handlebars/Parser.php:42
2013-09-28 07:16:59 --- DEBUG: #0 /var/www/epro/modules/beautiful-view/vendor/Handlebars/Parser.php(42): Handlebars_Parser->_buildTree(Object(ArrayIterator))
#1 /var/www/epro/modules/beautiful-view/vendor/Handlebars/Engine.php(484): Handlebars_Parser->parse(Array)
#2 /var/www/epro/modules/beautiful-view/vendor/Handlebars/Engine.php(427): Handlebars_Engine->_tokenize(Object(Handlebars_String))
#3 /var/www/epro/modules/beautiful-view/vendor/Handlebars/Template.php(316): Handlebars_Engine->loadPartial('frontpage-heade...')
#4 /var/www/epro/modules/beautiful-view/vendor/Handlebars/Template.php(163): Handlebars_Template->_partial(Object(Handlebars_Context), Array)
#5 /var/www/epro/modules/beautiful-view/classes/Template/Handlebars.php(78): Handlebars_Template->render(Object(View_SiteLayout))
#6 /var/www/epro/modules/beautiful-view/classes/Beautiful/View.php(291): Template_Handlebars->render(Object(View_SiteLayout))
#7 /var/www/epro/modules/beautiful-view/classes/Beautiful/View.php(207): Beautiful_View->render()
#8 /var/www/epro/system/classes/Kohana/Response.php(160): Beautiful_View->__toString()
#9 /var/www/epro/application/classes/Controller/App.php(1345): Kohana_Response->body(Object(View))
#10 /var/www/epro/application/classes/Controller/Site.php(135): Controller_App->after()
#11 /var/www/epro/system/classes/Kohana/Controller.php(87): Controller_Site->after()
#12 [internal function]: Kohana_Controller->execute()
#13 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_FrontPage))
#14 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#15 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#16 /var/www/epro/index.php(118): Kohana_Request->execute()
#17 {main} in /var/www/epro/modules/beautiful-view/vendor/Handlebars/Parser.php:42
2013-09-28 07:19:07 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'client_info.user_id' in 'on clause' [ SELECT `personnel_info`.`personnel_id` AS `personnel_info:personnel_id`, `personnel_info`.`user_id` AS `personnel_info:user_id`, `personnel_info`.`personnel_name` AS `personnel_info:personnel_name`, `personnel_info`.`personnel_status` AS `personnel_info:personnel_status`, `personnel_info`.`personnel_title` AS `personnel_info:personnel_title`, `personnel_info`.`personnel_active` AS `personnel_info:personnel_active`, `personnel_info`.`personnel_telephone` AS `personnel_info:personnel_telephone`, `personnel_info`.`personnel_email_address` AS `personnel_info:personnel_email_address`, `personnel_info`.`personnel_avatar` AS `personnel_info:personnel_avatar`, `personnel_info`.`delete_status` AS `personnel_info:delete_status`, `client_info`.`client_id` AS `client_info:client_id`, `client_info`.`client_name` AS `client_info:client_name`, `client_info`.`client_contact_person` AS `client_info:client_contact_person`, `client_info`.`client_telephone` AS `client_info:client_telephone`, `client_info`.`client_email_address` AS `client_info:client_email_address`, `client_info`.`client_postal_address` AS `client_info:client_postal_address`, `client_info`.`avatar` AS `client_info:avatar`, `client_info`.`delete_status` AS `client_info:delete_status`, `supplier_info`.`supplier_id` AS `supplier_info:supplier_id`, `supplier_info`.`supplier_code` AS `supplier_info:supplier_code`, `supplier_info`.`supplier_name` AS `supplier_info:supplier_name`, `supplier_info`.`supplier_contact_person` AS `supplier_info:supplier_contact_person`, `supplier_info`.`supplier_contact_title` AS `supplier_info:supplier_contact_title`, `supplier_info`.`supplier_cellphone` AS `supplier_info:supplier_cellphone`, `supplier_info`.`supplier_business_phone` AS `supplier_info:supplier_business_phone`, `supplier_info`.`supplier_business_phone_ext` AS `supplier_info:supplier_business_phone_ext`, `supplier_info`.`supplier_sales_email` AS `supplier_info:supplier_sales_email`, `supplier_info`.`supplier_order_email` AS `supplier_info:supplier_order_email`, `supplier_info`.`supplier_postal_code` AS `supplier_info:supplier_postal_code`, `supplier_info`.`supplier_city` AS `supplier_info:supplier_city`, `supplier_info`.`supplier_state_province` AS `supplier_info:supplier_state_province`, `supplier_info`.`supplier_street_address` AS `supplier_info:supplier_street_address`, `supplier_info`.`supplier_description` AS `supplier_info:supplier_description`, `supplier_info`.`supplier_website` AS `supplier_info:supplier_website`, `supplier_info`.`supplier_user_id` AS `supplier_info:supplier_user_id`, `supplier_info`.`delete_status` AS `supplier_info:delete_status`, `user`.`id` AS `id`, `user`.`email` AS `email`, `user`.`username` AS `username`, `user`.`password` AS `password`, `user`.`logins` AS `logins`, `user`.`last_login` AS `last_login`, `user`.`delete_status` AS `delete_status` FROM `users` AS `user` LEFT JOIN `fpro_personnel` AS `personnel_info` ON (`user`.`id` = `personnel_info`.`user_id`) LEFT JOIN `fpro_clients` AS `client_info` ON (`user`.`id` = `client_info`.`user_id`) LEFT JOIN `fpro_suppliers` AS `supplier_info` ON (`user`.`id` = `supplier_info`.`user_id`) WHERE `username` = 'admin' LIMIT 1 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/epro/modules/database/classes/Kohana/Database/Query.php:251
2013-09-28 07:19:07 --- DEBUG: #0 /var/www/epro/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `personn...', false, Array)
#1 /var/www/epro/modules/orm/classes/Kohana/ORM.php(1069): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/epro/modules/orm/classes/Kohana/ORM.php(976): Kohana_ORM->_load_result(false)
#3 /var/www/epro/modules/orm/classes/Kohana/Auth/ORM.php(76): Kohana_ORM->find()
#4 /var/www/epro/modules/auth/classes/Kohana/Auth.php(92): Kohana_Auth_ORM->_login('admin', 'admin', NULL)
#5 /var/www/epro/application/classes/Controller/User.php(29): Kohana_Auth->login('admin', 'admin', NULL)
#6 /var/www/epro/system/classes/Kohana/Controller.php(84): Controller_User->action_login()
#7 [internal function]: Kohana_Controller->execute()
#8 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_User))
#9 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#10 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#11 /var/www/epro/index.php(118): Kohana_Request->execute()
#12 {main} in /var/www/epro/modules/database/classes/Kohana/Database/Query.php:251
2013-09-28 07:50:26 --- DEBUG: Required permission does not exist: PERSONNEL_VIEW in /var/www/epro/application/classes/Controller/App.php:1040