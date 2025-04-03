<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Symfony\Component\Process\Process;

class Installer extends LibraryInstaller
{
    public function getInstallPath(PackageInterface $package)
    {
        $themeName = $this->getThemeName($package);
        return "Themes/{$themeName}";
    }

    public function supports($packageType)
    {
        return $packageType === 'focus-theme';
    }

    protected function getThemeName(PackageInterface $package)
    {
        $packageName = $package->getPrettyName();
        $packageName = str_replace('istvan/', '', $packageName);

        // Kötőjelből camel case (PSR-4 név konverzió)
        $themeName = str_replace('-', ' ', $packageName);
        $themeName = ucwords($themeName);
        $themeName = str_replace(' ', '', $themeName);

        return $themeName;
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);

        $themeName = $this->getThemeName($package);
        echo "<info>Running theme setup for: {$themeName}</info>\n";

        // Artisan parancs futtatása (theme:setup)
        $this->runArtisanCommand("theme:setup", $themeName);
    }

    protected function runArtisanCommand($command, $themeName)
    {
        $process = new Process(["php", "artisan", $command, $themeName]);
        $process->setWorkingDirectory(getcwd());
        $process->run();

        if (!$process->isSuccessful()) {
            echo "<error>{$command} failed: {$process->getErrorOutput()}</error>\n";
        } else {
            echo "<info>{$command} executed successfully!</info>\n";
        }
    }
}
