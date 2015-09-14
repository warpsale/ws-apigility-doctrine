<?php
namespace WSApigilityDoctrine;

use Doctrine\ORM\Query\Expr\Composite;
use Xiag\Rql\Parser\Parser;
use WSApigilityDoctrine\_Fix\FixRqlLexer;
use WSApigilityDoctrine\_Fix\FixRqlParser;
use WSApigilityDoctrine\_Fix\FixRqlDumper;

class DoctrineFilter 
{
    public static function filter($qb, $params) 
    {
        $rql = isset($params['query']) ? $params['query'] : NULL;
        if ($rql) {
            $tokens = (new FixRqlLexer())->tokenize($rql);
            $query = FixRqlParser::createDefault()->parse($tokens);
            $tree = (new FixRqlDumper())->createTree($query);
            
            $criteria = [];
            self::_tree2array($tree['nodes'][0], $criteria);
            $expr = self::_addCriteria($qb, $qb->expr()->andX(), $criteria);
            $qb->where($expr);
        }
    }
    
    private static function _tree2array($tree, &$criteria) { 
        foreach ($tree['nodes'] as $leaf) { 
            if (strpos($leaf['value'], '<operator>and</operator>') !== FALSE || strpos($leaf['value'], '<operator>or</operator>') !== FALSE) {
                $criteria[str_replace('<operator>', '', str_replace('</operator>', '', $leaf['value']))] = [];
                self::_tree2array($leaf, $criteria[str_replace('<operator>', '', str_replace('</operator>', '', $leaf['value']))]);
            } elseif (strpos($leaf['value'], '<operator>') !== FALSE && strpos($leaf['value'], '</operator>') !== FALSE) {
                $ix = count($criteria);
                $criteria[$ix] = [NULL, str_replace('<operator>', '', str_replace('</operator>', '', $leaf['value'])), NULL];
                self::_tree2array($leaf, $criteria[$ix]);
            } elseif (strpos($leaf['value'], '<field>') !== FALSE && strpos($leaf['value'], '</field>') !== FALSE) {
                $criteria[0] = (strpos($leaf['value'], '.') === FALSE ? 'e.' : '') . str_replace('<field>', '', str_replace('</field>', '', $leaf['value']));
            } else {
                $val = NULL;
                if (count($leaf['nodes']) == 0) {
                    $val = $leaf['value'];
                } else {
                    $val = [];
                    foreach ($leaf['nodes'] as $node) {
                        array_push($val, $node['value']);
                    }
                }
                $criteria[2] = $val; 
            }
        }
    }
    
    /**
     * Recursively takes the specified criteria and adds too the expression.
     *
     * The criteria is defined in an array notation where each item in the list
     * represents a comparison <fieldName, operator, value>. The operator maps to
     * comparison methods located in ExpressionBuilder. The key in the array can
     * be used to identify grouping of comparisons.
     *
     * @example
     * $criteria = array(
     *      'or' => array(
     *          array('field1', 'like', '%field1Value%'),
     *          array('field2', 'like', '%field2Value%')
     *      ),
     *      'and' => array(
     *          array('field3', 'eq', 3),
     *          array('field4', 'eq', 'four')
     *      ),
     *      array('field5', 'neq', 5)
     * );
     *
     * $qb = new QueryBuilder();
     * addCriteria($qb, $qb->expr()->andX(), $criteria);
     * echo $qb->getSQL();
     *
     * // Result:
     * // SELECT *
     * // FROM tableName
     * // WHERE ((field1 LIKE '%field1Value%') OR (field2 LIKE '%field2Value%'))
     * // AND ((field3 = '3') AND (field4 = 'four'))
     * // AND (field5 <> '5')
     *
     * @param QueryBuilder $qb
     * @param Composite $expr
     * @param array $criteria
     */
    private static function _addCriteria($qb, Composite $expr, array $criteria)
    {
        if (count($criteria)) { 
            foreach ($criteria as $expression => $comparison) {
                list($field, $operator, $value) = $comparison;
                
                // MINIMUM VALIDATION (to prevent silly access to database) - BEGIN
                if (is_string($operator)) {
                    if (!in_array($operator, ['and', 'or', 'like', 'eq', 'ne', 'lt', 'le', 'gt', 'ge', 'in', 'out'])) {
                        throw new \Exception('RQL: Operator not supported!');
                    }
                    if (!in_array($operator, ['and', 'or']) && is_null($value)) {
                        throw new \Exception('RQL: Value cannot be empty!');
                    }
                    if (in_array($operator, ['like', 'eq', 'ne', 'lt', 'le', 'gt', 'ge']) && is_array($value)) {
                        throw new \Exception('RQL: Value type cannot be Array!');
                    }
                    if ($operator == 'like' && !is_string($value)) {
                        throw new \Exception('RQL: Value type must be String!');
                    }
                    if ($operator == 'like' && ($value == 'true()' || $value == 'false()')) {
                        throw new \Exception('RQL: Value type cannot be boolean!');
                    }
                    if (in_array($operator, ['lt', 'le', 'gt', 'ge']) && !is_numeric($value)) {
                        throw new \Exception('RQL: Value type must be Numeric!');
                    }
                    if (in_array($operator, ['like', 'eq', 'ne', 'lt', 'le', 'gt', 'ge']) && trim($value) == '') {
                        throw new \Exception('RQL: Value cannot be an empty String/Number!');
                    }
                    if (in_array($operator, ['in', 'out']) && !is_array($value)) {
                        throw new \Exception('RQL: Value type must be Array!');
                    }
                    if (in_array($operator, ['in', 'out']) && count($value) == 0) {
                        throw new \Exception('RQL: Value type cannot be an empty Array!');
                    }
                    if (in_array($operator, ['in', 'out'])) {
                        foreach ($value as $v) {
                            if (is_array($v)) {
                                throw new \Exception('RQL: Value cannot be a multidimensional Array!');
                            }
                            if (is_null($v)) {
                                throw new \Exception('RQL: Value cannot have null Array elements!');
                            }
                            if (trim($v) == '') {
                                throw new \Exception('RQL: Value cannot have empty Array elements!');
                            }
                            if ($v == 'null()') {
                                throw new \Exception('RQL: Value cannot have null Array elements!');
                            }
                        }
                    }
                }
                // MINIMUM VALIDATION - END
                
                if ($expression === 'and') { 
                    $expr->add(self::_addCriteria(
                        $qb, 
                        $qb->expr()->andX(),
                        $comparison
                    ));
                } else if ($expression === 'or') { 
                    $expr->add(self::_addCriteria(
                        $qb,
                        $qb->expr()->orX(),
                        $comparison
                    ));
                } else {
                    switch ($operator) {
                        case 'like':
                            $field = 'LOWER('.$field.')';
                            if ($value == 'null()') {
                                $expr->add($qb->expr()->isNull($field));
                            } elseif ($value == 'empty()') {
                                $value = $qb->expr()->literal('');
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            } else {
                                $value = $qb->expr()->literal(strtolower($value));
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            }
                            break;
                        case 'eq':
                            if ($value == 'null()') {
                                $expr->add($qb->expr()->isNull($field));
                            } elseif ($value == 'true()') {
                                $value = $qb->expr()->literal(TRUE);
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            } elseif ($value == 'false()') {
                                $value = $qb->expr()->literal(FALSE);
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            } elseif ($value == 'empty()') {
                                $value = $qb->expr()->literal('');
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            } else {
                                $value = $qb->expr()->literal($value);
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            }
                            break;
                        case 'ne':
                            $operator = 'neq';
                            if ($value == 'null()') {
                                $expr->add($qb->expr()->isNotNull($field));
                            } elseif ($value == 'true()') {
                                $value = $qb->expr()->literal(TRUE);
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            } elseif ($value == 'false()') {
                                $value = $qb->expr()->literal(FALSE);
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            } elseif ($value == 'empty()') {
                                $value = $qb->expr()->literal('');
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            } else {
                                $value = $qb->expr()->literal($value);
                                $expr->add($qb->expr()->{$operator}($field, $value));
                            }
                            break;
                        case 'lt':
                            $value = $qb->expr()->literal($value);
                            $expr->add($qb->expr()->{$operator}($field, $value));
                            break;
                        case 'le':
                            $operator = 'lte';
                            $value = $qb->expr()->literal($value);
                            $expr->add($qb->expr()->{$operator}($field, $value));
                            break;
                        case 'gt':
                            $value = $qb->expr()->literal($value);
                            $expr->add($qb->expr()->{$operator}($field, $value));
                            break;
                        case 'ge':
                            $operator = 'gte';
                            $value = $qb->expr()->literal($value);
                            $expr->add($qb->expr()->{$operator}($field, $value));
                            break;
                        case 'in':
                            array_walk($value, function(&$v) { 
                                if ($v == 'true()') {
                                    $v = $qb->expr()->literal(TRUE);
                                } elseif ($v == 'false()') {
                                    $v = $qb->expr()->literal(FALSE);
                                } elseif ($v == 'empty()') {
                                    $v = $qb->expr()->literal('');
                                } else {
                                    $v = $qb->expr()->literal($v);
                                }
                            } );
                            $expr->add($qb->expr()->{$operator}($field, $value));
                            break;
                        case 'out':
                            $operator = 'notIn';
                            array_walk($value, function(&$v) { 
                                if ($v == 'true()') {
                                    $v = $qb->expr()->literal(TRUE);
                                } elseif ($v == 'false()') {
                                    $v = $qb->expr()->literal(FALSE);
                                } elseif ($v == 'empty()') {
                                    $v = $qb->expr()->literal('');
                                } else {
                                    $v = $qb->expr()->literal($v);
                                }
                            } );
                            $expr->add($qb->expr()->{$operator}($field, $value));
                            break;
                        default:
                            $value = $qb->expr()->literal($value);
                            $expr->add($qb->expr()->{$operator}($field, $value));
                    }
                }
            }
        }

        return $expr;
    }
}