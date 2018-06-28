<?php
namespace Weissheiten\Neos\MediaTools\Command;

/*
 * This file is part of the Weissheiten.Neos.MediaTools package.
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * Controller for checking calculated SHA1 of images against the one stored in the database
 *
 * @Flow\Scope("singleton")
 */
class SHA1OperationsCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var \Neos\Media\Domain\Repository\AssetRepository
     */
    protected $assetRepository;

    /**
     * Check the SHA1 value of all
     *
     * @return void
     */
    public function checkValuesCommand()
    {
        $this->outputLine("Comparing calculated SHA1 of files with SHA1 stored in the database...");

        $correct = [];
        $incorrect = [];

        $assets = $this->assetRepository->findAll()->toArray();
        /* @var $asset \Neos\Media\Domain\Model\Asset */
        foreach ($assets as $asset) {
            $fileinfo['sha1_db'] = $asset->getResource()->getSha1();
            try {
                $fileinfo['sha1_file'] = sha1_file($asset->getResource()->createTemporaryLocalCopy());
            } catch (\Neos\Flow\ResourceManagement\Exception $e) {
                $fileinfo['sha1_file'] = "Eror checking sha1 of file";
            }
            $fileinfo['name'] = $asset->getResource()->getFilename();

            if ($fileinfo['sha1_db']===$fileinfo['sha1_file']) {
                $correct[] = $fileinfo;
            } else {
                $incorrect[] = $fileinfo;
            }
        }

        $text_correct = <<<OUT
%s files with matching SHA1".
OUT;


        $text_incorrect = <<<OUT
%s files with non-matching SHA1".
OUT;

        $this->outputLine($text_incorrect, array(count($incorrect)));

        foreach ($incorrect as $item) {
            $text_item = <<<OUT
SHA1 Database: %s | SHA1 File: %s | Filename: %s".
OUT;

            $this->outputLine($text_item, array($item['sha1_db'],$item['sha1_file'], $item['name']));
        }
    }
}