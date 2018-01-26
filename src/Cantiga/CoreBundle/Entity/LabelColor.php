<?php
/**
 * Created by PhpStorm.
 * User: piotr.zak
 * Date: 2018-01-26
 * Time: 13:26
 */

namespace Cantiga\CoreBundle\Entity;


class LabelColor
{
    const STATUS_NEW = 'blue';
    const STATUS_VERIFICATION = 'purple';
    const STATUS_APPROVED = 'green';
    const STATUS_REVOKED = 'red';

    const LABEL_OK = 'green';
    const LABEL_WARNING = 'orange';
    const LABEL_ERROR = 'red';
}