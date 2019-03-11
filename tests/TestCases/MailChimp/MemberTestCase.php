<?php
declare(strict_types=1);

namespace Tests\App\TestCases\MailChimp;

use Mockery;
use Faker\Factory;
use Mailchimp\Mailchimp;
use Mockery\MockInterface;
use Illuminate\Http\JsonResponse;
use Tests\App\TestCases\WithDatabaseTestCase;
use App\Database\Entities\MailChimp\MailChimpList;
use App\Database\Entities\MailChimp\MailChimpMember;

class MemberTestCase extends WithDatabaseTestCase
{
    protected const MAILCHIMP_EXCEPTION_MESSAGE = 'MailChimp exception';

    /**
     * @var array
     */
    protected $createdMailChimpMemberIds = [];

    /**
     * @var array
     */
    protected static $memberData;

    /**
     * @var string
     */
    protected $mailChimpListId;

    /**
     * @var string
     */
    protected $listId;

    /**
     * @var array
     */
    protected static $notRequired = [
        'email_type',
        'merge_fields',
        'interests',
        'language',
        'vip',
        'location',
        'marketing_permissions',
        'ip_signup',
        'timestamp_signup',
        'ip_opt',
        'timestamp_opt',
        'tags',
    ];

    public function setUp(): void
    {
        parent::setUp();

        self::initMemberData();

        // create list, in which we will create member
        $this->post('/mailchimp/lists', ListTestCase::getListData());

        $content = \json_decode($this->response->getContent(), true);

        $this->mailChimpListId = $content['mail_chimp_id'];
        $this->listId = $content['list_id'];
    }

    /**
     * Call MailChimp to delete lists created during test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        /** @var Mailchimp $mailChimp */
        $mailChimp = $this->app->make(Mailchimp::class);

        // remove test members from list
        foreach ($this->createdMailChimpMemberIds as $memberHash) {
            $mailChimp->delete("lists/{$this->mailChimpListId}/members/$memberHash");
        }

        // remove list
        $mailChimp->delete("lists/{$this->mailChimpListId}");

        parent::tearDown();
    }

    /**
     * @return void
     */
    public static function initMemberData(): void
    {
        $faker = Factory::create();
        $ip = $faker->ipv4;
        $datetime = date('Y-m-d H:i:s');

        self::$memberData = [
            'email_address' => $faker->email,
            'email_type' => 'html',
            'status' => 'subscribed',
            'language' => 'ru',
            'vip' => false,
            'location' => [
                'latitude' => $faker->latitude,
                'longitude' => $faker->longitude,
            ],
            'ip_signup' => $ip,
            'timestamp_signup' => $datetime,
            'ip_opt' => $ip,
            'timestamp_opt' => $datetime,
            'tags' => [$faker->word, $faker->word],
        ];
    }

    /**
     * Asserts error response when member not found.
     *
     * @param string $memberId
     *
     * @return void
     */
    protected function assertMemberNotFoundResponse(string $memberId): void
    {
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseStatus(404);
        self::assertArrayHasKey('message', $content);
        self::assertEquals(\sprintf('MailChimpMember[%s] not found', $memberId), $content['message']);
    }

    /**
     * Returns mock of MailChimp to trow exception when requesting their API.
     *
     * @param string $method
     *
     * @return \Mockery\MockInterface
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Mockery requires static access to mock()
     */
    protected function mockMailChimpForException(string $method): MockInterface
    {
        $mailChimp = Mockery::mock(Mailchimp::class);

        $mailChimp
            ->shouldReceive($method)
            ->once()
            ->withArgs(function (string $method, ?array $options = null) {
                return !empty($method) && (null === $options || \is_array($options));
            })
            ->andThrow(new \Exception(self::MAILCHIMP_EXCEPTION_MESSAGE));

        return $mailChimp;
    }

    /**
     * Asserts error response when MailChimp exception is thrown.
     *
     * @param \Illuminate\Http\JsonResponse $response
     *
     * @return void
     */
    protected function assertMailChimpExceptionResponse(JsonResponse $response): void
    {
        $content = \json_decode($response->content(), true);

        self::assertEquals(400, $response->getStatusCode());
        self::assertArrayHasKey('message', $content);
        self::assertEquals(self::MAILCHIMP_EXCEPTION_MESSAGE, $content['message']);
    }

    /**
     * Create MailChimp list into database.
     *
     * @param array $data
     *
     * @return MailChimpList
     */
    protected function createList(array $data): MailChimpList
    {
        $list = new MailChimpList($data);

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        return $list;
    }

    /**
     * Create MailChimp member within list into database.
     *
     * @param MailChimpList $list
     * @param array $data
     *
     * @return MailChimpMember
     */
    protected function createMember(MailChimpList $list, array $data): MailChimpMember
    {
        $member = new MailChimpMember($data);
        $member->assignToList($list);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return $member;
    }
}
