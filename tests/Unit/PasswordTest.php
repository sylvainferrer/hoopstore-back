<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Tests unitaires minimalistes sur la longueur du mot de passe utilisateur.
 */
final class PasswordTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        // Pour lire les attributs #[Assert\...] de ton entité
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

   /** Mot de passe manquant -> violation NotBlank */
    public function testPasswordBlankIsInvalid(): void
    {
        $violations = $this->validator->validatePropertyValue(
            User::class,
            'password',
            null // ou '' pour tester la chaîne vide
        );

        $this->assertGreaterThan(
            0,
            count($violations),
            'Un mot de passe vide devrait déclencher une violation.'
        );
        $this->assertSame(
            'Le mot de passe est manquant ou erroné.',
            $violations[0]->getMessage(),
            'Le message attendu pour mot de passe vide ne correspond pas.'
        );
    }

    /** < 8 caractères -> violation Length(min=8) */
    public function testPasswordTooShortIsInvalid(): void
    {
        $violations = $this->validator->validatePropertyValue(
            User::class,
            'password',
            '1234567' // 7 caractères
        );

        $this->assertGreaterThan(
            0,
            count($violations),
            'Un mot de passe trop court devrait déclencher une violation.'
        );
        $this->assertSame(
            'Le mot de passe doit contenir au moins 8 caractères.',
            $violations[0]->getMessage(),
            'Le message attendu pour mot de passe trop court ne correspond pas.'
        );
    }

    /** >= 8 caractères -> aucune violation */
    public function testPasswordMinLengthIsValid(): void
    {
        $violations = $this->validator->validatePropertyValue(
            User::class,
            'password',
            '12345678' // 8 caractères
        );

        $this->assertCount(
            0,
            $violations,
            'Un mot de passe valide ne doit pas générer de violation.'
        );
    }

}
