<?php
declare(strict_types=1);

namespace App\Http\Controllers\MailChimp;

use Exception;
use Mailchimp\Mailchimp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Database\Entities\MailChimp\MailChimpList;
use App\Database\Entities\MailChimp\MailChimpMember;

/**
 * Class MembersController.
 *
 * @package App\Http\Controllers\MailChimp
 */
class MembersController extends Controller
{
    /**
     * @var \Mailchimp\Mailchimp
     */
    private $mailChimp;

    /**
     * MembersController constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Mailchimp\Mailchimp $mailchimp
     */
    public function __construct(EntityManagerInterface $entityManager, Mailchimp $mailchimp)
    {
        parent::__construct($entityManager);

        $this->mailChimp = $mailchimp;
    }

    /**
     * @param Request $request
     * @param $listId
     *
     * @return JsonResponse
     */
    public function create(Request $request, $listId): JsonResponse
    {
        $member = new MailChimpMember($request->all());

        $validator = $this->getValidationFactory()->make($member->toMailChimpArray(), $member->getValidationRules());

        if ($validator->fails()) {
            return $this->errorResponse([
                'message' => 'Invalid data given',
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        /** @var MailChimpList $list */
        $list = $this->entityManager->getRepository(MailChimpList::class)->find($listId);
        if (! $list) {
            return $this->errorResponse(
                [
                    'message' => 'Invalid list given',
                ]
            );
        }

        try {
            $member->assignToList($list);
            $this->saveEntity($member);
            $response = $this->mailChimp->post("/lists/{$list->getMailChimpId()}/members", $member->toMailChimpArray());
            $this->saveEntity($member->setMailChimpId($response->get('id')));
        } catch (Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse($member->toArray());
    }

    /**
     * Remove MailChimp member.
     *
     * @param string $listId
     * @param string $memberId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(string $listId, string $memberId): JsonResponse
    {
        /** @var \App\Database\Entities\MailChimp\MailChimpMember|null $member */
        $member = $this->entityManager->getRepository(MailChimpMember::class)->findOneBy([
            'memberId' => $memberId,
            'list' => $listId,
        ]);

        if ($member === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpMember[%s] not found', $memberId)],
                404
            );
        }

        try {
            // Remove list from database
            $this->removeEntity($member);
            // Remove list from MailChimp
            $listMailChimpId = $member->getList()->getMailChimpId();
            $memberMailChimpId = $member->getMailChimpId();
            $this->mailChimp->delete("lists/$listMailChimpId/members/$memberMailChimpId");
        } catch (Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse([]);
    }

    /**
     * Retrieve and return MailChimp member.
     *
     * @param string $listId
     * @param string $memberId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $listId, string $memberId): JsonResponse
    {
        /** @var \App\Database\Entities\MailChimp\MailChimpMember|null $member */
        $member = $this->entityManager->getRepository(MailChimpMember::class)->findOneBy([
            'memberId' => $memberId,
            'list' => $listId,
        ]);

        if ($member === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpMember[%s] not found', $memberId)],
                404
            );
        }

        return $this->successfulResponse($member->toArray());
    }

    /**
     * Update MailChimp member.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $listId
     * @param string $memberId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $listId, string $memberId): JsonResponse
    {
        /** @var \App\Database\Entities\MailChimp\MailChimpMember|null $member */
        $member = $this->entityManager->getRepository(MailChimpMember::class)->findOneBy([
            'memberId' => $memberId,
            'list' => $listId,
        ]);

        if ($member === null) {
            return $this->errorResponse(
                ['message' => \sprintf('MailChimpMember[%s] not found', $memberId)],
                404
            );
        }

        // Update list properties
        $member->fill($request->all());

        // Validate entity
        $validator = $this->getValidationFactory()->make($member->toMailChimpArray(), $member->getValidationRules());

        if ($validator->fails()) {
            // Return error response if validation failed
            return $this->errorResponse([
                'message' => 'Invalid data given',
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        try {
            // Update list into database
            $this->saveEntity($member);
            // Update list into MailChimp
            $listMailChimpId = $member->getList()->getMailChimpId();
            $memberMailChimpId = $member->getMailChimpId();
            $this->mailChimp->patch("lists/$listMailChimpId/members/$memberMailChimpId", $member->toMailChimpArray());
        } catch (Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse($member->toArray());
    }
}
