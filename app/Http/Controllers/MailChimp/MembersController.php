<?php
declare(strict_types=1);

namespace App\Http\Controllers\MailChimp;

use App\Database\Entities\MailChimp\MailChimpMember;
use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mailchimp\Mailchimp;

/**
 * Class MembersController
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
     * @return JsonResponse
     */
    public function create(Request $request, $listId): JsonResponse
    {
        $member = new MailChimpMember($request->all());

        $validator = $this->getValidationFactory()->make($member->toMailChimpArray(), $member->getValidationRules());

        if ($validator->fails()) {
            return $this->errorResponse([
                'message' => 'Invalid data given',
                'errors' => $validator->errors()->toArray()
            ]);
        }

        try {
            $this->saveEntity($member);
            $response = $this->mailChimp->post("/lists/$listId/members", $member->toMailChimpArray());
            $this->saveEntity($member->setMailChimpId($response->get('id')));
        } catch (Exception $exception) {
            return $this->errorResponse(['message' => $exception->getMessage()]);
        }

        return $this->successfulResponse($member->toArray());
    }
}