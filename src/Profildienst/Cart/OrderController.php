<?php
/**
 * Writes an order to a JSON file
 */

/**
 * @package Profildienst\Cart
 */
namespace Profildienst\Cart;

use Config\Configuration;
use Exceptions\UserException;
use Profildienst\Title\Title;
use Profildienst\Title\TitleRepository;
use Profildienst\User\User;

/**
 * Writes an order to a JSON file
 *
 * Class Order
 */
class OrderController {

    private $user;

    /**
     * @var Configuration
     */
    private $config;
    /**
     * @var TitleRepository
     */
    private $titleRepository;

    /**
     * Writes the cart to a JSON file and resets the cart.
     * @param User $user
     * @param Configuration $config
     * @param TitleRepository $titleRepository
     */
    public function __construct(User $user, Configuration $config, TitleRepository $titleRepository) {
        $this->user = $user;
        $this->config = $config;
        $this->titleRepository = $titleRepository;
    }

    public function order(Cart $cart) {

        $titles = $cart->getTitles();

        // nothing to do if there are no titles in the cart
        if (count($titles) === 0) {
            throw new UserException('Aktuell befinden sich keine Titel im Warenkorb, die bestellt werden könnten.');
        }

        $library = $this->user->getLibrary();

        if ($library->usesAdvancedExport()) {

            $iln = $library->getILN();

            $base = $this->tempdir() . '/';

            $reihen = [];

            foreach ($titles as $title) {

                $reihe = $title->getReihe();

                if (!isset($reihen[$reihe])) {
                    $reihen[$reihe] = $this->tempdir($base) . '/';
                }
                $dir = $reihen[$reihe];
                $ppn = $title->getPPN();

                $this->checkAndSetTitlesOrderInformation($title);

                $output = $this->outputTitle($title);
                file_put_contents($dir . $ppn . '.json', json_encode($output, JSON_PRETTY_PRINT));
            }

            //upload
            foreach ($reihen as $reihe => $dir) {

                $rdir = $this->config->getExportBasedir() . $iln . $reihe . '/return/';
                $host = $this->config->getExportUser() . '@' . $this->config->getExportHost() . ':' . $rdir;

                exec('rsync -azPi ' . $dir . ' ' . $host . ' 2>&1', $output, $ret);

                if ($ret != 0) {
                    throw new UserException('Bei der Datenübertragung ist ein Fehler aufgetreten.');
                }

            }

        } else {

            $dir = $this->tempdir() . '/';

            foreach ($titles as $title) {

                $ppn = $title->getPPN();

                $this->checkAndSetTitlesOrderInformation($title);

                $output = $this->outputTitle($title);

                file_put_contents($dir . $ppn . '.json', json_encode($output, JSON_PRETTY_PRINT));
            }

            // upload
            $rdir = $this->config->getExportBasedir() . $library->getExportDir() . '/return/';
            $host = $this->config->getExportUser() . '@' . $this->config->getExportHost() . ':' . $rdir;

            exec('rsync -azPi ' . $dir . ' ' . $host . ' 2>&1', $output, $ret);

            if ($ret != 0) {
                throw new UserException('Bei der Datenübertragung ist ein Fehler aufgetreten.');
            }
        }

        // update the status of the ordered titles
        return $this->titleRepository->changeStatusOfView('cart', 'pending');
    }

    /**
     * Creates a temporary directory, either in $dir (if specified)
     * or in the default system temp dir.
     *
     * @param bool|false|string $dir Directory to create the temp dir in
     * @return string Path of the created temp dir
     * @throws UserException
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

        throw new UserException('Failed to create a temporary dir');
    }

    private function checkAndSetTitlesOrderInformation(Title $title) {

        $defaults = $this->user->getDefaults();

        $toUpdate = [];

        // check if budget is not empty
        if (empty($title->getBudget())) {
            throw new UserException('Budget is not allowed to be empty');
        }
        // check if supplier is not empty
        if (empty($title->getSupplier())) {
            throw new UserException('Supplier is not allowed to be empty');
        }

        $selcode = $title->getSelcode();
        if (empty($selcode)) {
            $selcode = $defaults['selcode'];
            $toUpdate['selcode'] = $selcode;
        }

        $ssgnr = $title->getSSGNr();
        if (empty($ssgnr)) {
            $ssgnr = $defaults['ssgnr'];
            $toUpdate['ssgnr'] = $ssgnr;
        }

        if (count($toUpdate) > 0) {
            if (!$this->titleRepository->changeOrderInformationOfTitles([$title->getId()], $toUpdate)) {
                throw new UserException('Failed to update the order information of title ' . $title->getId());
            }
        }
    }

    private function outputTitle(Title $title) {
        return [
            'ppn' => $title->getPPN(),
            'budget' => $title->getBudget(),
            'lieft' => $title->getSupplier(),
            'selcode' => $title->getSelcode(),
            'ssgnr' => $title->getSSGNr(),
            'comment' => is_null($title->getComment()) ? '' : $title->getComment(),
            'user' => $this->user->getId()
        ];
    }

}
