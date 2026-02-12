<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Annotations;


use Fatbit\Utils\Params\Traits\ToArrayJson;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FormRequestRule implements Arrayable, Jsonable
{
    use ToArrayJson;

    /**
     * @param array|string $rule      规则
     * @param string       $attribute 属性(中文)
     * @param array        $messages  提示信息
     * @param string|null  $fieldName 字段别称
     * @param mixed|null   $default   默认值
     */
    public function __construct(
        public array|string $rule,
        public string       $attribute,
        public array        $messages = [],
        public ?string      $fieldName = null,
        public mixed        $default = null,
    ) {
    }

}