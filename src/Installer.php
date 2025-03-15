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
