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
        $member = \json_decode($this->response->getContent(), true);
        $this->createdMailChimpMemberIds[] = $member['mail_chimp_id'];

        $this->assertResponseOk();
        $this->seeJson(static::$memberData);
        $this->assertArrayHasKey('mail_chimp_id', $member);
        $this->assertNotNull($member['mail_chimp_id']);
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

    public function testShowMemberNotFoundException(): void
    {
        $this->get("/mailchimp/lists/{$this->listId}/members/invalid-member-id");

        $this->assertMemberNotFoundResponse('invalid-member-id');
    }

    /**
     * Test application returns successful response with list data when requesting existing list.
     *
     * @return void
     */
    public function testShowMemberSuccessfully(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);
        $member = \json_decode($this->response->content(), true);
        $this->createdMailChimpMemberIds[] = $member['mail_chimp_id'];

        $this->get("/mailchimp/lists/{$this->listId}/members/{$member['member_id']}");
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseOk();

        foreach (static::$memberData as $key => $value) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals($value, $content[$key]);
        }
    }

    public function testUpdateMemberNotFoundException(): void
    {
        $this->put("/mailchimp/lists/{$this->listId}/members/invalid-member-id");

        $this->assertMemberNotFoundResponse('invalid-member-id');
    }

    public function testUpdateMemberSuccessfully(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);
        $member = \json_decode($this->response->content(), true);
        $this->createdMailChimpMemberIds[] = $member['mail_chimp_id'];

        $this->put("/mailchimp/lists/{$this->listId}/members/{$member['member_id']}", ['email_type' => 'text']);
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseOk();

        foreach (\array_keys(static::$memberData) as $key) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals('text', $content['email_type']);
        }
    }

    public function testUpdateMemberValidationFailed(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);
        $member = \json_decode($this->response->content(), true);
        $this->createdMailChimpMemberIds[] = $member['mail_chimp_id'];

        $this->put("/mailchimp/lists/{$this->listId}/members/{$member['member_id']}", ['email_type' => 'invalid']);

        $content = \json_decode($this->response->content(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);
        self::assertArrayHasKey('email_type', $content['errors']);
        self::assertEquals('Invalid data given', $content['message']);
    }
}
