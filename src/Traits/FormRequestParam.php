<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Traits;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Abstracts\FormRequestParamInterface;
use Fatbit\FormRequestParam\FormRequestFieldMappingParam;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Fatbit\FormRequestParam\Annotations\FormRequestArrayRule;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\FormRequestParam\FormRequestRulesParam;

/**
 * @implements FormRequestParamInterface
 * @extends AbstractFormRequestParam
 */
trait FormRequestParam
{
    protected static ?FormRequestRulesParam $formRequestRule = null;


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
        return static::getThisReflectionClass()
                     ->getProperties();
    }

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
        return ($reflectionProperty->getAttributes(FormRequestRule::class)[0] ?? null)?->newInstance();
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
        return $reflectionProperty->getAttributes(FormRequestArrayRule::class);
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
        $formRequestRuleParam = new FormRequestRulesParam(
            [
                'rules'        => [],
                'attributes'   => [],
                'messages'     => [],
                'fieldMapping' => [],
            ],
        );

        foreach (static::getThisProperties() as $thisProperty) {
            $formRequestRule = static::getPropertyFormRequestRule($thisProperty);
            if (is_null($formRequestRule)) {
                continue;
            }
            $name = $formRequestRule->fieldName ?? $thisProperty->getName();

            $type         = $thisProperty->getType();
            $propertyType = null;
            $isArray      = false;

            if ($type instanceof \ReflectionNamedType) {
                $typeName = $type->getName();
                if ($typeName === 'array') {
                    $isArray = true;
                } elseif (!$type->isBuiltin()) {
                    // ReflectionNamedType::getName() 已经处理了 ? (Nullable) 前缀，直接返回类名
                    $propertyType = $typeName;
                } elseif ($typeName === 'object') {
                    $propertyType = 'object';
                }
            } elseif ($type instanceof \ReflectionUnionType) {
                foreach ($type->getTypes() as $unionType) {
                    if ($unionType instanceof \ReflectionNamedType) {
                        if ($unionType->getName() === 'array') {
                            $isArray = true;
                        } elseif (!$unionType->isBuiltin()) {
                            $propertyType = $unionType->getName();
                        } elseif ($unionType->getName() === 'object' && $propertyType === null) {
                            $propertyType = 'object';
                        }
                    }
                }
            } else {
                // 兼容 simple type or blank
                $propertyTypeStr = $type?->__toString();
                // 简单处理字符串类型（尽量避免使用，优先依赖 Reflection）
                if ($propertyTypeStr) {
                    // 移除开头可能存在的 ?
                    $cleanType = ltrim($propertyTypeStr, '?');
                    if ($cleanType === 'array') {
                        $isArray = true;
                    } elseif (class_exists($cleanType)) {
                        $propertyType = $cleanType;
                    } else {
                        // Fallback
                        $propertyType = $propertyTypeStr;
                    }
                }
            }

            $toVal         = null;
            $transformFlag = false;
            if ($propertyType && class_exists($propertyType) && is_subclass_of($propertyType, AbstractFormRequestParam::class)) {
                $toVal         = $propertyType;
                $transformFlag = true;
            }

            $formRequestRuleParam->appendFieldMapping(
                $name,
                new FormRequestFieldMappingParam(
                    [
                        'toKey'        => $thisProperty->getName(),
                        'sourceKey'    => $name,
                        'toVal'        => $toVal,
                        'propertyType' => $propertyType,
                        'isArray'      => $isArray,
                        'default'      => $formRequestRule->default ?? null,
                    ],
                ),
            );
            // 添加规则
            $formRequestRuleParam->appendByFormRequestRule($name, $formRequestRule);
            $formRequestArrayRules = static::getPropertyFormRequestArrayRules($thisProperty);
            if (empty($formRequestArrayRules)) {
                if ($transformFlag) {
                    $formRequestRuleParam->appendByFormRequestParamInterface($name, $toVal, $name);
                }
                continue;
            }
            foreach ($formRequestArrayRules as $formRequestArrayRule) {
                $formRequestArrayRule = $formRequestArrayRule->newInstance();
                /** @var FormRequestArrayRule $formRequestArrayRule */
                // 如果定义为数组 则需要添加数组的验证规则
                $formRequestRuleParam->appendByFormRequestArrayRule($name, $formRequestArrayRule);
            }
        }
        return $formRequestRuleParam;
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
     * @return array|array<string, FormRequestFieldMappingParam>
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

    /**
     * 转换数据
     *
     * @author XJ.
     * @Date   2026/2/10
     *
     * @param array $validatedData
     *
     * @return static
     */
    public static function transformSelf(array $validatedData): static
    {
        $data = [];
        foreach (static::getFieldMapping() as $key => $fieldMapping) {
            if (is_object($fieldMapping)) {
                $value = $fieldMapping->toValue($validatedData);
                if (is_null($value) && !array_key_exists($fieldMapping->sourceKey, $validatedData)) {
                    continue;
                }
                $data[$fieldMapping->toKey()] = $value;
                continue;
            }
            if (isset($validatedData[$key])) {
                $data[$fieldMapping] = $validatedData[$key];
            }
        }

        return new static($data);
    }

}