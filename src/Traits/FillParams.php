<?php
/**
 * @author XJ.
 * Date: 2023/7/3 0003
 */

namespace Fatbit\FormRequestParam\Traits;

trait FillParams
{
    use ToArrayJson;
    public function __construct(?array $data = null)
    {
        if ($data) {
            $vars = get_class_vars(static::class);
            foreach ($vars as $key => $val) {
                if (isset($data[$key])) {
                    $this->{$key} = $data[$key];
                }
            }
        }
        if (get_parent_class(self::class) !== false && method_exists(parent::class, '__construct')) {
            parent::__construct($data);
        }
    }

    /**
     * 批量创建自身
     * Created by XJ.
     * Date: 2021/11/15
     *
     * @param array $data 二维数组
     *
     * @return static[]
     */
    public static function batchCreateBySelf(array $data)
    {
        $res = [];
        foreach ($data as $datum) {
            $res[] = new static($datum);
        }

        return $res;
    }

    /**
     * 属性函数化
     *
     * @author XJ.
     * Date: 2023/1/13 0013
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed|void
     */
    public function __call(string $name, array $arguments)
    {

        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        if (get_parent_class(self::class) !== false && method_exists(parent::class, '__call')) {
            return parent::__call($name, $arguments);
        }
    }
}