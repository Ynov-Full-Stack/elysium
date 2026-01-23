<?php

namespace App\Mail;

use Symfony\Component\Messenger\MessageBusInterface;

final class MailService
{
    public function __construct(
        private MessageBusInterface $bus
    ) {}

    public function send(MailMessage $message): void
    {
        $this->bus->dispatch($message);
    }


    // Usage exemple:
    // $messages = $this->mailService->buildMessages(
    //     users: $userRepository->findAdmins();,
    //     factory: [MailMessage::class, 'wantedMailBuilder'],
    //     args: [
    //         arg1,
    //         arg2,
    //     ]
    // );
    // $this->mailService->sendToMany($messages);

    public function buildAndSendMessages(iterable $users, callable $factory, array $args = []) {
        $messages = [];

        foreach ($users as $user) {
            $messages[] = $factory(
                $user,
                ...$args
            );
        }

        foreach ($messages as $message) {
            $this->send($message);
        }
    }

    // public function sendToMany(iterable $messages): void
    // {
    //     foreach ($messages as $message) {
    //         $this->send($message);
    //     }
    // }
}
