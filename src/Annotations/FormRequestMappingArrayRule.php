<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Annotations;


use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\Utils\Params\Traits\ToArrayJson;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class FormRequestMappingArrayRule extends FormRequestArrayRule
{

    /**
     * @param string|class-string<AbstractFormRequestParam> $fieldName  字段别称
     * @param string                                        $mappingKey 映射字段
     * @param array|string|null                             $rule       规则
     * @param string|null                                   $attribute  属性(中文)
     * @param array                                         $messages   提示信息
     * @param mixed|null                                    $default    默认值
     */
    public function __construct(
        string            $fieldName,
        string            $mappingKey,
        array|string|null $rule = null,
        string|null       $attribute = null,
        array             $messages = [],
        mixed             $default = null,
    ) {
        parent::__construct($fieldName, $rule, $attribute, $messages, $default, $mappingKey);
    }

}