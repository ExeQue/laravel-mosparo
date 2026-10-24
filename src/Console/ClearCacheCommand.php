<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Console;

use ExeQue\Mosparo\Mosparo;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ClearCacheCommand extends Command
{
    protected $name = 'mosparo:clear-cache';

    protected $description = 'Clear what was cached from mosparo, for one project or all of them';

    public function handle(Mosparo $mosparo): int
    {
        $project = $this->argument('project');

        $mosparo->flushCache($project);

        $this->components->info($project === null
            ? 'Cleared the mosparo cache of every project.'
            : "Cleared the mosparo cache of the [{$project}] project.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, InputArgument>
     */
    protected function getArguments(): array
    {
        return [
            new InputArgument('project', InputArgument::OPTIONAL, 'The project to clear; every project when left out'),
        ];
    }
}
