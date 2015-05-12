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

require_once "parser.class.php";

/**
 * XSD visualizer
 *
 * @package XSDVis
 */
class Visualizer {
  /**
   * @var Parser Parser object instance
   */
  private $parser;

  /**
   * @param string $filepath Path to XML file
   * @param string $langCode Language code for localized string
   *
   * @throws \Exception If Language or parser error has occurred
   */
  public function __construct($filepath, $langCode = null) {
    if (!is_null($langCode)) {
      Language::setLang($langCode);
    }

    try {
      $this->parser = new Parser($filepath);
    } catch (\Exception $e) {
      throw new \Exception("Parser - Exception occurred: " . $e->getMessage());
    }
    $this->parser->parse();
  }

  /**
   * Layout parser results in HTML form
   *
   * @return string HTML output
   */
  public function draw() {
    $output = '';

    foreach ($this->parser->structure as $el) {
      $output .= $this->draw_routine($el, 0);
    }
    return $output;
  }

  /**
   * Internal routine for drawing element levels
   *
   * @param Types\XSDElement $el Currently drawn element
   * @param int $level Depth level
   * @return string HTML output
   */
  private function draw_routine($el, $level) {
    $output = '';
    if (is_string($el->type)) {
      if (!isset($this->parser->types[$el->type])) {
        $output .= $this->draw_element($el->name, array( 'name' => $el->type, 'desc' => Language::getString("visualizer.types.basic_type", $el->type) ), $el->annotation, array( 'min' => $el->minOccurs, 'max' => $el->maxOccurs ), array(), array(), '', $level);
        return $output;
      }
      else {
        /**
         * @var Types\XSDSimpleType|Types\XSDComplexType $type
         */
        $type = $this->parser->types[$el->type];
      }
    }
    else {
      /**
       * @var Types\XSDSimpleType|Types\XSDComplexType $type
       */
      $type = $el->type;
    }

    $annotation = empty($el->annotation) ? $type->annotation : $el->annotation;

    if ($type instanceof Types\XSDSimpleType) {
      $type_name = (!empty($type->baseType)) ? $type->baseType : $type->name;
      $type_desc = (!empty($type->baseType)) ? Language::getString("visualizer.types.simple_type_on_other", $type->baseType) : Language::getString("visualizer.types.simple_type_on_basic");

      $restrictions = array();
      if (!empty($type->values)) {
        $restrictions[] = Language::getString("visualizer.restrictions.allowed_values", implode('</b>, <b>', $type->values));
      }
      if (!empty($type->pattern)) {
        $restrictions[] = Language::getString("visualizer.restrictions.pattern", $type->pattern);
      }
      if (!empty($type->minInclusive)) {
        $restrictions[] = Language::getString("visualizer.restrictions.min_value_inc", $type->minInclusive);
      }
      if (!empty($type->maxInclusive)) {
        $restrictions[] = Language::getString("visualizer.restrictions.max_value_inc", $type->maxInclusive);
      }
      if (!empty($type->minExclusive)) {
        $restrictions[] = Language::getString("visualizer.restrictions.min_value_exc", $type->minExclusive);
      }
      if (!empty($type->maxExclusive)) {
        $restrictions[] = Language::getString("visualizer.restrictions.max_value_exc", $type->maxExclusive);
      }

      $output .= $this->draw_element($el->name, array( 'name' => $type_name, 'desc' => $type_desc ), $annotation, array( 'min' => $el->minOccurs, 'max' => $el->maxOccurs ), $restrictions, array(), '', $level);
    }
    elseif ($type instanceof Types\XSDComplexType) {
      $complex = array();
      switch($type->type) {
        case Enums\XSDNodeName::XS_ALL: $complex[] = Language::getString("visualizer.restrictions.complex_all"); break;
        case Enums\XSDNodeName::XS_CHOICE: $complex[] = Language::getString("visualizer.restrictions.complex_choice"); break;
        case Enums\XSDNodeName::XS_SEQUENCE: $complex[] = Language::getString("visualizer.restrictions.complex_sequence", $type->minOccurs, ($type->maxOccurs === INF ? Language::getString("visualizer.restrictions.complex_sequence_to_infinite") : $type->maxOccurs)); break;
      }
      $complex[] = Language::getString("visualizer.restrictions.complex_skip_not_required");

      $children = '';
      foreach ($type->children as $child) {
        $children .= $this->draw_routine($child, $level + 1);
      }
      $output .= $this->draw_element($el->name, array( 'name' => $type->name, 'desc' => Language::getString("visualizer.types.complex_type") ), $annotation, array( 'min' => $el->minOccurs, 'max' => $el->maxOccurs ), array(), $complex, $children, $level);
    }
    return $output;
  }

  /**
   * Internal routine for drawing elements
   *
   * @param string $name Element name
   * @param array $type Element type information
   * @param string $annotation Element description/annotation
   * @param array $occurs Element amount restriction information
   * @param array $restrictions Set of element restrictions
   * @param array $complex Complex type information
   * @param string $children Children HTML string
   * @param int $level Depth level
   * @return string HTML output
   */
  private function draw_element($name, $type, $annotation, $occurs, $restrictions, $complex, $children, $level) {
    $output = '';
    $output .= '
      <div class="xsd_element_block xsd_element_block_'. $level . '">
          <span class="xsd_element_name" title="' . Language::getString("visualizer.layout.element_name") . '">'. $name .'</span>
          <span class="xsd_element_type" title="'. $type['desc'] .'">'. $type['name'] .'</span>
          <span class="xsd_element_required">'. (($occurs['min'] == 0) ? '(' . Language::getString("visualizer.layout.not_required") . ')' : '') .'</span>

          ' . (!empty($annotation) ? '<div class="xsd_element_infoblock xsd_element_annotation"><span class="xsd_element_infoblock_title">' . Language::getString("visualizer.layout.description") . '</span>' . str_replace(chr(10), '<br />', $annotation) . '</div>' : '') . '
          ' . (!empty($restrictions) ? '<div class="xsd_element_infoblock xsd_element_restrictions"><span class="xsd_element_infoblock_title">' . Language::getString("visualizer.layout.restrictions") . '</span>' . implode('<br />', $restrictions) . '</div>' : '') . '
          ' . (!empty($complex) ? '<div class="xsd_element_infoblock xsd_element_complex"><span class="xsd_element_infoblock_title">' . Language::getString("visualizer.layout.complex_type") . '</span>' . implode('<br />', $complex) . '</div>' : '') . '

          ' . (!empty($children) ? '<div class="xsd_element_children"><div class="xsd_element_children_toggler" ref-state="closed"></div><div class="xsd_element_children_container">' . $children . '</div></div>' : '') . '
       </div>';
    return $output;
  }
}
?>
