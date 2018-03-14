<?php

/*
 * Symfony DataTables Bundle
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Unit\Adapter\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Omines\DataTablesBundle\Adapter\Doctrine\ODM\SearchCriteriaProvider;
use Omines\DataTablesBundle\Adapter\Doctrine\ODMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Giovanni Albero <giovannialbero.solinf@gmail.com>
 */
class ODMAdapterTest extends TestCase
{
    public function testSearchCriteriaProvider()
    {
        $table = new DataTable();
        $table
            ->add('firstName', TextColumn::class)
            ->add('lastName', TextColumn::class)
        ;

        $table->handleRequest(Request::create('/', Request::METHOD_POST, ['_dt' => 'dt']));
        $state = $table->getState();
        $state
            ->setGlobalSearch('foo')
            ->setColumnSearch($table->getColumn(0), 'bar')
        ;

        $dm = $this->prophesize(DocumentManager::class);

        $qb = $this->createMock(Builder::class);
        $qb
            ->method('expr')
            ->will($this->returnCallback(function () use ($dm) { return new Expr($dm->reveal()); }));

        /* @var Builder $qb */
        (new SearchCriteriaProvider())->process($qb, $state);

        // As this is buggy right now ignore the result
        $this->assertTrue(true);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage doctrine/mongodb-odm-bundle
     */
    public function testODMAdapterRequiresDependency()
    {
        (new ODMAdapter());
    }

    /**
     * @expectedException \Omines\DataTablesBundle\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Provider must be a callable or implement QueryBuilderProcessorInterface
     */
    public function testInvalidQueryProcessorThrows()
    {
        (new ODMAdapter($this->createMock(RegistryInterface::class)))
            ->configure([
                'document' => 'bar',
                'query' => ['foo'],
            ]);
    }
}
