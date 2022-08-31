<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutEventSubscriber implements EventSubscriberInterface
{
    private $urlGeneratorInterface;
    private $flashBagInterface;

    public function __construct(FlashBagInterface $flashBagInterface, UrlGeneratorInterface $urlGeneratorInterface)
    {
        $this->urlGeneratorInterface = $urlGeneratorInterface;
        $this->flashBagInterface = $flashBagInterface;
    }

    public function onLogoutEvent(LogoutEvent $event): void
    {
        $this->flashBagInterface->add(
            'success',
            'Logged out successfully!'
        );

        $event->setResponse(new RedirectResponse($this->urlGeneratorInterface->generate('app_home')));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
