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

use deasilworks\cef\StatementBuilder\InsertJson;
use deasilworks\cef\StatementBuilder\InsertModel;
use deasilworks\cef\StatementBuilder\Select;
use Pimple\Container;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class StatementManager
 * @package deasilworks\cef
 */
abstract class StatementManager
{
    /**
     * @var array
     */
    private $json_keys = array(
        'comm_rx',
        'comm_tx'
    );

    /**
     * A day in seconds
     */
    const DAY = 86400;

    /**
     * @var \Cassandra\Session
     */
    protected $session;

    /**
     * @var \Cassandra\Cluster\Builder
     */
    protected $cluster;

    /**
     * @var \Cassandra\Statement
     */
    protected $statement;

    /**
     * @var StatementBuilder
     */
    protected $sb;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var mixed
     */
    protected $consistency;

    /**
     * @var mixed
     */
    protected $retry_policy;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $previous_arguments;

    /**
     * @var EntityManager;
     */
    protected $entity_manager;

    /**
     * ResultContainer class
     * @var string
     */
    protected $result_container_class = ResultContainer::class;

    /**
     * EntityModel class
     * @var string
     */
    protected $result_model_class = EntityModel::class;

    /**
     * @return Container
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param Container $app
     * @return StatementManager
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @param array $config
     * @return StatementManager
     */
    public function setConfig($config)
    {
        $this->config = $config;

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
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    /**
     * @param EntityManager $entity_manager
     * @return StatementManager
     */
    public function setEntityManager($entity_manager)
    {
        $this->entity_manager = $entity_manager;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getCluster()
    {
        $config = $this->getConfig();

        if (!$this->cluster) {
            $retry_policy = new \Cassandra\RetryPolicy\DowngradingConsistency();
            $logged_retry = new \Cassandra\RetryPolicy\Logging($retry_policy);

            /** @var \Cassandra\Cluster\Builder $cluster */
            $cluster = \Cassandra::cluster();
            $cluster
                ->withCredentials($config['username'], $config['password'])
                ->withDefaultConsistency(\Cassandra::CONSISTENCY_LOCAL_QUORUM)
                ->withRetryPolicy($logged_retry)
                ->withTokenAwareRouting(true);

            call_user_func_array(array($cluster, "withContactPoints"), $config['contact_points']);
            $this->cluster = $cluster->build();
        }

        return $this->cluster;
    }

    /**
     * @return \Cassandra\Session
     */
    public function getSession()
    {
        $config = $this->getConfig();
        $cluster = $this->getCluster();

        if (!$this->session) {
            $this->session = $cluster->connect($config['keyspace']);
        }

        return $this->session;
    }

    /**
     * @return mixed
     */
    public function getConsistency()
    {
        return $this->consistency;
    }

    /**
     * @param mixed|null $consistency
     * @return $this
     */
    public function setConsistency($consistency=null)
    {
        $this->consistency = $consistency;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRetryPolicy()
    {
        return $this->retry_policy;
    }

    /**
     * @param mixed|null $retry_policy
     * @return $this
     */
    public function setRetryPolicy($retry_policy=null)
    {
        $this->retry_policy = $retry_policy;
        return $this;
    }


    /**
     * @param null|array $arguments
     * @return $this
     */
    public function setArguments($arguments=null)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return \DeasilWorks\CEF\StatementBuilder
     */
    public function getSb()
    {
        return $this->sb;
    }

    /**
     * @param \DeasilWorks\CEF\StatementBuilder $sb
     * @return $this
     */
    public function setSb($sb)
    {
        $this->sb = $sb;
        return $this;
    }

    /**
     * @param string|StatementBuilder $simple_statement
     * @return $this
     */
    public function setStatement($simple_statement)
    {
        if (is_object($simple_statement) && $simple_statement instanceof StatementBuilder) {
            $this->setSb($simple_statement);
        }

        $this->statement = $simple_statement;
        return $this;
    }

    /**
     * @return \Cassandra\Statement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        if ($this->getArguments()) {
            $this->previousArgs($this->getArguments());
        }

        $this->setArguments();
        $this->setConsistency();
        $this->setRetryPolicy();

        $this->getSession();
        return $this;
    }

    /**
     * @param string $type
     * @param bool $reset
     * @return mixed
     */
    private function executeStatement($type='execute', $reset=true)
    {
        $options = array(
            'consistency' => \Cassandra::CONSISTENCY_LOCAL_QUORUM,
        );

        if ($this->getConsistency()) {
            $options['consistency'] = $this->getConsistency();
        }

        if ($this->getRetryPolicy()) {
            $options['retry_policy'] = $this->getRetryPolicy();
        }

        if (is_array($this->getArguments())) {
            $options['arguments'] = $this->getArguments();
        }

        $cas_options = new \Cassandra\ExecutionOptions($options);
        $session = $this->getSession();

        // execute
        /** @var \Cassandra\Rows $result */
        $result = $session->$type($this->getStatement(), $cas_options);

        // deal with args
        if (array_key_exists('arguments', $options)) {
            $this->previousArgs($options['arguments']);
        }

        if ($reset) {
            $this->reset();
        }

        return $result;
    }

    /**
     * @return ResultContainer
     */
    public function execute()
    {
        $result = $this->executeStatement();

        $ec = null;

        // convert result to entity collection
        if ($result && $result instanceof \Cassandra\Rows) {
            /** @var ResultContainer $ec */
            $ec = $this->rowsToEntityCollection($result);
        }

        return $ec;
    }

    /**
     * @return \Cassandra\Future
     */
    public function executeAsync()
    {
        return $this->executeStatement('executeAsync');
    }

    /**
     * @return array
     */
    public function getJsonKeys()
    {
        return $this->json_keys;
    }

    /**
     * @param array $json_keys
     * @return $this
     */
    public function setJsonKeys($json_keys)
    {
        $this->json_keys = $json_keys;
        return $this;
    }

    /**
     * @return string
     */
    public function getResultContainerClass()
    {
        return $this->result_container_class;
    }

    /**
     * @param string $result_container_class
     * @return $this
     */
    public function setResultContainerClass($result_container_class)
    {
        $this->result_container_class = $result_container_class;

        /** @var ResultContainer $rc */
        $rc = new $result_container_class();

        // set the model class
        $this->setResultModelClass($rc->getModelClass());


        return $this;
    }

    /**
     * @return ResultContainer
     */
    public function getResultContainer()
    {
        $rc_class = $this->getResultContainerClass();

        // @TODO: throw exception if this fails / check for ResultContainer

        /** @var ResultContainer $rc */
        $rc = new $rc_class();
        $rc->setEntityManager($this->getEntityManager());

        return $rc;
    }

    /**
     * @return string
     */
    public function getResultModelClass()
    {
        return $this->result_model_class;
    }

    /**
     * @param string $result_model_class
     * @return $this
     */
    protected function setResultModelClass($result_model_class)
    {
        $this->result_model_class = $result_model_class;
        return $this;
    }

    /**
     * @return EntityModel
     */
    public function getResultModel()
    {
        $rm_class = $this->getResultModelClass();

        // @TODO: thow exception if this fails / check for EntityModel

        /** @var EntityModel $rc */
        $rm = new $rm_class();

        return $rm;
    }

    /**
     * @deprecated
     * @param ResultContainer | string $hydrate
     * @return StatementManager
     * @throws /Exception
     */
    public function setHydrate($hydrate)
    {
        throw new \Exception('Method setHydrate is deprecated on StatementManager. Use setResultContainerClass.');
    }

    /**
     * @param \Cassandra\Rows $rows
     * @return EntityCollection
     */
    protected function rowsToEntityCollection($rows)
    {
        /** @var ResultContainer $resultContainer */
        $resultContainer = $this->getResultContainer();

        $resultContainer->setArguments($this->previous_arguments);
        $resultContainer->setStatement((string)$this->getSb());

        $entries = array();

        // page through all results
        while (true) {
            while ($rows->valid()) {

                // generic marshall
                $entry = $this->normalize($rows->current());

                if ($entry) {
                    array_push($entries, $entry);
                } else {
                    // @todo: log a warning?
                }
                $rows->next();
            }

            if ($rows->isLastPage()) {
                break;
            }

            $rows = $rows->nextPage();
        }

        $resultContainer->setResults($entries);

        return $resultContainer;
    }

    /**
     * @param $builder_class
     * @return \DeasilWorks\CEF\StatementBuilder
     */
    public function getStatementBuilder($builder_class = Select::class)
    {
        // @todo check for instance of StatementBuilder

        /** @var StatementBuilder $sb */
        $sb = new $builder_class();
        $table = $this->getResultModel()->getTableName();
        $sb->setFrom($table);

        return $sb;
    }

    /**
     * @deprecated use getStatementBuilder(Select::class)
     * @return \DeasilWorks\CEF\StatementBuilder\Select
     */
    public function getSelectStatementBuilder()
    {
        $sb = new Select();
        $table = $this->getResultModel()->getTableName();
        $sb->setFrom($table);

        return $sb;
    }

    /**
     * @deprecated use getStatementBuilder(InsertJson::class)
     * @return \DeasilWorks\CEF\StatementBuilder\InsertJson
     */
    public function getInsertJsonStatementBuilder()
    {
        $sb = new InsertJson();
        $table = $this->getResultModel()->getTableName();
        $sb->setFrom($table);

        return $sb;
    }

    /**
     * @deprecated use getStatementBuilder(InsertModel::class)
     * @return \DeasilWorks\CEF\StatementBuilder\InsertModel
     */
    public function getInsertModelStatementBuilder()
    {
        $sb = new InsertModel();
        $table = $this->getResultModel()->getTableName();
        $sb->setFrom($table);

        return $sb;
    }

    /**
     * @param $row
     * @return mixed
     */
    protected function normalize($row)
    {
        // loop through the object keys and normalize
        $entry = array();

        if (is_object($row) && get_class($row) == 'Cassandra\\Map') {
            /** @var \Cassandra\Map $row */
            $keys = $row->keys();
            $data = array();
            foreach ($keys as $key) {
                $data[(string) $key] = $row->offsetGet($key);
            }
            $row = $data;
        }

        foreach ($row as $k => $v) {

            if (is_object($v)) {

                $class = get_class($v);
                switch ($class) {

                    case 'Cassandra\\Timestamp':
                        /** @var \Cassandra\Timestamp $timestamp */
                        $timestamp = $v;
                        $entry[$k] = $timestamp->time();
                        break;

                    case 'Cassandra\\UserTypeValue':
                        // recursion
                        $entry[$k] = $this->normalize($v);
                        break;

                    case 'Cassandra\\Map':
                        $entry[$k] = $this->normalize($v);
                        break;

                    case 'Cassandra\\Set':
                        $entry[$k] = $this->normalize($v);
                        break;

                    default:

                        $entry[$k] = (string) $v;

                }

            } else {

                // check for json keys
                //
                if (in_array($k, $this->json_keys)) {
                    $entry[$k] = json_decode($v, true);
                } else {
                    $entry[$k] = $v;
                }

            }

        }

        return $entry;
    }

    /**
     * @param array $a
     */
    private function previousArgs(array $a) {
        $this->previous_arguments = $a;
    }

}