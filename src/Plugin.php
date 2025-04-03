<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Container\Container;

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

        // Laravel parancsokat közvetlenül a Laravel konténer segítségével futtatjuk
        $this->runArtisanCommand($themeName);

        $io->write("Téma törölve: {$themeName}");
    }

    /**
     * Futtatja a Laravel Artisan parancsot.
     *
     * @param string $themeName
     */
    protected function runArtisanCommand($themeName)
    {
        // Hozzáférés a Laravel konténerhez
        $container = Container::getInstance();

        // Hívja meg a Laravel parancsot közvetlenül
        $artisan = $container->make(Artisan::class);
        $artisan->call('theme:remove', ['theme' => $themeName]);
    }

    /**
     * Kinyeri a téma nevét a csomagból.
     *
     * @param string $packageName
     * @return string
     */
    protected function getThemeName($packageName)
    {
        $packageName = str_replace('istvan/', '', $packageName);
        $themeName = str_replace('-', ' ', $packageName);
        $themeName = ucwords($themeName);
        return str_replace(' ', '', $themeName);
    }
}
