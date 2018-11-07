<?php

namespace MaxKosYii2\AdditionalExt\traits;

use yii\base\Arrayable;
use yii\helpers\ArrayHelper;
use yii\web\Linkable;

/**
 * Trait ModelAdditiona
 * @package MaxKosYii2\AdditionalExt\traits
 */
trait ModelAdditiona
{

    public function toRestArray($collectionName = null,  array $fields = [], array $expand = [], $recursive = true)
    {

//        if (is_array(static::$collectionFields) && $collectionName)
//        {
//            $_fields = array_keys(array_filter(static::$collectionFields,function($collections) use ($collectionName) {
//                return in_array($collectionName, $collections);
//            }));
//            if ($fields)
//                $fields = array_intersect($fields, $_fields);
//            else
//                $fields = $_fields;
//        }
        return $this->toArray($fields, $expand, $recursive, $collectionName);
    }

    /**
     * Converts the model into an array.
     *
     * This method will first identify which fields to be included in the resulting array by calling [[resolveFields()]].
     * It will then turn the model into an array with these fields. If `$recursive` is true,
     * any embedded objects will also be converted into arrays.
     * When embeded objects are [[Arrayable]], their respective nested fields will be extracted and passed to [[toArray()]].
     *
     * If the model implements the [[Linkable]] interface, the resulting array will also have a `_link` element
     * which refers to a list of links as specified by the interface.
     *
     * @param array $fields the fields being requested.
     * If empty or if it contains '*', all fields as specified by [[fields()]] will be returned.
     * Fields can be nested, separated with dots (.). e.g.: item.field.sub-field
     * `$recursive` must be true for nested fields to be extracted. If `$recursive` is false, only the root fields will be extracted.
     * @param array $expand the additional fields being requested for exporting. Only fields declared in [[extraFields()]]
     * will be considered.
     * Expand can also be nested, separated with dots (.). e.g.: item.expand1.expand2
     * `$recursive` must be true for nested expands to be extracted. If `$recursive` is false, only the root expands will be extracted.
     * @param bool $recursive whether to recursively return array representation of embedded objects.
     * @return array the array representation of the object
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true, $collectionName = null)
    {
        if (is_array(static::$collectionFields) && $collectionName)
        {
            $_fields = array_keys(array_filter(static::$collectionFields,function($collections) use ($collectionName) {
                return in_array($collectionName, $collections);
            }));
            if ($fields)
                $fields = array_intersect($fields, $_fields);
            else
                $fields = $_fields;
        }
        if (is_array(static::$collectionExpand) && $collectionName)
        {
            $_expand = array_keys(array_filter(static::$collectionExpand,function($collections) use ($collectionName) {
                return in_array($collectionName, $collections);
            }));
            if ($expand)
                $expand = array_intersect($expand, $_expand);
            else
                $expand = $_expand;
        }

        $data = [];
        foreach ($this->resolveFields($fields, $expand) as $field => $definition) {
            $attribute = is_string($definition) ? $this->$definition : $definition($this, $field);


            if ($recursive) {
                $nestedFields = $this->extractFieldsFor($fields, $field);
                $nestedExpand = $this->extractFieldsFor($expand, $field);
                if ($attribute instanceof Arrayable) {
                    $attribute = $attribute->toArray($nestedFields, $nestedExpand, $recursive, $collectionName);
                } elseif (is_array($attribute)) {
                    $attribute = array_map(
                        function ($item) use ($nestedFields, $nestedExpand, $recursive, $collectionName){
                            if ($item instanceof Arrayable) {
                                return $item->toArray($nestedFields, $nestedExpand, $recursive, $collectionName);
                            }
                            return $item;
                        },
                        $attribute
                    );
                }
            }
            $data[$field] = $attribute;
        }

        if ($this instanceof Linkable) {
            //$data['_links'] = Link::serialize($this->getLinks());
        }
        unset($data['_links']);
        return $recursive ? ArrayHelper::toArray($data) : $data;
    }
}