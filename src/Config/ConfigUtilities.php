<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 15.05.16
 * Time: 01:37
 */

namespace Config;


use Exceptions\ConfigurationException;

/**
 * Contains various utility functions for configuration and configuration checking purposes
 *
 * Class ConfigUtilities
 * @package Config
 */
class ConfigUtilities {

  /**
   * Appends a trailing slash to $path if one isn't already there
   *
   * @param $path
   * @return string
   */
  public static function addTrailingSlash($path){
    return rtrim($path, '/').'/';
  }

  /**
   * Checks if a field named $fieldName is present and non-empty in the dataset.
   * If specified, the function checks also for a subField of $fieldName.
   * In case the field could contains booleans, the parameter $checkForBoolean has
   * to be set to true.
   *
   * @param $dataset
   * @param $fieldName
   * @param null $subFieldName
   * @param bool $checkForBoolean
   * @return bool
   * @throws ConfigurationException
   */
  public static function checkField($dataset, $fieldName, $subFieldName = null, $checkForBoolean = false){

    if ($checkForBoolean){
      if (!is_null($subFieldName)){
        if (isset($dataset[$fieldName])){
          return isset($dataset[$fieldName][$subFieldName]);
        } else {
          return false;
        }
      } else {
        return isset($dataset[$fieldName]);
      }
    }

    if (empty($dataset[$fieldName]) || (!is_null($subFieldName) && empty($dataset[$fieldName][$subFieldName]))){
      $errMsg = 'Missing field '.$fieldName;

      if($subFieldName) {
        $errMsg.=', subfield '.$subFieldName;
      }

      throw new ConfigurationException($errMsg);
    }
  }

}