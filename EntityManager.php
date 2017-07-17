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

use deasilworks\cef\Statement\Simple;
use Pimple\Container;


/**
 * Class EntityManager
 * @package deasilworks\cef
 */
abstract class EntityManager
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * A ResultContainer class
     *
     * @var string $collection_class
     */
    protected $collection_class = ResultContainer::class;

    /**
     * @var
     */
    protected $config;

    /**
     * @return Container
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param Container $app
     * @return EntityManager
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->getApp()['config']->get('cassandra');
        }

        return $this->config;
    }

    /**
     * @param array $config
     * @return EntityManager
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get's the model associated witht the collection
     *
     * @return EntityModel
     */
    public function getModel()
    {
        if ($this->collection_class) {
            $collection = $this->getCollection();
            /** @var EntityModel $model */
            $model = $collection->getModel();
            $model->setEntityManager($this);
            return $model;
        } else {
            return new EntityModel();
        }
    }

    /**
     * Collection Factory
     *
     * @return \DeasilWorks\CEF\ResultContainer
     * @throws \Exception
     */
    public function getCollection()
    {
        $collection_class = $this->getCollectionClass();
        $collection = new $collection_class();

        if ($collection instanceof ResultContainer) {
            return $collection;
        } else {
            throw new \Exception('E5000', $collection_class . ' is not an instance of deasilworks\CEF\StatementManager.');
        }
    }

    /**
     * @param string $collection_class
     * @return EntityManager
     */
    public function setCollectionClass($collection_class)
    {
        $this->collection_class = $collection_class;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollectionClass()
    {
        return $this->collection_class;
    }


    /**
     * Statement Manager Factory
     * @deprecated use getStatementManager
     *
     * @param string $statement_type
     * @throws \Exception
     */
    function load($statement_type = 'Simple')
    {
        throw new \Exception('Method load is deprecated on EntityManager. Use getStatementManager.');
    }

    /**
     * Statement Manager Factory
     *
     * @param string $statement_class
     * @return StatementManager
     * @throws \Exception
     */
    function getStatementManager($statement_class = Simple::class)
    {
        $statement_manager = new $statement_class();

        $collection_class = $this->getCollectionClass();

        if ($statement_manager instanceof StatementManager) {
            $statement_manager->setApp($this->getApp());
            $statement_manager->setConfig($this->getConfig());
            $statement_manager->setResultContainerClass($collection_class);
            $statement_manager->setEntityManager($this);
        } else {
            throw new \Exception($statement_class . ' is not an instance of deasilworks\CEF\StatementManager.');
        }

        return $statement_manager;
    }

}