<?php

use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->accessDecisionManager()
        ->strategy('affirmative')
        ->allowIfAllAbstain(false)
    ;
};