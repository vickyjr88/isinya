<?php

defined('SYSPATH') or die('No direct script access.');
$session = Session::instance();
$template_name = Kohana::$config->load('settings.template') ? Kohana::$config->load('settings.template') : 'default'; //$session->get('sys_template_name');
$template_component = $session->get('sys_template_component', 'site');
$template_path = 'templates/' . $template_name . '/' . $template_component;
$template_path = file_exists(DOCROOT . $template_path) ? $template_path . '/' : '';
$browser_info = Request::user_agent(array('browser', 'version'));
return array(
    // add component-specific configs here e.g. theming etc
    'component_configs' => array(
        'site' => array(
            'content_blocks_folder' => 'contentblocks',
            'theme' => 'default',
            'themes' => array(
                'default' => array('css/style.css', 'css/bootstrap-theme.min.css')
            ),
        )
    ),
    // Begin css & js asset mapping
    'assets' => array(
        'site' => array(// component name
            'site' => array(//controller name
                'groups' => array(// signifies the start of asset group definition
                    'globalcss' => array(// the globalcss key MUST be in the main level2/component controller
                        array('CSS', $template_path . 'css/bootstrap.min.css', array(
                                'media' => 'screen',
                            )),
//                        array('CSS', $template_path . 'css/bootstrap-responsive.min.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/font-awesome.min.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/jquery-ui-1.10.3.custom.min.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/chat.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/jquery.dataTables.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/bootstrap-modal.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/datepicker.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/jquery.gritter.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/select2.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/bootstrap-editable.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/global.css', array(
//                                'media' => 'screen',
//                            )),
                    ),
                    'headglobaljs' => array(
                        array('JS', $template_path . 'js/jquery-2.0.3.min.js'),
//                        array('JS', $template_path . 'js/handlebars.js'),
//                        array('JS', $template_path . 'js/handlebars-server-helpers.js'),
//                        array('JS', $template_path . 'js/jquery.loadFromTemplate.min.js'),
//                        // debug tools, delete in production
//                        array('JS', $template_path . 'js/jquery.mockjax.js'),
                    ),
                    'globaljs' => array(
                        // global theme js files
                        array('JS', $template_path . 'js/bootstrap.min.js'),
//                        array('JS', $template_path . 'js/jquery-ui-1.10.3.custom.min.js'),
//                        array('JS', $template_path . 'js/jquery.ui.touch-punch.min.js'),
//                        //array('JS', $template_path . 'js/flot/jquery.flot.min.js'),
//                        //array('JS', $template_path . 'js/flot/jquery.flot.pie.min.js'),
//                        array('JS', $template_path . 'js/date-time/bootstrap-datepicker.min.js'),
//                        array('JS', $template_path . 'js/date-time/moment.min.js'),
                       
                        // insert 3rd party js files here
//                        array('JS', $template_path . 'js/bootstrap-modalmanager.js'),
//                        array('JS', $template_path . 'js/bootstrap-modal.js'),
//                        array('JS', $template_path . 'js/bootstrap-tab.js'),
//                        array('JS', $template_path . 'js/jquery.ui.widget.js'),
//                        array('JS', $template_path . 'js/jquery.iframe-transport.js'),
//                        array('JS', $template_path . 'js/jquery.fileupload.js'),
//                        array('JS', $template_path . 'js/jquery.form.js'),
//                        array('JS', $template_path . 'js/jquery.cookie.js'),
                      
                        // in-house js files, order matters here
                       // array('JS', $template_path . 'js/global.js'), // save global util functions here
                     
                      //  array('JS', $template_path . 'js/ui.js'), // ui concept code
                      
                    ),
                )
            ), // end controller
//            'dashboard' => array(
//                'groups' => array(
//                    'indexcss' => array(
//                        array('CSS', $template_path . 'css/jquery.handsontable.full.css', array(
//                                'media' => 'screen',
//                            ))
//                    ),
//                    'indexjs' => array(
//                        array('JS', $template_path . 'js/jquery.handsontable.full.js'),
//                        array('JS', $template_path . 'js/jquery.colorbox-min.js')
//                    ),
//                )
//            ),
//            'supplies' => array(
//                'groups' => array(
//                    'indexcss' => array(
//                        array('CSS', $template_path . 'css/jquery.handsontable.full.css', array(
//                                'media' => 'screen',
//                            ))
//                    ),
//                    'indexjs' => array(
//                        array('JS', $template_path . 'js/jquery.handsontable.full.js'),
//                        array('JS', $template_path . 'js/jquery.colorbox-min.js')
//                    ),
//                )
//            ),
//            'jobs' => array(
//                'groups' => array(
//                    'clientscss' => array(
//                        array('CSS', $template_path . 'css/fullcalendar.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/jquery.contextMenu.css', array(
//                                'media' => 'screen',
//                            )),
//                        array('CSS', $template_path . 'css/gantt.css', array(
//                                'media' => 'screen',
//                            )),
//                    ),
//                    'clientsjs' => array(
//                        array('JS', $template_path . 'js/fullcalendar.min.js'),
//                        array('JS', $template_path . 'js/jquery.contextMenu.js'),
//                        array('JS', $template_path . 'js/bootbox.min.js'),
//                        array('JS', $template_path . 'js/jobs.js'),
//                        array('JS', $template_path . 'js/jquery.fn.gantt.js'),
//                        array('JS', $template_path . 'js/data.js'),
//                        array('JS', $template_path . 'js/prettify.js'),
//                    ),
//                    'internalcss' => array(
//                        array('CSS', $template_path . 'css/fullcalendar.css', array(
//                                'media' => 'screen',
//                            )),
//                    ),
//                    'internaljs' => array(
//                        array('JS', $template_path . 'js/fullcalendar.min.js'),
//                        array('JS', $template_path . 'js/bootbox.min.js'),
//                        array('JS', $template_path . 'js/jobs.js'),
//                    ),
//                )
//            ),
//            'chat' => array(
//                'groups' => array(
//                    'conversationsjs' => array(
//                        array('JS', $template_path . 'js/list.min.js'),
//                    ),
//                )
//            ),
            'default' => array(
                'groups' => array(
                    'dashboardcss' => array(// action-specific css resoucres
                    // add here
                    ),
                    'dashboardjs' => array(// action-specific js resoucres
                    // add here
                    ),
                    'taskscss' => array(
                    // add here
                    ),
                    'tasksjs' => array(
                    // add here
                    ),
                    'inventorycss' => array(
                    // add here
                    ),
                    'inventoryjs' => array(
                    // add here
                    ),
                    'clientscss' => array(
                    // add here
                    ),
                    'clientsjs' => array(
                    // add here
                    ),
                    'personnelcss' => array(
                    // add here
                    ),
                    'personneljs' => array(
                    // add here
                    ),
                    'equipmentcss' => array(
                    // add here
                    ),
                    'equipmentjs' => array(
                    // add here
                    ),
                )
            ), // end controller
        )// end component
    )
);
