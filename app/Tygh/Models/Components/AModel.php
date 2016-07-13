<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

namespace Tygh\Models\Components;

use Tygh\Languages\Languages;
use Tygh\Models\Components\Query\Fields;
use Tygh\Models\Components\Query\Sorting;
use Tygh\Models\Components\Query\Joins;
use Tygh\Models\Components\Query\Condition;
use Tygh\Models\Components\Query\Limit;
use Tygh\Models\Components\Relation;
use Tygh\Navigation\LastView;

abstract class AModel implements IModel, \IteratorAggregate, \ArrayAccess
{

    protected static $models = array();

    protected $id;

    protected $attributes = array();

    protected $current_attributes = array();

    /**
     * Relations schema.
     *
     * Example:
     * array(
     *     'companies' => array(Relation::HAS_MANY, 'Tygh\Models\Company', 'plan_id'),
     *     'category' => array(Relation::HAS_ONE, 'Tygh\Models\Category', 'category_id'),
     *     'companiesCount' => array(Relation::HAS_MANY, 'Tygh\Models\Company', 'plan_id', null, array(
     *         'get_count' => true,
     *     )),
     * );
     *
     * @var array
     */
    protected $relations = array();

    protected $related = array();

    protected $params = array();

    public function __construct($params = array(), $attributes = array())
    {
        $this->params = array_merge(array(
            'lang_code' => CART_LANGUAGE,
        ), $params);

        if (!empty($attributes)) {
            $this->load($attributes);
        }
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        } elseif (isset($this->related[$name])) {
            return $this->related[$name];
        } elseif (isset($this->relations[$name])) {
            return $this->getRelated($name);
        }

        return null;
    }

    public function __isset($name)
    {
        if (isset($this->attributes[$name])) {
            return true;
        } elseif (isset($this->related[$name])) {
            return true;
        } elseif (isset($this->relations[$name])) {
            return $this->getRelated($name) !== null;
        }
    }

    public function __unset($name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * Returns whether there is an element at the specified offset.
     * This method is required by the interface ArrayAccess.
     *
     * @param  mixed   $offset the offset to check on
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Returns the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     *
     * @param  integer $offset the offset to retrieve element.
     * @return mixed   the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Sets the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     *
     * @param integer $offset the offset to set element
     * @param mixed   $item   the element value
     */
    public function offsetSet($offset, $item)
    {
        $this->$offset = $item;
    }

    /**
     * Unsets the element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Returns an iterator for traversing the attributes in the model.
     * This method is required by the interface IteratorAggregate.
     *
     * @return CMapIterator an iterator for traversing the items in the list.
     */
    public function getIterator()
    {
        return new Iterator($this->attributes);
    }

    public static function model($params = array())
    {
        $class = get_called_class();
        $hash = md5(serialize($params));

        if (!isset(static::$models[$class][$hash])) {
            static::$models[$class][$hash] = new static($params);
        }

        return static::$models[$class][$hash];
    }

    public function findMany($params = array())
    {
        $this->beforeFind($params);

        $params = array_merge($this->params, $params);

        // Init filter
        if ($last_view_object_name = $this->getLastViewObjectName()) {
            $params = LastView::instance()->update($last_view_object_name, $params);
        }

        $fields    = new    Fields($this, $params);
        $sorting   = new   Sorting($this, $params);
        $joins     = new     Joins($this, $params);
        $condition = new Condition($this, $params, $joins);

        $this->prepareQuery($params, $fields->result, $sorting->result, $joins->result, $condition->result);

        $limit = new Limit($this, $params, $joins, $condition);

        if (!empty($params['get_count']) && isset($params['total_items'])) {
            return $params['total_items'];
        }

        $query_foundation =
            " FROM " . $this->getTableName()
            . $joins->get()
            . $condition->get()
            . $sorting->get()
            . $limit->get()
        ;

        if (!empty($params['get_ids'])) {
            return db_get_fields("SELECT " . $this->getTableName() . "." . $this->getPrimaryField() . $query_foundation);
        }

        $items = db_get_array("SELECT " . $fields->get() . $query_foundation);

        $this->gatherAdditionalItemsData($items, $params);

        if (!empty($params['to_array'])) {
            $models = $items;
        } else {
            $models = $this->loadMany($items, true);
        }

        if (!empty($params['return_params'])) {
            return array($models, $params);
        }

        return $models;
    }

    public function find($id, $params = array())
    {
        if (is_array($id) && empty($params)) {
            $params = $id;
            $id = 0;
        }

        if (!empty($id)) {
            $params['ids'] = $id;
        }

        $params['limit'] = 1;

        $items = $this->findMany($params);

        return reset($items);
    }

    public function findAll($params = array())
    {
        return $this->findMany($params);
    }

    public function attributes($attributes = array())
    {
        if (!empty($attributes)) {
            if (!is_array($attributes) && is_a($attributes, 'Tygh\Models\IModel')) {
                $attributes = $attributes->attributes();
            }

            if (is_array($attributes)) {
                $this->attributes = array_merge($this->attributes, $attributes);
            }
        }

        return $this->attributes;
    }

    public function getRelations()
    {
        return array();
    }

    public function save()
    {
        if ($this->beforeSave()) {
            $result = $this->isNewRecord() ? $this->insert() : $this->update();

            $this->load($this->find($this->id));

            $this->afterSave();

            return $result;
        }

        return false;
    }

    public function delete()
    {
        $is_deleted = false;

        if (!$this->isNewRecord() && $this->beforeDelete()) {

            $table_name = $this->getTableName();
            $description_table_name = $this->getDescriptionTableName();
            $primary_field = $this->getPrimaryField();

            $is_deleted = db_query(
                sprintf('DELETE FROM %s WHERE %s = ?s', $table_name, $primary_field),
                $this->id
            );

            if ($is_deleted && !empty($description_table_name)) {
                $is_deleted = db_query(
                    sprintf("DELETE FROM %s WHERE %s = ?s", $description_table_name, $primary_field),
                    $this->id
                );
            }

            if ($is_deleted) {
                $this->id = null;
                $this->afterDelete();
            }
        }

        return $is_deleted;
    }

    public function deleteMany($params)
    {
        $models = $this->findMany($params);

        foreach ($models as $model) {
            $model->delete();
        }

        return true;
    }

    /**
     * Link other model with this
     * @param  string $name          Relations name
     * @param  IModel $related_model Model instance
     * @return bool
     */
    public function link($name, IModel $related_model)
    {
        if ($this->relations[$name]) {
            $relation = new Relation;
            $relation->setOwner($this);
            call_user_func_array(array($relation, 'setRule'), $this->relations[$name]);

            return $relation->link($related_model);
        }

        return false;
    }

    public function getParams()
    {
        return $this->params;
    }

    protected function insert()
    {
        $table_name = $this->getTableName();
        $description_table_name = $this->getDescriptionTableName();
        $primary_field = $this->getPrimaryField();

        $_data = $this->prepareAttributes();

        $result = db_query("INSERT INTO $table_name ?e", $_data);

        if ($this->primaryAutoIncrement()) {
            $this->id = $result;
        } else {
            $this->id = $this->{$primary_field};
        }

        if (!empty($description_table_name)) {
            $_data[$primary_field] = $this->id;
            foreach ($this->getLangCodes() as $_data['lang_code']) {
                db_query("INSERT INTO $description_table_name ?e", $_data);
            }
        }

        $this->{$primary_field} = $this->id;

        return true;
    }

    protected function update()
    {
        $table_name = $this->getTableName();
        $primary_field = $this->getPrimaryField();
        $description_table_name = $this->getDescriptionTableName();

        $_data = $this->prepareAttributes();

        db_query("UPDATE $table_name SET ?u WHERE $primary_field = ?s", $_data, $this->id);

        if (!empty($description_table_name)) {
            db_query(
                "UPDATE $description_table_name SET ?u WHERE $primary_field = ?s AND lang_code = ?s",
                $_data, $this->id, $this->params['lang_code']
            );
        }

        return true;
    }

    public function isNewRecord()
    {
        return empty($this->id);
    }

    public function getFields($params)
    {
        return array(
            $this->getTableName() . '.*',
        );
    }

    protected function prepareAttributes()
    {
        $attributes = $this->attributes;

        if ($this->primaryAutoIncrement()) {
            if (isset($attributes[$this->getPrimaryField()]) && !empty($this->id)) {
                unset($attributes[$this->getPrimaryField()]);
            }
        }

        return $attributes;
    }

    protected function load($attributes, $find = false)
    {
        $primary_field = $this->getPrimaryField();

        if (isset($attributes[$primary_field])) {
            $this->id = $attributes[$primary_field];
        }

        $this->current_attributes = $this->attributes($attributes);

        $this->relations = $this->getRelations();

        if ($find) {
            $this->afterFind();
        }

        return $this;
    }

    protected function loadMany($items, $find = false)
    {
        $models = array();

        foreach ($items as $item) {
            $model = new static($this->params);
            $model->load($item, $find);

            $models[] = $model;
        }

        return $models;
    }

    protected function getRelated($name)
    {
        $relation = new Relation;
        $relation->setOwner($this);
        call_user_func_array(array($relation, 'setRule'), $this->relations[$name]);
        $this->related[$name] = $relation->find();

        return $this->related[$name];
    }

    protected function getLangCodes()
    {
        return array_keys(Languages::getAll());
    }

    /**
     * Setting DB table name of related multi-lamguage data.
     * @return str
     */
    public function getDescriptionTableName()
    {
        return '';
    }

    /**
     * Autoincrement enabled?
     * @return bool
     */
    protected function primaryAutoIncrement()
    {
        return true;
    }

    /**
     * Getting default sort directino.
     * @return str asc or desc
     */
    public function getSortDefaultDirection()
    {
        return 'asc';
    }

    /**
     * Ability to rewrite query parts.
     *
     * @param array  $params    Params
     * @param array  $fields    Fields
     * @param array  $sorting   Sorting
     * @param array  $joins     Joins
     * @param array  $condition Condition
     * @param string $limit     Limit
     */
    public function prepareQuery(&$params, &$fields, &$sorting, &$joins, &$condition)
    {
    }

    /**
     * Setting extra conditions
     *
     * @param  array $params Params
     * @return array Conditions
     */
    public function getExtraCondition($params)
    {
        return array();
    }

    /**
     * Gather additional items data. Items modified by link.
     *
     * @param  array &$items Selected items
     * @param array $params Params
     */
    protected function gatherAdditionalItemsData(&$items, $params)
    {
    }

    /**
     * Getting query joins
     * @param  array $params params
     * @return array
     */
    public function getJoins($params)
    {
        $joins = array();

        $description_table_name = $this->getDescriptionTableName();

        if (!empty($description_table_name)) {

            $table_name = $this->getTableName();
            $primary_field = $this->getPrimaryField();

            $joins[] = db_quote(
                " LEFT JOIN $description_table_name"
                . " ON $description_table_name.$primary_field = $table_name.$primary_field"
                . " AND $description_table_name.lang_code = ?s", $this->params['lang_code']
            );
        }

        return $joins;
    }

    /**
     * Getting search fields schema. Available keys: number, range, in, not_in, string, text, time. For more info see: Condition::prepare()
     * @return array
     */
    public function getSearchFields()
    {
        return array();
    }

    public function getSortFields()
    {
        return array(
            'id' => $this->getTableName() . '.' . $this->getPrimaryField()
        );
    }

    public function getLastViewObjectName()
    {
        return false; // disabled by default
    }

    // Events

    public function beforeFind(&$params)
    {
    }

    public function afterFind()
    {
    }

    public function beforeSave()
    {
        return true;
    }

    public function afterSave()
    {
    }

    public function beforeDelete()
    {
        return true;
    }

    public function afterDelete()
    {
    }

}
