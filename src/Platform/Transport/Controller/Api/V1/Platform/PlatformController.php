<?php

declare(strict_types=1);

namespace App\Platform\Transport\Controller\Api\V1\Platform;

use App\General\Transport\Rest\Controller;
use App\General\Transport\Rest\ResponseHandler;
use App\General\Transport\Rest\Traits\Actions;
use App\Platform\Application\DTO\Platform\PlatformCreate;
use App\Platform\Application\DTO\Platform\PlatformPatch;
use App\Platform\Application\DTO\Platform\PlatformUpdate;
use App\Platform\Application\Resource\PlatformResource;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @package App\Platform
 *
 * @method PlatformResource getResource()
 * @method ResponseHandler getResponseHandler()
 */
#[AsController]
#[Route(
    path: '/v1/platform',
)]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[OA\Tag(name: 'Platform Management')]
class PlatformController extends Controller
{
    use Actions\Admin\CountAction;
    use Actions\Admin\FindAction;
    use Actions\Admin\FindOneAction;
    use Actions\Admin\IdsAction;
    use Actions\Root\CreateAction;
    use Actions\Root\DeleteAction;
    use Actions\Root\PatchAction;
    use Actions\Root\UpdateAction;

    /**
     * @var array<string, string>
     */
    protected static array $dtoClasses = [
        Controller::METHOD_CREATE => PlatformCreate::class,
        Controller::METHOD_UPDATE => PlatformUpdate::class,
        Controller::METHOD_PATCH => PlatformPatch::class,
    ];

    public function __construct(
        PlatformResource $resource,
    ) {
        parent::__construct($resource);
    }
}
