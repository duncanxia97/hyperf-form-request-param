<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;

class ULIDRequestParam extends AbstractFormRequestParam
{
    #[FormRequestRule('required|string|size:26', 'id')]
    public string $id;
}