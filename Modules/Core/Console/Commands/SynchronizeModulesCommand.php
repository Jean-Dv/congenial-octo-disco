<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Application\Module\SynchronizeModulesUseCase;

final class SynchronizeModulesCommand extends Command
{
    protected $signature = 'moon:sync-modules';

    protected $description = 'Synchronize discovered modules with the database';

    public function handle(SynchronizeModulesUseCase $synchronizeModules): int
    {
        $synchronizeModules->handle();
        $this->info('Modules synchronized successfully.');

        return self::SUCCESS;
    }
}
