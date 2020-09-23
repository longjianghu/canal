<?php declare(strict_types=1);

namespace App\Validator\Rule;

use App\Annotation\Mapping\JsonStr;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Validator\Contract\RuleInterface;
use Swoft\Validator\Exception\ValidatorException;

/**
 * Class JsonStrRule
 *
 * @Bean(JsonStr::class)
 */
class JsonStrRule implements RuleInterface
{
    /**
     * @param array  $data
     * @param string $propertyName
     * @param object $item
     * @param null   $default
     * @param bool   $strict
     *
     * @return array
     * @throws ValidatorException
     */
    public function validate(array $data, string $propertyName, $item, $default = null, $strict = false): array
    {
        $message = $item->getMessage();

        if ( ! isset($data[$propertyName]) && $default === null) {
            $message = (empty($message)) ? sprintf('%s must exist!', $propertyName) : $message;
            throw new ValidatorException($message);
        }

        $value = $data[$propertyName];
        json_decode($value, true);

        if (json_last_error() == JSON_ERROR_NONE) {
            return $data;
        }

        $message = (empty($message)) ? sprintf('%s is not valid!', $propertyName) : $message;

        throw new ValidatorException($message);
    }
}