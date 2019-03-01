<?php

namespace Cantiga\UserBundle\Extension;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

trait SecuredExtensionTrait
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var string[]|null */
    private $allForGranted;

    /** @var string[]|null */
    private $anyForGranted;

    public function setAuthChecker(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function setGrantedForAll(...$attributes)
    {
        $this->allForGranted = $attributes;
    }

    public function setGrantedForAny(...$attributes)
    {
        $this->anyForGranted = $attributes;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailableForAll() && $this->isAvailableForAny();
    }

    private function isAvailableForAll(): bool
    {
        if (!isset($this->allForGranted)) {
            return true;
        }

        foreach ($this->allForGranted as $role) {
            if (!$this->authChecker->isGranted($role)) {
                return false;
            }
        }

        return true;
    }

    private function isAvailableForAny(): bool
    {
        if (!isset($this->anyForGranted)) {
            return true;
        }

        foreach ($this->anyForGranted as $role) {
            if ($this->authChecker->isGranted($role)) {
                return true;
            }
        }

        return false;
    }
}
