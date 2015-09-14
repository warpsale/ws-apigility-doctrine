<?php
namespace WSApigilityDoctrine\_Fix;

use Xiag\Rql\Parser\DataType\Glob;


class FixRqlGlob extends Glob
{
    public function __construct(Glob $glob)
    {
        $reflectionClass = new \ReflectionClass('Xiag\Rql\Parser\DataType\Glob');
        $reflectionProperty = $reflectionClass->getProperty('glob');
        $reflectionProperty->setAccessible(true);
        $glob = $reflectionProperty->getValue($glob);
        
        parent::__construct($glob);
    }

    public function toRegex()
    {
        return str_replace('*', '%', $this->glob);
    }
}