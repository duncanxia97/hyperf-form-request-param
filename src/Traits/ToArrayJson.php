<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Traits;

use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;

/**
 * @implements Arrayable
 * @implements Jsonable
 */
trait ToArrayJson
{

    /**
     * 转换成数组
     * Created by XJ.
     * Date: 2021/11/12
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * 转换成json
     * Created by XJ.
     * Date: 2021/11/12
     *
     * @return array|object|string|null
     */
    public function toJson($options = 256)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Created by XJ.
     * Date: 2022/1/7
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->toJson();
    }
}