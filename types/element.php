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
 * XSD Element node object
 *
 * @package XSDVis
 */
class XSDElement {
  public $name = "";
  public $annotation = "";
  public $type = "";
  public $attributes = null;

  public $minOccurs = 1;
  public $maxOccurs = 1;

  public function __construct() { }
}