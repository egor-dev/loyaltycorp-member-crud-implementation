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

    /**
     * @ORM\Column(name="mail_chimp_id", type="string", nullable=true)
     *
     * @var string
     */
    private $mailChimpId;

    /**
     * @ORM\Column(name="name", type="string")
     *
     * @var string
     */
    private $emailAddress;

    /**
     * @ORM\Column(name="status", type="string")
     *
     * @var string
     */
    private $status;

    /**
     * @ORM\Column(name="location", type="array")
     *
     * @var array
     */
    private $location;

    /**
     * @ORM\Column(name="marketing_permissions", type="array")
     *
     * @var array
     */
    private $marketingPermissions;

    /**
     * @ORM\Column(name="email_type", type="string")
     *
     * @var string
     */
    private $emailType;

    /**
     * @ORM\Column(name="language", type="string")
     *
     * @var string
     */
    private $language;

    /**
     * @ORM\Column(name="vip", type="boolean")
     *
     * @var bool
     */
    private $vip;

    /**
     * @ORM\Column(name="ip_signup", type="string", nullable=true)
     *
     * @var string
     */
    private $ipSignup;

    /**
     * @ORM\Column(name="timestamp_signup", type="string", nullable=true)
     *
     * @var string
     */
    private $timestampSignup;

    /**
     * @ORM\Column(name="ip_opt", type="string", nullable=true)
     *
     * @var string
     */
    private $ipOpt;

    /**
     * @ORM\Column(name="timestamp_opt", type="string", nullable=true)
     *
     * @var string
     */
    private $timestampOpt;

    /**
     * @ORM\Column(name="tags", type="array")
     *
     * @var array
     */
    private $tags;

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
     * @param array $location
     * @return MailChimpMember
     */
    public function setLocation(array $location): MailChimpMember
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @param string $emailType
     * @return MailChimpMember
     */
    public function setEmailType(string $emailType): MailChimpMember
    {
        $this->emailType = $emailType;

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
            'email_type' => 'nullable|string|in:html,text',
            'status' => 'required|string|in:'.implode(',', self::STATUS_ALLOWED),
            'merge_fields' => 'nullable|array',
            'interests' => 'nullable|array',
            'language' => 'nullable|string|size:2',
            'vip' => 'nullable|boolean',

            'location' => 'nullable|array',
            'location.latitude' => 'required_with:location|numeric|between:-90,90',
            'location.longitude' => 'required_with:location|numeric|between:-180,180',

            'marketing_permissions' => 'nullable|array',
            'marketing_permissions.marketing_permission_id' => 'required_with:marketing_permissions|string',
            'marketing_permissions.enabled'  => 'required_with:marketing_permissions|boolean',

            'ip_signup' => 'nullable|ip',
            'timestamp_signup' => 'nullable|date_format:Y-m-d H:i:s',
            'ip_opt' => 'nullable|ip',
            'timestamp_opt' => 'nullable|date_format:Y-m-d H:i:s',
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

    /**
     * @param mixed $language
     * @return MailChimpMember
     */
    public function setLanguage($language): MailChimpMember
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param bool $vip
     * @return MailChimpMember
     */
    public function setVip(bool $vip): MailChimpMember
    {
        $this->vip = $vip;

        return $this;
    }

    /**
     * @param string $ipSignup
     * @return MailChimpMember
     */
    public function setIpSignup(string $ipSignup): MailChimpMember
    {
        $this->ipSignup = $ipSignup;

        return $this;
    }

    /**
     * @param string $timestampSignup
     * @return MailChimpMember
     */
    public function setTimestampSignup(string $timestampSignup): MailChimpMember
    {
        $this->timestampSignup = $timestampSignup;

        return $this;
    }

    /**
     * @param string $ipOpt
     * @return MailChimpMember
     */
    public function setIpOpt(string $ipOpt): MailChimpMember
    {
        $this->ipOpt = $ipOpt;

        return $this;
    }

    /**
     * @param string $timestampOpt
     * @return MailChimpMember
     */
    public function setTimestampOpt(string $timestampOpt): MailChimpMember
    {
        $this->timestampOpt = $timestampOpt;

        return $this;
    }

    /**
     * @param array $tags
     * @return MailChimpMember
     */
    public function setTags(array $tags): MailChimpMember
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param array $marketingPermissions
     * @return MailChimpMember
     */
    public function setMarketingPermissions(array $marketingPermissions): MailChimpMember
    {
        $this->marketingPermissions = $marketingPermissions;

        return $this;
    }
}