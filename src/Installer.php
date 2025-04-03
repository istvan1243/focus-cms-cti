<?php

namespace Istvan\ComposerFocusThemeInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Installer\PackageEvent;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
        $themeName = str_replace('-', ' ', $packageName);
        $themeName = ucwords($themeName);
        $themeName = str_replace(' ', '', $themeName);

        return $themeName;
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        if ($package->getType() === 'focus-theme') {
            $installer = new self($event->getIO(), $event->getComposer());
            $themeName = $installer->getThemeName($package);
            $io = $event->getIO();

            $io->write("<info>Theme telepítés indítása: {$themeName}</info>");
            self::executeArtisanCommand($io, "theme:setup {$themeName}");
        }
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        if ($package->getType() === 'focus-theme') {
            $installer = new self($event->getIO(), $event->getComposer());
            $themeName = $installer->getThemeName($package);
            $io = $event->getIO();

            $io->write("<info>Theme eltávolítás indítása: {$themeName}</info>");
            self::executeArtisanCommand($io, "theme:remove {$themeName}");
        }
    }

    protected static function executeArtisanCommand(IOInterface $io, $command)
    {
        $cwd = getcwd();
        $artisanPath = $cwd . '/artisan';

        if (!file_exists($artisanPath)) {
            $io->writeError("<error>Hiba: Artisan fájl nem található a következő útvonalon: {$artisanPath}</error>");
            return false;
        }

        $fullCommand = ['php', $artisanPath, ...explode(' ', $command)];

        $process = new Process($fullCommand, $cwd);
        $process->setTimeout(300); // 5 perc timeout
        $process->setIdleTimeout(60); // 1 perc idle timeout

        try {
            $io->write("<comment>Végrehajtás: php artisan {$command}</comment>");

            $process->mustRun(function ($type, $buffer) use ($io) {
                if (Process::ERR === $type) {
                    $io->writeError("<error>{$buffer}</error>");
                } else {
                    $io->write($buffer);
                }
            });

            $io->write("<info>Parancs sikeresen lefutott</info>");
            return true;
        } catch (ProcessFailedException $e) {
            $io->writeError("<error>Hiba a parancs végrehajtásakor: {$e->getMessage()}</error>");
            $io->writeError("<error>Kimenet: " . $process->getOutput() . "</error>");
            $io->writeError("<error>Hiba kimenet: " . $process->getErrorOutput() . "</error>");
            return false;
        }
    }
}