<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class Plugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Nincs szükség külön implementációra
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        $package = $composer->getPackage();
        $themeName = $this->getThemeName($package->getPrettyName());

        $this->runArtisanCommand("theme:remove", $themeName, $io);
    }

    protected function getThemeName($packageName)
    {
        $packageName = str_replace('istvan/', '', $packageName);
        $themeName = str_replace('-', ' ', $packageName);
        $themeName = ucwords($themeName);
        return str_replace(' ', '', $themeName);
    }

    protected function runArtisanCommand($command, $themeName, IOInterface $io)
    {
        $process = new Process(["php", "artisan", $command, $themeName]);
        $process->setWorkingDirectory(getcwd());
        $process->run();

        if (!$process->isSuccessful()) {
            $io->writeError("<error>{$command} failed: " . $process->getErrorOutput() . "</error>");
        } else {
            $io->write("<info>{$command} executed successfully for {$themeName}!</info>");
        }
    }
}
