<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

class testCommand extends Command
{
    protected $signature = 'my:command';
    protected $description = 'My custom command that does some operations';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Manually resolve SymfonyStyle
        $io = new SymfonyStyle($this->input, $this->output);

        $io->title('Executing My Command');

        $confirmation = $io->confirm('Do you want to continue?', true);

        if ($confirmation) {
            $io->success('The operation was successful!');
        } else {
            $io->warning('The operation was cancelled.');
        }
    }
}
