<?php

/*
 * Installs mosparo unattended on SQLite and creates the workbench project, so
 * `docker compose up serve mosparo` gives the demo forms a working mosparo
 * without clicking through the installer. Runs on every start; both steps
 * skip what is already there.
 *
 * Login: admin@example.com / password, at http://localhost:8088.
 */

declare(strict_types=1);

$configFile = '/mosparo-data/env.mosparo.php';
$config = is_file($configFile) ? (include $configFile) : [];

$write = function (array $values) use ($configFile, &$config): void {
    $config = array_merge(is_array($config) ? $config : [], $values);
    file_put_contents($configFile, '<?php'.PHP_EOL.PHP_EOL.'return '.var_export($config, true).';'.PHP_EOL);
};

if (empty($config['database_path'])) {
    @mkdir('/mosparo-data/var/data', 0775, true);

    $write([
        'database_system' => 'sqlite',
        'database_driver' => 'pdo_sqlite',
        'database_path' => '/mosparo-data/var/data/mosparo.db',
        'database_version' => new PDO('sqlite::memory:')->query('select sqlite_version()')->fetchColumn(),
        'mosparo_name' => 'mosparo',
        'encryption_key' => bin2hex(random_bytes(32)),
        'secret' => bin2hex(random_bytes(32)),
    ]);
}

@unlink('/mosparo/config/env.mosparo.php');
symlink($configFile, '/mosparo/config/env.mosparo.php');

passthru('php /mosparo/bin/console doctrine:migrations:migrate -n', $exitCode);

if ($exitCode !== 0) {
    exit($exitCode);
}

require '/mosparo/vendor/autoload.php';

new Symfony\Component\Dotenv\Dotenv()->bootEnv('/mosparo/.env');
$kernel = new Mosparo\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$entityManager = $kernel->getContainer()->get('doctrine')->getManager();

$user = $entityManager->getRepository(Mosparo\Entity\User::class)->findOneBy(['email' => 'admin@example.com']);

if (! $user) {
    $user = new Mosparo\Entity\User();
    $user->setEmail('admin@example.com');
    $hashers = new Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory(['auto' => ['algorithm' => 'auto']]);
    $user->setPassword($hashers->getPasswordHasher('auto')->hash('password'));
    $user->addRole('ROLE_USER');
    $user->addRole('ROLE_ADMIN');
    $entityManager->persist($user);
    $entityManager->flush();

    $write([
        'mosparo_installed' => true,
        'mosparo_installed_version' => Mosparo\Kernel::VERSION,
        'mosparo_initial_version' => Mosparo\Kernel::VERSION,
        'mosparo_assets_version' => bin2hex(random_bytes(8)),
    ]);
}

if ($entityManager->getRepository(Mosparo\Entity\Project::class)->findOneBy(['uuid' => 'workbench'])) {
    return;
}

$project = new Mosparo\Entity\Project();
$project->setName('workbench');
$project->setHosts(['localhost', '127.0.0.1']);
$project->setPublicKey('workbench-public');
$project->setPrivateKey('workbench-private');
// The constructor assigns a random uuid and there is no setter.
new ReflectionProperty($project, 'uuid')->setValue($project, 'workbench');
$project->setConfigValue('designMode', 'invisible-simple');

$member = new Mosparo\Entity\ProjectMember();
$member->setProject($project);
$member->setUser($user);
$member->setRole(Mosparo\Entity\ProjectMember::ROLE_OWNER);

$entityManager->persist($project);
$entityManager->persist($member);
$entityManager->flush();

// Without a rendered stylesheet mosparo serves its default, visible design.
$kernel->getContainer()->get('workbench.design_helper')->generateCssCache($project);
$entityManager->flush();
