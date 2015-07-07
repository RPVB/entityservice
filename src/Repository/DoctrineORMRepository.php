<?php
/**
 * Polder Knowledge / Entity Service (http://polderknowledge.nl)
 *
 * @link http://developers.polderknowledge.nl/gitlab/polderknowledge/entityservice for the canonical source repository
 * @copyright Copyright (c) 2015-2015 Polder Knowledge (http://www.polderknowledge.nl)
 * @license http://polderknowledge.nl/license/proprietary proprietary
 */

namespace PolderKnowledge\EntityService\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PolderKnowledge\EntityService\Feature\DeletableInterface as FeatureDeletable;
use PolderKnowledge\EntityService\Feature\IdentifiableInterface;
use PolderKnowledge\EntityService\Repository\DoctrineQueryBuilderExpressionVisitor;

/**
 * Class DoctrineORMRepository is a default implementation for a repository using doctrine orm.
 */
class DoctrineORMRepository implements
    EntityRepositoryInterface,
    DeletableInterface,
    FlushableInterface,
    ReadableInterface,
    TransactionAwareInterface,
    WritableInterface
{
    /**
     * The Doctrine ORM entity manager that is used to retrieve and store data.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The FQCN of the entity to work with.
     *
     * @var string
     */
    protected $entityName;

    /**
     * The Doctrine ORM repository that is used to retrieve and store data.
     *
     * @var EntityRepository
     */
    protected $repository;

    /**
     * Initializes the self::$entityManager and self::$entityName
     *
     * @param EntityManagerInterface $entityManager The Doctrine ORM entity manager used to retrieve and store data.
     * @param string $entityName The FQCN of the entity to work with.
     */
    public function __construct(EntityManagerInterface $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     *
     * @param array|Criteria $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return int
     */
    public function countBy(array $criteria)
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('e');
        $queryBuilder->select('count(e)');

        if (!empty($criteria)) {
            foreach ($criteria as $field => $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq('e.' . $field, ':'.$field));
                $queryBuilder->setParameter(':'.$field, $value);
            }
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     *
     * @param Criteria $criteria The criteria to match on.
     * @return int
     */
    public function countByCriteria(Criteria $criteria)
    {
        $queryBuilder = $this->getQueryBuilder($criteria);
        $queryBuilder->select('count(e)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     *
     * @param FeatureDeletable $entity
     */
    public function delete(FeatureDeletable $entity)
    {
        $this->entityManager->remove($entity);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $criteria
     */
    public function deleteBy(array $criteria)
    {
        $entities = $this->findBy($criteria);

        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param array|Criteria $criteria
     */
    public function deleteByCriteria(Criteria $criteria)
    {
        $entities = $this->findByCriteria($criteria);

        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $id
     * @return object
     */
    public function find($id)
    {
        return $this->entityManager->find($this->entityName, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * {@inheritdoc}
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * @param Criteria $criteria
     * @return array
     */
    public function findByCriteria(Criteria $criteria)
    {
        $queryBuilder = $this->getQueryBuilder($criteria);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     *
     * @param array $criteria
     * @param array|null $order
     * @return null|object
     */
    public function findOneBy(array $criteria, array $order = null)
    {
        return $this->getRepository()->findOneBy($criteria, $order);
    }

    /**
     * {@inheritdoc}
     *
     * @param Criteria $criteria
     * @return null|object
     */
    public function findOneByCriteria(Criteria $criteria)
    {
        $queryBuilder = $this->getQueryBuilder($criteria);
        $result = $queryBuilder->getQuery()->getResult();

        return current($result);
    }

    /**
     * {@inheritdoc}
     *
     * @param Criteria $criteria The criteria to find entities by.
     * @return QueryBuilder
     */
    protected function getQueryBuilder(Criteria $criteria)
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('e');

        $visitor = new DoctrineQueryBuilderExpressionVisitor('e', $queryBuilder);

        $whereExpression = $criteria->getWhereExpression();
        if ($whereExpression !== null) {
            $queryBuilder->andWhere($visitor->dispatch($whereExpression));
            $queryBuilder->setParameters($visitor->getParameters());
        }

        if ($criteria->getFirstResult() !== null) {
            $queryBuilder->setFirstResult($criteria->getFirstResult());
        }

        if ($criteria->getMaxResults() !== null) {
            $queryBuilder->setMaxResults($criteria->getMaxResults());
        }

        if ($criteria->getOrderings()) {
            foreach ($criteria->getOrderings() as $field => $direction) {
                $queryBuilder->addOrderBy(sprintf('e.%s', $field), $direction);
            }
        }

        return $queryBuilder;
    }

    /**
     * Gets the Doctrine EntityManager.
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     *
     * @return EntityRepository
     */
    public function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->entityManager->getRepository($this->entityName);
        }

        return $this->repository;
    }

    /**
     * {@inheritdoc}
     *
     * @param IdentifiableInterface $entity
     */
    public function flush(IdentifiableInterface $entity = null)
    {
        $this->entityManager->flush($entity);
    }

    /**
     * {@inheritdoc}
     *
     * @param IdentifiableInterface $entity
     */
    public function persist(IdentifiableInterface $entity)
    {
        $this->entityManager->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commitTransaction()
    {
        $this->entityManager->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollBackTransaction()
    {
        $this->entityManager->rollback();
    }
}
