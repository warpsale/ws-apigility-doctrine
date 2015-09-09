<?php
namespace WSApigilityDoctrine\_Adapter\_Fix;

use Xiag\Rql\Command\Utils\Dumper;
use Xiag\Rql\Parser\DataType\Glob;

/**
 * @link https://github.com/liushuping/freetree
 */
class FixRqlDumper extends Dumper
{
    protected function dumpValue($value)
    {
        if ($value === null) {
            return 'null';
        } elseif ($value === true) {
            return 'true';
        } elseif ($value === false) {
            return 'false';
        } elseif (is_int($value) || is_float($value)) {
            return $this->dumpNumber($value);
        } elseif (is_string($value)) {
            return sprintf('%s', $value);
        } elseif ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        } elseif ($value instanceof Glob) {
            return(new FixRqlGlob($value))->toRegex();
        } elseif (is_array($value)) {
            return '[' . implode(', ', array_map([$this, 'dumpValue'], $value)) . ']';
        } else {
            return (string)$value;
        }
    }
}
