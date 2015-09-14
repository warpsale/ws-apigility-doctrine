<?php
namespace WSApigilityDoctrine\_Fix;

use Xiag\Rql\Command\Utils\Dumper;
use Xiag\Rql\Parser\DataType\Glob;


class FixRqlDumper extends Dumper
{
    protected function dumpValue($value)
    {
        if ($value === null) { 
            return 'null()';
        } elseif ($value === true) {
            return 'true()';
        } elseif ($value === false) {
            return 'false()';
        } elseif (empty($value)) {
            return 'empty()';
        } elseif (is_string($value)) {
            return sprintf('%s', $value);
        } elseif ($value instanceof Glob) {
            return(new FixRqlGlob($value))->toRegex();
        } else {
            return parent::dumpValue($value);
        }
    }
}