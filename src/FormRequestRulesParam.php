<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam;


use Fatbit\FormRequestParam\Abstracts\AbstractParam;

class FormRequestRulesParam extends AbstractParam
{
    /**
     * @var array
     */
    public array $rules;

    /**
     * @var array
     */
    public array $attributes;

    /**
     * @var array
     */
    public array $messages;

    /**
     * @var array
     */
    public array $fieldMapping;

}