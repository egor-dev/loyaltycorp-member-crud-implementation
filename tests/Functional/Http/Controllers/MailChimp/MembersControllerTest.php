<?php
declare(strict_types=1);

namespace Tests\App\Functional\Http\Controllers\MailChimp;

use Tests\App\TestCases\MailChimp\MemberTestCase;

class MembersControllerTest extends MemberTestCase
{
    /**
     * Test application creates successfully member in list and returns it back with id from MailChimp.
     *
     * @return void
     */
    public function testCreateMemberInListSuccessfully(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);

        $content = \json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->seeJson(static::$memberData);
        $this->assertArrayHasKey('mail_chimp_id', $content);
        $this->assertNotNull($content['mail_chimp_id']);

        $this->createdMailChimpMemberIds[] = $content['mail_chimp_id'];
    }

    public function testCreateMemberValidationFailed(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members");

        $content = \json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);
        self::assertEquals('Invalid data given', $content['message']);

        foreach (\array_keys(static::$memberData) as $key) {
            if (\in_array($key, static::$notRequired, true)) {
                continue;
            }

            self::assertArrayHasKey($key, $content['errors']);
        }
    }

    /**
     * Test application returns error response when list not found.
     *
     * @return void
     */
    public function testRemoveMemberNotFoundException(): void
    {
        $this->delete("/mailchimp/lists/{$this->listId}/members/invalid-member-id");

        $this->assertMemberNotFoundResponse('invalid-member-id');
    }


    /**
     * Test application returns empty successful response when removing existing list.
     *
     * @return void
     */
    public function testRemoveMemberSuccessfully(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);

        $member = \json_decode($this->response->content(), true);

        $this->delete("/mailchimp/lists/{$this->listId}/members/{$member['member_id']}");

        $this->assertResponseOk();
        self::assertEmpty(\json_decode($this->response->content(), true));
    }
}