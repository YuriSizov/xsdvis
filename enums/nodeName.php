<?php
/**
 * XSD Parser & Visualizer PHP library
 *
 * @author  Yuri Sizov <yuris@humnom.net>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @version 1.0
 */
namespace XSDVis\Enums;

/**
 * XSD node names
 *
 * @package XSDVis
 */
class XSDNodeName {
  const XS_ANNOTATION = "annotation";
  const XS_ELEMENT = "element";
  const XS_SIMPLE_TYPE = "simpleType";
  const XS_COMPLEX_TYPE = "complexType";

  const XS_RESTRICTION = "restriction";
  const XS_LIST = "list";
  const XS_UNION = "union";

  const XS_ENUMERATION = "enumeration";
  const XS_PATTERN = "pattern";
  const XS_MIN_INCLUSIVE = "minInclusive";
  const XS_MAX_INCLUSIVE = "maxInclusive";
  const XS_MIN_EXCLUSIVE = "minExclusive";
  const XS_MAX_EXCLUSIVE = "maxExclusive";

  const XS_ALL = "all";
  const XS_CHOICE = "choice";
  const XS_SEQUENCE = "sequence";
}