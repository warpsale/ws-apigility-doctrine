<?php
namespace WSApigilityDoctrine\_Fix;

use Xiag\Rql\Parser\Parser;
use Xiag\Rql\Parser\TokenParserGroup;


class FixRqlParser extends Parser
{
    public static function createDefault()
    {
        $queryTokenParser = new TokenParserGroup();
        $queryTokenParser
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\GroupTokenParser($queryTokenParser))

            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\LogicOperator\AndTokenParser($queryTokenParser))
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\LogicOperator\OrTokenParser($queryTokenParser))
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\LogicOperator\NotTokenParser($queryTokenParser))

            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ArrayOperator\InTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ArrayOperator\OutTokenParser())

            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ScalarOperator\EqTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ScalarOperator\NeTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ScalarOperator\LtTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ScalarOperator\GtTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ScalarOperator\LeTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ScalarOperator\GeTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Basic\ScalarOperator\LikeTokenParser())

            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ArrayOperator\InTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ArrayOperator\OutTokenParser())

            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ScalarOperator\EqTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ScalarOperator\NeTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ScalarOperator\LtTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ScalarOperator\GtTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ScalarOperator\LeTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ScalarOperator\GeTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\Query\Fiql\ScalarOperator\LikeTokenParser());

        return (new self(
            (new FixRqlExpressionParser())
                ->registerTypeCaster('string', new \Xiag\Rql\Parser\TypeCaster\StringTypeCaster())
                ->registerTypeCaster('integer', new \Xiag\Rql\Parser\TypeCaster\IntegerTypeCaster())
                ->registerTypeCaster('float', new \Xiag\Rql\Parser\TypeCaster\FloatTypeCaster())
                ->registerTypeCaster('boolean', new \Xiag\Rql\Parser\TypeCaster\BooleanTypeCaster())
        ))
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\SelectTokenParser())
            ->addTokenParser($queryTokenParser)
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\SortTokenParser())
            ->addTokenParser(new \Xiag\Rql\Parser\TokenParser\LimitTokenParser());
    }
}