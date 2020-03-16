<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Controller\SwordController;

use AppBundle\DataFixtures\ORM\LoadWhitelist;
use AppBundle\Entity\Journal;

class ServiceDocumentTest extends AbstractSwordTestCase {
    public function testServiceDocument() : void {
        $this->testClient->request('GET', '/api/sword/2.0/sd-iri', [], [], [
            'HTTP_On-Behalf-Of' => '7AD045C9-89E6-4ACA-8363-56FE9A45C34F',
            'HTTP_Journal-Url' => 'http://example.com',
        ]);
        $this->assertSame(200, $this->testClient->getResponse()->getStatusCode());
    }

    public function testServiceDocumentNoOBH() : void {
        $this->testClient->request('GET', '/api/sword/2.0/sd-iri', [], [], [
            'HTTP_Journal-Url' => 'http://example.com',
        ]);
        $this->assertSame(400, $this->testClient->getResponse()->getStatusCode());
    }

    public function testServiceDocumentNoJournalUrl() : void {
        $this->testClient->request('GET', '/api/sword/2.0/sd-iri', [], [], [
            'HTTP_On-Behalf-Of' => '7AD045C9-89E6-4ACA-8363-56FE9A45C34F',
        ]);
        $this->assertSame(400, $this->testClient->getResponse()->getStatusCode());
    }

    public function testServiceDocumentBadObh() : void {
        $this->testClient->request('GET', '/api/sword/2.0/sd-iri', [], [], [
            'HTTP_On-Behalf-Of' => '',
            'HTTP_Journal-Url' => 'http://example.com',
        ]);
        $this->assertSame(400, $this->testClient->getResponse()->getStatusCode());
    }

    public function testServiceDocumentBadJournalUrl() : void {
        $this->testClient->request('GET', '/api/sword/2.0/sd-iri', [], [], [
            'HTTP_On-Behalf-Of' => '7AD045C9-89E6-4ACA-8363-56FE9A45C34F',
            'HTTP_Journal-Url' => '',
        ]);
        $this->assertSame(400, $this->testClient->getResponse()->getStatusCode());
    }

    public function testServiceDocumentContentNewJournal() : void {
        $count = count($this->em->getRepository(Journal::class)->findAll());

        $this->testClient->request('GET', '/api/sword/2.0/sd-iri', [], [], [
            'HTTP_On-Behalf-Of' => '7AD045C9-89E6-4ACA-8363-56FE9A45C34F',
            'HTTP_Journal-Url' => 'http://example.com',
        ]);
        $this->assertSame(200, $this->testClient->getResponse()->getStatusCode());
        $xml = $this->getXml($this->testClient);
        $this->assertSame('service', $xml->getName());
        $this->assertSame('2.0', $this->getXmlValue($xml, '//sword:version'));
        $this->assertSame('No', $this->getXmlValue($xml, '//pkp:pln_accepting/@is_accepting'));
        $this->assertSame('The PKP PLN does not know about this journal yet.', $this->getXmlValue($xml, '//pkp:pln_accepting'));
        $this->assertCount(4, $xml->xpath('//pkp:terms_of_use/*'));
        $this->assertSame('PKP PLN deposit for 7AD045C9-89E6-4ACA-8363-56FE9A45C34F', $this->getXmlValue($xml, '//atom:title'));
        $this->assertSame('http://localhost/api/sword/2.0/col-iri/7AD045C9-89E6-4ACA-8363-56FE9A45C34F', $this->getXmlValue($xml, '//app:collection/@href'));

        $this->assertCount($count + 1, $this->em->getRepository('AppBundle:Journal')->findAll());

        $journal = $this->em->getRepository('AppBundle:Journal')->findOneBy(['uuid' => '7AD045C9-89E6-4ACA-8363-56FE9A45C34F']);
        $this->assertNotNull($journal);
        $this->assertNull($journal->getTitle());
        $this->assertSame('http://example.com', $journal->getUrl());
        $this->assertSame('new', $journal->getStatus());
    }

    public function testServiceDocumentContentWhitelistedJournal() : void {
        $count = count($this->em->getRepository(Journal::class)->findAll());

        $this->testClient->request('GET', '/api/sword/2.0/sd-iri', [], [], [
            'HTTP_On-Behalf-Of' => LoadWhitelist::UUIDS[0],
            'HTTP_Journal-Url' => 'http://example.com',
        ]);
        $this->assertSame(200, $this->testClient->getResponse()->getStatusCode());
        $xml = $this->getXml($this->testClient);
        $this->assertSame('service', $xml->getName());
        $this->assertSame('2.0', $this->getXmlValue($xml, '//sword:version'));
        $this->assertSame('Yes', $this->getXmlValue($xml, '//pkp:pln_accepting/@is_accepting'));
        $this->assertSame('The PKP PLN can accept deposits from this journal.', $this->getXmlValue($xml, '//pkp:pln_accepting'));
        $this->assertCount(4, $xml->xpath('//pkp:terms_of_use/*'));
        $this->assertSame('PKP PLN deposit for ' . LoadWhitelist::UUIDS[0], $this->getXmlValue($xml, '//atom:title'));
        $this->assertSame('http://localhost/api/sword/2.0/col-iri/' . LoadWhitelist::UUIDS[0], $this->getXmlValue($xml, '//app:collection/@href'));

        $this->em->clear();
        $this->assertCount($count, $this->em->getRepository('AppBundle:Journal')->findAll());
        $journal = $this->em->getRepository('AppBundle:Journal')->findOneBy(['uuid' => LoadWhitelist::UUIDS[0]]);
        $this->assertSame('http://example.com', $journal->getUrl());
    }
}
