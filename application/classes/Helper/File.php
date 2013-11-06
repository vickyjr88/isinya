<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Helper File that handles all file manipulation actions
 *
 * PHP version 5.3
 *
 * @category  Helpers
 * @package   Efficiency_Pro
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.8
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Helper_File{

	 /**
         * Checks whether the file exists in the file systen     
         * @param $directoy the directory to look for the file
         * @param $filename the file to search for
         * @return  boolean [True/Fslse]
         */
        public static function check_file_exists($directory=false,$filename)
        {
           if(file_exists($directory.$filename)) {
                return TRUE;
           }
           return FALSE;
        }
   
}