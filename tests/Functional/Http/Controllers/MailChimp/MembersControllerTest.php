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

        $this->createdMemberIds[] = $content['mail_chimp_id'];
    }
}