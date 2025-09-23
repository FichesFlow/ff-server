<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        $data = $event->getData();

        // Add custom data
        $data['id'] = $user->getId();
        $data['username'] = $user->getUsername();

        $event->setData($data);
    }
}
