<?php
/**
 * Knowfox - Personal Knowledge Management
 * Copyright (C) 2017  Olav Schettler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Knowfox\Console\Commands;

use Illuminate\Console\Command;
use Knowfox\Jobs\ImportEvernote as ImportJob;
use Knowfox\User;

class ImportEvernote extends Command
{
    const OWNER_ID = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evernote:import {notebook}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import concepts from Evernote';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notebook_name = $this->argument('notebook');

        $user = User::find(self::OWNER_ID);

        dispatch(new ImportJob($user, 'notebook_name', $notebook_name));
        $this->info("Import of {$notebook_name} for {$user->email} initiated");
    }
}
