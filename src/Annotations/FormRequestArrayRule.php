<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Annotations;


use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Fatbit\FormRequestParam\Traits\ToArrayJson;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FormRequestArrayRule implements Arrayable, Jsonable
{
    use ToArrayJson;

    /**
     * @param string       $fieldName 字段别称
     * @param array|string $rule      规则
     * @param string       $attribute 属性(中文)
     * @param array        $messages  提示信息
     */
    public function __construct(
        public string       $fieldName,
        public array|string $rule,
        public string       $attribute,
        public array        $messages = [],
    )
    {
    }

}