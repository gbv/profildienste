<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 15.11.16
 * Time: 18:18
 */

namespace Profildienst\Cart;

use Exception;
use Exceptions\UserErrorException;

trait OrderUtilities {

    private function checkPrerequisites() {

        // check if rsync is available
        exec('rsync --version', $output, $returnCode);
        if ($returnCode !== 0) {
            throw new Exception($this->formatOutputError(
                'Order process: rsync is not available on the system!', $output));
        }
        unset($output);

        // check if ssh is available
//        exec('ssh -V', $output, $returnCode);
//        if ($returnCode !== 0) {
//            throw new Exception($this->formatOutputError(
//                'Order process: ssh is not available on the system!', $output));
//        }
//        unset($output);

        // check if the remote system can be accessed
//        exec('ssh ' . $this->host . ' whoami', $output, $returnCode);
//        if ($returnCode !== 0 || $output[0] !== $this->config->getExportUser()) {
//            throw new Exception($this->formatOutputError(
//                'Login not possible with ' . $this->host, $output));
//        }
    }

    private function formatOutputError(string $message, array $output) {
        return sprintf("%s\nOutput: %s", $message, join("\n", $output));
    }

    private function checkRemoteDirectoryExists($remoteDir) {
        exec('ssh ' . $this->host . ' test -d ' . $remoteDir, $output, $returnCode);
        if ($returnCode !== 0) {
            throw new Exception('Remote directory not found: ' . $remoteDir);
        }
    }

    /**
     * Creates a temporary directory, either in $dir (if specified)
     * or in the default system temp dir.
     *
     * @param bool|false|string $dir Directory to create the temp dir in
     * @return string Path of the created temp dir
     * @throws UserErrorException
     */
    private function tempdir($dir = false) {
        if ($dir !== false) {
            $tempfile = tempnam($dir, 'pd_');
        } else {
            $tempfile = tempnam(sys_get_temp_dir(), 'pd_');
        }
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        if (is_dir($tempfile)) {
            return $tempfile;
        }

        throw new UserErrorException('Failed to create a temporary dir');
    }
}
