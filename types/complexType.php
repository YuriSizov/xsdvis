<?php
/**
 * XSD Parser & Visualizer PHP library
 *
 * @author  Yuri Sizov <yuris@humnom.net>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @version 1.0
 */
namespace XSDVis\Types;

/**
 * XSD ComplexType node object
 *
 * @package XSDVis
 */
class XSDComplexType {
  public $name = "";
  public $annotation = "";
  public $type = "";
  public $children = null;

  public $minOccurs = 1;
  public $maxOccurs = 1;

  public function __construct() { }
}