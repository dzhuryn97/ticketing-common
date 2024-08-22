<?php

declare(strict_types=1);

namespace Ticketing\Common\Application\Security;



interface Security
{
    public function isAuthenticated(): bool;

    public function connectedUser(): ?AuthUserDto;
}
