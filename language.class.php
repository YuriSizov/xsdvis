<?php
/**
 * XSD Parser & Visualizer PHP library
 *
 * @author  Yuri Sizov <yuris@humnom.net>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @version 1.0
 */
namespace XSDVis;

/**
 * Localization helper
 *
 * @package XSDVis
 */
class Language {
  /**
   * @var Language Singleton instance
   */
  private static $instance = null;
  /**
   * Get singleton instance
   *
   * @return Language
   */
  private static function get() {
    if (is_null(self::$instance) || !(self::$instance instanceof Language)) {
      self::$instance = new Language();
    }
    return self::$instance;
  }

  /**
   * @var string Default, fallback language
   */
  const DEFAULT_LANG = "en";
  /**
   * @var string User set language, if parsed correctly
   */
  private $currentLang = "";

  /**
   * @var array Strings for default language
   */
  private $langDataDefault = null;
  /**
   * @var array String for user set language
   */
  private $langDataCurrent = null;

  /**
   * @throws \Exception If default language could not be parsed
   */
  private function __construct() {
    if (!$this->setLangDefault(self::DEFAULT_LANG)) {
      throw new \Exception("Unable to load language data, missing default language '" . self::DEFAULT_LANG . "'");
    }
  }

  /**
   * Load language file and parse string
   *
   * @param string $langCode Language code, corresponding to a file in /lang folder
   * @return array|null Parsed strings
   */
  private function loadLang($langCode) {
    $langFile = "lang/" . $langCode . ".json";
    if (!file_exists($langFile)) {
      return null;
    }
    return json_decode(file_get_contents($langFile));
  }
  /**
   * Load default language strings
   *
   * @param string $langCode Language code, corresponding to a file in /lang folder
   * @return bool Operation success status
   */
  private function setLangDefault($langCode) {
    $langData = $this->loadLang($langCode);
    if (is_null($langData)) {
      return false;
    }

    $this->langDataDefault = $langData;
    return true;
  }
  /**
   * Load user set language strings and save language code
   *
   * @param string $langCode Language code, corresponding to a file in /lang folder
   * @return bool Operation success status
   */
  private function setLangCurrent($langCode) {
    if ($langCode == $this->currentLang) {
      return true;
    }

    $langData = $this->loadLang($langCode);
    if (is_null($langData)) {
      return false;
    }

    $this->currentLang = $langCode;
    $this->langDataCurrent = $langData;
    return true;
  }

  /**
   * Set user specific language to be used instead of default one
   *
   * @param string $langCode Language code, corresponding to a file in /lang folder
   * @return bool Operation success status
   */
  public static function setLang($langCode) {
    return self::get()->setLangCurrent($langCode);
  }

  /**
   * Get localized string from user set language, or, if missing entirely, from default language
   *
   * @param string $stringCode String code in json-like format (e.g. parent.parent.child)
   * @param mixed $replacements Additional parameters to be put if formatting
   * @return string Localized string, or string containing error message
   */
  public static function getString($stringCode, ...$replacements) {
    if ($stringCode == "") {
      return "[ EMPTY STRING CODE ]";
    }

    $stringCodeParts = explode(".", $stringCode);
    $partsCount = count($stringCodeParts);

    $instance = self::get();
    $currentNode = (is_null($instance->langDataCurrent) ? $instance->langDataDefault : $instance->langDataCurrent);
    for ($i = 0; $i < $partsCount; $i++) {
      if (!isset($currentNode->$stringCodeParts[$i])) {
        return "[ INVALID STRING CODE ]";
      }
      $currentNode = $currentNode->$stringCodeParts[$i];
    }

    if ($currentNode instanceof \stdClass) {
      return "[ AMBIGUOUS STRING CODE ]";
    }

    return "" . vsprintf($currentNode, $replacements);
  }
}