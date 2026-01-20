<?php
// src/Form/Model/ChangePassword.php
namespace App\Form\Model;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ChangePassword
{
    #[UserPassword(message: 'Mot de passe actuel incorrect.')]
    #[NotBlank]
    private ?string $currentPassword = null;

    #[Length(min: 6, minMessage: 'Le mot de passe doit faire au moins 6 caractères.')]
    #[NotBlank]
    private ?string $newPassword = null;

    // Getters/setters...
    public function getCurrentPassword(): ?string { return $this->currentPassword; }
    public function setCurrentPassword(string $currentPassword): self { $this->currentPassword = $currentPassword; return $this; }

    public function getNewPassword(): ?string { return $this->newPassword; }
    public function setNewPassword(string $newPassword): self { $this->newPassword = $newPassword; return $this; }
}
