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
 * XSD SimpleType node object
 *
 * @package XSDVis
 */
class XSDSimpleType {
  public $name = "";
  public $annotation = "";
  public $baseType = "";

  public $values = null;
  public $pattern = "";
  public $minInclusive = null;
  public $maxInclusive = null;
  public $minExclusive = null;
  public $maxExclusive = null;

  public function __construct() { }
}