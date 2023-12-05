<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam;

use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;

class IdRequestParam extends AbstractFormRequestParam
{
    #[FormRequestRule('required|integer', 'id')]
    public int $id;
}