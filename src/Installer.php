<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class Installer extends LibraryInstaller implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $composer->getInstallationManager()->addInstaller($this);
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Nem kötelező implementálni, de a PluginInterface követelménye
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // A PluginInterface követelménye
    }

    public function uninstallPackage(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // A LibraryInstaller követelménye
        parent::uninstall($repo, $package);
    }

    public function getInstallPath(PackageInterface $package)
    {
        $themeName = $this->getThemeName($package);
        return "app/Themes/{$themeName}";
    }

    public function supports($packageType)
    {
        return $packageType === 'focus-theme';
    }

    protected function getThemeName(PackageInterface $package)
    {
        $packageName = $package->getPrettyName();
        return str_replace('istvan/', '', $packageName);
    }
}