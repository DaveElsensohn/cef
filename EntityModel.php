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

use JMS\Serializer\Annotation\Exclude;
use deasilworks\cef\StatementBuilder\InsertModel;
use deasilworks\cef\Statement\Simple;

/**
 * Class EntityModel
 *
 * This is the generic entity model and allows the setting of arbitrary properties.
 *
 * @package deasilworks\cef
 */
class EntityModel extends CEFData
{
    /**
     * @Exclude()
     * @var string
     */
    protected $table_name;

    /**
     * @Exclude()
     * @var EntityManager
     */
    protected $entity_manager;

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @param string $table_name
     * @return EntityModel
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;
        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    /**
     * @param EntityManager $entity_manager
     * @return EntityModel
     */
    public function setEntityManager(EntityManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
        return $this;
    }

    /**
     * Saves a model to the database
     *
     * @return EntityCollection
     * @throws \Exception
     */
    public function save()
    {
        if (!$em = $this->getEntityManager()) {
            throw new \Exception('Model can not be inserted without an Entity Manager (setEntityManager).');
        }

        if (!$this->getTableName()) {
            throw new \Exception('Model can not be inserted without a defined table name.');
        }

        $sm = $em->getStatementManager(Simple::class);

        /** @var InsertModel $sb */
        $sb = $sm->getStatementBuilder(InsertModel::class);

        return $sm
            ->setStatement($sb->setModel($this))
            ->setConsistency(\Cassandra::CONSISTENCY_LOCAL_ONE)
            ->execute();
    }
}