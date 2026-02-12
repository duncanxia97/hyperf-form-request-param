<?php
/**
 * @author XJ.
 * @Date   2026/2/10
 */

namespace Fatbit\FormRequestParam;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\Utils\Params\AbstractParam;

class FormRequestFieldMappingParam extends AbstractParam
{

    public ?string              $sourceKey;

    public ?string              $toKey;

    public string|\Closure|null $toVal = null;

    /**
     * 默认值
     *
     * @var mixed|null
     */
    public mixed $default = null;

    /**
     * 属性类型
     *
     * @var string
     */
    public ?string $propertyType = null;

    /**
     * @var array|self[]
     */
    public array $arrayItems = [];

    /**
     * 是否为数组
     *
     * @var bool
     */
    public bool $isArray = false;

    /**
     * 转换值
     *
     * @author XJ.
     * @Date   2026/2/10
     *
     * @param $data
     *
     * @return AbstractFormRequestParam|mixed|null
     */
    public function toValue($data)
    {
        // 1. 获取当前层级的值
        if ($this->sourceKey === null) {
            $value = $data;
        } else {
            // 如果字段不存在，则返回默认值
            if (!array_key_exists($this->sourceKey, $data)) {
                if (is_string($this->default)) {
                    if (class_exists($this->default) && class_instantiable($this->default)) {
                        $defaultClass = $this->default;
                        $res          = new $defaultClass($data);
                        // 兼容数组
                        if ($this->isArray && method_exists($res, 'toArray')) {
                            return $res->toArray();
                        }

                        return $res;
                    }
                    if (function_exists($this->default)) {
                        return call_user_func($this->default, $data);
                    }

                }

                return $this->default;
            }
            $value = $data[$this->sourceKey];
        }

        // 2. 如果存在数组映射定义 (arrayItems)
        if (!empty($this->arrayItems) && is_array($value)) {

            $resultMap = $value;
            foreach ($this->arrayItems as $key => $mapping) {
                $resultMap[$mapping->toKey() ?? $key] = $mapping->toValue($value);
            }

            return $resultMap;
        }

        if (is_callable($this->toVal)) {
            return call_user_func($this->toVal, $value, $data);
        }

        // 动态获取目标类：优先使用 toVal，其次尝试使用 propertyType
        $targetClass = null;
        if (is_string($this->toVal) && class_exists($this->toVal)) {
            $targetClass = $this->toVal;
        } elseif ($this->propertyType && class_exists($this->propertyType)) {
            $targetClass = $this->propertyType;
        }

        // 如果是 AbstractFormRequestParam 的子类 则进行实例化
        if ($targetClass && is_subclass_of($targetClass, AbstractFormRequestParam::class)) {
            $reflection = new \ReflectionClass($targetClass);
            if ($reflection->isInstantiable()) {
                $param = new $targetClass($value);

                if ($this->isArray && empty($this->propertyType)) {
                    return $param->toArray();
                }

                return $param;
            }
        }

        return $value;
    }

    /**
     * 转换为key
     *
     * @author XJ.
     * @Date   2026/2/10
     */
    public function toKey()
    {
        return $this->toKey;
    }

}