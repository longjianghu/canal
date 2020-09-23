<?php declare(strict_types=1);

namespace App\Annotation\Parser;

use ReflectionException;

use App\Annotation\Mapping\JsonStr;

use Swoft\Validator\ValidatorRegister;
use Swoft\Annotation\Annotation\Parser\Parser;
use Swoft\Validator\Exception\ValidatorException;
use Swoft\Annotation\Annotation\Mapping\AnnotationParser;

/**
 * Class JsonStrParser
 *
 * @AnnotationParser(annotation=JsonStr::class)
 */
class JsonStrParser extends Parser
{
    /**
     * @param int    $type
     * @param object $annotationObject
     *
     * @return array
     * @throws ReflectionException
     * @throws ValidatorException
     */
    public function parse(int $type, $annotationObject): array
    {
        if ($type != self::TYPE_PROPERTY) {
            return [];
        }
        //向验证器注册一个验证规则
        ValidatorRegister::registerValidatorItem($this->className, $this->propertyName, $annotationObject);

        return [];
    }
}