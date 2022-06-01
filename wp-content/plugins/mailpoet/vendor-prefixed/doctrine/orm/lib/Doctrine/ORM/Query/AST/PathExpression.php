<?php
 namespace MailPoetVendor\Doctrine\ORM\Query\AST; if (!defined('ABSPATH')) exit; class PathExpression extends \MailPoetVendor\Doctrine\ORM\Query\AST\Node { const TYPE_COLLECTION_VALUED_ASSOCIATION = 2; const TYPE_SINGLE_VALUED_ASSOCIATION = 4; const TYPE_STATE_FIELD = 8; public $type; public $expectedType; public $identificationVariable; public $field; public function __construct($expectedType, $identificationVariable, $field = null) { $this->expectedType = $expectedType; $this->identificationVariable = $identificationVariable; $this->field = $field; } public function dispatch($walker) { return $walker->walkPathExpression($this); } } 