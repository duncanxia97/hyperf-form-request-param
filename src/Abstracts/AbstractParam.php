<?php
/**
 * @author XJ.
 * Date: 2023/7/3 0003
 */

namespace Fatbit\FormRequestParam\Abstracts;

use Fatbit\FormRequestParam\Traits\FillParams;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;

abstract class AbstractParam implements Arrayable, Jsonable
{
    use FillParams;

}