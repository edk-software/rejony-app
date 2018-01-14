<?php

namespace Cantiga\KnowledgeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HtmlStringValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        /** @var HtmlString $constraint */
        if ($value != strip_tags($value, implode('', $constraint->allowableTags))) {
            $this->context
                ->buildViolation($constraint->messageImproperTags)
                ->setParameter('{{ tags }}', implode(', ', $constraint->allowableTags))
                ->addViolation()
            ;
            return;
        }

        $tagNames = [];
        foreach ($constraint->allowableTags as $tag) {
            $tagNames[] = preg_replace('#[^0-9a-z]#i', '', $tag);
        }
        $matches = [];
        if (preg_match_all('#(<(/?(' . implode('|', $tagNames) . '))(\s[^>]*)?>)#mi', $value, $matches)) {
            $selfClosingTags = [
                '<br>',
            ];
            $tagNumbers = [];
            foreach ($matches[2] as $tagName) {
                if (in_array($tagName, $selfClosingTags)) {
                    continue;
                }
                $change = strpos($tagName, '/') === 0 ? -1 : 1;
                if ($change === -1) {
                    $tagName = substr($tagName, 1);
                }
                if (!array_key_exists($tagName, $tagNumbers)) {
                    $tagNumbers[$tagName] = 0;
                }
                $tagNumbers[$tagName] += $change;
                if ($tagNumbers[$tagName] < 0) {
                    $this->context
                        ->buildViolation($constraint->messageTagIncorrectlyClosed)
                        ->setParameter('{{ tag }}', '<' . $tagName . '>')
                        ->addViolation()
                    ;
                    $tagNumbers[$tagName] = 0;
                }
            }
            foreach ($tagNumbers as $tagName => $tagNumber) {
                if ($tagNumber > 0) {
                    $this->context
                        ->buildViolation($constraint->messageTagUnclosed)
                        ->setParameter('{{ tag }}', '<' . $tagName . '>')
                        ->addViolation()
                    ;
                }
            }
        }
    }
}
