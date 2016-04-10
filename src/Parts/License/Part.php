<?php

namespace Studio\Parts\License;

use Composer\Spdx\SpdxLicenses;
use Studio\Filesystem\Directory;
use Studio\Parts\AbstractPart;

class Part extends AbstractPart
{

    public function setupPackage($composer, Directory $target)
    {
        if ($this->input->confirm('Do you want to configure a license for your project?')) {
            $licenses = new SpdxLicenses();

            // 1. Choose a license from a list
            // 2. Generate the license file, with year and name
            // 3. Add it to composer.json
        }
    }

}
