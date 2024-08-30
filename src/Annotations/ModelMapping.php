<?php
/**
 * @author XJ.
 * @Date   2024/8/16 星期五
 */

namespace Fatbit\FormRequestParam\Annotations;

/**
 * 模型映射
 *
 * @author XJ.
 * @Date   2024/8/19 星期一
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ModelMapping
{

    public function __construct(
        public string  $model,
        public ?string  $filed = null,
        public ?string $modelAlias = null,
    )
    {}

}