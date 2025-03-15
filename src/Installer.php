<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

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
        // Nem kötelező implementálni, de a PluginInterface követelménye
    }

    public function supports($packageType)
    {
        return $packageType === 'focus-theme';
    }

    public function getInstallPath(PackageInterface $package)
    {
        $themeName = $this->getThemeName($package);
        return "app/Themes/{$themeName}";
    }

    protected function getThemeName(PackageInterface $package)
    {
        $packageName = $package->getPrettyName();
        return str_replace('istvan/', '', $packageName);
    }
}