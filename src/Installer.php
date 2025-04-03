<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;

class Plugin implements PluginInterface, EventSubscriberInterface
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
        // Itt sem szükséges implementáció
    }

    public static function getSubscribedEvents()
    {
        return [
            'post-package-install' => 'postPackageInstall',
            'post-package-uninstall' => 'postPackageUninstall',
        ];
    }

    public function postPackageInstall(\Composer\Installer\PackageEvent $event)
    {
        Installer::postPackageInstall($event);
    }

    public function postPackageUninstall(\Composer\Installer\PackageEvent $event)
    {
        Installer::postPackageUninstall($event);
    }
}