<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Deposit.
 *
 * @ORM\Table(name="deposit", indexes={
 * @ORM\Index(columns={"deposit_uuid", "url"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DepositRepository")
 */
class Deposit extends AbstractEntity {

    /**
     * Default OJS version.
     *
     * The journal version was added to the PKP PLN plugin in OJS version 3. If
     * a deposit doesn't have a version attribute, then assume it is OJS 2.4.8.
     */
    const DEFAULT_JOURNAL_VERSION = '2.4.8';

    /**
     * The journal that sent this deposit.
     *
     * @var Journal
     *
     * @ORM\ManyToOne(targetEntity="Journal", inversedBy="deposits")
     * @ORM\JoinColumn(name="journal_id", referencedColumnName="id")
     */
    private $journal;

    /**
     * The version of OJS that made the deposit and created the export file.
     *
     * The default is 2.4.8. If annotations made use of class constants, it would use
     * self::DEFAULT_JOURNAL_VERSION.
     *
     * @var string
     * @ORM\Column(type="string", length=15, nullable=false, options={"default": "2.4.8"})
     */
    private $journalVersion;

    /**
     * Serialized list of licensing terms as reported in the ATOM deposit.
     *
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $license;

    /**
     * Mime type from the deposit.
     *
     * Bagit doesn't understand compressed files that don't have a file
     * extension. So set the file type, and build file names from that.
     *
     * @var string
     * @ORM\Column(type="string", nullable=false);
     */
    private $fileType;

    /**
     * Deposit UUID, as generated by the PLN plugin in OJS.
     *
     * @var string
     *
     * @Assert\Uuid
     * @ORM\Column(type="string", length=36, nullable=false, unique=true)
     */
    private $depositUuid;

    /**
     * When the deposit was received.
     *
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $received;

    /**
     * The deposit action (add, edit).
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $action;

    /**
     * The issue volume number.
     *
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $volume;

    /**
     * The issue number for the deposit.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $issue;

    /**
     * Publication date of the deposit content.
     *
     * @var DateTime
     * @ORM\Column(type="date")
     */
    private $pubDate;

    /**
     * The checksum type for the deposit (SHA1, MD5).
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $checksumType;

    /**
     * The checksum value, in hex.
     *
     * @var string
     * @Assert\Regex("/^[0-9a-f]+$/");
     * @ORM\Column(type="string")
     */
    private $checksumValue;

    /**
     * The source URL for the deposit. This may be a very large string.
     *
     * @var string
     *
     * @Assert\Url
     * @ORM\Column(type="string", length=2048)
     */
    private $url;

    /**
     * Size of the deposit, in bytes.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * Current processing state.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $state;

    /**
     * List of errors that occured while processing.
     *
     * @var array
     * @ORM\Column(type="array", nullable=false)
     */
    private $errorLog;

    /**
     * State of the deposit in LOCKSSOMatic.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $plnState;

    /**
     * Size of the processed package file, ready for deposit to LOCKSS.
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $packageSize;

    /**
     * Processed package checksum type.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $packageChecksumType;

    /**
     * Checksum for the processed package file.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $packageChecksumValue;

    /**
     * Date the deposit was sent to LOCKSSOmatic or the PLN.
     *
     * @var DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $depositDate;

    /**
     * URL for the deposit receipt in LOCKSSOMatic.
     *
     * @var string
     * @Assert\Url
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private $depositReceipt;

    /**
     * Processing log for this deposit.
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $processingLog;

    /**
     * Number of times the the server has attempted to harvest the deposit.
     * @var int
     * @ORM\Column(type="integer")
     */
    private $harvestAttempts;

    /**
     * Construct a deposit.
     */
    public function __construct() {
        $this->license = array();
        $this->received = new DateTime();
        $this->processingLog = '';
        $this->state = 'depositedByJournal';
        $this->errorLog = array();
        $this->errorCount = 0;
        $this->harvestAttempts = 0;
        $this->journalVersion = self::DEFAULT_JOURNAL_VERSION;
    }

    /**
     * Return the deposit UUID.
     *
     * @return string
     *   Deposit UUID.
     */
    public function __toString() {
        return $this->getDepositUuid();
    }

    /**
     * Set journalVersion.
     *
     * @param string $journalVersion
     *   Version string like '2.4.8.1'.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setJournalVersion($journalVersion) {
        $this->journalVersion = $journalVersion;

        return $this;
    }

    /**
     * Get journalVersion.
     *
     * @return string
     *   a version string like '3.1.0.0'.
     */
    public function getJournalVersion() {
        return $this->journalVersion;
    }

    /**
     * Set license.
     *
     * @param array $license
     *   List of licensing terms.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setLicense(array $license) {
        $this->license = $license;

        return $this;
    }

    /**
     * Add a bit of licensing information to a deposit.
     *
     * @param mixed $key
     *   License identifier.
     * @param mixed $value
     *   Human-readable license information.
     *
     * @return Deposit
     *   returns $this.
     */
    public function addLicense($key, $value) {
        if (trim($value)) {
            $this->license[$key] = trim($value);
        }
        return $this;
    }

    /**
     * Get license.
     *
     * @return array
     *   List of licensing terms.
     */
    public function getLicense() {
        return $this->license;
    }

    /**
     * Set fileType.
     *
     * @param string $fileType
     *   A mime-type string.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setFileType($fileType) {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * Get fileType.
     *
     * @return string
     *   A mime-type string.
     */
    public function getFileType() {
        return $this->fileType;
    }

    /**
     * Set depositUuid.
     *
     * UUIDs are stored and returned in upper case letters.
     *
     * @param string $depositUuid
     *   a 36-character UUID string.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setDepositUuid($depositUuid) {
        $this->depositUuid = strtoupper($depositUuid);

        return $this;
    }

    /**
     * Get depositUuid.
     *
     * @return string
     *   a 36-character (uppercase) UUID string.
     */
    public function getDepositUuid() {
        return $this->depositUuid;
    }

    /**
     * Set received.
     *
     * @param DateTime $received
     *   The date the deposit was received.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setReceived(DateTime $received) {
        $this->received = $received;

        return $this;
    }

    /**
     * Get received.
     *
     * @return DateTime
     *   The date the deposit was received.
     */
    public function getReceived() {
        return $this->received;
    }

    /**
     * Set action.
     *
     * @param string $action
     *   A string like "add" or "edit".
     *
     * @return Deposit
     *   returns $this.
     */
    public function setAction($action) {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string
     *   "add" or "edit".
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set volume.
     *
     * @param int $volume
     *   Volume number.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setVolume($volume) {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume.
     *
     * @return int
     *   Volume number.
     */
    public function getVolume() {
        return $this->volume;
    }

    /**
     * Set issue.
     *
     * @param int $issue
     *   Issue number.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setIssue($issue) {
        $this->issue = $issue;

        return $this;
    }

    /**
     * Get issue.
     *
     * @return int
     *   Issue number.
     */
    public function getIssue() {
        return $this->issue;
    }

    /**
     * Set pubDate.
     *
     * @param DateTime $pubDate
     *   The publication date.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setPubDate(DateTime $pubDate) {
        $this->pubDate = $pubDate;

        return $this;
    }

    /**
     * Get pubDate.
     *
     * @return DateTime
     *   The publication date.
     */
    public function getPubDate() {
        return $this->pubDate;
    }

    /**
     * Set checksumType.
     *
     * @param string $checksumType
     *   A string like "sha1" or "md5" etc.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setChecksumType($checksumType) {
        $this->checksumType = strtolower($checksumType);

        return $this;
    }

    /**
     * Get checksumType.
     *
     * @return string
     *   "sha1" or "md5" etc.
     */
    public function getChecksumType() {
        return $this->checksumType;
    }

    /**
     * Set checksumValue.
     *
     * @param string $checksumValue
     *   Uppercase checksum value.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setChecksumValue($checksumValue) {
        $this->checksumValue = strtoupper($checksumValue);

        return $this;
    }

    /**
     * Get checksumValue.
     *
     * @return string
     *   Uppercase checksum value.
     */
    public function getChecksumValue() {
        return $this->checksumValue;
    }

    /**
     * Set url.
     *
     * @param string $url
     *   URL where the deposit can be harvested.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     *   URL where the deposit can be harvested.
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set size.
     *
     * @param int $size
     *   Deposit size in kb.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return int
     *   Deposit size in kb.
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * Set state.
     *
     * @param string $state
     *   Processing state.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setState($state) {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string
     *   Processing state.
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Set errorLog.
     *
     * @param array $errorLog
     *   List of errors encountered during processing.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setErrorLog(array $errorLog) {
        $this->errorLog = $errorLog;

        return $this;
    }

    /**
     * Get errorLog.
     *
     * @return array|string
     *   Errors encountered during processing, either as a string or list.
     */
    public function getErrorLog($delim = null) {
        if ($delim) {
            return implode($delim, $this->errorLog);
        }
        return $this->errorLog;
    }

    /**
     * Add a message to the error log.
     *
     * @param string $error
     *   Error message.
     *
     * @return Deposit
     *   returns $this.
     */
    public function addErrorLog($error) {
        $this->errorLog[] = $error;
        return $this;
    }

    /**
     * Set plnState.
     *
     * @param string $plnState
     *   PLN state.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setPlnState($plnState) {
        $this->plnState = $plnState;

        return $this;
    }

    /**
     * Get plnState.
     *
     * @return string
     *   PLN state.
     */
    public function getPlnState() {
        return $this->plnState;
    }

    /**
     * Set packageSize.
     *
     * @param int $packageSize
     *   Processed package size in kb.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setPackageSize($packageSize) {
        $this->packageSize = $packageSize;

        return $this;
    }

    /**
     * Get packageSize.
     *
     * @return int
     *   Processed package size in kb.
     */
    public function getPackageSize() {
        return $this->packageSize;
    }

    /**
     * Set packageChecksumType.
     *
     * @param string $packageChecksumType
     *   Lowercase checksum name.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setPackageChecksumType($packageChecksumType) {
        $this->packageChecksumType = strtolower($packageChecksumType);

        return $this;
    }

    /**
     * Get packageChecksumType.
     *
     * @return string
     *   Lowercase checksum name.
     */
    public function getPackageChecksumType() {
        return $this->packageChecksumType;
    }

    /**
     * Set packageChecksumValue.
     *
     * @param string $packageChecksumValue
     *   Uppercase checksum value.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setPackageChecksumValue($packageChecksumValue) {
        $this->packageChecksumValue = strtoupper($packageChecksumValue);

        return $this;
    }

    /**
     * Get packageChecksumValue.
     *
     * @return string
     *   Uppercase checksum value.
     */
    public function getPackageChecksumValue() {
        return $this->packageChecksumValue;
    }

    /**
     * Set depositDate.
     *
     * @param DateTime $depositDate
     *   Date the deposit was sent to LOCKSSOMatic.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setDepositDate(DateTime $depositDate) {
        $this->depositDate = $depositDate;

        return $this;
    }

    /**
     * Get depositDate.
     *
     * @return DateTime
     *   Date the deposit was sent to LOCKSSOMatic.
     */
    public function getDepositDate() {
        return $this->depositDate;
    }

    /**
     * Set depositReceipt.
     *
     * @param string $depositReceipt
     *   URL for the deposit receipt in LOCKSSOMatic.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setDepositReceipt($depositReceipt) {
        $this->depositReceipt = $depositReceipt;

        return $this;
    }

    /**
     * Get depositReceipt.
     *
     * @return string
     *   URL for the deposit receipt in LOCKSSOMatic.
     */
    public function getDepositReceipt() {
        return $this->depositReceipt;
    }

    /**
     * Set processingLog.
     *
     * @param string $processingLog
     *   Processing log.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setProcessingLog($processingLog) {
        $this->processingLog = $processingLog;

        return $this;
    }

    /**
     * Get processingLog.
     *
     * @return string
     *   Processing log.
     */
    public function getProcessingLog() {
        return $this->processingLog;
    }

    /**
     * Append to the processing history.
     *
     * @param string $content
     *   Log message to add to the processing log.
     */
    public function addToProcessingLog($content) {
        $date = date(DateTime::ATOM);
        $this->processingLog .= "{$date}\n{$content}\n\n";
    }

    /**
     * Set harvestAttempts.
     *
     * @param int $harvestAttempts
     *   Number of attempted harvests.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setHarvestAttempts($harvestAttempts) {
        $this->harvestAttempts = $harvestAttempts;

        return $this;
    }

    /**
     * Get harvestAttempts.
     *
     * @return int
     *   Number of attempted harvests.
     */
    public function getHarvestAttempts() {
        return $this->harvestAttempts;
    }

    /**
     * Set journal.
     *
     * @param Journal $journal
     *   Journal that owns the deposit.
     *
     * @return Deposit
     *   returns $this.
     */
    public function setJournal(Journal $journal = null) {
        $this->journal = $journal;

        return $this;
    }

    /**
     * Get journal.
     *
     * @return Journal
     *   Journal that owns the deposit.
     */
    public function getJournal() {
        return $this->journal;
    }

}
