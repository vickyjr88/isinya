<?php defined('SYSPATH') OR die('No direct script access.'); ?>

2013-10-17 07:56:27 --- EMERGENCY: ErrorException [ 8 ]: Trying to get property of non-object ~ APPPATH/classes/Model/Message.php [ 100 ] in /var/www/epro/application/classes/Model/Message.php:100
2013-10-17 07:56:27 --- DEBUG: #0 /var/www/epro/application/classes/Model/Message.php(100): Kohana_Core::error_handler(8, 'Trying to get p...', '/var/www/epro/a...', 100, Array)
#1 /var/www/epro/application/classes/Controller/Chat.php(149): Model_Message->get_inbox_contacts('1')
#2 /var/www/epro/system/classes/Kohana/Controller.php(84): Controller_Chat->action_conversations()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Chat))
#5 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/epro/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/epro/application/classes/Model/Message.php:100
2013-10-17 08:09:04 --- EMERGENCY: ErrorException [ 8 ]: Trying to get property of non-object ~ APPPATH/classes/Model/Message.php [ 102 ] in /var/www/epro/application/classes/Model/Message.php:102
2013-10-17 08:09:04 --- DEBUG: #0 /var/www/epro/application/classes/Model/Message.php(102): Kohana_Core::error_handler(8, 'Trying to get p...', '/var/www/epro/a...', 102, Array)
#1 /var/www/epro/application/classes/Controller/Chat.php(149): Model_Message->get_inbox_contacts('1')
#2 /var/www/epro/system/classes/Kohana/Controller.php(84): Controller_Chat->action_conversations()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Chat))
#5 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/epro/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/epro/application/classes/Model/Message.php:102
2013-10-17 08:21:53 --- EMERGENCY: ErrorException [ 8 ]: Trying to get property of non-object ~ APPPATH/classes/Controller/Chat.php [ 149 ] in /var/www/epro/application/classes/Controller/Chat.php:149
2013-10-17 08:21:53 --- DEBUG: #0 /var/www/epro/application/classes/Controller/Chat.php(149): Kohana_Core::error_handler(8, 'Trying to get p...', '/var/www/epro/a...', 149, Array)
#1 /var/www/epro/system/classes/Kohana/Controller.php(84): Controller_Chat->action_conversations()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/epro/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Chat))
#4 /var/www/epro/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/epro/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/epro/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/epro/application/classes/Controller/Chat.php:149