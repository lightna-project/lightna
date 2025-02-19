<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\App\Query;

use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Combine;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use Lightna\Engine\App\Context;
use Lightna\Engine\App\ObjectA;
use Lightna\Engine\App\Project\Database;

abstract class EavAbstract extends ObjectA
{
    public const ENTITY_TYPE = self::ENTITY_TYPE;
    public const ENTITY_TABLE = self::ENTITY_TABLE;
    protected Database $db;
    protected Context $context;
    protected array $attributes;
    protected array $attributesById;

    protected function defineAttributes(): void
    {
        $select = $this->db
            ->select(['a' => 'eav_attribute'])
            ->where(['a.entity_type_id = ?' => $this::ENTITY_TYPE]);

        $this->attributes = $this->db->fetch($select, 'attribute_code');

        foreach ($this->attributes as $attribute) {
            $this->attributesById[$attribute['attribute_id']] = $attribute;
        }
    }

    /** @noinspection PhpUnused */
    protected function defineAttributesById(): void
    {
        $this->defineAttributes();
    }

    public function getAttributeValues(array $entityIds, array $attributeCodes, array $rawValueAttributes = []): array
    {
        $attributes = $this->getAttributeValuesRaw($entityIds, $attributeCodes);
        $this->processRawValueAttributes($attributes, $rawValueAttributes);
        $this->processAttributeOptions($attributes);

        return $attributes;
    }

    public function getAttributeValuesRaw(array $entityIds, array $attributeCodes): array
    {
        $attributes = [];
        $rows = $this->db->fetch($this->getAttributeValuesSelect($entityIds, $attributeCodes));

        // Squash default and store specific
        foreach ($rows as $row) {
            $attributes[$row['entity_id']][$row['attribute_code']] = $row;
        }

        return $attributes;
    }

    protected function processRawValueAttributes(array &$attributeValues, array $rawValueAttributes = []): void
    {
        foreach ($attributeValues as $id => $values) {
            foreach ($rawValueAttributes as $code) {
                if (isset($values[$code])) {
                    $attributeValues[$id][$code . 'Raw'] = $values[$code]['value'];
                }
            }
        }
    }

    protected function processAttributeOptions(array &$attributesBatch): void
    {
        $options = $this->getOptions();
        foreach ($attributesBatch as &$attributes) {
            foreach ($attributes as &$data) {
                if (!is_array($data)) {
                    // Skip raw values
                    continue;
                }

                $isMultiselect = $this->attributesById[$data['attribute_id']]['frontend_input'] === 'multiselect';
                $values = $isMultiselect ? explode(',', (string)$data['value']) : [$data['value']];

                foreach ($values as &$value) {
                    $value = $options[$data['attribute_id']][$value] ?? $value;
                }
                $data = implode(', ', $values);
            }
        }
    }

    public function getOptions(): array
    {
        return merge(
            $this->getDefaultOptions(),
            $this->getBooleanOptions(),
        );
    }

    public function getDefaultOptions(): array
    {
        $options = [];
        foreach ($this->db->fetch($this->getOptionsSelect()) as $row) {
            $options[$row['attribute_id']][$row['option_id']] = $row['value'];
        }

        return $options;
    }

    protected function getBooleanOptions(): array
    {
        $options = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute['frontend_input'] === 'boolean') {
                $options[$attribute['attribute_id']] = [
                    0 => _phrase('No'),
                    1 => _phrase('Yes'),
                ];
            }
        }

        return $options;
    }

    protected function getOptionsSelect(): Select
    {
        $ids = [];
        foreach ($this->attributes as $attribute) {
            $ids[] = $attribute['attribute_id'];
        }

        $select = $this->db->select()
            ->from(['o' => 'eav_attribute_option'])
            ->columns(['option_id', 'attribute_id'])
            ->join(
                ['v' => 'eav_attribute_option_value'],
                'v.option_id = o.option_id',
                ['store_id', 'value']
            )
            ->order('store_id');

        $select->where
            ->in('store_id', [0, $this->context->scope])
            ->in('attribute_id', $ids);

        return $select;
    }

    protected function getAttributeValuesSelect(array $ids, array $attributeCodes): AbstractPreparableSql
    {
        $attributes = $this->attributes;
        if ($attributeCodes) {
            $attributes = array_intersect_key($attributes, array_flip($attributeCodes));
        }

        $attributeByType = [];
        foreach ($attributes as $attribute) {
            $attributeByType[$attribute['backend_type']][] = $attribute['attribute_id'];
        }

        $union = [];
        foreach ($attributeByType as $type => $attributeIds) {
            if ($type === 'static' || empty($attributeIds)) {
                continue;
            }

            $union[] = $this->getTypeAttributeValuesSelect($type, $attributeIds, $ids);
        }

        $mainSelect = new Combine();
        foreach ($union as $select) {
            $mainSelect->union($select);
        }

        return $mainSelect;
    }

    protected function getTypeAttributeValuesSelect(
        string $type,
        array $attributeIds,
        array $entityIds,
    ): AbstractPreparableSql {
        $select = $this->db
            ->select(['entity' => $this::ENTITY_TABLE])
            ->columns(['entity_id'])
            ->join(
                ['av' => $this::ENTITY_TABLE . '_' . $type],
                'av.entity_id = entity.entity_id',
                ['attribute_id', 'store_id', 'value'])
            ->join(
                ['a' => 'eav_attribute'],
                'a.attribute_id = av.attribute_id',
                ['attribute_code'])
            // 0 (default) must be first
            ->order('store_id');

        $select->where
            ->in('av.store_id', [0, $this->context->scope])
            ->in('a.attribute_id', $attributeIds)
            ->in('entity.entity_id', $entityIds);

        return $select;
    }

    public function joinAttribute(string $code, Select $select): string
    {
        $id = $this->attributes[$code]['attribute_id'];
        $table = $this::ENTITY_TABLE . '_' . $this->attributes[$code]['backend_type'];
        $alias = $code . '_attr';
        $aliasDef = $code . '_attr_def';
        $valueExpr = new Expression("ifnull({$alias}.value, {$aliasDef}.value)");

        $select
            ->join(
                [$alias => $table],
                new Expression(
                    "{$alias}.attribute_id = ?"
                    . " and {$alias}.entity_id = e.entity_id"
                    . " and {$alias}.store_id = ?",
                    $id,
                    $this->context->scope,
                ),
                [],
                SELECT::JOIN_LEFT,
            )
            ->join(
                [$aliasDef => $table],
                new Expression(
                    "{$aliasDef}.attribute_id = ?"
                    . " and {$aliasDef}.entity_id = e.entity_id"
                    . " and {$aliasDef}.store_id = 0",
                    $id,
                ),
                [$code => $valueExpr],
                SELECT::JOIN_LEFT,
            );

        return $valueExpr->getExpression();
    }

    public function joinUrl(Select $select): void
    {
        $select->join(
            ['u' => 'url_rewrite'],
            new Expression(
                'u.entity_id = e.entity_id and u.entity_type = ? and u.store_id = ? and redirect_type = 0',
                $this->getEntityUrlType(),
                $this->context->scope,
            ),
            ['url' => 'request_path'],
        );
    }

    protected function getEntityUrlType(): string
    {
        return match (static::ENTITY_TYPE) {
            3 => 'category',
            4 => 'product',
        };
    }
}
