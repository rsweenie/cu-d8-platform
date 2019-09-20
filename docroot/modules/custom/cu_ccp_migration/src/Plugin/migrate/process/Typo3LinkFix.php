<?php

namespace Drupal\cu_ccp_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;


/**
 * Perform custom value transformations.
 * fixes links in typo3 documents(hopefully)
 * 
 * @MigrateProcessPlugin(
 *   id = "typo3_link_fix"
 * )
 */

class Typo3LinkFix extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Throw an error if value doesnt have enough ham
    if (gettype($value['value']) !== 'string') {
      throw new MigrateException('Not a string');
    }

    return fix_links($value['value']);
  }
  //----------------------------------------------
  // port from typo3 -> d7 migration module
  //----------------------------------------------
  /**
   *
   * Handle the LINK tag breakage
   * @param String $word
   */
  public function fix_links($word) {
    $word = str_ireplace("<link ", " <link ", $word);
    $word = str_ireplace(">", "> ", $word);
    $wordArr = explode(" ", $word);
    for ($i=0;$i<count($wordArr);$i++) {
      if ($wordArr[$i] == "<link") {
        $this->remove_words_after_position($wordArr, $i, ">");
      }
    }
    $finalArr =  $this->handle_space_word($wordArr);
    $word = implode(" ", $finalArr);
    $word = str_ireplace("<link ", '<a href="', $word);
    $word = str_ireplace("</link>", '</a>', $word);
    $word = str_ireplace(' ">', '">', $word);
    return $word;
  }

  /**
   *
   * Spaces are left out considering they are not neccessary
   * @param Array $words
   */
  public function handle_space_word(&$words) {
    $output = array();
    for ($i=0;$i<count($words);$i++) {
      if ($words[$i] == '') {
        continue;
      }
      $output[] = $words[$i];
    }
    return $output;
  }

  /**
   *
   * This will remove unwanted data from the word after the link tag is found
   * @param Array $words
   * @param Integer $pos
   * @param string $find
   */
  public function remove_words_after_position(&$words, $pos, $find=">") {
    $fWord = $words[$pos+1];
    $fWordChanged = str_replace(">", "", $fWord);
    if ($fWord != $fWordChanged) {
      $fWordChanged = ltrim(rtrim($fWordChanged));
      $words[$pos+1] = $fWordChanged . '">';
      $wordChanged = str_ireplace("@", "", $fWordChanged . '">');
      if ($wordChanged != $words[$pos+1] ) {
        $words[$pos+1] = "mailto:" . $words[$pos+1];
      }
      else{
        if (intval($words[$pos+1])) {
          $words[$pos+1] = "/nid/" . $words[$pos+1];
        }
        else{
          $words[$pos+1] = "/" . $words[$pos+1];
          $words[$pos+1] = str_ireplace("/http", "http", $words[$pos+1]);
        }
      }
      return '';
    }
    else{
      if (intval($words[$pos+1])) {    // if link is integer
          $words[$pos+1] = "/nid/" . $words[$pos+1];
        }
        else{                          // if there is @ present
          $wordChanged = str_ireplace("@", "", $words[$pos+1]);
          if ($wordChanged != $words[$pos+1] ) {
            $words[$pos+1] = "mailto:" . $words[$pos+1];
          }
          else{
            $words[$pos+1] = "/" . $words[$pos+1];
            $words[$pos+1] = str_ireplace("/http", "http", $words[$pos+1]);
          }
        }
    }

    $startPos = $pos + 2;
    for ($j=$startPos;$j<count($words);$j++) {
      $str = trim($words[$j]);
      $strChanged = str_replace($find, "", $str);
      if ($str == $strChanged) {
        $words[$j] = '';
        continue;
      }
      else{
        $words[$j] = '">';
        return '';
      }
    }
  }
}