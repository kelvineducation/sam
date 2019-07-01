<?php

namespace App\Pages;

use App\Models\Package;
use App\Models\Version;
use App\Routers\MethodNotAllowedException;
use App\Routers\NotFoundException;
use Phar;
use PharData;
use PharFileInfo;
use RecursiveIteratorIterator;
use function The\db;

class PackagesVersionsPage extends FwPage
{
    /**
     * @throws NotFoundException
     */
    protected function index()
    {
        throw new NotFoundException();
    }

    /**
     * @throws NotFoundException
     */
    protected function new()
    {
        throw new NotFoundException();
    }

    protected function create(Package $package)
    {
        db()->beginTransaction('create-version');

        if ($package->findVersion($this->getParam('name'))) {
            $this->json([
                'error' => 'A version by that name already exists',
                'code'  => 409,
            ], 409);
            return;
        }

        $version = Version::create([
            'name'       => $this->getParam('name'),
            'package_id' => $package->getId(),
        ]);

        if ($this->getParam('archive_base64')) {
            $archive_path = tempnam('/tmp', 'deliverer_') . '.temp';
            file_put_contents($archive_path, base64_decode($this->getParam('archive_base64')));

            if ($this->hasInvalidHash($archive_path, $this->getParam('archive_hash'))) {
                $this->json([
                    'error' => 'The provided archive hash does not match '
                        . 'the sha256 hash of the provided base64 archive',
                    'code'  => 422,
                ], 422);
                return;
            }

            $archive = new PharData($archive_path, Phar::CURRENT_AS_FILEINFO);

            $base_path = "phar://{$archive->getPath()}/";
            $base_length = strlen($base_path);
            foreach (new RecursiveIteratorIterator($archive) as $file_info) {
                /** @var $file_info PharFileInfo */
                $asset_path = substr($file_info->getPathname(), $base_length);
                $content = $file_info->getContent();

                $version->addAsset($asset_path, $content);
            }
            unset($archive);

            unlink($archive_path);
        }

        db()->acceptTransaction('create-version');

        $this->json([
            'version_id' => $version->getId(),
            'name'       => $version->getData('name'),
            'package_id' => $version->getData('version_id'),
        ], 201);
    }

    protected function show(Package $package, Version $version)
    {
        $this->json([
            'version_id' => $version->getId(),
            'name'       => $version->getData('name'),
            'package_id' => $version->getData('version_id'),
        ]);
    }

    /**
     * @throws NotFoundException
     */
    protected function edit()
    {
        throw new NotFoundException();
    }

    /**
     * @throws MethodNotAllowedException
     */
    protected function update()
    {
        throw new MethodNotAllowedException();
    }

    /**
     * @throws MethodNotAllowedException
     */
    protected function destroy()
    {
        throw new MethodNotAllowedException();
    }

    private function hasInvalidHash($path, $hash)
    {
        if ($hash === null) {
            return false;
        }

        $calculated_hash = hash('sha256', file_get_contents($path));

        return $hash !== $calculated_hash;
    }
}
