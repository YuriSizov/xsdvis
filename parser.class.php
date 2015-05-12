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

require_once "language.class.php";

require_once "types/element.php";
require_once "types/simpleType.php";
require_once "types/complexType.php";

require_once "enums/nodeName.php";
require_once "enums/nodeAttr.php";

/**
 * XSD parser
 *
 * @package XSDVis
 */
class Parser {
  /**
   * @var string Main node name
   */
  const XS_NODE = "schema";
  /**
   * @var string XSD namespace
   */
  const XS_NS = "http://www.w3.org/2001/XMLSchema";
  /**
   * @var string XSD namespace alias
   */
  public $XS_NS_ALIAS;

  /**
   * @var \DOMDocument DOMDocument instance
   */
  private $doc;
  /**
   * @var array Parsed hierarchy collection
   */
  public $structure = array();
  /**
   * @var array Parsed type collection
   */
  public $types = array();

  /**
   * Capture libxml errors and throw an exception
   *
   * @param string $innerMessage Preset user error message
   * @throws \DOMException Complete exception
   */
  private static function get_xml_errors($innerMessage) {
    $xml_errors = libxml_get_errors();
    $xml_errors_str = array();
    foreach ($xml_errors as $xml_error) {
      $xml_error_str = '';
      switch ($xml_error->level) {
        case LIBXML_ERR_WARNING:
          $xml_error_str .= '[XML WARNING] ';
          break;
        default:
        case LIBXML_ERR_ERROR:
          $xml_error_str .= '[XML ERROR] ';
          break;
        case LIBXML_ERR_FATAL:
          $xml_error_str .= '[XML FATAL ERROR] ';
          break;
      }
      $xml_error_str .= '#' . $xml_error->code . ': ' . $xml_error->message . ' @l:' . $xml_error->line . '|c:' . $xml_error->column;
      $xml_errors_str[] = $xml_error_str;
    }
    if (!empty($xml_errors_str)) {
      throw new \DOMException($innerMessage . ": " . PHP_EOL . implode(PHP_EOL, $xml_errors_str));
    }
  }

  /**
   * @param string $filepath Path to XML file
   * @param string $langCode Language code for localized string
   *
   * @throws \DOMException
   * @throws \InvalidArgumentException
   */
  public function __construct($filepath, $langCode = null) {
    if (!file_exists($filepath)) {
      throw new \InvalidArgumentException("File at '" . $filepath . "' does not exist");
    }

    if (!is_null($langCode)) {
      Language::setLang($langCode);
    }

    libxml_use_internal_errors(true);
    $doc = new \DOMDocument('1.0', 'UTF-8');
    $doc->loadXML(file_get_contents($filepath));
    self::get_xml_errors("Unable to load XML file at '" . $filepath . "'");

    if (strtolower($doc->firstChild->localName) != self::XS_NODE || $doc->firstChild->namespaceURI != self::XS_NS) {
      throw new \DOMException("Supplied XML file is not a valid XML Schema Description document");
    }
    $this->XS_NS_ALIAS = $doc->firstChild->lookupPrefix(self::XS_NS);

    $this->doc = $doc;
  }

  /**
   * Parse loaded file into Structure and Types collections
   */
  public function parse() {
    $root = $this->doc->firstChild;
    for ($i = 0; $i < $root->childNodes->length; $i++) {
      $child = $root->childNodes->item($i);
      if ($child->nodeType === XML_ELEMENT_NODE) {
        switch ($child->localName) {
          default: break;
          case Enums\XSDNodeName::XS_ELEMENT: $this->structure[] = $this->parse_element($child); break;
          case Enums\XSDNodeName::XS_SIMPLE_TYPE: $this->parse_type_simple($child, $this->types); break;
          case Enums\XSDNodeName::XS_COMPLEX_TYPE: $this->parse_type_complex($child, $this->types); break;
        }
      }
    }
  }

  /**
   * Parse Element node
   *
   * @param \DOMNode $node Element node
   * @return Types\XSDElement
   */
  private function parse_element($node) {
    $el = new Types\XSDElement();
    $this->parse_attrs($el, $node);

    if ($node->hasChildNodes()) {
      for ($i = 0; $i < $node->childNodes->length; $i++) {
        $child = $node->childNodes->item($i);
        if ($child->nodeType === XML_ELEMENT_NODE) {
          switch ($child->localName) {
            default: break;
            case Enums\XSDNodeName::XS_ANNOTATION: $el->annotation = trim($child->textContent); break;
            case Enums\XSDNodeName::XS_SIMPLE_TYPE: $this->parse_type_simple($child, $el->type); break;
            case Enums\XSDNodeName::XS_COMPLEX_TYPE: $this->parse_type_complex($child, $el->type); break;
          }
        }
      }
    }

    return $el;
  }

  /**
   * Parse node attributes
   *
   * @param Types\XSDElement|Types\XSDSimpleType|Types\XSDComplexType $el Element instance to parse node in
   * @param \DOMNode $node DOM node
   */
  private function parse_attrs(&$el, $node) {
    if ($node->hasAttributes()) {
      $el->attributes = array();
      for ($i = 0; $i < $node->attributes->length; $i++) {
        $attr = $node->attributes->item($i);
        if ($attr->nodeType === XML_ATTRIBUTE_NODE) {
          switch ($attr->localName) {
            default: $el->attributes[$attr->localName] = $attr->textContent; break;
            case Enums\XSDNodeAttr::XS_NAME: $el->name = $attr->textContent; break;
            case Enums\XSDNodeAttr::XS_TYPE: $el->type = $attr->textContent; break;
            case Enums\XSDNodeAttr::XS_BASE: $el->baseType = $attr->textContent; break;
            case Enums\XSDNodeAttr::XS_MIN_OCCURS: $el->minOccurs = (int)$attr->textContent; break;
            case Enums\XSDNodeAttr::XS_MAX_OCCURS: $el->maxOccurs = ($attr->textContent == "unbounded" ? INF : (int)$attr->textContent); break;
          }
        }
      }
      if (empty($el->attributes)) { $el->attributes = null; }
    }
  }

  /**
   * Parse SimpleType node
   *
   * @param \DOMNode $node SimpleType node
   * @param array|mixed $ownerType Parent element's type
   */
  private function parse_type_simple($node, &$ownerType) {
    $el = new Types\XSDSimpleType();
    $el->name = Language::getString("parser.types.simple_type");
    $this->parse_attrs($el, $node);

    if ($node->hasChildNodes()) {
      for ($i = 0; $i < $node->childNodes->length; $i++) {
        $child = $node->childNodes->item($i);
        if ($child->nodeType === XML_ELEMENT_NODE) {
          switch ($child->localName) {
            default: break;
            case Enums\XSDNodeName::XS_ANNOTATION: $el->annotation = trim($child->textContent); break;

            case Enums\XSDNodeName::XS_RESTRICTION:
              $this->parse_attrs($el, $child);
              $el->values = array();
              if ($child->hasChildNodes()) {
                for ($i = 0; $i < $child->childNodes->length; $i++) {
                  $restrict = $child->childNodes->item($i);
                  if ($restrict->nodeType === XML_ELEMENT_NODE) {
                    switch ($restrict->localName) {
                      default: break;
                      case Enums\XSDNodeName::XS_ENUMERATION: $el->values[] = $restrict->attributes->getNamedItem(Enums\XSDNodeAttr::XS_VALUE)->textContent; break;
                      case Enums\XSDNodeName::XS_PATTERN: $el->pattern = $restrict->attributes->getNamedItem(Enums\XSDNodeAttr::XS_VALUE)->textContent; break;
                      case Enums\XSDNodeName::XS_MIN_INCLUSIVE: $el->minInclusive = $restrict->attributes->getNamedItem(Enums\XSDNodeAttr::XS_VALUE)->textContent; break;
                      case Enums\XSDNodeName::XS_MAX_INCLUSIVE: $el->maxInclusive = $restrict->attributes->getNamedItem(Enums\XSDNodeAttr::XS_VALUE)->textContent; break;
                      case Enums\XSDNodeName::XS_MIN_EXCLUSIVE: $el->minExclusive = $restrict->attributes->getNamedItem(Enums\XSDNodeAttr::XS_VALUE)->textContent; break;
                      case Enums\XSDNodeName::XS_MAX_EXCLUSIVE: $el->maxExclusive = $restrict->attributes->getNamedItem(Enums\XSDNodeAttr::XS_VALUE)->textContent; break;
                    }
                  }
                }
              }
              if (empty($el->values)) { $el->values = null; }
              break;
          }
        }
      }
    }
    if (is_array($ownerType)) {
      $ownerType[$el->name] = $el;
    } else {
      $ownerType = $el;
    }
  }

  /**
   * Parse ComplexType node
   *
   * @param \DOMNode $node ComplexType node
   * @param array|mixed $ownerType Parent element's type
   */
  private function parse_type_complex($node, &$ownerType) {
    $el = new Types\XSDComplexType();
    $el->name = Language::getString("parser.types.complex_type");
    $this->parse_attrs($el, $node);

    if ($node->hasChildNodes()) {
      for ($i = 0; $i < $node->childNodes->length; $i++) {
        $child = $node->childNodes->item($i);
        if ($child->nodeType === XML_ELEMENT_NODE) {
          switch ($child->localName) {
            default: break;
            case Enums\XSDNodeName::XS_ANNOTATION:
              $el->annotation = trim($child->textContent);
              break;

            case Enums\XSDNodeName::XS_ALL:
              $el->type = Enums\XSDNodeName::XS_ALL;
              break;
            case Enums\XSDNodeName::XS_CHOICE:
              $el->type = Enums\XSDNodeName::XS_CHOICE;
              break;

            case Enums\XSDNodeName::XS_SEQUENCE:
              $el->type = Enums\XSDNodeName::XS_SEQUENCE;
              $this->parse_attrs($el, $child);
              break;
          }

          if (in_array($child->localName, array( Enums\XSDNodeName::XS_ALL, Enums\XSDNodeName::XS_CHOICE, Enums\XSDNodeName::XS_SEQUENCE ))) {
            if ($child->hasChildNodes()) {
              $el->children = array();
              for ($i = 0; $i < $child->childNodes->length; $i++) {
                $option = $child->childNodes->item($i);
                if ($option->nodeType === XML_ELEMENT_NODE) {
                  switch ($option->localName) {
                    default: break;
                    case Enums\XSDNodeName::XS_ELEMENT: $el->children[] = $this->parse_element($option); break;
                  }
                }
              }
              if (empty($el->children)) { $el->children = null; }
            }
          }
        }
      }
    }
    if (is_array($ownerType)) {
      $ownerType[$el->name] = $el;
    } else {
      $ownerType = $el;
    }
  }
}