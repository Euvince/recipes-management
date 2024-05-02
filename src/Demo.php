<?php 

namespace App;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Demo
{
    function __construct(
        private readonly ValidatorInterface $validator,
        /* #[Autowire("hello")] */
        private readonly string $key
    )
    {
    }

}