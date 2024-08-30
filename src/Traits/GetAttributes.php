<?php
/**
 * @author XJ.
 * @Date   2024/8/30 星期五
 */

namespace Fatbit\FormRequestParam\Traits;

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
     * @return array|T[]
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
        return (static::getPropertyAttributes($reflectionProperty, $attributeName)[0] ?? null)->newInstance();
    }
}