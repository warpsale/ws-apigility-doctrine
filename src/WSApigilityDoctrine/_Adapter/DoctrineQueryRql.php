<?php
namespace WSApigilityDoctrine\_Adapter;

use Doctrine\ORM\Query\Expr\Composite;
use Xiag\Rql\Parser\Lexer;
use Xiag\Rql\Parser\Parser;
use WSApigilityDoctrine\_Adapter\_Fix\FixRqlDumper;

class DoctrineQueryRql 
{
    private $qb;
    private $rql;
    
    public function __construct($qb, $rql)
    {
        $this->qb = $qb;
        $this->rql = $rql;
    }
    
    public function getUpdatedQueryBuilder() 
    {
        $tokens = (new Lexer())->tokenize($this->rql); 
        $query = Parser::createDefault()->parse($tokens); 
        $tree = (new FixRqlDumper())->createTree($query); 

        $criteria = [];
        $this->tree2array($tree['nodes'][0], $criteria);
        $expr = $this->addCriteria($this->qb->expr()->andX(), $criteria);
        $this->qb->where($expr);

        return $this->qb;
    }
    
    private function tree2array($tree, &$criteria) { 
        foreach ($tree['nodes'] as $leaf) {
            if (strpos($leaf['value'], '<operator>and</operator>') !== FALSE || strpos($leaf['value'], '<operator>or</operator>') !== FALSE) {
                $criteria[str_replace('<operator>', '', str_replace('</operator>', '', $leaf['value']))] = [];
                $this->tree2array($leaf, $criteria[str_replace('<operator>', '', str_replace('</operator>', '', $leaf['value']))]);
            } elseif (strpos($leaf['value'], '<operator>') !== FALSE && strpos($leaf['value'], '</operator>') !== FALSE) {
                $ix = count($criteria);
                $criteria[$ix] = [NULL, str_replace('<operator>', '', str_replace('</operator>', '', $leaf['value'])), NULL];
                $this->tree2array($leaf, $criteria[$ix]);
            } elseif (strpos($leaf['value'], '<field>') !== FALSE && strpos($leaf['value'], '</field>') !== FALSE) {
                $criteria[0] = (strpos($leaf['value'], '.') === FALSE ? 'e.' : '') . str_replace('<field>', '', str_replace('</field>', '', $leaf['value']));
            } else {
                $criteria[2] = $leaf['value'];
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
    private function addCriteria(Composite $expr, array $criteria)
    {
        if (count($criteria)) {
            foreach ($criteria as $expression => $comparison) {
                list($field, $operator, $value) = $comparison;
                if ($expression === 'or') { 
                    $expr->add($this->addCriteria(
                        $this->qb->expr()->orX(),
                        $comparison
                    )); 
                } else if ($expression === 'and') { 
                    $expr->add($this->addCriteria(
                        $this->qb->expr()->andX(),
                        $comparison
                    )); 
                } else {
                    $expr->add($this->qb->expr()->{$operator}(($operator == 'like' ? 'LOWER('.$field.')' : $field), $this->qb->expr()->literal(($operator == 'like' ? strtolower($value) : $value)))); 
                }
            }
        }

        return $expr;
    }
}