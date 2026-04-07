<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeNewThemeCommand extends \Hexadog\ThemesManager\Console\Generators\MakeTheme
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'template:make {name} {--T|type=} {--D|description=} {--P|parent=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make new template';

    protected function askVendor()
    {
        $vendor = strtolower($this->option('type'));

        do {
            $this->theme['vendor'] = $vendor;
        } while (!strlen($this->theme['vendor']));
    }

    protected function askName()
    {
        $name = strtolower($this->argument('name'));

        do {
            $this->theme['name'] = $name;
        } while (!strlen($this->theme['name']));
    }

    protected function askDescription()
    {
        $description = $this->option('description');
        $this->theme['description'] = $description ? $description : "CBMS theme";
    }

    protected function askParent()
    {
        $parent = $this->option('parent');
        if ($parent) {
            $this->theme['parent'] = $parent;
            $this->theme['parent'] = mb_strtolower($this->theme['parent']);
        }
    }

    protected function askVersion()
    {
        $this->theme['version'] = "1.0";

        if (!strlen($this->theme['version'])) {
            $this->theme['version'] = null;
        }
    }
}
