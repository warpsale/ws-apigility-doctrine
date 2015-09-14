<?php
namespace WSApigilityDoctrine\_Fix;

use Xiag\Rql\Parser\ExpressionParser;
use Xiag\Rql\Parser\Token;


class FixRqlExpressionParser extends ExpressionParser
{
    protected function getScalarValue(Token $token)
    { 
        if ($token->test(Token::T_INTEGER)) {
            return $token->getValue();
        } else {
            return parent::getScalarValue($token);
        }
    }
}