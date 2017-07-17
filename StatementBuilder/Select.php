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

namespace deasilworks\cef\StatementBuilder;

use deasilworks\cef\StatementBuilder;

/**
 * Class Select
 * @package deasilworks\cef\StatementBuilder
 */
class Select extends StatementBuilder
{
    /**
     * @var string
     */
    protected $type = 'SELECT JSON';

    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var array
     */
    protected $where = array();

    /**
     * To String
     */
    public function __toString()
    {
        return $this->getStatement();
    }

    /**
     * @return string
     */
    public function getStatement()
    {
        $cql = '';

        if ($this->getType() == 'SELECT' || $this->getType() == 'SELECT JSON') {
            $cql = $this->getType() . ' ' . $this->getColumns();
        }
        $cql .= ' FROM ' . $this->getFrom();

        if ($where = $this->getWhere()) {
            $cql .= ' WHERE ' . $where;
        }

        return $cql;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Select
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $columns_string = implode(', ', $this->columns);

        if (!$columns_string) {
            $columns_string = '*';
        }

        return $columns_string;
    }

    /**
     * @param array $columns
     * @return Select
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return string
     */
    public function getWhere()
    {
        $where_string = implode(' and ', $this->where);

        return $where_string;
    }

    /**
     * @param array $where
     * @return Select
     */
    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

}