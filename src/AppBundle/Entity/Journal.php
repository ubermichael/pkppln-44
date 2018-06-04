<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Journal.
 *
 * @ORM\Table(name="journal", indexes={
 * @ORM\Index(columns={"uuid", "title", "issn", "url", "email", "publisher_name", "publisher_url"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\JournalRepository")
 */
class Journal extends AbstractEntity {

    /**
     * List of states where a deposit has been sent to LOCKSSOMatic.
     *
     * @todo shouldn't this live in Deposit?
     */
    const SENTSTATES = array(
        'deposited',
        'complete',
        'status-error',
    );

    /**
     * The URL suffix for the ping gateway.
     *
     * This suffix is appened to the Journal's URL for to build the ping URL.
     */
    const GATEWAY_URL_SUFFIX = '/gateway/plugin/PLNGatewayPlugin';

    /**
     * Journal UUID, as generated by the PLN plugin.
     *
     * @var string
     * @ORM\Column(type="string", length=36, nullable=false)
     */
    private $uuid;

    /**
     * When the journal last contacted the staging server.
     *
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $contacted;

    /**
     * OJS version powering the journal.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true, length=12)
     */
    private $ojsVersion;

    /**
     * When the journal manager was notified.
     *
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $notified;

    /**
     * The title of the journal.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * Journal's ISSN.
     *
     * @var string
     * @ORM\Column(type="string", length=9, nullable=true)
     */
    private $issn;

    /**
     * The journal's URL.
     *
     * @var string
     *
     * @Assert\Url
     * @ORM\Column(type="string", nullable=false)
     */
    private $url;

    /**
     * The status of the journal's health. One of new, healthy, unhealthy,
     * triggered, or abandoned.
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $status;

    /**
     * True if a ping reports that the journal manager has accepts the terms of
     * use.
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $termsAccepted;

    /**
     * Email address to contact the journal manager.
     *
     * @var string
     * @Assert\Email
     * @ORM\Column(type="string", nullable=false)
     */
    private $email;

    /**
     * Name of the publisher.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $publisherName;

    /**
     * Publisher's website.
     *
     * @var string
     * @Assert\Url
     * @ORM\Column(type="string", nullable=true)
     */
    private $publisherUrl;

    /**
     * The journal's deposits.
     *
     * @var Deposit[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Deposit", mappedBy="journal")
     */
    private $deposits;

    /**
     *
     */
    public function __construct() {
        parent::__construct();
        $this->status = 'healthy';
        $this->contacted = new DateTime();
        $this->termsAccepted = false;
    }

    /**
     *
     */
    public function __toString() {
        if($this->title) {
            return $this->title;
        }
        return $this->uuid;
    }

    /**
     * Set uuid.
     *
     * @param string $uuid
     *
     * @return Journal
     */
    public function setUuid($uuid) {
        $this->uuid = strtoupper($uuid);

        return $this;
    }

    /**
     * Get uuid.
     *
     * @return string
     */
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * Set contacted.
     *
     * @param DateTime $contacted
     *
     * @return Journal
     */
    public function setContacted($contacted) {
        $this->contacted = $contacted;

        return $this;
    }

    /**
     * Get contacted.
     *
     * @return DateTime
     */
    public function getContacted() {
        return $this->contacted;
    }

    /**
     * Set ojsVersion.
     *
     * @param string $ojsVersion
     *
     * @return Journal
     */
    public function setOjsVersion($ojsVersion) {
        $this->ojsVersion = $ojsVersion;

        return $this;
    }

    /**
     * Get ojsVersion.
     *
     * @return string
     */
    public function getOjsVersion() {
        return $this->ojsVersion;
    }

    /**
     * Set notified.
     *
     * @param DateTime $notified
     *
     * @return Journal
     */
    public function setNotified(DateTime $notified) {
        $this->notified = $notified;

        return $this;
    }

    /**
     * Get notified.
     *
     * @return DateTime
     */
    public function getNotified() {
        return $this->notified;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Journal
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set issn.
     *
     * @param string $issn
     *
     * @return Journal
     */
    public function setIssn($issn) {
        $this->issn = $issn;

        return $this;
    }

    /**
     * Get issn.
     *
     * @return string
     */
    public function getIssn() {
        return $this->issn;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Journal
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     *
     */
    public function getGatewayUrl() {
        return $this->url . self::GATEWAY_URL_SUFFIX;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Journal
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set termsAccepted.
     *
     * @param bool $termsAccepted
     *
     * @return Journal
     */
    public function setTermsAccepted($termsAccepted) {
        $this->termsAccepted = $termsAccepted;

        return $this;
    }

    /**
     * Get termsAccepted.
     *
     * @return bool
     */
    public function getTermsAccepted() {
        return $this->termsAccepted;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Journal
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set publisherName.
     *
     * @param string $publisherName
     *
     * @return Journal
     */
    public function setPublisherName($publisherName) {
        $this->publisherName = $publisherName;

        return $this;
    }

    /**
     * Get publisherName.
     *
     * @return string
     */
    public function getPublisherName() {
        return $this->publisherName;
    }

    /**
     * Set publisherUrl.
     *
     * @param string $publisherUrl
     *
     * @return Journal
     */
    public function setPublisherUrl($publisherUrl) {
        $this->publisherUrl = $publisherUrl;

        return $this;
    }

    /**
     * Get publisherUrl.
     *
     * @return string
     */
    public function getPublisherUrl() {
        return $this->publisherUrl;
    }

    /**
     * Add deposit.
     *
     * @param Deposit $deposit
     *
     * @return Journal
     */
    public function addDeposit(Deposit $deposit) {
        $this->deposits[] = $deposit;

        return $this;
    }

    /**
     * Remove deposit.
     *
     * @param Deposit $deposit
     */
    public function removeDeposit(Deposit $deposit) {
        $this->deposits->removeElement($deposit);
    }

    /**
     * Get deposits.
     *
     * @return Collection
     */
    public function getDeposits() {
        return $this->deposits;
    }

}
