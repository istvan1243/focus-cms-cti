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

    public function deactivate(Composer $composer, IOInterface $io) {}
    public function uninstall(Composer $composer, IOInterface $io) {}

    public static function getSubscribedEvents()
    {
        return [
            'post-package-install' => ['onPostPackageInstall', 0],
            'post-package-update' => ['onPostPackageUpdate', 0],
            'post-package-uninstall' => ['onPostPackageUninstall', 0],
            'post-update-cmd' => ['onPostUpdate', 0],  // Új esemény
        ];
    }

    public function onPostPackageInstall(\Composer\Installer\PackageEvent $event)
    {
        Installer::postPackageInstall($event);
    }

    public function onPostPackageUninstall(\Composer\Installer\PackageEvent $event)
    {
        Installer::postPackageUninstall($event);
    }

    public function onPostUpdate(\Composer\Script\Event $event)
    {
        $packages = $event->getComposer()->getRepositoryManager()->getLocalRepository()->getPackages();
        foreach ($packages as $package) {
            if ($package->getType() === 'focus-theme') {
                $installer = new Installer($event->getIO(), $event->getComposer());
                $themeName = $installer->getThemeName($package);
                Installer::executeArtisanCommand($event->getIO(), "theme:setup {$themeName}");
            }
        }
    }
}