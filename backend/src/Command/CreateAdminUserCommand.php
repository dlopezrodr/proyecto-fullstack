<?php

namespace App\Command;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Crea un usuario administrador inicial si no existe.',
)]
class CreateAdminUserCommand extends Command
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. Verificar si el usuario ya existe (para evitar duplicados)
        $repository = $this->entityManager->getRepository(Usuario::class);
        if ($repository->findOneBy(['username' => 'admin'])) {
            $output->writeln('<comment>El usuario administrador ya existe. Omitiendo creación.</comment>');
            return Command::SUCCESS;
        }

        // 2. Crear y configurar la entidad Usuario
        $user = new Usuario();
        $user->setUsername('admin');
        $user->setEmail('admin@booksmart.com');
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        // 3. Hashear la contraseña (¡CRÍTICO!)
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            '1234' // Contraseña simple para inicio (cambiar en producción)
        );
        $user->setPasswordHash($hashedPassword);

        // 4. Persistir en la DB
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>✅ Usuario administrador "admin" creado exitosamente.</info>');

        return Command::SUCCESS;
    }
}