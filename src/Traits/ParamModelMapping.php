<?php
/**
 * @author XJ.
 * @Date   2024/8/30 星期五
 */

namespace Fatbit\FormRequestParam\Traits;

use Fatbit\FormRequestParam\Abstracts\ParamModelMappingInterface;
use Fatbit\FormRequestParam\Annotations\ArrayModelMapping;
use Fatbit\FormRequestParam\Annotations\ModelMapping;

/**
 * @implements ParamModelMappingInterface
 */
trait ParamModelMapping
{
    use GetAttributes;

    protected $modelMapping       = [];

    protected $arrayModelsMapping = [];

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
    public function toModel(string $modelName): ?object
    {
        if (empty($this->modelMapping)) {
            $this->loadModelMapping();
        }
        if (isset($this->modelMapping[$modelName])) {
            return clone $this->modelMapping[$modelName];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function toModels(array $modelNames): array
    {
        if (empty($this->modelMapping)) {
            $this->loadModelMapping();
        }
        $models = [];
        foreach ($modelNames as $modelName) {
            if (isset($this->modelMapping[$modelName])) {
                $models[] = clone $this->modelMapping[$modelName];
            }
        }

        return $models;
    }

    /**
     * @inheritDoc
     */
    public function toArrayModels(string|array $modelName, ?callable $call = null, bool $isAppend = true): array
    {
        if (empty($this->arrayModelsMapping)) {
            $this->loadModelsMapping();
        }
        if (isset($this->arrayModelsMapping[$modelName])) {
            if (is_string($modelName)) {
                return $this->getArrayModels($modelName, $call);
            }
            if (is_array($modelName)) {
                $models = [];
                foreach ($modelName as $model) {
                    if ($isAppend) {
                        $models = [...$models, ...$this->getArrayModels($model, $call)];
                    } else {
                        $models[$model] = $this->getArrayModels($model, $call);
                    }
                }
                return $models;
            }
        }

        return [];
    }

    /**
     * 获取模型数组
     *
     * @author XJ.
     * @Date   2024/9/2 星期一
     * @template T
     *
     * @param string|class-string<T> $modelName
     * @param callable|null          $call
     *
     * @return array|T[]
     */
    protected function getArrayModels(string $modelName, ?callable $call = null): array
    {
        return array_map(
            function ($model) use ($call) {
                $model = clone $model;
                if (!is_null($call)) {
                    $call($model);
                }

                return $model;
            },
            $this->arrayModelsMapping[$modelName]
        );
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
     * 清空数组模型映射
     *
     * @author XJ.
     * @Date   2024/8/30 星期五
     */
    public function clearArrayModelMapping()
    {
        $this->arrayModelsMapping = [];
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
                $modelMapping = static::getReflectionAttributeInstance($modelMapping);
                /** @var ModelMapping $modelMapping */
                if (!isset($this->modelMapping[$modelMapping->model])) {
                    if (!class_exists($modelMapping->model)) {
                        throw new \ErrorException('class "' . $modelMapping->model . '" does not exist.', 500);
                    }
                    $this->modelMapping[$modelMapping->model] = new $modelMapping->model;
                }
                $propertyName                                              = empty($modelMapping->filed) ?
                    $thisProperty->getName() : $modelMapping->filed;
                $this->modelMapping[$modelMapping->model]->{$propertyName} = $this->{$thisProperty->getName()};
                if (!empty($modelMapping->modelAlias)) {
                    $modelMapping->modelAlias = &$this->modelMapping[$modelMapping->model];
                }
            }
        }
    }

    /**
     * 加载数组模型映射
     *
     * @author XJ.
     * @Date   2024/9/2 星期一
     * @throws \ErrorException
     */
    protected function loadModelsMapping()
    {
        if (!empty($this->arrayModelsMapping)) {
            return;
        }
        foreach (static::getThisProperties() as $thisProperty) {
            $arrayModelMappings = static::getPropertyAttributes($thisProperty, ArrayModelMapping::class);
            foreach ($arrayModelMappings as $arrayModelMapping) {
                $arrayModelMapping = static::getReflectionAttributeInstance($arrayModelMapping);
                /** @var ArrayModelMapping $arrayModelMapping */
                if (!isset($this->arrayModelsMapping[$arrayModelMapping->model])) {
                    if (!class_exists($arrayModelMapping->model)) {
                        throw new \ErrorException('class "' . $arrayModelMapping->model . '" does not exist.', 500);
                    }
                    $this->arrayModelsMapping[$arrayModelMapping->model] = [];
                }
                $model = new $arrayModelMapping->model;
                if (!empty($arrayModelMapping->fixed)) {
                    foreach ($arrayModelMapping->fixed as $field => $value) {
                        $model->{$field} = $value;
                    }
                }
                foreach ($this->{$thisProperty->getName()} as $value) {
                    $newModel = clone $model;
                    if (is_string($arrayModelMapping->filedMapping)) {
                        $newModel->{$arrayModelMapping->filedMapping} = $value;
                    } else if (is_null($arrayModelMapping->filedMapping)) {
                        $newModel->{$thisProperty->getName()} = $value;
                    } else if (is_array($arrayModelMapping->filedMapping)) {
                        foreach ($arrayModelMapping->filedMapping as $field => $mapping) {
                            foreach ($value as $k => $v) {
                                if (is_numeric($field) && $mapping == $k) {
                                    $newModel->{$k} = $v;
                                } else if (is_string($field) && $field == $k) {
                                    $newModel->{$mapping} = $v;
                                }
                            }
                        }
                    }
                    $this->arrayModelsMapping[$arrayModelMapping->model] = $newModel;
                }
                if (!empty($arrayModelMapping->modelAlias)) {
                    $arrayModelMapping->modelAlias = &$this->arrayModelsMapping[$arrayModelMapping->model];
                }
            }
        }
    }
}