<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class BanWords extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */

    function __construct(
        public $message = 'This contains a banned word "{{ banWord }}".',
        public $banWords = ['Viagra', 'Spam'],
        ?array $groups = null,
        mixed $payload = null,
    )
    {
        parent::__construct(null, $groups, $payload);
    }

}
