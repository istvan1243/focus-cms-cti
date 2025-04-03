<?php

namespace Istvan\ComposerFocusThemeInstaller;

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

        // Kötőjelből CamelCase alakítás
        $themeName = str_replace('-', ' ', $packageName);
        $themeName = ucwords($themeName);
        $themeName = str_replace(' ', '', $themeName);

        return $themeName;
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);

        $themeName = $this->getThemeName($package);
        echo "Running theme setup for: {$themeName}\n";

        // Artisan parancs futtatása telepítéskor
        $this->runArtisanCommand("theme:setup", $themeName);
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::uninstall($repo, $package);

        $themeName = $this->getThemeName($package);
        echo "Removing theme: {$themeName}\n";

        // Artisan parancs futtatása eltávolításkor
        $this->runArtisanCommand("theme:remove", $themeName);
    }

    protected function runArtisanCommand($command, $themeName)
    {
        $process = new Process(["php", "artisan", $command, $themeName]);
        $process->setWorkingDirectory(getcwd());
        $process->run();

        if (!$process->isSuccessful()) {
            echo "{$command} failed: " . $process->getErrorOutput() . "\n";
        } else {
            echo "{$command} executed successfully!\n";
        }
    }
}
