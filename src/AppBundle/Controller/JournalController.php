<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Journal;
use AppBundle\Services\Ping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Journal controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/journal")
 */
class JournalController extends Controller {

    /**
     * Lists all Journal entities.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array
     *   Array data for the template processor.
     *
     * @Route("/", name="journal_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Journal::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $journals = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'journals' => $journals,
        );
    }

    /**
     * Search for Journal entities.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @Route("/search", name="journal_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Journal::class);
        $q = $request->query->get('q');
        $paginator = $this->get('knp_paginator');
        if ($q) {
            $query = $repo->searchQuery($q);
            $journals = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $journals = $paginator->paginate(array(), $request->query->getInt('page', 1), 25);
        }

        return array(
            'journals' => $journals,
            'q' => $q,
        );
    }

    /**
     * Finds and displays a Journal entity.
     *
     * @param Journal $journal
     *   The Journal to show.
     *
     * @return array
     *   Array data for the template processor.
     *
     * @Route("/{id}", name="journal_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Journal $journal) {

        return array(
            'journal' => $journal,
        );
    }

    /**
     * Pings a journal and displays the result.
     *
     * @param Journal $journal
     *   The Journal to show.
     * @param Ping $ping
     *   Dependency-injected Ping service.
     *
     * @return array
     *   Array data for the template processor.
     *
     * @Route("/{id}/ping", name="journal_ping")
     * @Method("GET")
     * @Template()
     */
    public function pingAction(Journal $journal, Ping $ping) {
        $result = $ping->ping($journal);
        return array(
            'journal' => $journal,
            'result' => $result,
        );
    }

}
