<?php
/**
 * Polder Knowledge / Entity Service (http://polderknowledge.nl)
 *
 * @link http://developers.polderknowledge.nl/gitlab/polderknowledge/entityservice for the canonical source repository
 * @copyright Copyright (c) 2015-2015 Polder Knowledge (http://www.polderknowledge.nl)
 * @license http://polderknowledge.nl/license/proprietary proprietary
 */

namespace PolderKnowledge\EntityService\Service;

/**
 * The TransactionAwareInterface interface makes it possible work with transactions on a service.
 */
interface TransactionAwareInterface
{
    /**
     * Starts a new transaction.
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commits a started transaction.
     *
     * @return void
     */
    public function commitTransaction();

    /**
     * Rolls back a started transaction.
     *
     * @return void
     */
    public function rollBackTransaction();

    /**
     * Returns true when posible to start an transaction
     *
     * @return boolean
     */
    public function isTransactionEnabled();
}