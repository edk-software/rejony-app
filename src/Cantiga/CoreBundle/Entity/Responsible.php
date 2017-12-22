<?php

namespace Cantiga\CoreBundle\Entity;

/**
 * Short user information about responsible person
 * It is a virtual model
 */
class Responsible
{
	private $id;
	private $name;

	public function __construct($id, string $name)
	{
		$this->id = (int) $id;
		$this->name = $name;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}
}
