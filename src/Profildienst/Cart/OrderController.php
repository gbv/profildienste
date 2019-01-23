<?php /**
 * Writes an order to a JSON file
 */ /**
 * @package Profildienst\Cart
 */ namespace Profildienst\Cart; use Config\Configuration; use Exception; use Exceptions\CustomMailMessageException; use Exceptions\UserErrorException; use Profildienst\Title\Title; use Profildienst\Title\TitleRepository; use Profildienst\User\User; /**
 * Writes an order to a JSON file
 *
 * Class Order
 */ class OrderController {
    use OrderUtilities;
    private $user;
    /**
     * @var Configuration
     */
    private $config;
    /**
     * @var TitleRepository
     */
    private $titleRepository;
    private $host;
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
        $this->host = $this->config->getExportUser() . '@' . $this->config->getExportHost();
    }
    public function order(Cart $cart) {
        $titles = $cart->getTitles();
        // nothing to do if there are no titles in the cart
        if (count($titles) === 0) {
            throw new UserErrorException('Aktuell befinden sich keine Titel im Warenkorb, die bestellt werden könnten.');
        }
        // Ensure that everything works before we continue
        $this->checkPrerequisites();
        $library = $this->user->getLibrary();
        if ($library->usesAdvancedExport()) {
            $eln = $library->getELN();

            $base = $this->tempdir() . '/';
            // sort titles respective to their "reihe".
            // each reihe has its own dir where the JSON dumps of the titles are stored
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
            // check if all necessary remote directories exist
            //foreach (array_keys($reihen) as $reihe) {
                //$rdir = $this->config->getExportBasedir() . $eln . $reihe . '/return/';
                //$this->checkRemoteDirectoryExists($rdir);
            //}
            //upload to the JSON dumps to the server
            foreach ($reihen as $reihe => $dir) {
                //$rdir = $this->config->getExportBasedir() . $eln . $reihe . '/return/';
		// ExportBaseDir nun in rrsync script
		$rdir = $eln . $reihe . '/return/';
                $remoteLocation = $this->host . ':' . $rdir;
                //exec('rsync -azPi ' . $dir . ' ' . $remoteLocation . ' 2>&1', $output, $ret);

		$exec_str = 'rsync -e "ssh -i $HOME/.ssh/id_rsa_profildienst_rrsync" -av '.$dir.' '.$remoteLocation;
		exec($exec_str, $output, $ret);

                if ($ret !== 0) {
                    throw new CustomMailMessageException('Bei der Datenübertragung ist ein Fehler aufgetreten.',
                    $this->formatOutputError('Bestellung fehlgeschlagen (ELN: ' . $eln . ')', $output));
                }
            }
        } else {

            $eln = $library->getELN();
            $dir = $this->tempdir() . '/';
            foreach ($titles as $title) {
                $ppn = $title->getPPN();
                $this->checkAndSetTitlesOrderInformation($title);
                $output = $this->outputTitle($title);
                file_put_contents($dir . $ppn . '.json', json_encode($output, JSON_PRETTY_PRINT));
            }
            //$rdir = $this->config->getExportBasedir() . $library->getExportDir() . '/return/';
            // ExportBaseDir nun in rrsync script
            $rdir = $library->getExportDir() . '/return/';
            // check if the remote directories exist
            //$this->checkRemoteDirectoryExists($rdir);
            // upload JSON dumps
            $remoteLocation = $this->host . ':' . $rdir;
            //exec('rsync -azPi ' . $dir . ' ' . $remoteLocation . ' 2>&1', $output, $ret);

            $exec_str = 'rsync -e "ssh -i $HOME/.ssh/id_rsa_profildienst_rrsync" -av '.$dir.' '.$remoteLocation;
            exec($exec_str, $output, $ret);

            if ($ret !== 0) {
                throw new CustomMailMessageException('Bei der Datenübertragung ist ein Fehler aufgetreten.',
                $this->formatOutputError('Bestellung fehlgeschlagen (ELN: ' . $eln . ')', $output));
            }
        }
        // update the status of the ordered titles
        return $this->titleRepository->changeStatusOfView('cart', 'pending');
    }
    private function checkAndSetTitlesOrderInformation(Title $title) {
        $defaults = $this->user->getDefaults();
        $toUpdate = [];
        if (empty($title->getBudget())) {
            $toUpdate['budget'] = $defaults['budget'];
        }
        if (empty($title->getSupplier())) {
            $toUpdate['supplier'] = $defaults['supplier'];
        }
        if (empty($title->getSSGNr())) {
            $toUpdate['ssgnr'] = $defaults['ssgnr'];
        }
        if (empty($title->getSelcode())) {
            $toUpdate['selcode'] = $defaults['selcode'];
        }
        if (count($toUpdate) > 0) {
            if (!$this->titleRepository->changeOrderInformationOfTitles([$title->getId()], $toUpdate)) {
                throw new UserErrorException('Failed to update the order information of title ' . $title->getId());
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
