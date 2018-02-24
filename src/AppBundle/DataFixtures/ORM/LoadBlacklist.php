<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Blacklist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadBlacklist form.
 */
class LoadBlacklist extends Fixture {

    const UUIDS = array(
        'AC54ED1A-9795-4EED-94FD-D80CB62E0C84',
        'B156FACD-5210-4111-B4C2-D5C0C348D93A',
        '2DE4DC03-3E02-43D3-A088-E7536743C083',
        'A13C33E6-CDC4-4D09-BB62-1BE3B0E74A0A',
    );
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Blacklist();
            $fixture->setUuid(self::UUIDS[$i]);
            $fixture->setComment('Comment ' . $i);

            $em->persist($fixture);
            $this->setReference('blacklist.' . $i, $fixture);
        }

        $em->flush();
    }

}
