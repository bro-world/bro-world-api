<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\General;

use App\Crm\Domain\Entity\Billing;
use App\Crm\Domain\Entity\Company;
use App\Crm\Domain\Entity\Project;
use App\Crm\Domain\Entity\Sprint;
use App\Crm\Domain\Entity\Task;
use App\Crm\Domain\Entity\TaskRequest;
use App\Crm\Domain\Enum\ProjectStatus;
use App\Crm\Domain\Enum\SprintStatus;
use App\Crm\Domain\Enum\TaskPriority;
use App\Crm\Domain\Enum\TaskRequestStatus;
use App\Crm\Domain\Enum\TaskStatus;
use App\Crm\Infrastructure\Repository\BillingRepository;
use App\Crm\Infrastructure\Repository\CompanyRepository;
use App\Crm\Infrastructure\Repository\CrmProjectRepositoryRepository;
use App\Crm\Infrastructure\Repository\CrmRepository;
use App\Crm\Infrastructure\Repository\ProjectRepository;
use App\Crm\Infrastructure\Repository\SprintRepository;
use App\Crm\Infrastructure\Repository\TaskRepository;
use App\Crm\Infrastructure\Repository\TaskRequestRepository;
use App\Role\Domain\Enum\Role;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function array_map;
use function is_array;
use function is_numeric;
use function is_string;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_MANAGER->value)]
final readonly class GeneralCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CrmRepository $crmRepository,
        private CompanyRepository $companyRepository,
        private BillingRepository $billingRepository,
        private ProjectRepository $projectRepository,
        private TaskRepository $taskRepository,
        private TaskRequestRepository $taskRequestRepository,
        private SprintRepository $sprintRepository,
        private CrmProjectRepositoryRepository $crmProjectRepositoryRepository,
    ) {
    }

    #[Route('/v1/crm/general/companies', methods: [Request::METHOD_POST])]
    #[OA\Post(summary: 'General - Create Company')]
    public function createCompany(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $crmId = $payload['crmId'] ?? null;
        $name = $payload['name'] ?? null;
        if (!is_string($crmId) || !is_string($name) || $name === '') {
            return $this->badRequest('Fields "crmId" and "name" are required.');
        }

        $crm = $this->crmRepository->find($crmId);
        if ($crm === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'CRM not found.');
        }

        $company = (new Company())
            ->setCrm($crm)
            ->setName($name)
            ->setIndustry($this->nullableString($payload['industry'] ?? null))
            ->setWebsite($this->nullableString($payload['website'] ?? null))
            ->setContactEmail($this->nullableString($payload['contactEmail'] ?? null))
            ->setPhone($this->nullableString($payload['phone'] ?? null));

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $company->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/crm/general/companies/{company}', methods: [Request::METHOD_PATCH])]
    #[OA\Patch(summary: 'General - Update Company')]
    public function patchCompany(Company $company, Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        if (isset($payload['name']) && is_string($payload['name']) && $payload['name'] !== '') {
            $company->setName($payload['name']);
        }

        if (isset($payload['industry'])) {
            $company->setIndustry($this->nullableString($payload['industry']));
        }

        if (isset($payload['website'])) {
            $company->setWebsite($this->nullableString($payload['website']));
        }

        if (isset($payload['contactEmail'])) {
            $company->setContactEmail($this->nullableString($payload['contactEmail']));
        }

        if (isset($payload['phone'])) {
            $company->setPhone($this->nullableString($payload['phone']));
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $company->getId()]);
    }

    #[Route('/v1/crm/general/companies/{company}', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(summary: 'General - Delete Company')]
    public function deleteCompany(Company $company): JsonResponse
    {
        $this->entityManager->remove($company);
        $this->entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/crm/general/billings', methods: [Request::METHOD_GET])]
    #[OA\Get(summary: 'General - List Billings')]
    public function listBillings(): JsonResponse
    {
        $items = array_map(fn (Billing $billing): array => $this->serializeBilling($billing), $this->billingRepository->findBy([], ['createdAt' => 'DESC']));

        return new JsonResponse(['items' => $items]);
    }

    #[Route('/v1/crm/general/billings/{billing}', methods: [Request::METHOD_GET])]
    #[OA\Get(summary: 'General - Get Billing')]
    public function getBilling(Billing $billing): JsonResponse
    {
        return new JsonResponse($this->serializeBilling($billing));
    }

    #[Route('/v1/crm/general/billings', methods: [Request::METHOD_POST])]
    #[OA\Post(summary: 'General - Create Billing')]
    public function createBilling(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $companyId = $payload['companyId'] ?? null;
        $label = $payload['label'] ?? null;
        $amount = $payload['amount'] ?? null;

        if (!is_string($companyId) || !is_string($label) || !is_numeric($amount)) {
            return $this->badRequest('Fields "companyId", "label" and "amount" are required.');
        }

        $company = $this->companyRepository->find($companyId);
        if ($company === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Company not found.');
        }

        $billing = (new Billing())
            ->setCompany($company)
            ->setLabel($label)
            ->setAmount((float)$amount)
            ->setCurrency($this->nullableString($payload['currency'] ?? null) ?? 'EUR')
            ->setStatus($this->nullableString($payload['status'] ?? null) ?? 'pending')
            ->setDueAt($this->parseNullableDate($payload['dueAt'] ?? null))
            ->setPaidAt($this->parseNullableDate($payload['paidAt'] ?? null));

        $this->entityManager->persist($billing);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $billing->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/crm/general/billings/{billing}', methods: [Request::METHOD_PATCH])]
    #[OA\Patch(summary: 'General - Update Billing')]
    public function patchBilling(Billing $billing, Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        if (isset($payload['label']) && is_string($payload['label']) && $payload['label'] !== '') {
            $billing->setLabel($payload['label']);
        }

        if (isset($payload['amount']) && is_numeric($payload['amount'])) {
            $billing->setAmount((float)$payload['amount']);
        }

        if (isset($payload['currency'])) {
            $billing->setCurrency($this->nullableString($payload['currency']) ?? 'EUR');
        }

        if (isset($payload['status'])) {
            $billing->setStatus($this->nullableString($payload['status']) ?? 'pending');
        }

        if (isset($payload['dueAt'])) {
            $billing->setDueAt($this->parseNullableDate($payload['dueAt']));
        }

        if (isset($payload['paidAt'])) {
            $billing->setPaidAt($this->parseNullableDate($payload['paidAt']));
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $billing->getId()]);
    }

    #[Route('/v1/crm/general/billings/{billing}', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(summary: 'General - Delete Billing')]
    public function deleteBilling(Billing $billing): JsonResponse
    {
        $this->entityManager->remove($billing);
        $this->entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/crm/general/projects', methods: [Request::METHOD_POST])]
    #[OA\Post(summary: 'General - Create Project')]
    public function createProject(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $companyId = $payload['companyId'] ?? null;
        $name = $payload['name'] ?? null;
        if (!is_string($companyId) || !is_string($name) || $name === '') {
            return $this->badRequest('Fields "companyId" and "name" are required.');
        }

        $company = $this->companyRepository->find($companyId);
        if ($company === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Company not found.');
        }

        $project = (new Project())
            ->setCompany($company)
            ->setName($name)
            ->setCode($this->nullableString($payload['code'] ?? null))
            ->setDescription($this->nullableString($payload['description'] ?? null))
            ->setStatus(ProjectStatus::tryFrom((string)($payload['status'] ?? 'planned')) ?? ProjectStatus::PLANNED)
            ->setStartedAt($this->parseNullableDate($payload['startedAt'] ?? null))
            ->setDueAt($this->parseNullableDate($payload['dueAt'] ?? null));

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $project->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/crm/general/projects/{project}', methods: [Request::METHOD_PATCH])]
    #[OA\Patch(summary: 'General - Update Project')]
    public function patchProject(Project $project, Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        if (isset($payload['name']) && is_string($payload['name']) && $payload['name'] !== '') {
            $project->setName($payload['name']);
        }

        if (isset($payload['code'])) {
            $project->setCode($this->nullableString($payload['code']));
        }

        if (isset($payload['description'])) {
            $project->setDescription($this->nullableString($payload['description']));
        }

        if (isset($payload['status'])) {
            $project->setStatus(ProjectStatus::tryFrom((string)$payload['status']) ?? ProjectStatus::PLANNED);
        }

        if (isset($payload['startedAt'])) {
            $project->setStartedAt($this->parseNullableDate($payload['startedAt']));
        }

        if (isset($payload['dueAt'])) {
            $project->setDueAt($this->parseNullableDate($payload['dueAt']));
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $project->getId()]);
    }

    #[Route('/v1/crm/general/projects/{project}', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(summary: 'General - Delete Project')]
    public function deleteProject(Project $project): JsonResponse
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/crm/general/tasks', methods: [Request::METHOD_POST])]
    #[OA\Post(summary: 'General - Create Task')]
    public function createTask(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $projectId = $payload['projectId'] ?? null;
        $title = $payload['title'] ?? null;
        if (!is_string($projectId) || !is_string($title) || $title === '') {
            return $this->badRequest('Fields "projectId" and "title" are required.');
        }

        $project = $this->projectRepository->find($projectId);
        if ($project === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Project not found.');
        }

        $task = (new Task())
            ->setProject($project)
            ->setTitle($title)
            ->setDescription($this->nullableString($payload['description'] ?? null))
            ->setStatus(TaskStatus::tryFrom((string)($payload['status'] ?? 'todo')) ?? TaskStatus::TODO)
            ->setPriority(TaskPriority::tryFrom((string)($payload['priority'] ?? 'medium')) ?? TaskPriority::MEDIUM)
            ->setDueAt($this->parseNullableDate($payload['dueAt'] ?? null));

        if (isset($payload['estimatedHours']) && is_numeric($payload['estimatedHours'])) {
            $task->setEstimatedHours((float)$payload['estimatedHours']);
        }

        if (isset($payload['sprintId']) && is_string($payload['sprintId'])) {
            $sprint = $this->sprintRepository->find($payload['sprintId']);
            if ($sprint !== null) {
                $task->setSprint($sprint);
            }
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $task->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/crm/general/tasks/{task}', methods: [Request::METHOD_PATCH])]
    #[OA\Patch(summary: 'General - Update Task')]
    public function patchTask(Task $task, Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        if (isset($payload['title']) && is_string($payload['title']) && $payload['title'] !== '') {
            $task->setTitle($payload['title']);
        }

        if (isset($payload['description'])) {
            $task->setDescription($this->nullableString($payload['description']));
        }

        if (isset($payload['status'])) {
            $task->setStatus(TaskStatus::tryFrom((string)$payload['status']) ?? TaskStatus::TODO);
        }

        if (isset($payload['priority'])) {
            $task->setPriority(TaskPriority::tryFrom((string)$payload['priority']) ?? TaskPriority::MEDIUM);
        }

        if (isset($payload['dueAt'])) {
            $task->setDueAt($this->parseNullableDate($payload['dueAt']));
        }

        if (isset($payload['estimatedHours']) && is_numeric($payload['estimatedHours'])) {
            $task->setEstimatedHours((float)$payload['estimatedHours']);
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $task->getId()]);
    }

    #[Route('/v1/crm/general/tasks/{task}', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(summary: 'General - Delete Task')]
    public function deleteTask(Task $task): JsonResponse
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/crm/general/task-requests', methods: [Request::METHOD_GET])]
    #[OA\Get(summary: 'General - List Task Requests')]
    public function listTaskRequests(): JsonResponse
    {
        $items = array_map(fn (TaskRequest $taskRequest): array => $this->serializeTaskRequest($taskRequest), $this->taskRequestRepository->findBy([], ['createdAt' => 'DESC']));

        return new JsonResponse(['items' => $items]);
    }

    #[Route('/v1/crm/general/task-requests/{taskRequest}', methods: [Request::METHOD_GET])]
    #[OA\Get(summary: 'General - Get Task Request')]
    public function getTaskRequest(TaskRequest $taskRequest): JsonResponse
    {
        return new JsonResponse($this->serializeTaskRequest($taskRequest));
    }

    #[Route('/v1/crm/general/task-requests', methods: [Request::METHOD_POST])]
    #[OA\Post(summary: 'General - Create Task Request')]
    public function createTaskRequest(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $taskId = $payload['taskId'] ?? null;
        $repositoryId = $payload['repositoryId'] ?? null;
        $title = $payload['title'] ?? null;

        if (!is_string($taskId) || !is_string($repositoryId) || !is_string($title) || $title === '') {
            return $this->badRequest('Fields "taskId", "repositoryId" and "title" are required.');
        }

        $task = $this->taskRepository->find($taskId);
        if ($task === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Task not found.');
        }

        $repository = $this->crmProjectRepositoryRepository->find($repositoryId);
        if ($repository === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Repository not found.');
        }

        $taskRequest = (new TaskRequest())
            ->setTask($task)
            ->setRepository($repository)
            ->setTitle($title)
            ->setDescription($this->nullableString($payload['description'] ?? null))
            ->setStatus(TaskRequestStatus::tryFrom((string)($payload['status'] ?? 'pending')) ?? TaskRequestStatus::PENDING);

        if (isset($payload['requestedAt']) && is_string($payload['requestedAt'])) {
            $taskRequest->setRequestedAt($this->parseDate($payload['requestedAt']));
        }

        if (isset($payload['resolvedAt'])) {
            $taskRequest->setResolvedAt($this->parseNullableDate($payload['resolvedAt']));
        }

        $this->entityManager->persist($taskRequest);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $taskRequest->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/crm/general/task-requests/{taskRequest}', methods: [Request::METHOD_PATCH])]
    #[OA\Patch(summary: 'General - Update Task Request')]
    public function patchTaskRequest(TaskRequest $taskRequest, Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        if (isset($payload['title']) && is_string($payload['title']) && $payload['title'] !== '') {
            $taskRequest->setTitle($payload['title']);
        }

        if (isset($payload['description'])) {
            $taskRequest->setDescription($this->nullableString($payload['description']));
        }

        if (isset($payload['status'])) {
            $taskRequest->setStatus(TaskRequestStatus::tryFrom((string)$payload['status']) ?? TaskRequestStatus::PENDING);
        }

        if (isset($payload['resolvedAt'])) {
            $taskRequest->setResolvedAt($this->parseNullableDate($payload['resolvedAt']));
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $taskRequest->getId()]);
    }

    #[Route('/v1/crm/general/task-requests/{taskRequest}', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(summary: 'General - Delete Task Request')]
    public function deleteTaskRequest(TaskRequest $taskRequest): JsonResponse
    {
        $this->entityManager->remove($taskRequest);
        $this->entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/crm/general/sprints', methods: [Request::METHOD_POST])]
    #[OA\Post(summary: 'General - Create Sprint')]
    public function createSprint(Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $projectId = $payload['projectId'] ?? null;
        $name = $payload['name'] ?? null;

        if (!is_string($projectId) || !is_string($name) || $name === '') {
            return $this->badRequest('Fields "projectId" and "name" are required.');
        }

        $project = $this->projectRepository->find($projectId);
        if ($project === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Project not found.');
        }

        $sprint = (new Sprint())
            ->setProject($project)
            ->setName($name)
            ->setGoal($this->nullableString($payload['goal'] ?? null))
            ->setStatus(SprintStatus::tryFrom((string)($payload['status'] ?? 'planned')) ?? SprintStatus::PLANNED)
            ->setStartDate($this->parseNullableDate($payload['startDate'] ?? null))
            ->setEndDate($this->parseNullableDate($payload['endDate'] ?? null));

        $this->entityManager->persist($sprint);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $sprint->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/crm/general/sprints/{sprint}', methods: [Request::METHOD_PATCH])]
    #[OA\Patch(summary: 'General - Update Sprint')]
    public function patchSprint(Sprint $sprint, Request $request): JsonResponse
    {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        if (isset($payload['name']) && is_string($payload['name']) && $payload['name'] !== '') {
            $sprint->setName($payload['name']);
        }

        if (isset($payload['goal'])) {
            $sprint->setGoal($this->nullableString($payload['goal']));
        }

        if (isset($payload['status'])) {
            $sprint->setStatus(SprintStatus::tryFrom((string)$payload['status']) ?? SprintStatus::PLANNED);
        }

        if (isset($payload['startDate'])) {
            $sprint->setStartDate($this->parseNullableDate($payload['startDate']));
        }

        if (isset($payload['endDate'])) {
            $sprint->setEndDate($this->parseNullableDate($payload['endDate']));
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $sprint->getId()]);
    }

    #[Route('/v1/crm/general/sprints/{sprint}', methods: [Request::METHOD_DELETE])]
    #[OA\Delete(summary: 'General - Delete Sprint')]
    public function deleteSprint(Sprint $sprint): JsonResponse
    {
        $this->entityManager->remove($sprint);
        $this->entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /** @return array<string,mixed>|JsonResponse */
    private function decodePayload(Request $request): array|JsonResponse
    {
        try {
            $payload = json_decode((string)$request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->badRequest('Invalid JSON payload.');
        }

        if (!is_array($payload)) {
            return $this->badRequest('Invalid JSON payload.');
        }

        return $payload;
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $value);
        if ($date === false) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid ISO 8601 date format.');
        }

        return $date;
    }

    private function parseNullableDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Date must be a string or null.');
        }

        return $this->parseDate($value);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    private function badRequest(string $message): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'errors' => [],
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    /** @return array<string,mixed> */
    private function serializeBilling(Billing $billing): array
    {
        return [
            'id' => $billing->getId(),
            'companyId' => $billing->getCompany()?->getId(),
            'label' => $billing->getLabel(),
            'amount' => $billing->getAmount(),
            'currency' => $billing->getCurrency(),
            'status' => $billing->getStatus(),
            'dueAt' => $billing->getDueAt()?->format(DATE_ATOM),
            'paidAt' => $billing->getPaidAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string,mixed> */
    private function serializeTaskRequest(TaskRequest $taskRequest): array
    {
        return [
            'id' => $taskRequest->getId(),
            'taskId' => $taskRequest->getTask()?->getId(),
            'repositoryId' => $taskRequest->getRepository()?->getId(),
            'title' => $taskRequest->getTitle(),
            'description' => $taskRequest->getDescription(),
            'status' => $taskRequest->getStatus()->value,
            'requestedAt' => $taskRequest->getRequestedAt()->format(DATE_ATOM),
            'resolvedAt' => $taskRequest->getResolvedAt()?->format(DATE_ATOM),
        ];
    }
}
