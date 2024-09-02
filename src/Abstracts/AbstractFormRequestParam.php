<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Abstracts;

use Fatbit\FormRequestParam\Traits\FormRequestParam;
use Fatbit\FormRequestParam\Traits\ParamModelMapping;

abstract class AbstractFormRequestParam extends AbstractParam implements FormRequestParamInterface, ParamModelMappingInterface
{
    use FormRequestParam, ParamModelMapping;
}