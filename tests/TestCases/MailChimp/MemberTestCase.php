<?php
declare(strict_types=1);

namespace Tests\App\TestCases\MailChimp;

use Faker\Factory;
use Mailchimp\Mailchimp;
use Tests\App\TestCases\WithDatabaseTestCase;

class MemberTestCase extends WithDatabaseTestCase
{
    /**
     * @var array
     */
    protected $createdMemberIds = [];

    /**
     * @var array
     */
    protected static $memberData;

    /**
     * @var string
     */
    protected $listId;

    public function setUp(): void
    {
        parent::setUp();

        self::initMemberData();

        // create list, in which we will create member
        $this->post('/mailchimp/lists', ListTestCase::getListData());

        $content = \json_decode($this->response->getContent(), true);

        $this->listId = $content['mail_chimp_id'];
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
        foreach ($this->createdMemberIds as $memberHash) {
            $mailChimp->delete("lists/{$this->listId}/members/$memberHash");
        }

        // remove list
        $mailChimp->delete("lists/{$this->listId}");

        parent::tearDown();
    }

    /**
     * @return void
     */
    public static function initMemberData(): void
    {
        $faker = Factory::create();

        self::$memberData = [
            'email_address' => $faker->email,
            'status' => 'subscribed',
        ];
    }
}