<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\StateMachine\Exception;

class NoTransitionException extends \Exception{
    protected $message = 'No transition necessary';
}
