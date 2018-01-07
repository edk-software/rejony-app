<?php

namespace Cantiga\KnowledgeBundle\Entity;

interface LevelAwareInterface
{
    const LEVEL_ALL = 0;
    const LEVEL_AREA = 1;
    const LEVEL_GROUP = 2;
    const LEVEL_PROJECT = 3;

    public function getLevel();

    public function setLevel($level);
}
