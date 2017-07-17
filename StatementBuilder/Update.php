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
 * Class Update
 * @package deasilworks\cef\StatementBuilder
 */
class Update extends StatementBuilder
{
    /**
     * @var string
     */
    protected $type = 'UPDATE';

    /**
     * @var array
     */
    protected $col_val_map = array();

    /**
     * @var array
     */
    protected $where = array();

    /**
     * @var bool
     */
    protected $if_exists = false;

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

        $cql .= $this->getType() . ' ' . $this->getFrom() . ' SET ' . $this->getColValMap();

        if ($where = $this->getWhere()) {
            $cql .= ' WHERE ' . $where;

            if ($this->isIfExists()) {
                $cql .= ' IF EXISTS';
            }
        }

        return $cql;
    }

    /**
     * @return string | null
     */
    public function getColValMap()
    {
        $set_string = null;

        foreach ($this->col_val_map as $col => $val) {
            $set_string ? $set_string .= ', ' : false;

            if (is_string($val)) {
                $val = '\'' . str_replace("'","''", $val) . '\'';
            }


            $set_string .= $col . ' = ' . $val;
        }

        return $set_string;
    }

    /**
     * @param array $col_val_map
     * @return Update
     */
    public function setColValMap($col_val_map)
    {
        $this->col_val_map = $col_val_map;
        return $this;
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
     * @return Update
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return Update
     */
    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIfExists()
    {
        return $this->if_exists;
    }

    /**
     * @param bool $if_exists
     * @return Update
     */
    public function setIfExists($if_exists)
    {
        $this->if_exists = $if_exists;
        return $this;
    }

}