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
 * Class InsertJson
 * @package deasilworks\cef\StatementBuilder
 */
class InsertJson extends StatementBuilder
{
    /**
     * @var string
     */
    protected $type = 'INSERT JSON';

    /**
     * @var string
     */
    protected $json;

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
        // cassandra string support
        $json = str_replace("'", "''", $this->getJson());

        $cql = 'INSERT INTO ' . $this->getFrom() . " JSON '" . $json . "'";


        return $cql;
    }

    /**
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @param string $json
     * @return InsertJson
     */
    public function setJson($json)
    {
        $this->json = $json;
        return $this;
    }


}