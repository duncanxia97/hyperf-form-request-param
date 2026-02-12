<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;

class UUIDRequestParam extends AbstractFormRequestParam
{
    #[FormRequestRule('required|string', 'id')]
    public string $id;
}