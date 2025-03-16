<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

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

        // Átalakítás PSR-4 kompatibilis névre
        $themeName = str_replace('-', ' ', $packageName); // Kötőjelek helyett szóköz
        $themeName = ucwords($themeName); // Minden szó első betűje nagybetű
        $themeName = str_replace(' ', '', $themeName); // Szóközök eltávolítása

        return $themeName;
    }
}