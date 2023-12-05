<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam\Abstracts;

interface FormRequestParamInterface
{
    /**
     * 获取验证规则
     *
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array
     */
    public static function getRules(): array;

    /**
     * 字段映射关系
     *
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array
     */
    public static function getFieldMapping(): array;

    /**
     * 获取所有可以的错误信息
     *
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array
     */
    public static function getAttributes(): array;

    /**
     * 获取定义的所有错误信息
     *
     * @author XJ.
     * @Date   2023/8/28 0028
     * @return array
     */
    public static function getMessages(): array;

}