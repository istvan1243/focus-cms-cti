<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Script\Event;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private static $themeSetupRun = false;

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
            'post-update-cmd' => ['onPostUpdate', 0],
            //'post-autoload-dump' => ['onPostAutoloadDump', 0],
        ];
    }

    public function onPostPackageInstall(PackageEvent $event)
    {
        Installer::postPackageInstall($event);
    }

    public function onPostPackageUpdate(PackageEvent $event)
    {
        Installer::postPackageUpdate($event);
    }

    public function onPostPackageUninstall(PackageEvent $event)
    {
        Installer::postPackageUninstall($event);
    }

    public function onPostUpdate(Event $event)
    {
        if (!file_exists(getcwd() . '/vendor/autoload.php')) {
            return;
        }
        $this->runThemeSetupForAllThemes($event->getIO(), $event->getComposer());
    }

    public function onPostAutoloadDump(Event $event)
    {
        if (self::$themeSetupRun || !file_exists(getcwd() . '/vendor/autoload.php')) {
            return;
        }
        $this->runThemeSetupForAllThemes($event->getIO(), $event->getComposer());
    }

    private function runThemeSetupForAllThemes(IOInterface $io, Composer $composer)
    {
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
        foreach ($packages as $package) {
            if ($package->getType() === 'focus-theme') {
                $themeName = Installer::getThemeNameForPackage($package);
                Installer::executeArtisanCommand($io, "theme:setup {$themeName}");
            }
        }
        self::$themeSetupRun = true;
    }
}