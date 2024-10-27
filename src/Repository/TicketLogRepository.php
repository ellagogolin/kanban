<?php

namespace App\Repository;

use App\Entity\TicketLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TicketLog>
 */
class TicketLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketLog::class);
    }

    public function getBurndownResult(): array
    {
        $this->getEntityManager()->getConnection()->executeQuery('SET @runningTotal = 0;');
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('e_Date', 'date', 'date');
        $rsm->addScalarResult('load_change', 'load_change', 'integer');
        $rsm->addScalarResult('runningTotal', 'runningTotal', 'integer');
        $query = "SELECT e_Date,load_change,@runningTotal := @runningTotal + totals.load_change AS runningTotal FROM (SELECT e.date AS e_date, SUM(load_change) AS load_change FROM ticket_log AS e GROUP BY DATE(e.date)) totals ORDER BY e_date;";

        return $this->getEntityManager()->createNativeQuery($query, $rsm)->getResult();
    }
}
