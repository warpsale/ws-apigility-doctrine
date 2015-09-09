<?php
namespace WSApigilityDoctrine\_Adapter\_Fix;

use Xiag\Rql\Parser\DataType\Glob;

/**
 */
class FixRqlGlob extends Glob
{
    /**
     * @param Glob $glob
     */
    public function __construct(Glob $glob)
    {
        $reflectionClass = new \ReflectionClass('Xiag\Rql\Parser\DataType\Glob');
        $reflectionProperty = $reflectionClass->getProperty('glob');
        $reflectionProperty->setAccessible(true);
        $this->glob = $reflectionProperty->getValue($glob);
    }
    
    /**
     * @return string
     */
    public function toRegex()
    {
        return str_replace('*', '%', $this->glob);
    }
}
