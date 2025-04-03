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
    // ... (a meglévő metódusok maradnak) ...

    public static function postPackageInstall(PackageEvent $event)
    {
        $io = $event->getIO();
        $io->write('<info>Kezdjük a theme telepítését</info>');

        $package = $event->getOperation()->getPackage();
        if ($package->getType() === 'focus-theme') {
            $installer = new self($io, $event->getComposer());
            $themeName = $installer->getThemeName($package);

            $io->write("<info>Theme setup parancs indítása: {$themeName}</info>");
            self::executeArtisanCommand($io, "theme:setup {$themeName}");
        }
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        $io = $event->getIO();
        $io->write('<info>Kezdjük a theme eltávolítását</info>');

        $package = $event->getOperation()->getPackage();
        if ($package->getType() === 'focus-theme') {
            $installer = new self($io, $event->getComposer());
            $themeName = $installer->getThemeName($package);

            $io->write("<info>Theme remove parancs indítása: {$themeName}</info>");
            self::executeArtisanCommand($io, "theme:remove {$themeName}");
        }
    }

    protected static function executeArtisanCommand(IOInterface $io, $command)
    {
        $cwd = getcwd();
        $artisanPath = realpath($cwd.'/artisan');

        if (!$artisanPath || !file_exists($artisanPath)) {
            $io->writeError('<error>Hiba: Az artisan fájl nem található!</error>');
            $io->writeError("<error>Keresett útvonal: {$cwd}/artisan</error>");
            return false;
        }

        $process = new Process(['php', $artisanPath, ...explode(' ', $command)]);
        $process->setTimeout(300);
        $process->setWorkingDirectory($cwd);

        try {
            $io->write("<comment>Végrehajtás: php artisan {$command}</comment>");

            $process->mustRun(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });

            $io->write('<info>Parancs sikeresen lefutott</info>');
            return true;
        } catch (ProcessFailedException $e) {
            $io->writeError('<error>Hiba történt a parancs végrehajtása közben:</error>');
            $io->writeError($e->getMessage());
            return false;
        }
    }
}