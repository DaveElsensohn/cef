<?php

/*
 * This file is part of cef (a 4klift component).
 *
 * Copyright (c) 2017 Deasil Works Inc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace deasilworks\cef;

use JMS\Serializer\Annotation as JMS;

/**
 * Class EntityCollection
 * @package deasilworks\cef
 */
class EntityCollection extends CEFData implements \Iterator
{
    /**
     * Class name of values
     * @JMS\Exclude()
     * @var string
     */
    protected $value_class = EntityModel::class;

    /**
     * @var array
     */
    protected $collection = array();

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * EntityCollection constructor.
     */
    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * To JSON
     *
     * @param $serialize_null boolean serialize null values?
     * @return string
     */
    public function toJson($serialize_null = true)
    {
        return $this->serialize($this->collection, 'json', $serialize_null);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return EntityCollection
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Model Factory
     *
     * @return mixed
     * @throws \Exception
     */
    public function getModel()
    {
        $name_path = $this->getModelClass();
        $model = new $name_path();

        if ($model instanceof EntityModel) {
        } else {
            throw new \Exception($this->value_class . ' is not an instance of EntityModel.');
        }

        return $model;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->value_class;
    }

    /**
     * @return string
     */
    public function getValueClass()
    {
        return $this->value_class;
    }

    /**
     * @param string $value_class
     * @return EntityCollection
     */
    public function setModelClass($value_class)
    {
        $this->value_class = $value_class;
        return $this;
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Creates all entities from an array at once.
     * These are an array of entries that need to be converted to models.
     *
     * @param array $collection
     * @return EntityCollection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
        $this->setCount(count($this->collection));

        return $this;
    }

    /**
     * Adds a single entity
     *
     * @param array $entity
     * @return EntityCollection
     */
    public function addEntity($entity)
    {
        $this->collection[] = $entity;
        $this->setCount(count($this->collection));
        return $this;
    }

    /**
     * Iterator rewind
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     * Iterator current
     * @return mixed
     */
    public function current() {
        return $this->collection[$this->position];
    }

    /**
     * Iterator key
     * @return int
     */
    public function key() {
        return $this->position;
    }

    /**
     *  Iterator next
     */
    public function next() {
        ++$this->position;
    }

    /**
     * Iterator valid
     * @return bool
     */
    public function valid() {
        return isset($this->collection[$this->position]);
    }
}