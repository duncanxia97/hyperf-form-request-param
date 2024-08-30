<?php
/**
 * @author XJ.
 * @Date   2024/8/30 星期五
 */

namespace Fatbit\FormRequestParam\Traits;

use Fatbit\FormRequestParam\Annotations\ModelMapping;

trait ParamModelMapping
{
    use GetAttributes;

    protected $modelMapping = [];

    /**
     * 获取模型
     *
     * @author XJ.
     * @Date   2024/8/30 星期五
     * @template T
     *
     * @param string|class-string<T> $model
     *
     * @return object|null|T
     */
    public function getModel(string $model): ?object
    {
        if (empty($this->modelMapping)) {
            $this->loadModelMapping();
        }
        if (isset($this->modelMapping[$model])) {
            return clone $this->modelMapping[$model];
        }

        return null;
    }

    /**
     * 清空模型映射
     *
     * @author XJ.
     * @Date   2024/8/30 星期五
     */
    public function clearModelMapping()
    {
        $this->modelMapping = [];
    }

    /**
     * 加载模型映射
     *
     * @author XJ.
     * @Date   2024/8/30 星期五
     */
    protected function loadModelMapping()
    {
        if (!empty($this->modelMapping)) {
            return;
        }
        foreach (static::getThisProperties() as $thisProperty) {
            $modelMappings = static::getPropertyAttributes($thisProperty, ModelMapping::class);
            foreach ($modelMappings as $modelMapping) {
                if (!isset($this->modelMapping[$modelMapping->model])) {
                    if (!class_exists($modelMapping->model)) {
                        throw new \ErrorException('class "' . $modelMapping->model . '" does not exist.', 500);
                    }
                    $this->modelMapping[$modelMapping->model] = new $modelMapping->model;
                }
                $propertyName                                              = empty($modelMapping->filed) ? $thisProperty->getName(
                ) : $modelMapping->filed;
                $this->modelMapping[$modelMapping->model]->{$propertyName} = $this->{$propertyName};
                if (!empty($modelMapping->modelAlias)) {
                    $modelMapping->modelAlias = &$this->modelMapping[$modelMapping->model];
                }
            }
        }
    }
}