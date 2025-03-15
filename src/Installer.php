<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class Installer extends LibraryInstaller
{
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