<?php
namespace WSApigilityDoctrine\_Fix;

use Xiag\Rql\Parser\Lexer;
use Xiag\Rql\Parser\Token;


class FixRqlLexer extends Lexer
{
    protected function processValue($value)
    {
        if ($value === 'true()') {
            $this->pushToken(Token::T_TRUE, $value);
            $this->moveCursor($value);
        } elseif ($value === 'false()') {
            $this->pushToken(Token::T_FALSE, $value);
            $this->moveCursor($value);
        } elseif ($value === 'null()') {
            $this->pushToken(Token::T_NULL, $value);
            $this->moveCursor($value);
        } elseif ($value === 'empty()') {
            $this->pushToken(Token::T_EMPTY, $value);
            $this->moveCursor($value);
        } elseif ($value === 'true' || $value === 'false' || $value === 'null') {
            $this->pushToken(Token::T_STRING, $value);
            $this->moveCursor($value);
        } else {
            parent::processValue($value);
        }
    }
}