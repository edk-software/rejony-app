<?php

namespace Cantiga\UserBundle\EventListener;

use Cantiga\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Event listener
 */
class TimezoneListener
{
	/** @var AuthorizationCheckerInterface */
	private $authorizationChecker;

	/** @var TokenStorage */
	private $tokenStorage;

	/**
	 * Constructor
	 *
	 * @param AuthorizationCheckerInterface $authorizationChecker authorization checker
	 * @param TokenStorage                  $tokenStorage         token storage
	 */
	public function __construct(AuthorizationCheckerInterface $authorizationChecker, TokenStorage $tokenStorage)
	{
		$this->authorizationChecker = $authorizationChecker;
		$this->tokenStorage = $tokenStorage;
	}

	/**
	 * On kernel request
	 */
	public function onKernelRequest()
	{
		$token = $this->tokenStorage->getToken();
		if (!isset($token)) {
			return;
		}

		if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
			return;
		}

		$user = $token->getUser();
		if (!($user instanceof User) || empty($user->getSettingsTimezone())) {
			return;
		}

		date_default_timezone_set($user->getSettingsTimezone());
	}
}
