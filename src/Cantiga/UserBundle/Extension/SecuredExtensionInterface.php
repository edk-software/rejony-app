<?php

namespace Cantiga\UserBundle\Extension;

/**
 * Provides extension's security.
 */
interface SecuredExtensionInterface
{
    public function setGrantedForAll(...$attributes);
    public function setGrantedForAny(...$attributes);
    public function isAvailable() : bool;
}
