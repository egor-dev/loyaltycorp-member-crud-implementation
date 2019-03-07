<?php
declare(strict_types=1);

namespace App\Database\Entities\MailChimp;

use Doctrine\ORM\Mapping as ORM;
use EoneoPay\Utils\Str;

/**
 * @ORM\Entity()
 */
class MailChimpMember extends MailChimpEntity
{
    private const STATUS_SUBSCRIBED = 'subscribed';
    private const STATUS_UNSUBSCRIBED = 'unsubscribed';
    private const STATUS_CLEANED = 'cleaned';
    private const STATUS_PENDING = 'pending';
    private const STATUS_ALLOWED = [
        self::STATUS_SUBSCRIBED,
        self::STATUS_UNSUBSCRIBED,
        self::STATUS_CLEANED,
        self::STATUS_PENDING,
    ];

    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $memberId;

    /** @ORM\Column(name="mail_chimp_id", type="string", nullable=true) */
    private $mailChimpId;

    /** @ORM\Column(name="name", type="string") */
    private $emailAddress;

    /** @ORM\Column(type="string") */
    private $status;

    /**
     * Get id.
     *
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->memberId;
    }

    /**
     * Set mailchimp id of the list.
     *
     * @param string $mailChimpId
     *
     * @return \App\Database\Entities\MailChimp\MailChimpMember
     */
    public function setMailChimpId(string $mailChimpId): MailChimpMember
    {
        $this->mailChimpId = $mailChimpId;

        return $this;
    }

    /**
     * @param string $emailAddress
     * @return MailChimpMember
     */
    public function setEmailAddress(string $emailAddress): MailChimpMember
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * @param $status
     * @return MailChimpMember
     */
    public function setStatus($status): MailChimpMember
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get validation rules for mailchimp entity.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'email_address' => 'required|email',
            'status' => 'required|string|in:'.implode(',', self::STATUS_ALLOWED),
        ];
    }

    /**
     * Get array representation of entity.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        $str = new Str();

        foreach (\get_object_vars($this) as $property => $value) {
            $array[$str->snake($property)] = $value;
        }

        return $array;
    }
}