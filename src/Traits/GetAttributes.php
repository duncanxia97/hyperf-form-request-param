<?php
/**
 * @author XJ.
 * @Date   2024/8/30 星期五
 */

namespace Fatbit\FormRequestParam\Traits;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

trait GetAttributes
{
    /**
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return ReflectionClass
     */
    protected static function getThisReflectionClass(): ReflectionClass
    {
        return new ReflectionClass(static::class);
    }

    /**
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array|ReflectionProperty[]
     */
    protected static function getThisProperties(): array
    {
        return static::getThisReflectionClass()->getProperties();
    }

    /**
     * @author XJ.
     * @Date   2024/8/30 星期五
     * @template T
     *
     * @param ReflectionProperty     $reflectionProperty
     * @param string|class-string<T> $attributeName
     *
     * @return array|ReflectionAttribute<T>|ReflectionAttribute[]
     */
    protected static function getPropertyAttributes(ReflectionProperty $reflectionProperty, string $attributeName): array
    {
        return $reflectionProperty->getAttributes($attributeName);
    }

    /**
     * @author XJ.
     * @Date   2024/8/30 星期五
     * @template T
     *
     * @param ReflectionProperty     $reflectionProperty
     * @param string|class-string<T> $attributeName
     *
     * @return object|null|T
     */
    protected static function getPropertyAttribute(ReflectionProperty $reflectionProperty, string $attributeName): ?object
    {
        if (empty(static::getPropertyAttributes($reflectionProperty, $attributeName))) {
            return null;
        }
        $attribute = static::getPropertyAttributes($reflectionProperty, $attributeName)[0];

        return static::getReflectionAttributeInstance($attribute);
    }

    /**
     * 修复8.0 swoole 兼容性
     *
     * @author XJ.
     * @Date   2024/9/2 星期一
     *
     * @param ReflectionAttribute $reflectionAttribute
     *
     */
    protected static function getReflectionAttributeInstance(ReflectionAttribute $reflectionAttribute): object
    {
        $className = $reflectionAttribute->getName();

        return new $className(...$reflectionAttribute->getArguments());
    }
}