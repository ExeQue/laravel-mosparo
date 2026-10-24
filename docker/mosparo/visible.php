<?php

/*
 * Shows the box of one project as a checkbox: sets its design to mosparo's
 * visible "simple" mode and renders the project's stylesheet.
 *
 * Usage: php visible.php <project-uuid>
 */

declare(strict_types=1);

require '/mosparo/vendor/autoload.php';

new Symfony\Component\Dotenv\Dotenv()->bootEnv('/mosparo/.env');
$kernel = new Mosparo\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();

$project = $entityManager->getRepository(Mosparo\Entity\Project::class)->findOneBy(['uuid' => $argv[1] ?? '']);

if (! $project) {
    fwrite(STDERR, 'No project with uuid ['.($argv[1] ?? '').'].'.PHP_EOL);
    exit(1);
}

$project->setConfigValue('designMode', 'simple');
$entityManager->flush();

$helper = $container->has('workbench.design_helper') ? 'workbench.design_helper' : 'lerd.design_helper';
$container->get($helper)->generateCssCache($project);
$entityManager->flush();
