<?php declare(strict_types=1);

namespace Circli\ApiAuth;

use Circli\Extension\Auth\Auth;
use Circli\Extension\Auth\Events\AccessDenied;
use Circli\Extension\Auth\Events\AclAccessRequest;
use Circli\Extension\Auth\Events\OwnerAccessRequest;
use Circli\Extension\Auth\Repositories\Objects\AuthObject;
use Circli\Extension\Auth\Repositories\Objects\NullAuthObject;
use Circli\Extension\Auth\Voter\AccessRequestEventInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ApiAuth implements Auth
{
    /** @var AuthObject */
    private $object;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(AuthObject $object, EventDispatcherInterface $eventDispatcher)
    {
        $this->object = $object;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getObject(): AuthObject
    {
        return $this->object;
    }

    public function isAuthenticated(): bool
    {
        return !$this->object instanceof NullAuthObject;
    }

    public function isAllowed(string $key): bool
    {
        /** @var AclAccessRequest $event */
        $event = $this->eventDispatcher->dispatch(new AclAccessRequest($key));
        if (!$event->allowed()) {
            $this->eventDispatcher->dispatch(new AccessDenied($this->object, AccessDenied::PERMISSION, $key));
        }
        return $event->allowed();
    }

    public function haveAccess(AccessRequestEventInterface $key): bool
    {
        /** @var AclAccessRequest $event */
        $event = $this->eventDispatcher->dispatch($key);
        return $event->allowed();
    }

    public function isOwner(object $obj): bool
    {
        /** @var OwnerAccessRequest $event */
        $event = $this->eventDispatcher->dispatch(new OwnerAccessRequest($obj));
        if (!$event->allowed()) {
            $this->eventDispatcher->dispatch(new AccessDenied($this->object, AccessDenied::OWNER, $obj));
        }
        return $event->allowed();
    }
}
