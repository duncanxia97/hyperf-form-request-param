<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Traits;

use Fatbit\FormRequestParam\Abstracts\FormRequestParamInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Fatbit\FormRequestParam\Annotations\FormRequestArrayRule;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\FormRequestParam\FormRequestRulesParam;

/**
 * @implements FormRequestParamInterface
 */
trait FormRequestParam
{
    use GetAttributes;

    protected static ?FormRequestRulesParam $formRequestRule = null;

    /**
     * @author XJ.
     * @Date   2023/8/28 0028
     *
     * @param ReflectionProperty $reflectionProperty
     *
     * @return FormRequestRule|null
     */
    protected static function getPropertyFormRequestRule(ReflectionProperty $reflectionProperty): ?FormRequestRule
    {
        return static::getPropertyAttribute($reflectionProperty, FormRequestRule::class);
    }

    /**
     * 获取数组验证规则
     *
     * @author XJ.
     * @Date   2023/8/29 0029
     *
     * @param ReflectionProperty $reflectionProperty
     *
     * @return array|ReflectionAttribute[]
     */
    protected static function getPropertyFormRequestArrayRules(ReflectionProperty $reflectionProperty): array
    {
        return static::getPropertyAttributes($reflectionProperty, FormRequestArrayRule::class);
    }

    /**
     * @author       XJ.
     * @Date         2023/8/28 0028
     * @return FormRequestRulesParam
     */
    protected static function getFormRequestRule(): FormRequestRulesParam
    {
        if (!is_null(static::$formRequestRule)) {
            return static::$formRequestRule;
        }
        $rules        = [];
        $attributes   = [];
        $messages     = [];
        $fieldMapping = [];
        foreach (static::getThisProperties() as $thisProperty) {
            $formRequestRule = static::getPropertyFormRequestRule($thisProperty);
            if (is_null($formRequestRule)) {
                continue;
            }
            $name                = $formRequestRule->fieldName ?? $thisProperty->getName();
            $fieldMapping[$name] = $thisProperty->getName();
            $rules[$name]        = $formRequestRule->rule;
            $attributes[$name]   = $formRequestRule->attribute;
            if ($formRequestRule->messages) {
                foreach ($formRequestRule->messages as $rule => $message) {
                    $messages[$name . '.' . $rule] = $message;
                }
            }
            $formRequestArrayRules = static::getPropertyFormRequestArrayRules($thisProperty);
            foreach ($formRequestArrayRules as $formRequestArrayRule) {
                $formRequestArrayRule = static::getReflectionAttributeInstance($formRequestArrayRule);
                /** @var FormRequestArrayRule $formRequestArrayRule */
                $fieldName              = $name . '.' . $formRequestArrayRule->fieldName;
                $rules[$fieldName]      = $formRequestArrayRule->rule;
                $attributes[$fieldName] = $formRequestArrayRule->attribute;
                if ($formRequestArrayRule->messages) {
                    foreach ($formRequestArrayRule->messages as $rule => $message) {
                        $messages[$fieldName . '.' . $rule] = $message;
                    }
                }
            }
        }

        return new FormRequestRulesParam(compact('rules', 'attributes', 'messages', 'fieldMapping'));
    }

    /**
     * @inheritDoc
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array
     */
    public static function getRules(): array
    {
        return static::getFormRequestRule()->rules;
    }

    /**
     * 字段映射
     *
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array
     */
    public static function getFieldMapping(): array
    {
        return static::getFormRequestRule()->fieldMapping;
    }

    /**
     * @inheritDoc
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array
     */
    public static function getAttributes(): array
    {
        return static::getFormRequestRule()->attributes;
    }

    /**
     * @inheritDoc
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array
     */
    public static function getMessages(): array
    {
        return static::getFormRequestRule()->messages;
    }

}