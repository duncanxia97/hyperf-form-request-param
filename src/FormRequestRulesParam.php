<?php
/**
 * @author XJ.
 * @Date   2023/8/28 0028
 */

namespace Fatbit\FormRequestParam;


use Fatbit\FormRequestParam\Abstracts\AbstractFormRequestParam;
use Fatbit\FormRequestParam\Abstracts\FormRequestParamInterface;
use Fatbit\FormRequestParam\Annotations\FormRequestArrayRule;
use Fatbit\FormRequestParam\Annotations\FormRequestRule;
use Fatbit\Utils\Params\AbstractParam;

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
     * @var array|array<string, FormRequestFieldMappingParam>
     */
    public array $fieldMapping;

    /**
     * 通过FormRequestRule追加规则
     *
     * @author XJ.
     * @Date   2026/2/11
     *
     * @param string          $field
     * @param FormRequestRule $formRequestRule
     */
    public function appendByFormRequestRule(string $field, FormRequestRule $formRequestRule)
    {
        $this->rules[$field]      = $formRequestRule->rule;
        $this->attributes[$field] = $formRequestRule->attribute;
        if ($formRequestRule->messages) {
            foreach ($formRequestRule->messages as $rule => $message) {
                $this->messages[$field . '.' . $rule] = $message;
            }
        }
    }

    /**
     * 通过FormRequestArrayRule追加规则
     *
     * @author XJ.
     * @Date   2026/2/11
     *
     * @param string               $field
     * @param FormRequestArrayRule $formRequestArrayRule
     */
    public function appendByFormRequestArrayRule(string $field, FormRequestArrayRule $formRequestArrayRule)
    {
        $filedName = $field;
        if ($formRequestArrayRule->arrayField === true) {
            $filedName .= '.*';
        } elseif (is_string($formRequestArrayRule->arrayField)) {
            $filedName .= '.' . $formRequestArrayRule->arrayField;
        } elseif (is_int($formRequestArrayRule->arrayField)) {
            $filedName .= '.' . ((string)$formRequestArrayRule->arrayField);
        }
        /** @var FormRequestArrayRule $formRequestArrayRule */
        // 如果是 AbstractFormRequestParam 那么实例化获取对应的属性
        if (class_exists($formRequestArrayRule->fieldName) && is_a($formRequestArrayRule->fieldName, AbstractFormRequestParam::class, true)) {
            // 添加数组映射子项
            $this->appendFiledMappingArrayItems(
                $field,
                $formRequestArrayRule,
                new FormRequestFieldMappingParam(
                    [
                        'toKey'        => null,
                        'sourceKey'    => is_string($formRequestArrayRule->arrayField) || is_int($formRequestArrayRule->arrayField) ? $formRequestArrayRule->arrayField : null,
                        'toVal'        => $formRequestArrayRule->fieldName,
                        'propertyType' => $formRequestArrayRule->fieldName,
                        'default'      => $formRequestArrayRule->default,
                    ],
                ),
            );
            $this->appendByFormRequestParamInterface($filedName, $formRequestArrayRule->fieldName, $field);
        } else {
            $fieldName                    = $filedName . '.' . $formRequestArrayRule->fieldName;
            $this->rules[$fieldName]      = $formRequestArrayRule->rule;
            $this->attributes[$fieldName] = $formRequestArrayRule->attribute;
            if ($formRequestArrayRule->messages) {
                foreach ($formRequestArrayRule->messages as $rule => $message) {
                    $this->messages[$fieldName . '.' . $rule] = $message;
                }
            }
        }
    }

    /**
     * 添加字段映射
     *
     * @author XJ.
     * @Date   2026/2/11
     *
     * @param string                       $field
     * @param FormRequestFieldMappingParam $formRequestFieldMappingParam
     */
    public function appendFieldMapping(string $field, FormRequestFieldMappingParam $formRequestFieldMappingParam)
    {
        $this->fieldMapping[$field] = $formRequestFieldMappingParam;
    }

    /**
     * 添加字段映射数组项
     *
     * @author XJ.
     * @Date   2026/2/11
     *
     * @param string                       $field
     * @param FormRequestArrayRule         $formRequestArrayRule
     * @param FormRequestFieldMappingParam $formRequestFieldMappingParam
     */
    public function appendFiledMappingArrayItems(string $field, FormRequestArrayRule $formRequestArrayRule, FormRequestFieldMappingParam $formRequestFieldMappingParam)
    {
        if ($formRequestArrayRule->arrayField === true) {
            $this->fieldMapping[$field]->arrayItems[] = $formRequestFieldMappingParam;
        } elseif (is_string($formRequestArrayRule->arrayField) || is_int($formRequestArrayRule->arrayField)) {
            $this->fieldMapping[$field]->arrayItems[$formRequestArrayRule->arrayField] = $formRequestFieldMappingParam;
        } elseif ($formRequestArrayRule->arrayField === false && $this->fieldMapping[$field]->propertyType === 'object') {
            $this->fieldMapping[$field]->propertyType = $formRequestArrayRule->fieldName;
            $this->fieldMapping[$field]->toVal        = $formRequestArrayRule->fieldName;
        }
    }

    /**
     * 通过FormRequestParamInterface追加规则
     *
     * @author XJ.
     * @Date   2026/2/11
     *
     * @param string                                         $field
     * @param string|class-string<FormRequestParamInterface> $formRequestParam
     */
    public function appendByFormRequestParamInterface(string $field, string $formRequestParam, string $sourceField = null)
    {
        if (class_exists($formRequestParam) && is_a($formRequestParam, FormRequestParamInterface::class, true)) {
            $filedName       = $field . '.';
            $arrayRules      = $formRequestParam::getRules();
            $arrayMessages   = $formRequestParam::getMessages();
            $arrayAttributes = $formRequestParam::getAttributes();
            foreach ($arrayRules as $key => $rule) {
                $this->rules[$filedName . $key] = $rule;
            }
            foreach ($arrayMessages as $key => $message) {
                $this->messages[$filedName . $key] = $message;
            }
            foreach ($arrayAttributes as $key => $attribute) {
                $sourceAttribute                     = $this->attributes[$sourceField] ?? '';
                $this->attributes[$filedName . $key] = (empty($sourceAttribute) ? '' : $sourceAttribute . ' - ') . $attribute;
            }
        }
    }

}