<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Illuminate\Support\Facades\Artisan;

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
        // Csomag neve alapján törli a téma beállításait
        $package = $composer->getPackage();
        $themeName = $this->getThemeName($package->getPrettyName());

        // Laravel Artisan parancs meghívása
        Artisan::call('theme:remove', ['theme' => $themeName]);

        $io->write("Téma törölve: {$themeName}");
    }

    protected function getThemeName($packageName)
    {
        $packageName = str_replace('istvan/', '', $packageName);
        $themeName = str_replace('-', ' ', $packageName);
        $themeName = ucwords($themeName);
        return str_replace(' ', '', $themeName);
    }
}
