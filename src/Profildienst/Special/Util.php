<?php
/**
 * Utility functions
 */
/**
 * @package Special
 */
namespace Special;

/**
 * This class contains helper functions
 *
 * Class Util
 */
class Util {

  /**
   * This function returns an array copy from an object.
   *
   * The database driver returns BSONDocuments which are ArrayObjects and therefore objects which act as arrays.
   * Due to legacy reasons most of the code assumes that the database result is a plain array, hence this function
   * has to be used to convert the results to plain arrays
   *
   * @TODO: Remove this function and rework the whole DB set up
   * @param $obj Input object
   * @return array Array
   */
  public static function getArray($obj) {

    if (is_object($obj)) {
      $obj = (array)$obj;
    }

    if (is_array($obj)) {
      $newArr = array();
      foreach ($obj as $key => $val) {
        $newArr[$key] = self::getArray($val);
      }
    } else {
      $newArr = $obj;
    }
    return $newArr;
  }
}