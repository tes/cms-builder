<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 29/03/2017
 * Time: 12:37
 */

namespace tes\CmsBuilder;

use Humbug\SelfUpdate\Strategy\ShaStrategy;
use Humbug\SelfUpdate\Updater;

class RemoteSha1Strategy extends ShaStrategy
{
    /**
     * Retrieve the current version available remotely.
     *
     * @param Updater $updater
     * @return string|bool
     */
    public function getCurrentRemoteVersion(Updater $updater)
    {
        return hash_file('sha1', $this->getPharUrl());
    }

}
